// Shared lounge scanner, used by the staff dashboard (/lounge) and the kiosk
// screen (/lounge/display). Both pages get exactly the same behavior:
//   - QR scanner (Clabel T27H): types the QR text + Enter into a hidden input
//   - RFID reader (ESP32 over USB, Chrome Web Serial): prints "Card: 43 A8 7D 1A"
//   - debounce (same code within 5s = one scan) and a busy lock (one scan at a time)
//   - POST /api/lounge/scan, where the backend makes the whole time in/out decision
// Each page only decides how to SHOW the result (onResult).
//
//   const scanner = createLoungeScanner({ getToken, onResult, onError, onStatus });
//   scanner.attachInput(inputElement);  // QR scanner
//   scanner.autoConnectRfid();          // RFID reader (if this browser was allowed before)
//   scanner.state                       // reactive: rfidConnected, scannerFocused, busy
//   scanner.destroy();                  // on unmount

import axios from "axios";
import { reactive } from "vue";

const SAME_CODE_MS = 5000; // same card/QR again within 5 seconds = the same scan
const FOCUS_CHECK_MS = 500;

export function createLoungeScanner({
    getToken, // () => bearer token (staff "auth-token" or the kiosk token)
    onResult, // (data) => void      — the /lounge/scan answer
    onError, //  (message, error) => void
    onStatus = () => {}, // (type, text) => void — reader connected / disconnected, etc.
}) {
    const state = reactive({
        rfidSupported: typeof navigator !== "undefined" && "serial" in navigator,
        rfidConnected: false,
        scannerFocused: false,
        busy: false,
    });

    // Kept outside `state`: Vue's reactivity wrapper breaks the browser's SerialPort
    let port = null;
    let reader = null;
    let readLoop = null;
    let keepReading = false;
    let onSerialConnect = null;

    let input = null;
    let focusTimer = null;
    let onFocusOut = null;
    const inputHandlers = {};

    let lastCode = null;
    let lastAt = 0;

    // ── The one scan path (both sources) ──
    async function submit(raw) {
        const code = String(raw || "").trim();
        if (!code) return;

        const now = Date.now();
        if (code === lastCode && now - lastAt < SAME_CODE_MS) return;
        lastCode = code;
        lastAt = now;

        if (state.busy) return;
        state.busy = true;
        try {
            const res = await axios.post(
                "/api/lounge/scan",
                { code },
                { headers: { Authorization: `Bearer ${getToken()}` } },
            );
            onResult(res.data);
        } catch (e) {
            // A 422 for an unreadable code still carries a normal answer
            if (e.response?.data?.result) {
                onResult(e.response.data);
            } else {
                onError(
                    e.response?.data?.message ||
                        "Scan failed. Check the internet connection.",
                    e,
                );
            }
        } finally {
            state.busy = false;
        }
    }

    // Lets the same code be scanned again right away (e.g. after closing a window)
    function forgetLastCode() {
        lastCode = null;
    }

    // ── QR scanner: hidden input that keeps its focus ──
    function attachInput(el) {
        input = el;
        inputHandlers.keydown = (e) => {
            if (e.key === "Enter" || e.key === "Tab") {
                e.preventDefault();
                const code = el.value;
                el.value = "";
                submit(code);
            }
        };
        inputHandlers.focus = () => (state.scannerFocused = true);
        inputHandlers.blur = () => (state.scannerFocused = false);
        for (const [ev, fn] of Object.entries(inputHandlers)) el.addEventListener(ev, fn);

        focusTimer = setInterval(keepFocus, FOCUS_CHECK_MS);
        onFocusOut = () => setTimeout(keepFocus, 0);
        document.addEventListener("focusout", onFocusOut);
        keepFocus();
    }

    // Refocus the hidden input, EXCEPT while someone is typing in a text box.
    // Buttons don't count: if a clicked button kept focus, the scanner's Enter
    // would press it again.
    function keepFocus() {
        if (!input || document.hidden) return;
        const el = document.activeElement;
        if (el === input) return;
        const typing =
            el &&
            (el.isContentEditable ||
                el.tagName === "TEXTAREA" ||
                el.tagName === "SELECT" ||
                (el.tagName === "INPUT" &&
                    !["button", "submit", "checkbox", "radio", "range", "reset"].includes(
                        (el.type || "").toLowerCase(),
                    )));
        if (!typing) input.focus({ preventScroll: true });
    }

    // ── RFID reader (Chrome/Edge Web Serial) ──
    // First time: connectRfid() shows Chrome's picker (choose the ESP32, COM5).
    // After that this site remembers it and autoConnectRfid() reconnects by itself.
    async function autoConnectRfid() {
        if (!state.rfidSupported) return;
        onSerialConnect = (e) => {
            if (!state.rfidConnected) openPort(e.target).catch(() => {});
        };
        navigator.serial.addEventListener("connect", onSerialConnect);
        try {
            const ports = await navigator.serial.getPorts();
            if (ports.length > 0) await openPort(ports[0]);
        } catch (e) {
            console.error("RFID auto-connect failed", e);
        }
    }

    async function connectRfid() {
        if (!state.rfidSupported) {
            onStatus("error", "This browser can't read USB devices. Use Chrome or Edge.");
            return;
        }
        try {
            const p = await navigator.serial.requestPort();
            await openPort(p);
        } catch (e) {
            if (e.name !== "NotFoundError") console.error(e); // NotFoundError = picker closed
        }
    }

    async function openPort(p) {
        if (state.rfidConnected) return;
        try {
            await p.open({ baudRate: 115200 });
        } catch (e) {
            // Only one tab/program can use the reader at a time
            onStatus(
                "warn",
                "The RFID reader is in use by another screen or program (kiosk, PowerShell, Arduino IDE).",
            );
            throw e;
        }
        await p.setSignals({ dataTerminalReady: true, requestToSend: false });
        port = p;
        state.rfidConnected = true;
        keepReading = true;
        onStatus("info", "RFID reader connected. Tap a card.");
        readLoop = readCards(p); // runs in the background
    }

    // Reads the board's text line by line and picks out "Card: 43 A8 7D 1A"
    async function readCards(p) {
        const decoder = new TextDecoder();
        let buffer = "";

        while (p.readable && keepReading) {
            reader = p.readable.getReader();
            try {
                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;
                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split(/\r?\n/);
                    buffer = lines.pop(); // keep the unfinished line
                    for (const line of lines) {
                        const m = line.match(/Card:\s*((?:[0-9A-F]{2}\s*){4,10})/i);
                        if (m) submit(m[1].replace(/\s+/g, "").toUpperCase());
                    }
                }
            } catch (e) {
                console.error("RFID read error", e); // e.g. cable pulled
            } finally {
                reader.releaseLock();
                reader = null;
            }
        }

        try {
            await p.close();
        } catch (e) {
            // already closed
        }
        const unexpected = keepReading;
        port = null;
        state.rfidConnected = false;
        keepReading = false;
        if (unexpected) {
            onStatus(
                "error",
                "RFID reader disconnected. Plug it back in (it reconnects by itself) or click Connect RFID.",
            );
        }
    }

    async function disconnectRfid() {
        keepReading = false;
        try {
            if (reader) await reader.cancel();
        } catch (e) {
            console.error(e);
        }
        if (readLoop) await readLoop; // the loop closes the port
    }

    function destroy() {
        clearInterval(focusTimer);
        if (onFocusOut) document.removeEventListener("focusout", onFocusOut);
        if (input) {
            for (const [ev, fn] of Object.entries(inputHandlers)) input.removeEventListener(ev, fn);
        }
        if (onSerialConnect && state.rfidSupported) {
            navigator.serial.removeEventListener("connect", onSerialConnect);
        }
        disconnectRfid();
    }

    return {
        state,
        submit,
        forgetLastCode,
        attachInput,
        keepFocus,
        autoConnectRfid,
        connectRfid,
        disconnectRfid,
        destroy,
    };
}
