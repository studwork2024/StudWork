<?php
session_start();

$message = '';
$messageType = '';

// ✅ DATABASE CONNECTION
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = 'Please provide both email and password.';
        $messageType = 'error';
    } else {

        // ✅ FETCH USER BY EMAIL
        $stmt = $conn->prepare("SELECT fullname, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ VERIFY PASSWORD
            if (password_verify($password, $user['password'])) {

                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['fullname'] = $user['fullname'];

                // ✅ ROLE-BASED REDIRECT
                switch ($user['role']) {
                    case 'student':
                        header("Location: student.php");
                        exit;
                    case 'employer':
                        header("Location: employer.php");
                        exit;
                    case 'admin':
                        header("Location: admin.php");
                        exit;
                    default:
                        $message = 'Invalid user role.';
                        $messageType = 'error';
                }

            } else {
                $message = 'Invalid email or password.';
                $messageType = 'error';
            }

        } else {
            $message = 'Invalid email or password.';
            $messageType = 'error';
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Log In — StudWork</title>
<style>
:root{--maroon:#7a0000;--white:#ffffff;--muted:#fdf6f6}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:var(--muted);color:var(--maroon)}
.topbar{background:var(--maroon);color:var(--white);padding:0;width:100%;position:fixed;top:0;left:0;right:0;z-index:1000}
.topbar .wrap{max-width:1100px;margin:0;display:flex;align-items:center;gap:14px;padding:12px 20px;justify-content:flex-start;height:64px}
body{padding-top:64px}
.brand-container{display:flex;align-items:center;gap:10px}
.brand-logo{width:clamp(32px,5vw,52px);height:clamp(32px,5vw,52px);border-radius:50%;background:linear-gradient(180deg,#ffd96b,#ffb83b);display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.15)}
.brand-logo svg{width:70%;height:70%}
.logo-text{fill:#fff;font-weight:800;font-size:.6em}
.brand .title{font-weight:800;letter-spacing:1px}
.brand .tag{font-size:.85rem;color:#ffdcbc}
.pattern{min-height:calc(100vh - 64px)}
.main-wrap{max-width:1100px;margin:28px auto;padding:24px 18px}
.panel{max-width:420px;margin:auto;background:#fff;border-radius:14px;padding:22px;border:2px solid #e9e3e3;box-shadow:0 8px 30px rgba(0,0,0,.06)}
h1{text-align:center;margin:0 0 8px}
p.lead{text-align:center;color:#444;margin-bottom:16px}
label{display:block;font-weight:600;margin-top:12px}
input{width:100%;padding:10px;border-radius:8px;border:1px solid #e6e0e0;margin-top:6px}
.actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}
.btn{background:var(--maroon);color:#fff;border:none;padding:10px 14px;border-radius:10px;font-weight:700;cursor:pointer}
.btn.ghost{background:none;border:1px solid var(--maroon);color:var(--maroon);text-decoration:none;display:inline-flex;align-items:center}
.msg{margin-top:14px;padding:10px;border-radius:8px}
.success{background:#eaf8ef;border-left:4px solid #3ca66b;color:#0b6b34}
.error{background:#fff1f1;border-left:4px solid #d34b4b;color:#8b1e1e}
.footer-strip{position:fixed;bottom:0;left:0;right:0;background:var(--maroon);color:#fff;padding:10px;text-align:center}
.footer-strip a{color:#fff;text-decoration:none}
</style>
</head>
<body>

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

<div class="pattern">
<main class="main-wrap">
<div class="panel">
<h1>Log In</h1>
<p class="lead">Sign in to your StudWork account.</p>

<form method="POST" action="">
<label>Email</label>
<input type="email" name="email" required>

<label>Password</label>
<input type="password" name="password" required>

<div class="actions">
    <a href="index.php" class="btn ghost">Back</a>
    <button type="submit" class="btn">Log In</button>
</div>

<?php if ($message): ?>
<div class="msg <?= $messageType ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
</form>
</div>
</main>
</div>

<div class="footer-strip">
<a href="user-agreement.html">User Agreement</a> |
<a href="privacy-policy.html">Privacy Policy</a> |
<a href="mailto:support@studwork.ph">Contact Support</a>
</div>

</body>
</html>
