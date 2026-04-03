<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Level Up — Gamified Social Marketplace</title>
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

    .hero-actions {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 2.5rem;
      animation: fadeUp 0.7s 0.3s ease both;
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

    .btn-secondary {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      border: 1px solid rgba(255,255,255,0.2);
      color: var(--white);
      text-decoration: none;
      padding: 0.85rem 2rem;
      border-radius: 999px;
      font-weight: 500;
      font-size: 1rem;
      transition: border-color 0.2s, transform 0.2s;
    }
    .btn-secondary:hover { border-color: var(--purple); transform: translateY(-2px); }

    /* Play Store icon */
    .ps-icon { width: 20px; height: 20px; }

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

    /* --- FEATURES --- */
    .features {
      position: relative;
      z-index: 1;
      padding: 6rem 1.5rem;
      max-width: 1100px;
      margin: 0 auto;
    }

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
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

    /* --- CTA SECTION --- */
    .cta-section {
      position: relative;
      z-index: 1;
      margin: 2rem auto 6rem;
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

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 600px) {
      nav { padding: 1rem 1.2rem; }
      .stats { gap: 1.5rem; }
      .cta-section { margin: 2rem 1rem 4rem; }
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

    <div class="hero-badge">
      <span class="dot"></span>
      Now available on Google Play
    </div>

    <h1>Shop. Earn. <span class="accent">Level</span> <span class="gold">Up.</span></h1>

    <p>A gamified social marketplace built for the Philippines. Earn points, climb leaderboards, and unlock premium perks — all while buying and selling locally.</p>

    <div class="hero-actions" id="download">
      <a href="https://play.google.com/store/apps/details?id=com.johnsimonsantiago.LevelUpApp" class="btn-primary" target="_blank">
        <svg class="ps-icon" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M3.18 23.76c.3.17.65.19.97.07l12.67-7.31-2.79-2.79-10.85 10z"/><path d="M22.47 10.53l-2.87-1.65-3.13 3.13 3.13 3.13 2.9-1.67c.82-.48.82-1.66-.03-2.14z"/><path d="M2.21.33A1 1 0 0 0 1 1.28V22.7a1 1 0 0 0 1.21.95L14.96 12 2.21.33z"/><path d="M16.82 8.45L4.15.31 14.96 12l1.86-1.86-2.79-2.79 2.79 2.79z" opacity=".5"/></svg>
        Download on Google Play
      </a>
<a href="#features" class="btn-secondary">
  See Features ↓
</a>
<a href="/dashboard" class="btn-secondary">
  Web Login
</a>
    </div>
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

  <!-- FEATURES -->
  <section class="features" id="features">
    <p class="section-label">Why Level Up?</p>
    <h2 class="section-title">Everything in one place</h2>

    <div class="feature-grid">
      <div class="feature-card">
        <span class="feature-icon">⭐</span>
        <h3>Points & Stars</h3>
        <p>Every purchase earns you points and stars. Spend them in shops or climb the leaderboard to flex your rank.</p>
      </div>
      <div class="feature-card">
        <span class="feature-icon">🏪</span>
        <h3>User-Created Shops</h3>
        <p>Anyone can open a shop. List items, manage orders, and grow your business — all from your phone.</p>
      </div>
      <div class="feature-card">
        <span class="feature-icon">🏆</span>
        <h3>Leaderboards</h3>
        <p>See who's on top. Compete with other users weekly and earn bragging rights with your community.</p>
      </div>
      <div class="feature-card">
        <span class="feature-icon">💳</span>
        <h3>Loyalty Cards</h3>
        <p>Earn digital loyalty stamps at your favorite shops and unlock exclusive rewards as a returning customer.</p>
      </div>
      <div class="feature-card">
        <span class="feature-icon">🤝</span>
        <h3>Bets & Trades</h3>
        <p>Challenge friends, make trades, and place bets in a social layer that makes every transaction exciting.</p>
      </div>
      <div class="feature-card">
        <span class="feature-icon">👑</span>
        <h3>Premium Membership</h3>
        <p>Upgrade for exclusive perks, higher limits, and features that give you an edge in the marketplace.</p>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <h2>Ready to Level Up?</h2>
    <p>Join the gamified marketplace built for Filipino buyers and sellers. Download free on Android today.</p>
    <a href="https://play.google.com/store/apps/details?id=com.johnsimonsantiago.LevelUpApp" class="btn-primary" target="_blank">
      <svg class="ps-icon" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M3.18 23.76c.3.17.65.19.97.07l12.67-7.31-2.79-2.79-10.85 10z"/><path d="M22.47 10.53l-2.87-1.65-3.13 3.13 3.13 3.13 2.9-1.67c.82-.48.82-1.66-.03-2.14z"/><path d="M2.21.33A1 1 0 0 0 1 1.28V22.7a1 1 0 0 0 1.21.95L14.96 12 2.21.33z"/><path d="M16.82 8.45L4.15.31 14.96 12l1.86-1.86-2.79-2.79 2.79z" opacity=".5"/></svg>
      Download Free
    </a>
  </section>

  <!-- FOOTER -->
  <footer>
    <p>&copy; 2026 Level Up · <a href="mailto:simonsantiago7@gmail.com">Contact</a> · <a href="/delete-account">Delete Account</a></p>
  </footer>

</body>
</html>