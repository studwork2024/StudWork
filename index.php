<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>StudWork — Find Part-Time Jobs While You Study</title>
    <style>
        :root{--maroon:#7a0000;--white:#ffffff;--muted:#fdf6f6;--accent:#b33a3a;--radius:26px;--card-radius:18px}
        *{box-sizing:border-box}
        body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial; background:var(--muted);color:var(--maroon)}
        /* Styles truncated for brevity */
        /* top bar (full-bleed stripe) */
		.topbar{background:var(--maroon);color:var(--white);padding:0;width:100%;position:fixed;top:0;left:0;right:0;z-index:1000}
		.topbar .wrap{max-width:1100px;margin:0;display:flex;align-items:center;gap:14px;padding:18px 28px;justify-content:flex-start;height:64px;position:relative}
		body{padding-top:64px}
		.brand-container{display:flex;align-items:center;gap:10px}
		/* brand-container removed from header; logo will live in the bottom stripe */
		.brand-logo{width:clamp(32px,5vw,52px);height:clamp(32px,5vw,52px);border-radius:50%;background:linear-gradient(180deg,#ffd96b,#ffb83b);display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.15);flex:0 0 auto}
		.brand-logo svg{width:70%;height:70%;display:block}
		.brand-logo svg .logo-text{font-family:Helvetica, Arial, sans-serif;fill:#fff;font-weight:800;font-size:0.6em}
		.brand{line-height:1}
		.brand .title{font-weight:800;letter-spacing:1px;font-size:clamp(0.95rem,1.6vw,1.15rem)}
		.brand .tag{font-size:clamp(0.72rem,1.1vw,0.95rem);opacity:0.9;margin-top:4px;color:#ffdcbc}

		/* repeating background pattern */
		.pattern{background-image:radial-gradient(circle at 50% 50%, rgba(123,0,0,0.06) 1px, transparent 2px);}
		/* center content area */
		.main-wrap{max-width:1100px;margin:28px auto;padding:0 18px;padding-bottom:110px}

		/* main hero card (center rounded white box) */
		.hero-card{background:var(--white);border-radius:var(--card-radius);border:2px solid #0b0b0b;padding:28px 36px;margin:0 auto;max-width:680px;text-align:center;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
		.hero-card h1{margin:2px 0 10px;font-size:1.65rem;color:#111;font-weight:800}
		.hero-card p.lead{margin:10px 0 22px;color:#222;font-size:1.02rem}

		.hero-actions{display:flex;gap:36px;align-items:center;justify-content:center;margin-top:12px}
		.huge-btn{width:160px;height:120px;border-radius:28px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.05rem;cursor:pointer}
		.huge-btn.primary{background:var(--maroon);color:var(--white);box-shadow:0 6px 18px rgba(0,0,0,0.12)}
		.huge-btn.ghost{background:transparent;border:6px solid var(--maroon);color:var(--maroon)}

		/* features row */
		.features{display:flex;gap:36px;justify-content:space-between;margin-top:36px;align-items:flex-start}
		.feature{flex:1;background:var(--white);border-radius:26px;border:2px solid #000;padding:18px;text-align:center;min-height:110px}
		.feature h3{margin:0;color:var(--maroon);font-size:1rem}
		.feature p{margin-top:8px;color:#333;font-size:0.95rem}

		/* footer bar (fixed full-bleed stripe at bottom) */
		.footer-strip{position:fixed;left:0;right:0;bottom:0;background:var(--maroon);display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:64px;z-index:999;padding:12px 0}
		.footer-strip .brand-logo{position:absolute;left:16px;top:50%;transform:translateY(-50%);width:clamp(36px,5vw,56px);height:clamp(36px,5vw,56px);border-radius:50%;display:flex;align-items:center;justify-content:center}
		.footer-strip .brand-logo svg{width:70%;height:70%;display:block}
		.user-agreement{width:100%;max-width:none;margin:0;padding:8px 12px;text-align:center;color:#fff;font-weight:600;font-size:0.95rem}
		.user-agreement a{color:#fff;text-decoration:none;cursor:pointer}
		.user-agreement a:hover{text-decoration:underline;opacity:0.9}
		.footer{width:100%;max-width:none;margin:0;padding:6px 12px;text-align:center;color:#fff;font-weight:600}

    </style>
</head>
<body>
    <?php
        include 'config.php';
    ?>
    <header class="topbar">
<div class="wrap">
    <div class="brand-container">
        <div class="brand-logo">
            <svg viewBox="0 0 24 24">
                <text x="12" y="12" text-anchor="middle" dominant-baseline="middle" class="logo-text">SW</text>
            </svg>
        </div>
        <div class="brand">
            <div class="title">STUDWORK</div>
            <div class="tag">Connecting Students and Employers</div>
        </div>
    </div>
</div>
</header>

    <main class="main-wrap">
        <div class="hero-card">
            <h1>Find Part-Time Jobs While You Study</h1>
            <p class="lead">Employers can post openings. Students can sign up, browse, and apply — all right here.</p>

            <div class="hero-actions" role="group" aria-label="Primary actions">
                <a href="create-account.php" class="huge-btn primary">Create Account</a>
                <a href="login.php" class="huge-btn ghost">Log In</a>
            </div>
        </div>

        <div style="height:28px"></div>

        <div class="features">
            <div class="feature">
                <h3>Browse Jobs</h3>
                <p>Students can view job offers that align with their skill sets.</p>
            </div>
            <div class="feature">
                <h3>Post Jobs</h3>
                <p>Companies and Employers can post job offers.</p>
            </div>
            <div class="feature">
                <h3>Role-based Access</h3>
                <p>Sign up as a Student or Employer.</p>
            </div>
        </div>
    </main>

    <footer class="footer-strip">
        <div class="user-agreement">
            <a href="user-agreement.php">User Agreement</a> | <a href="privacy-policy.php">Privacy Policy</a> | <a href="mailto:support@studwork.ph">Contact Support</a>
        </div>
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
        // Buttons are placeholders; links should navigate, other buttons show demo alert
        document.querySelectorAll('.huge-btn').forEach(b => {
            if (b.matches && b.matches('a[href]')) return; // skip anchors (they navigate)
            b.addEventListener('click', () => alert('Demo: action placeholder'));
        });
    </script>
</body>
</html>
