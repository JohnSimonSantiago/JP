// Feature switches for the web app. Change a value here, then run `npm run build`.

// Receipt printing (the 80mm check-in slip on /lounge).
//   false = never print, no print dialog. Receipt (CN) numbers are still reserved
//           and saved, so turning this back on just works.
//   true  = print a slip automatically after every check-in (button, card tap or QR scan).
export const RECEIPTS_ENABLED = false;
