<?php
session_start();
include 'config.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $company = trim($_POST['company_name'] ?? '');

    if (!$fullname || !$email || !$password || !$confirm || !$role) {
        $message = "Please fill out all required fields.";
        $messageType = "error";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif ($role === "employer" && !$company) {
        $message = "Please provide your company or organization name.";
        $messageType = "error";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if ($role === "student") {
            $company = null;
        }

        $stmt = $conn->prepare(
            "INSERT INTO users (fullname, email, password, role, company_name)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $fullname, $email, $hashed, $role, $company);

        if ($stmt->execute()) {
            // ✅ Auto-login after successful registration
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;
            $_SESSION['fullname'] = $fullname;

            // Redirect based on role
            if ($role === "student") {
                header("Location: student.php");
            } elseif ($role === "employer") {
                header("Location: employer.php");
            }
            exit;
        } else {
            if ($conn->errno === 1062) {
                $message = "Email already exists.";
            } else {
                $message = "Something went wrong. Please try again.";
            }
            $messageType = "error";
        }

        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Create an Account — StudWork</title>
  <style>
    :root{--maroon:#7a0000;--white:#ffffff;--muted:#fdf6f6;--accent:#b33a3a}
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:var(--muted);color:var(--maroon)}
    .topbar{background:var(--maroon);color:var(--white);position:fixed;top:0;left:0;right:0;z-index:1000}
    .topbar .wrap{display:flex;align-items:center;padding:12px 20px;height:64px}
    body{padding-top:64px}
    .pattern{min-height:calc(100vh - 64px)}
    .panel{max-width:520px;margin:40px auto;background:#fff;padding:22px;border-radius:14px}
    label{display:block;margin-top:12px;font-weight:600}
    input,select{width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid #ccc}
    .actions{margin-top:16px;display:flex;gap:10px;justify-content:flex-end}
    .btn{background:var(--maroon);color:#fff;border:none;padding:10px 14px;border-radius:10px;cursor:pointer;font-weight:700}
    .btn.ghost{background:transparent;color:var(--maroon);border:1px solid var(--maroon)}
    .msg{margin-top:12px;padding:10px;border-radius:8px}
    .success{background:#eaf8ef;border-left:4px solid #3ca66b}
    .error{background:#fff1f1;border-left:4px solid #d34b4b}
  </style>
</head>
<body>

<div class="pattern">
  <main class="panel">
    <h1>Create an account</h1>
    <p>Sign up as a Student or Employer to get started.</p>

    <form method="POST" novalidate>
      <label>Full name</label>
      <input name="fullname" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm password</label>
      <input type="password" name="confirm_password" required>

      <label>I am a</label>
      <select name="role" id="role" required>
        <option value="student">Student</option>
        <option value="employer">Employer</option>
      </select>

      <div id="employer-fields" style="display:none">
        <label>Company / Organization</label>
        <input name="company_name">
      </div>

      <div class="actions">
        <a href="index.php" class="btn ghost">Back</a>
        <button class="btn">Create account</button>
      </div>

      <?php if ($message): ?>
        <div class="msg <?= $messageType ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

    </form>
  </main>
</div>

<script>
const role = document.getElementById('role');
const employer = document.getElementById('employer-fields');
function toggle(){ employer.style.display = role.value === 'employer' ? 'block' : 'none'; }
role.addEventListener('change', toggle);
toggle();
</script>

</body>
</html>
