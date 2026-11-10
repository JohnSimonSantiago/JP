// Display helpers for the app wallet, shown to users as "Coins" (1 Coin = ₱1).
// Display only: the database column and API key are still `cash`.

// 150 -> "150", 1500 -> "1,500", 83.5 -> "83.50"
export function formatCoins(value) {
    const n = Number(value) || 0;
    const whole = Number.isInteger(Math.round(n * 100) / 100);
    return n.toLocaleString("en-PH", {
        minimumFractionDigits: whole ? 0 : 2,
        maximumFractionDigits: whole ? 0 : 2,
    });
}

// "1 Coin", "150 Coins"
export function coinsLabel(value) {
    return Number(value) === 1 ? "Coin" : "Coins";
}
