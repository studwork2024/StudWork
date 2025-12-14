<?php
session_start();

// 🔒 Protect page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

// ✅ Database connection
include 'config.php';

// ✅ Fetch student data using session email
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT fullname FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: logout.php');
    exit;
}

$user = $result->fetch_assoc();
$fullname = $user['fullname'];
$stmt->close();

$message = '';

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Handle credentials submission ---
    if (isset($_POST['phone'], $_POST['email_form'], $_FILES['resume'], $_FILES['valid_id'], $_FILES['school_id'])) {
        $phone = $_POST['phone'];
        $email_form = $_POST['email_form'];

        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $timestamp = time();
        $resume_path = $upload_dir . $timestamp . '_' . basename($_FILES['resume']['name']);
        $valid_id_path = $upload_dir . $timestamp . '_' . basename($_FILES['valid_id']['name']);
        $school_id_path = $upload_dir . $timestamp . '_' . basename($_FILES['school_id']['name']);

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path) &&
            move_uploaded_file($_FILES['valid_id']['tmp_name'], $valid_id_path) &&
            move_uploaded_file($_FILES['school_id']['tmp_name'], $school_id_path)) {

            $stmt = $conn->prepare("INSERT INTO student_submissions 
                (student_email, phone, email, resume, valid_id, school_id, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssss", $email, $phone, $email_form, $resume_path, $valid_id_path, $school_id_path);
            $message = $stmt->execute() ? 'Submission successful!' : 'Failed to save submission.';
            $stmt->close();
        } else {
            $message = 'Failed to upload files.';
        }
    }

    // --- Handle job application ---
    if (isset($_POST['apply_job'], $_POST['job_id'], $_POST['employer_email'])) {
        $job_id = intval($_POST['job_id']);
        $employer_email = $_POST['employer_email'];

        $check = $conn->prepare("SELECT id FROM job_applications WHERE job_id = ? AND student_email = ?");
        $check->bind_param("is", $job_id, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            $apply = $conn->prepare("INSERT INTO job_applications 
                (job_id, employer_email, student_email, status, applied_at) 
                VALUES (?, ?, ?, 'pending', NOW())");
            $apply->bind_param("iss", $job_id, $employer_email, $email);
            $apply->execute();
            $apply->close();
            $message = "Application submitted. Status: Pending.";
        } else {
            $message = "You have already applied for this job.";
        }
        $check->close();
    }
}

// ✅ Fetch available job posts
$jobs = [];
$jobStmt = $conn->prepare("SELECT id, employer_email, job_title, job_type, job_description, posted_at FROM job_posts ORDER BY posted_at DESC");
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
while ($row = $jobResult->fetch_assoc()) {
    $jobs[] = $row;
}
$jobStmt->close();

// ✅ Fetch all student applications with status
$applications = [];
$stmt = $conn->prepare("
    SELECT ja.job_id, ja.status
    FROM job_applications ja
    WHERE ja.student_email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $applications[$row['job_id']] = $row['status']; // key: job_id, value: status
}
$stmt->close();

// ✅ Fetch pending applications details
$pending = [];
$stmt = $conn->prepare("
    SELECT ja.job_id, jp.job_title, jp.job_type, ja.status, ja.applied_at
    FROM job_applications ja
    JOIN job_posts jp ON jp.id = ja.job_id
    WHERE ja.student_email = ?
    ORDER BY ja.applied_at DESC
");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $pending[] = $row;
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Student Dashboard — StudWork</title>
<style>
:root{--maroon:#7a0000;--white:#ffffff;--muted:#fdf6f6;}
body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:var(--muted);color:var(--maroon);}
.topbar{background:var(--maroon);color:var(--white);width:100%;position:fixed;top:0;left:0;right:0;z-index:1000;}
.topbar .wrap{max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:12px 20px;height:64px;}
.brand-container{display:flex;align-items:center;gap:10px;}
.brand-logo{ width: clamp(32px, 5vw, 52px); height: clamp(32px, 5vw, 52px); border-radius: 50%; background: linear-gradient(180deg, #ffd96b, #ffb83b); display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);}
.brand-logo .logo-text {fill: #fff; font-weight: 800; font-size: .6em;}
.brand-logo svg{width:70%;height:70%;}
.brand .title{font-weight:800;font-size:1.1rem;}
.brand .tag{font-size:0.85rem;color:#ffdcbc;}
.user-info{display:flex;align-items:center;gap:12px;}
.user-info span{font-weight:600;}
.user-info a{color:#fff;text-decoration:none;font-weight:600;padding:6px 12px;border:1px solid #fff;border-radius:8px;}
.main-wrap{margin-top:80px;padding:24px;display:flex; flex-direction:column; max-width:1100px; margin-inline:auto;}
.submission-form{display:flex;gap:24px;flex-wrap:wrap;}
.form-card{flex:1;min-width:280px;background:#fff;padding:20px;border-radius:16px;border:2px solid #ddd;box-shadow:0 6px 18px rgba(0,0,0,0.06);}
.form-card h3{margin-top:0;margin-bottom:16px;color:var(--maroon);font-size:1.1rem;font-weight:700;}
.form-card label{display:block;font-weight:600;margin-bottom:4px;}
.form-card input[type="text"],.form-card input[type="email"],.form-card input[type="file"]{width:-webkit-fill-available;padding:10px;margin-top:6px;border-radius:8px;border:1px solid #ccc;}
.submit-btn{background:var(--maroon);color:#fff;border:none;padding:12px 24px;border-radius:12px;font-weight:700;cursor:pointer;transition:0.3s;width: fit-content;
    margin-left: auto;}
.submit-btn:hover{background:#5c0000;}
@media(max-width:720px){.submission-form{flex-direction:column;}}
.file-row{display:flex;gap:16px;margin-top:16px; justify-content:space-between;}
.file-row .file-div{display:flex;flex-direction:column;align-items:center;}
.upload_file{width:150px;cursor:pointer;}
.dropdowns{display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap;justify-content: end; align-items:center;}
.dropdowns select{padding:8px 12px;border-radius:8px;border:1px solid #ccc;cursor:pointer;}
.dropdowns select:hover{border-color:var(--maroon);}
.app-section{display:none;gap:15px;flex-wrap:wrap;}
</style>
<script>
function previewFile(input, previewId) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e){
            document.getElementById(previewId).src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
function toggleSection() {
    const type = document.getElementById('type-dropdown').value;
    document.getElementById('credentials-section').style.display = type === 'credentials' ? 'block' : 'none';
    document.getElementById('application-section').style.display = type === 'application' ? 'flex' : 'none';
}
window.addEventListener('DOMContentLoaded', toggleSection);
</script>
</head>
<body>
<header class="topbar">
<div class="wrap">
<div class="brand-container">
    <div class="brand-logo">
        <svg viewBox="0 0 24 24"><text x="12" y="12" text-anchor="middle" dominant-baseline="middle" class="logo-text">SW</text></svg>
    </div>
<div class="brand">
<div class="title">STUDWORK</div>
<div class="tag">Connecting Students and Employers</div>
</div>
</div>
<div class="user-info">
<h3>Welcome, <?= htmlspecialchars($fullname) ?></h3>
<a href="logout.php">Logout</a>
</div>
</div>
</header>

<main class="main-wrap">
<div class="dropdowns">
    <label for="type-dropdown">Select:</label>
    <select id="type-dropdown" onchange="toggleSection()">
        <option value="credentials">Credentials</option>
        <option value="application">Application</option>
    </select>
</div>

<?php if ($message): ?>
<div style="padding:10px;background:#eaf8ef;border-left:4px solid #3ca66b;margin-bottom:16px;">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Credentials Form -->
<div id="credentials-section">
<form method="POST" enctype="multipart/form-data" class="submission-form">
    <div class="form-card">
        <h3>Documents</h3>
        <div class="file-row">
            <div class="file-div">
                <label>Resume / CV</label>
                <img class="upload_file" id="resume-preview" src="uploads/upload_image.jpg" onclick="document.getElementById('resume').click();">
                <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx" required onchange="previewFile(this,'resume-preview')" hidden>
            </div>
            <div class="file-div">
                <label>Valid ID</label>
                <img class="upload_file" id="valid-id-preview" src="uploads/upload_image.jpg" onclick="document.getElementById('valid_id').click();">
                <input type="file" name="valid_id" id="valid_id" accept=".jpg,.jpeg,.png,.pdf" required onchange="previewFile(this,'valid-id-preview')" hidden>
            </div>
        </div>
        <div class="file-row" style="justify-content:center;">
            <div class="file-div">
                <label>School ID</label>
                <img class="upload_file" id="school-id-preview" src="uploads/upload_image.jpg" onclick="document.getElementById('school_id').click();">
                <input type="file" name="school_id" id="school_id" accept=".jpg,.jpeg,.png,.pdf" required onchange="previewFile(this,'school-id-preview')" hidden>
            </div>
        </div>
    </div>
    <div class="form-card">
        <h3>Contact Info</h3>
        <label>Phone Number</label>
        <input type="text" name="phone" required>
        <label>Email</label>
        <input type="email" name="email_form" required>
    </div>
    <div style="flex-basis:100%;text-align:center;margin-top:20px;">
        <button type="submit" class="submit-btn">Submit</button>
    </div>
</form>
</div>

<!-- Application Section -->
<div id="application-section" class="app-section">
    <div class="form-card">
        <h3>Available Jobs</h3>
        <div style="display:flex;flex-direction:column;gap:12px;max-height:300px;overflow-y:auto;">
            <?php if(empty($jobs)): ?>
                <div style="padding:12px;background:#f0f0f0;border-radius:8px;text-align:center;">No job postings available at the moment.</div>
            <?php else: ?>
                <?php foreach($jobs as $job): ?>
                    <div style="padding:14px;border:1px solid #ddd;border-radius:12px;background:#fff;display:flex;flex-direction:column;">
                        <h4 style="margin:0 0 6px;color:#7a0000;"><?= htmlspecialchars($job['job_title']) ?></h4>
                        <div style="font-size:0.9rem;color:#555;">
                            <strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?><br>
                            <strong>Employer:</strong> <?= htmlspecialchars($job['employer_email']) ?><br>
                            <strong>Posted:</strong> <?= date('M d, Y', strtotime($job['posted_at'])) ?><br>
                            <strong>Description:</strong><br><?= htmlspecialchars($job['job_description']) ?>
                        </div>

                        <?php
                        if(isset($applications[$job['id']])) {
                            $status = $applications[$job['id']];
                            $text = ucfirst($status);
                            $color = '#aaa';
                            if($status=='accepted') $color = '#3ca66b';
                            if($status=='rejected') $color = '#d9534f';
                            echo "<button class='submit-btn' disabled style='margin-top:8px;background:$color;'>$text</button>";
                        } else {
                        ?>
                            <form method="POST" style="margin-left:auto;margin-top:8px;">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <input type="hidden" name="employer_email" value="<?= htmlspecialchars($job['employer_email']) ?>">
                                <button type="submit" name="apply_job" class="submit-btn">Apply</button>
                            </form>
                        <?php } ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-card">
        <h3>Pending Applications</h3>
        <?php if(empty($pending)): ?>
            <div style="padding:12px;background:#f0f0f0;border-radius:8px;text-align:center;">No pending applications.</div>
        <?php else: ?>
            <?php foreach($pending as $p): ?>
                <div style="padding:12px;border:1px solid #ddd;border-radius:10px;margin-bottom:10px;">
                    <strong><?= htmlspecialchars($p['job_title']) ?></strong><br>
                    <small><?= htmlspecialchars($p['job_type']) ?></small><br><br>
                    <?php
                    $status = $p['status'];
                    $text = ucfirst($status);
                    $color = '#aaa';
                    if($status=='accepted') $color = '#3ca66b';
                    if($status=='rejected') $color = '#d9534f';
                    ?>
                    <button class="submit-btn" disabled style="padding:6px 14px;font-size:0.8rem;background:<?= $color ?>;"><?= $text ?></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</main>
</body>
</html>
