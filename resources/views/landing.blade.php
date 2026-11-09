@php
  /*
  |--------------------------------------------------------------------------
  | LANDING PAGE CONTENT — edit the text here, the page below updates itself.
  |--------------------------------------------------------------------------
  */

  $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.johnsimonsantiago.LevelUpApp';
  $webLoginUrl  = '/dashboard'; // opens the web app; not logged in = login screen

  // "What We Offer" cards
  $offers = [
    ['icon' => '🎮', 'title' => 'PS4 Consoles',            'text' => 'Squad up and play with friends.'],
    ['icon' => '❄️', 'title' => 'Fully Air-Conditioned',   'text' => 'Stay cool while you grind.'],
    ['icon' => '📶', 'title' => 'Free WiFi',               'text' => 'Fast connection for gaming or studying.'],
    ['icon' => '🔌', 'title' => 'Charging Stations',       'text' => 'Never run out of battery.'],
    ['icon' => '👥', 'title' => 'Exclusive Room Booking',  'text' => 'Book a whole room for your group of 16, no extra fee.'],
    ['icon' => '🏆', 'title' => 'Earn Rewards',            'text' => 'Every visit earns points, loyalty stamps and a spot on the leaderboard.'],
  ];

  $signupUrl = '/signup'; // "Register Free" button

  // "Walk-in or Member?" comparison
  $compareTitle = 'Walk-in or Member? 🤔';
  $compareSub   = 'The main difference: your time carries over — and registration is FREE for a limited time only!';

  // 'best' => true = the highlighted card (glow, "Best Value" badge, raised).
  // 'banner' shows a pulsing strip on top of the card; 'cta' adds a button. Use null to hide either.
  $plans = [
    [
      'icon'   => '🚶',
      'name'   => 'Walk-in',
      'best'   => false,
      'banner' => null,
      'prices' => [
        ['amount' => '₱50',  'per' => '/ hour'],
        ['amount' => '₱120', 'per' => '/ 3 hours'],
      ],
      'points' => [
        ['icon' => '⏳', 'text' => "Once your paid time is up, it's gone, even if you didn't use all of it."],
      ],
      'cta'    => null,
    ],
    [
      'icon'   => '⚡',
      'name'   => 'Member',
      'best'   => true,
      'banner' => '🔥 FREE REGISTRATION — Limited Time Only!',
      'prices' => [
        ['amount' => '₱40',  'per' => '/ hour'],
        ['amount' => '₱100', 'per' => '/ 3 hours'],
      ],
      'points' => [
        ['icon' => '✅', 'text' => 'Leftover time stays in your account, so you can use it on your next visit. Nothing goes to waste!'],
        ['icon' => '✅', 'text' => 'Better rates than walk-in'],
        ['icon' => '✅', 'text' => 'App perks: points, loyalty stamps, leaderboard and rewards'],
        ['icon' => '✅', 'text' => 'Registration is free for a limited time only'],
      ],
      // iPhone/computer -> web sign-up. Android phones -> Play Store (they register in the app).
      'cta'    => [
        'label'         => 'Register Free',
        'url'           => $signupUrl,
        'android_label' => 'Register Free in the App',
        'android_url'   => $playStoreUrl,
      ],
    ],
  ];
  $ratesNote = '5-minute grace period — 34 mins is billed as 30 mins, 35 mins as 1 hour';

  // "House Rules" (shown numbered, in this order)
  $rules = [
    ['icon' => '🕐', 'text' => 'Weekdays are first come, first served — no reservations.'],
    ['icon' => '📅', 'text' => 'Reservations are only for Saturday and Sunday. Your spot is held for 15 minutes, then it goes to walk-ins.'],
    ['icon' => '👥', 'text' => 'Exclusive Booking: one room for a minimum of 16 people, requested at least 2 days ahead with the date and time.'],
    ['icon' => '📲', 'text' => 'Scan your QR at the kiosk to time in and time out.'],
    ['icon' => '🎮', 'text' => 'Handle controllers and equipment with care.'],
    ['icon' => '🤝', 'text' => 'Respect other players — keep it fun for everyone.'],
  ];

  // Footer location
  $location = [
    'name'    => 'Level Lounge',
    'address' => 'Brgy 5, Ablan Avenue, Laoag City',
  ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leveling Lounge</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --indigo-deep: #0f0e2a;
      --indigo-mid: #1a1750;
      --indigo-bright: #3b35a8;
      --purple: #6C63FF;
      --purple-light: #9d97ff;
      --gold: #f4c542;
      --white: #ffffff;
      --text-muted: #9b96cc;
      --android: #3DDC84;
      --silver: #e4e6ee;
    }

    html { scroll-behavior: smooth; }

    body {
      background-color: var(--indigo-deep);
      color: var(--white);
      font-family: 'DM Sans', sans-serif;
      overflow-x: hidden;
    }

    /* --- NOISE OVERLAY --- */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
      opacity: 0.4;
    }

    /* --- NAV --- */
    nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.2rem 2.5rem;
      background: rgba(15, 14, 42, 0.8);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(108, 99, 255, 0.15);
    }

    .nav-logo {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.4rem;
      color: var(--white);
      letter-spacing: -0.02em;
    }
    .nav-logo span { color: var(--purple); }

    .nav-cta {
      background: var(--purple);
      color: var(--white);
      border: none;
      padding: 0.55rem 1.4rem;
      border-radius: 999px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s, transform 0.15s;
    }
    .nav-cta:hover { background: var(--purple-light); transform: translateY(-1px); }

    /* --- HERO --- */
    .hero {
      position: relative;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 8rem 1.5rem 5rem;
      overflow: hidden;
      z-index: 1;
    }

    /* Glow blobs */
    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(100px);
      opacity: 0.18;
      pointer-events: none;
    }
    .blob-1 { width: 500px; height: 500px; background: var(--purple); top: -100px; left: -150px; animation: drift 12s ease-in-out infinite alternate; }
    .blob-2 { width: 400px; height: 400px; background: #4f46e5; bottom: -80px; right: -100px; animation: drift 15s ease-in-out infinite alternate-reverse; }
    .blob-3 { width: 300px; height: 300px; background: var(--gold); top: 40%; left: 50%; transform: translate(-50%,-50%); animation: pulse 8s ease-in-out infinite; opacity: 0.07; }

    @keyframes drift { from { transform: translate(0,0); } to { transform: translate(40px, 30px); } }
    @keyframes pulse { 0%,100% { transform: translate(-50%,-50%) scale(1); } 50% { transform: translate(-50%,-50%) scale(1.2); } }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: rgba(108, 99, 255, 0.15);
      border: 1px solid rgba(108, 99, 255, 0.35);
      color: var(--purple-light);
      border-radius: 999px;
      padding: 0.35rem 1rem;
      font-size: 0.8rem;
      font-weight: 500;
      margin-bottom: 1.8rem;
      letter-spacing: 0.04em;
      animation: fadeUp 0.6s ease both;
    }
    .hero-badge .dot { width: 6px; height: 6px; background: var(--purple-light); border-radius: 50%; animation: blink 1.5s ease infinite; }
    @keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0.3; } }

    .hero h1 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(2.8rem, 7vw, 5.5rem);
      font-weight: 800;
      line-height: 1.05;
      letter-spacing: -0.03em;
      max-width: 800px;
      animation: fadeUp 0.7s 0.1s ease both;
    }

    .hero h1 .accent { color: var(--purple); }
    .hero h1 .gold { color: var(--gold); }

    .hero p {
      color: var(--text-muted);
      font-size: 1.1rem;
      font-weight: 300;
      max-width: 520px;
      margin: 1.5rem auto 0;
      line-height: 1.7;
      animation: fadeUp 0.7s 0.2s ease both;
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: var(--purple);
      color: var(--white);
      text-decoration: none;
      padding: 0.85rem 2rem;
      border-radius: 999px;
      font-weight: 500;
      font-size: 1rem;
      transition: transform 0.2s, box-shadow 0.2s;
      box-shadow: 0 0 30px rgba(108,99,255,0.4);
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 0 45px rgba(108,99,255,0.6); }

    /* Play Store icon */
    .ps-icon { width: 20px; height: 20px; }

    /* --- PLATFORM PICKER (Android = app, iPhone = web) --- */
    .platform-wrap {
      width: 100%;
      max-width: 860px;
      margin: 0 auto 3.5rem;
      scroll-margin-top: 6rem;
      animation: fadeUp 0.6s ease both;
    }

    .platform-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    .platform-card {
      --accent: var(--purple);
      --accent-rgb: 108, 99, 255;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      background: rgba(26,23,80,0.55);
      border: 1px solid rgba(var(--accent-rgb), 0.3);
      border-radius: 24px;
      padding: 2.4rem 1.8rem 2rem;
      overflow: hidden;
      transition: border-color 0.3s, transform 0.3s, box-shadow 0.3s;
    }
    .platform-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top, rgba(var(--accent-rgb), 0.14), transparent 65%);
      pointer-events: none;
    }
    .platform-card:hover { border-color: rgba(var(--accent-rgb), 0.65); transform: translateY(-4px); }

    .platform-card.android { --accent: var(--android); --accent-rgb: 61, 220, 132; }
    .platform-card.iphone  { --accent: var(--silver);  --accent-rgb: 228, 230, 238; }

    .platform-icon {
      width: 64px;
      height: 64px;
      fill: var(--accent);
      margin-bottom: 1.2rem;
      filter: drop-shadow(0 0 18px rgba(var(--accent-rgb), 0.45));
    }

    .platform-card h2 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(1.7rem, 4vw, 2.2rem);
      font-weight: 800;
      letter-spacing: -0.02em;
      line-height: 1.1;
    }
    .platform-card h2 span { color: var(--accent); }

    .platform-card .sub {
      color: var(--text-muted);
      font-size: 1rem;
      margin: 0.7rem 0 1.8rem;
      line-height: 1.5;
    }

    .platform-btn {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      width: 100%;
      margin-top: auto;
      background: var(--accent);
      color: var(--indigo-deep);
      text-decoration: none;
      padding: 1rem 1.5rem;
      border-radius: 999px;
      font-weight: 700;
      font-size: 1.05rem;
      box-shadow: 0 0 28px rgba(var(--accent-rgb), 0.35);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .platform-btn:hover { transform: translateY(-2px); box-shadow: 0 0 42px rgba(var(--accent-rgb), 0.55); }
    .platform-btn svg { width: 20px; height: 20px; fill: currentColor; }

    /* Highlight for the visitor's own phone (set by the script at the bottom) */
    .for-you-badge {
      display: none;
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: var(--accent);
      color: var(--indigo-deep);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.04em;
      padding: 0.3rem 0.75rem;
      border-radius: 999px;
    }
    .platform-card.is-for-you {
      border: 2px solid var(--accent);
      box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.12), 0 0 50px rgba(var(--accent-rgb), 0.35);
    }
    .platform-card.is-for-you .for-you-badge { display: inline-block; }
    .platform-card.is-dimmed { opacity: 0.6; }

    .platform-help {
      margin-top: 1.4rem;
      color: var(--text-muted);
      font-size: 0.9rem;
      letter-spacing: 0.02em;
    }

    /* --- STATS BAR --- */
    .stats {
      position: relative;
      z-index: 1;
      display: flex;
      justify-content: center;
      gap: 3rem;
      flex-wrap: wrap;
      padding: 2.5rem 1.5rem;
      border-top: 1px solid rgba(108,99,255,0.12);
      border-bottom: 1px solid rgba(108,99,255,0.12);
      background: rgba(26,23,80,0.4);
      animation: fadeUp 0.7s 0.5s ease both;
    }
    .stat { text-align: center; }
    .stat-num {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: var(--white);
    }
    .stat-num span { color: var(--purple); }
    .stat-label { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem; letter-spacing: 0.05em; }

    /* --- SECTIONS (What We Offer / Rates / House Rules) --- */
    .section {
      position: relative;
      z-index: 1;
      padding: 6rem 1.5rem 0;
      max-width: 1100px;
      margin: 0 auto;
    }
    .section:last-of-type { padding-bottom: 2rem; }

    .section-label {
      text-align: center;
      font-size: 0.75rem;
      letter-spacing: 0.15em;
      color: var(--purple-light);
      text-transform: uppercase;
      margin-bottom: 0.8rem;
    }
    .section-title {
      font-family: 'Syne', sans-serif;
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      text-align: center;
      margin-bottom: 3.5rem;
      letter-spacing: -0.02em;
    }

    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr));
      gap: 1.5rem;
    }

    .feature-card {
      background: rgba(26,23,80,0.5);
      border: 1px solid rgba(108,99,255,0.18);
      border-radius: 20px;
      padding: 2rem;
      transition: border-color 0.3s, transform 0.3s;
      position: relative;
      overflow: hidden;
    }
    .feature-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top left, rgba(108,99,255,0.08), transparent 60%);
      pointer-events: none;
    }
    .feature-card:hover { border-color: rgba(108,99,255,0.5); transform: translateY(-4px); }

    .feature-icon {
      font-size: 2rem;
      margin-bottom: 1rem;
      display: block;
    }
    .feature-card h3 {
      font-family: 'Syne', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 0.6rem;
    }
    .feature-card p {
      color: var(--text-muted);
      font-size: 0.9rem;
      line-height: 1.65;
    }

    /* Walk-in or Member? comparison */
    .section-title.with-sub { margin-bottom: 1rem; }
    .section-sub {
      text-align: center;
      color: var(--text-muted);
      font-size: 1.1rem;
      line-height: 1.6;
      max-width: 580px;
      margin: 0 auto 3.5rem;
    }
    .section-sub strong { color: var(--gold); font-weight: 700; }

    .compare-grid {
      display: grid;
      grid-template-columns: 1fr 1.08fr;
      gap: 1.5rem;
      align-items: center;
      max-width: 880px;
      margin: 0 auto;
    }

    .plan-card {
      --pad-y: 2.2rem;
      --pad-x: 2rem;
      display: flex;
      flex-direction: column;
      padding: var(--pad-y) var(--pad-x);
    }
    .plan-card > * { position: relative; } /* above the card's glow overlay */

    /* Walk-in: muted on purpose */
    .plan-card:not(.best) {
      background: rgba(26,23,80,0.3);
      border-color: rgba(155,150,204,0.15);
    }
    .plan-card:not(.best):hover { transform: none; border-color: rgba(155,150,204,0.25); }
    .plan-card:not(.best) .rate-amount { color: rgba(255,255,255,0.75); }
    .plan-card:not(.best) .plan-points li { color: var(--text-muted); }

    /* Member: the clear winner */
    .plan-card.best {
      --pad-y: 2.6rem;
      border: 2px solid var(--purple);
      background: rgba(59,53,168,0.38);
      box-shadow: 0 0 0 4px rgba(108,99,255,0.12), 0 0 60px rgba(108,99,255,0.35);
      transform: translateY(-6px);
    }
    .plan-card.best:hover { transform: translateY(-10px); border-color: var(--purple-light); }
    .plan-card.best .rate-amount { color: var(--gold); }

    .plan-head {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      gap: 0.6rem;
    }
    .plan-head .feature-icon { font-size: 1.8rem; margin: 0; }
    .plan-head h3 { font-size: 1.6rem; margin: 0; }

    .best-badge {
      background: var(--gold);
      color: var(--indigo-deep);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      padding: 0.3rem 0.75rem;
      border-radius: 999px;
    }

    .plan-prices {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 0.4rem 1.6rem;
      margin: 1.4rem 0 1.5rem;
      padding: 1rem 0;
      border-top: 1px solid rgba(108,99,255,0.15);
      border-bottom: 1px solid rgba(108,99,255,0.15);
    }
    .plan-price { display: flex; align-items: baseline; gap: 0.35rem; }
    .rate-amount {
      font-family: 'Syne', sans-serif;
      font-size: 2.2rem;
      font-weight: 800;
      letter-spacing: -0.02em;
    }
    .rate-per { color: var(--text-muted); font-size: 0.95rem; }

    .plan-points {
      list-style: none;
      display: grid;
      gap: 0.8rem;
    }
    .plan-points li {
      display: flex;
      gap: 0.6rem;
      font-size: 0.95rem;
      line-height: 1.5;
    }
    .plan-points li span { flex-shrink: 0; }

    .plan-cta {
      justify-content: center;
      margin-top: 1.8rem;
      font-weight: 700;
    }

    /* Pulsing limited-time strip across the top of the member card */
    .promo-banner {
      margin: calc(-1 * var(--pad-y)) calc(-1 * var(--pad-x)) 1.6rem;
      padding: 0.8rem 1rem;
      text-align: center;
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 0.95rem;
      letter-spacing: 0.02em;
      color: var(--indigo-deep);
      background: linear-gradient(90deg, #ff7a3d, var(--gold), #ff7a3d);
      background-size: 200% 100%;
      animation: promoShine 3s linear infinite, promoPulse 1.6s ease-in-out infinite;
    }
    @keyframes promoShine { from { background-position: 0% 0; } to { background-position: 200% 0; } }
    @keyframes promoPulse { 0%,100% { filter: brightness(1); } 50% { filter: brightness(1.2); } }

    .rates-note {
      text-align: center;
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-top: 2rem;
    }
    .rates-note::before { content: '⏱️ '; }

    /* House rules */
    .rule-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(min(320px, 100%), 1fr));
      gap: 1rem;
      list-style: none;
    }
    .rule-card {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      padding: 1.3rem 1.4rem;
    }
    .rule-num {
      flex-shrink: 0;
      width: 2.2rem;
      height: 2.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: rgba(108,99,255,0.2);
      border: 1px solid rgba(108,99,255,0.45);
      color: var(--purple-light);
      font-family: 'Syne', sans-serif;
      font-weight: 800;
    }
    .rule-card p { color: var(--white); font-size: 0.95rem; line-height: 1.55; }
    .rule-card .rule-icon { margin-right: 0.35rem; }

    /* --- CTA SECTION --- */
    .cta-section {
      position: relative;
      z-index: 1;
      margin: 6rem auto 6rem;
      max-width: 700px;
      text-align: center;
      padding: 4rem 2rem;
      background: rgba(26,23,80,0.6);
      border: 1px solid rgba(108,99,255,0.25);
      border-radius: 28px;
      overflow: hidden;
    }
    .cta-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at center top, rgba(108,99,255,0.15), transparent 60%);
      pointer-events: none;
    }
    .cta-section h2 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(1.8rem, 4vw, 2.6rem);
      font-weight: 800;
      margin-bottom: 1rem;
      letter-spacing: -0.02em;
    }
    .cta-section p { color: var(--text-muted); margin-bottom: 2rem; line-height: 1.7; }

    /* --- FOOTER --- */
    footer {
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 2rem 1.5rem;
      border-top: 1px solid rgba(108,99,255,0.12);
      color: var(--text-muted);
      font-size: 0.85rem;
    }
    footer a { color: var(--purple-light); text-decoration: none; }
    footer a:hover { text-decoration: underline; }

    .location {
      margin-bottom: 1rem;
      color: var(--white);
      font-size: 0.95rem;
      line-height: 1.6;
    }
    .location strong { font-family: 'Syne', sans-serif; font-weight: 700; }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* Phones: keep the two cards side by side (Android left, iPhone right),
       just smaller, so both are visible without scrolling */
    @media (max-width: 700px) {
      .platform-wrap { margin-bottom: 2.5rem; }
      .platform-grid { gap: 0.75rem; }
      .platform-card { padding: 1.3rem 0.8rem 1rem; border-radius: 18px; }
      .platform-icon { width: 42px; height: 42px; margin-bottom: 0.6rem; }
      .platform-card h2 { font-size: 1.2rem; }
      .platform-card .sub { font-size: 0.8rem; margin: 0.4rem 0 1rem; line-height: 1.4; }
      .platform-btn { padding: 0.75rem 0.6rem; font-size: 0.85rem; gap: 0.35rem; line-height: 1.2; }
      .platform-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
      /* Badge sits above the icon instead of in the corner (no room there) */
      .for-you-badge { position: static; margin-bottom: 0.6rem; font-size: 0.65rem; padding: 0.2rem 0.6rem; }
      .platform-help { font-size: 0.8rem; margin-top: 1rem; }

      /* Comparison stacks, member card first so the better deal is seen first */
      .compare-grid { grid-template-columns: 1fr; gap: 1.2rem; }
      .plan-card { --pad-y: 1.8rem; --pad-x: 1.4rem; }
      .plan-card.best { --pad-y: 1.8rem; order: -1; transform: none; }
      .plan-card.best:hover { transform: none; }
      .promo-banner { font-size: 0.85rem; }
      .rate-amount { font-size: 1.9rem; }
      .section-sub { font-size: 1rem; margin-bottom: 2.2rem; }
    }

    @media (prefers-reduced-motion: reduce) {
      .promo-banner { animation: none; }
    }

    @media (max-width: 600px) {
      nav { padding: 1rem 1.2rem; }
      .hero { padding-top: 5.5rem; }
      .stats { gap: 1.5rem; }
      .section { padding-top: 4.5rem; }
      .section-title { margin-bottom: 2.2rem; }
      .cta-section { margin: 4.5rem 1rem 4rem; }
    }
  </style>
