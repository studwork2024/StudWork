<?php
session_start();

// 🔒 Protect page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employer') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "", "db_studwork");
if ($conn->connect_error) {
    die("Database connection failed");
}

$employer_email = $_SESSION['email'];
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// 🔐 Verify job belongs to employer
$check = $conn->prepare("
    SELECT id, job_title 
    FROM job_posts 
    WHERE id=? AND employer_email=?
");
$check->bind_param("is", $job_id, $employer_email);
$check->execute();
$job = $check->get_result()->fetch_assoc();
$check->close();

if (!$job) {
    die("Unauthorized access.");
}

// ================= HANDLE ACCEPT / REJECT =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {

    $application_id = intval($_POST['application_id']);
    $status = null;

    if (isset($_POST['accept'])) {
        $status = 'accepted';
    }
    if (isset($_POST['reject'])) {
        $status = 'rejected';
    }

    if ($status) {
        $stmt = $conn->prepare("
            UPDATE job_applications
            SET status=?
            WHERE id=? AND employer_email=?
        ");
        $stmt->bind_param("sis", $status, $application_id, $employer_email);
        $stmt->execute();
        $stmt->close();
    }
}

// ================= FETCH APPLICANTS =================
$stmt = $conn->prepare("
    SELECT 
        ja.id AS application_id,
        ja.status,
        ja.applied_at,

        u.fullname,
        u.email AS student_email,

        ss.phone,
        ss.resume,
        ss.valid_id,
        ss.school_id

    FROM job_applications ja
    JOIN users u ON u.email = ja.student_email
    LEFT JOIN student_submissions ss ON ss.student_email = ja.student_email

    WHERE ja.job_id = ?
    ORDER BY ja.applied_at DESC
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$applicants = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Applicants — <?= htmlspecialchars($job['job_title']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:Inter,Arial;background:#fdf6f6;margin:0;padding:20px}
.card{background:#fff;padding:18px;border-radius:12px;border:1px solid #ccc;margin-bottom:15px}
.status{font-weight:700}
.accept{background:#28a745;color:#fff;border:none;padding:6px 14px;border-radius:6px}
.reject{background:#dc3545;color:#fff;border:none;padding:6px 14px;border-radius:6px}
.back{display:inline-block;margin-bottom:20px;text-decoration:none;color:#7a0000;font-weight:700}
</style>
</head>

<body>

<a href="employer.php" class="back">← Back to Jobs</a>

<h2>Applicants for: <?= htmlspecialchars($job['job_title']) ?></h2>

<?php if ($applicants->num_rows === 0): ?>
    <p>No applicants yet.</p>
<?php endif; ?>

<?php while ($row = $applicants->fetch_assoc()): ?>
<div class="card">

    <strong><?= htmlspecialchars($row['fullname']) ?></strong><br>
    <small><?= htmlspecialchars($row['student_email']) ?></small><br><br>

    <span class="status">
        Status:
        <span style="color:
            <?= $row['status']=='accepted'?'green':
               ($row['status']=='rejected'?'red':'orange') ?>">
            <?= strtoupper($row['status']) ?>
        </span>
    </span>

    <p>
        📞 <?= htmlspecialchars($row['phone'] ?? 'N/A') ?><br>
        📄 <a href="<?= htmlspecialchars($row['resume']) ?>" target="_blank">Resume</a><br>
        🪪 <a href="<?= htmlspecialchars($row['valid_id']) ?>" target="_blank">Valid ID</a><br>
        🏫 School ID: <?= htmlspecialchars($row['school_id'] ?? 'N/A') ?>
    </p>

    <?php if ($row['status'] === 'pending'): ?>
    <form method="POST" style="display:flex;gap:10px;">
        <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
        <button type="submit" name="accept" class="accept">Accept</button>
        <button type="submit" name="reject" class="reject">Reject</button>
    </form>
    <?php endif; ?>

</div>
<?php endwhile; ?>

</body>
</html>