</head>
<body>

  <!-- NAV -->
  <nav>
    <span class="nav-logo">Level<span>Up</span></span>
    <a href="#download" class="nav-cta">Get the App</a>
  </nav>

  <!-- HERO -->
  <section class="hero">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Platform picker: Android = app, iPhone = web (first thing on screen) -->
    <div class="platform-wrap" id="download">
      <div class="platform-grid">

        <div class="platform-card android" id="card-android">
          <span class="for-you-badge">✨ This is for you</span>
          <svg class="platform-icon" viewBox="0 0 1024 1024" aria-hidden="true"><g transform="translate(0,960) scale(1,-1)"><path d="M897.818 617.989c-33.052 0-59.847-26.794-59.847-59.847v0-271.219c0-33.052 26.794-59.847 59.847-59.847s59.847 26.794 59.847 59.847v0 271.219c0 33.052-26.794 59.847-59.847 59.847v0zM126.182 617.989c-33.052 0-59.847-26.794-59.847-59.847v0-271.219c0-33.052 26.794-59.847 59.847-59.847s59.847 26.794 59.847 59.847v0 271.219c0 33.052-26.794 59.847-59.847 59.847v0zM229.321 617.989v-407.465c0-37.624 30.5-68.122 68.122-68.122v0h43.293v-144.522c0-33.052 26.794-59.847 59.847-59.847s59.847 26.794 59.847 59.847v0 0 144.522h103.139v-144.522c0-33.052 26.794-59.847 59.847-59.847s59.847 26.794 59.847 59.847v0 144.522h43.293c37.624 0 68.122 30.5 68.122 68.122v0 407.465zM787.039 702.666c-19.494 64.041-60.923 116.427-115.35 149.595l-1.159 0.657-9.55 5.73-10.187 5.093 11.46 19.737 33.743 63.667c0.321 0.883 0.507 1.901 0.507 2.963 0 2.96-1.443 5.584-3.665 7.206l-0.025 0.018h-4.457c-3.404-0.157-6.379-1.864-8.255-4.426l-0.021-0.030-34.38-59.21-10.823-19.737-10.187 4.457-10.823 3.82c-30.354 10.882-65.375 17.173-101.867 17.173s-71.511-6.291-104.038-17.846l2.171 0.673-10.187-3.82-10.823-4.457-10.823 19.737-34.38 63.667c-1.592 2.804-4.559 4.665-7.958 4.665-5.038 0-9.122-4.084-9.122-9.122 0-1.637 0.431-3.174 1.186-4.502l-0.024 0.045 33.743-63.667 11.46-19.737-10.187-5.093-9.55-5.73c-55.806-33.938-97.174-86.897-115.417-149.695l-0.455-1.831c-4.717-15.122-7.703-32.57-8.27-50.627l-0.007-0.307h565.357c-0.686 18.4-3.432 35.851-8.017 52.541l0.378-1.609zM384.668 736.408c-14.065 0-25.467 11.402-25.467 25.467s11.402 25.467 25.467 25.467c14.065 0 25.467-11.402 25.467-25.467v0c0-14.065-11.402-25.467-25.467-25.467v0zM639.332 736.408c-14.065 0-25.467 11.402-25.467 25.467s11.402 25.467 25.467 25.467c14.065 0 25.467-11.402 25.467-25.467v0c0-14.065-11.402-25.467-25.467-25.467v0z"/></g></svg>
          <h2><span>Android</span> User?</h2>
          <p class="sub">Download the Level Lounge app</p>
          <a href="{{ $playStoreUrl }}" class="platform-btn" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.18 23.76c.3.17.65.19.97.07l12.67-7.31-2.79-2.79-10.85 10z"/><path d="M22.47 10.53l-2.87-1.65-3.13 3.13 3.13 3.13 2.9-1.67c.82-.48.82-1.66-.03-2.14z"/><path d="M2.21.33A1 1 0 0 0 1 1.28V22.7a1 1 0 0 0 1.21.95L14.96 12 2.21.33z"/><path d="M16.82 8.45L4.15.31 14.96 12l1.86-1.86-2.79-2.79 2.79 2.79z" opacity=".5"/></svg>
            Get it on Google Play
          </a>
        </div>

        <div class="platform-card iphone" id="card-iphone">
          <span class="for-you-badge">✨ This is for you</span>
          <svg class="platform-icon" viewBox="0 0 1024 1024" aria-hidden="true"><g transform="translate(0,960) scale(1,-1)"><path d="M800.988 418.589c-0.025 1.227-0.040 2.673-0.040 4.124 0 81.14 45.665 151.616 112.697 187.127l1.149 0.554c-43.688 60.224-112.883 99.642-191.399 102.285l-0.408 0.010c-81.199 6.394-169.43-47.312-202.037-47.312s-113.166 44.755-174.544 44.755c-127.87 0-262.775-101.657-262.775-304.332 0.671-66.632 12.562-130.276 33.869-189.424l-1.262 4.011c28.131-81.837 133.625-287.070 243.594-283.874 57.542 0 98.461 40.919 173.266 40.919s109.97-40.919 174.544-40.919c110.608 0 206.511 188.61 234.004 272.365-83.223 34.69-140.659 115.384-140.659 209.49 0 0.076 0 0.154 0 0.231v-0.012zM673.117 793.891c33.682 37.846 54.259 88 54.259 142.959 0 7.972-0.433 15.844-1.277 23.592l0.085-0.959c-61.9-6.974-115.875-36.341-154.514-79.679l-0.211-0.24c-37.077-39.007-59.884-91.883-59.884-150.086 0-4.782 0.154-9.526 0.457-14.231l-0.033 0.641c1.605-0.048 3.493-0.075 5.387-0.075 63.128 0 119.134 30.497 154.087 77.563l0.366 0.514z"/></g></svg>
          <h2><span>iPhone</span> User?</h2>
          <p class="sub">No app needed — use our web version</p>
          <a href="{{ $webLoginUrl }}" class="platform-btn">
            Log in on Web →
          </a>
        </div>

      </div>
      <p class="platform-help">📱 Android = App &nbsp;•&nbsp; 🍎 iPhone = Web Login</p>
    </div>

    <div class="hero-badge">
      <span class="dot"></span>
      Now available on Google Play
    </div>

    <h1>Shop. Earn. <span class="accent">Level</span> <span class="gold">Up.</span></h1>

    <p>A gamified social marketplace built for the Philippines. Earn points, climb leaderboards, and unlock premium perks — all while buying and selling locally.</p>
  </section>

  <!-- STATS -->
  <div class="stats">
    <div class="stat">
      <div class="stat-num">2<span>x</span></div>
      <div class="stat-label">CURRENCY SYSTEM</div>
    </div>
    <div class="stat">
      <div class="stat-num">∞<span></span></div>
      <div class="stat-label">USER-CREATED SHOPS</div>
    </div>
    <div class="stat">
      <div class="stat-num">🇵🇭</div>
      <div class="stat-label">BUILT FOR THE PHILIPPINES</div>
    </div>
    <div class="stat">
      <div class="stat-num">0<span>₱</span></div>
      <div class="stat-label">FREE TO DOWNLOAD</div>
    </div>
  </div>

  <!-- WHAT WE OFFER -->
  <section class="section" id="offer">
    <p class="section-label">Built for Stallions 🐎</p>
    <h2 class="section-title">What We Offer</h2>

    <div class="feature-grid">
      @foreach ($offers as $offer)
        <div class="feature-card">
          <span class="feature-icon">{{ $offer['icon'] }}</span>
          <h3>{{ $offer['title'] }}</h3>
          <p>{{ $offer['text'] }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <!-- RATES -->
  <section class="section" id="rates">
    <p class="section-label">Play more, pay less</p>
    <h2 class="section-title with-sub">{{ $compareTitle }}</h2>
    {{-- "FREE" in the subheading is highlighted in gold --}}
    <p class="section-sub">{!! str_replace('FREE', '<strong>FREE</strong>', e($compareSub)) !!}</p>

    <div class="compare-grid">
      @foreach ($plans as $plan)
        <div class="feature-card plan-card {{ $plan['best'] ? 'best' : '' }}">
          @if ($plan['banner'])
            <div class="promo-banner">{{ $plan['banner'] }}</div>
          @endif

          <div class="plan-head">
            <span class="feature-icon">{{ $plan['icon'] }}</span>
            <h3>{{ $plan['name'] }}</h3>
            @if ($plan['best'])
              <span class="best-badge">Best Value</span>
            @endif
          </div>

          <div class="plan-prices">
            @foreach ($plan['prices'] as $price)
              <div class="plan-price">
                <span class="rate-amount">{{ $price['amount'] }}</span>
                <span class="rate-per">{{ $price['per'] }}</span>
              </div>
            @endforeach
          </div>

          <ul class="plan-points">
            @foreach ($plan['points'] as $point)
              <li><span>{{ $point['icon'] }}</span>{{ $point['text'] }}</li>
            @endforeach
          </ul>

          @if ($plan['cta'])
            <a href="{{ $plan['cta']['url'] }}" class="btn-primary plan-cta js-register"
               data-android-url="{{ $plan['cta']['android_url'] ?? '' }}"
               data-android-label="{{ $plan['cta']['android_label'] ?? '' }}">{{ $plan['cta']['label'] }} →</a>
          @endif
        </div>
      @endforeach
    </div>
    <p class="rates-note">{{ $ratesNote }}</p>
  </section>

  <!-- HOUSE RULES -->
  <section class="section" id="rules">
    <p class="section-label">Keep it fun for everyone</p>
    <h2 class="section-title">House Rules</h2>

    <ol class="rule-grid">
      @foreach ($rules as $i => $rule)
        <li class="feature-card rule-card">
          <span class="rule-num">{{ $i + 1 }}</span>
          <p><span class="rule-icon">{{ $rule['icon'] }}</span>{{ $rule['text'] }}</p>
        </li>
      @endforeach
    </ol>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <h2>Ready to Level Up?</h2>
    <p>Join the gamified marketplace built for Filipino buyers and sellers. Download free on Android today.</p>
    <a href="{{ $playStoreUrl }}" class="btn-primary" target="_blank" rel="noopener">
      <svg class="ps-icon" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M3.18 23.76c.3.17.65.19.97.07l12.67-7.31-2.79-2.79-10.85 10z"/><path d="M22.47 10.53l-2.87-1.65-3.13 3.13 3.13 3.13 2.9-1.67c.82-.48.82-1.66-.03-2.14z"/><path d="M2.21.33A1 1 0 0 0 1 1.28V22.7a1 1 0 0 0 1.21.95L14.96 12 2.21.33z"/><path d="M16.82 8.45L4.15.31 14.96 12l1.86-1.86-2.79-2.79 2.79 2.79z" opacity=".5"/></svg>
      Download Free
    </a>
  </section>

  <!-- FOOTER -->
  <footer>
    <p class="location">📍 <strong>{{ $location['name'] }}</strong> — {{ $location['address'] }}</p>
    <p>&copy; 2026 Level Up · <a href="mailto:simonsantiago7@gmail.com">Contact</a> · <a href="/delete-account">Delete Account</a></p>
  </footer>

  <script>
    // Phones only: highlight the card that matches this phone and put it first.
    // Desktop visitors see both cards the same.
    (function () {
      var ua = navigator.userAgent || '';
      var isAndroid = /Android/i.test(ua);
      // iPads on iPadOS 13+ say "Macintosh", so also check for a touch screen
      var isIOS = /iPhone|iPad|iPod/i.test(ua) ||
                  (/Macintosh/i.test(ua) && navigator.maxTouchPoints > 1);
      // Register buttons: Android phones register in the app, so send them to the Play Store.
      // iPhones and computers keep the web sign-up link.
      if (isAndroid) {
        document.querySelectorAll('.js-register').forEach(function (btn) {
          if (!btn.dataset.androidUrl) return;
          btn.href = btn.dataset.androidUrl;
          btn.target = '_blank';
          btn.rel = 'noopener';
          if (btn.dataset.androidLabel) btn.textContent = btn.dataset.androidLabel + ' →';
        });
      }

      if (!isAndroid && !isIOS) return;

      var mine  = document.getElementById(isAndroid ? 'card-android' : 'card-iphone');
      var other = document.getElementById(isAndroid ? 'card-iphone' : 'card-android');
      mine.classList.add('is-for-you');
      other.classList.add('is-dimmed');
    })();
  </script>

</body>
</html>
