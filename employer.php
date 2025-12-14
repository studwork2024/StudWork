<?php
session_start();

// 🔒 Protect page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employer') {
    header('Location: login.php');
    exit;
}

// ✅ Database connection
$conn = new mysqli("localhost", "root", "", "db_studwork");
if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);

// ✅ Fetch employer data
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT fullname, company_name FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header('Location: logout.php'); exit;
}
$user = $result->fetch_assoc();
$fullname = $user['fullname'];
$company_name = $user['company_name'] ?? '';
$stmt->close();

$message = '';
$activeSection = 'credentials'; // default section


// ===== Handle Credentials Upload =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credentials_submit'])) {
    $activeSection = 'credentials';
    $telephone = $_POST['telephone'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $docs = [
        'hiring_doc' => 'Hiring & Onboarding Docs',
        'termination_doc' => 'Termination & Exit Docs',
        'contracts_doc' => 'Employment Contracts',
        'privacy_doc' => 'Privacy & Compliance Docs'
    ];

    $all_uploaded = true;

    foreach ($docs as $input_name => $label) {
        $file_path = null;

        // Only try to upload if file was provided
        if (!empty($_FILES[$input_name]['name'])) {
            $ext = pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION);
            $file_path = $upload_dir . $email . "_" . $input_name . "_" . time() . "." . $ext;

            if (!move_uploaded_file($_FILES[$input_name]['tmp_name'], $file_path)) {
                $all_uploaded = false;
                $message = "Failed to upload $label.";
                continue; // skip DB insert for this file
            }
        }

        // Check if document exists
        $check = $conn->prepare("SELECT id FROM employer_documents WHERE employer_email=? AND document_name=? LIMIT 1");
        $check->bind_param("ss", $email, $label);
        $check->execute();
        $res = $check->get_result();
        $check->close();

        if ($res->num_rows > 0) {
            // Update existing
            $row = $res->fetch_assoc();
            $doc_id = $row['id'];
            if ($file_path) {
                $stmt = $conn->prepare("UPDATE employer_documents SET file_path=?, telephone=?, mobile=? WHERE id=?");
                $stmt->bind_param("sssi", $file_path, $telephone, $mobile, $doc_id);
            } else {
                $stmt = $conn->prepare("UPDATE employer_documents SET telephone=?, mobile=? WHERE id=?");
                $stmt->bind_param("ssi", $telephone, $mobile, $doc_id);
            }
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO employer_documents (employer_email, document_name, file_path, telephone, mobile) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $email, $label, $file_path, $telephone, $mobile);
        }

        if (!$stmt->execute()) {
            $all_uploaded = false;
            $message = "Error saving $label: " . $stmt->error;
        }
        $stmt->close();
    }

    if ($all_uploaded && !$message) {
        $message = 'Documents saved successfully!';
    }
}


// ===== Handle Job Post Submission =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_submit'])) {
     $activeSection = 'application';
    $job_title = $_POST['job_title'] ?? '';
    $job_type = $_POST['job_type'] ?? '';
    $job_description = $_POST['job_description'] ?? '';

    if ($job_title && $job_type && $job_description) {
        $stmt = $conn->prepare("INSERT INTO job_posts (employer_email, job_title, job_type, job_description, posted_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $email, $job_title, $job_type, $job_description);
        if (!$stmt->execute()) {
            $message = "Error posting job: " . $stmt->error;
        } else {
            $message = 'Job posted successfully!';
            $_POST = [];
        }
        $stmt->close();
    } else {
        $message = 'Please fill all fields for the job post.';
    }
}

// ===== Fetch Submitted Jobs =====
$jobs = [];

$stmt = $conn->prepare("
    SELECT 
        jp.id,
        jp.job_title,
        jp.job_type,
        jp.job_description,
        jp.posted_at,
        COUNT(ja.id) AS applicant_count
    FROM job_posts jp
    LEFT JOIN job_applications ja ON ja.job_id = jp.id
    WHERE jp.employer_email = ?
    GROUP BY jp.id
    ORDER BY jp.posted_at DESC
");

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}
$stmt->close();


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Employer Dashboard — StudWork</title>
<style>
:root{--maroon:#7a0000;--white:#ffffff;--muted:#fdf6f6;}
body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:var(--muted);color:var(--maroon);}
.topbar{background:var(--maroon);color:var(--white);width:100%;position:fixed;top:0;left:0;right:0;z-index:1000;}
.topbar .wrap{max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:12px 20px;height:64px;}
.brand-container{display:flex;align-items:center;gap:10px;}
.brand-logo{ width: clamp(32px, 5vw, 52px);
    height: clamp(32px, 5vw, 52px);
    border-radius: 50%;
    background: linear-gradient(180deg, #ffd96b, #ffb83b);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);}
    .brand-logo .logo-text {
        fill: #fff;
    font-weight: 800;
    font-size: .6em;
    }
.brand-logo svg{width:70%;height:70%;}
.brand .title{font-weight:800;font-size:1.1rem;}
.brand .tag{font-size:0.85rem;color:#ffdcbc;}
.user-info{display:flex;align-items:center;gap:12px;}
.user-info span{font-weight:600;}
.user-info a{color:#fff;text-decoration:none;font-weight:600;padding:6px 12px;border:1px solid #fff;border-radius:8px;}
.main-wrap{margin-top:80px;padding:24px;max-width:1100px;margin-inline:auto;}
.submission-form{display:flex;gap:5px;flex-wrap:wrap;}
.form-card{flex:1;min-width:280px;background:#fff;padding:20px;border-radius:16px;border:2px solid #ddd;box-shadow:0 6px 18px rgba(0,0,0,0.06);}
.form-card h3{margin-top:0;margin-bottom:16px;color:var(--maroon);font-size:1.1rem;font-weight:700;}
.form-card label{display:block;font-weight:600;}
.form-card input[type="text"],.form-card textarea,.form-card input[type="file"],.form-card select{width:-webkit-fill-available;padding:10px;margin-top:6px;border-radius:8px;border:1px solid #ccc;}
.submit-btn{background:var(--maroon);color:#fff;border:none;padding:12px 24px;border-radius:12px;font-weight:700;cursor:pointer;transition:0.3s;}
.submit-btn:hover{background:#5c0000;}
@media(max-width:720px){.submission-form{flex-direction:column;}}
.file-row{display:flex;gap:16px;margin-top:16px; flex-wrap:wrap; justify-content:center;}
.file-row .file-div{display:flex;flex-direction:column;align-items:center;}
.upload_file{width:150px;cursor:pointer;}
.dropdowns{    display: flex;
    gap: 5px;
    margin-bottom: 15px;
    flex-wrap: wrap;
    align-items: center;
justify-content: end;}
.dropdowns select{padding:8px 12px;border-radius:8px;border:1px solid #ccc;cursor:pointer;}
.dropdowns select:hover{border-color:var(--maroon);}
.app-section{display:none; gap:15px; flex-wrap:wrap;}
.message{padding:10px;background:#eaf8ef;border-left:4px solid #3ca66b;margin-bottom:16px;}
#credentials-section .submission-form .contact-information input,
#credentials-section .submission-form .contact-information label{margin-bottom:6px;}
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
    document.getElementById('credentials-section').style.display =
        type === 'credentials' ? 'block' : 'none';
    document.getElementById('application-section').style.display =
        type === 'application' ? 'flex' : 'none';
}

window.addEventListener('DOMContentLoaded', function () {
    const active = "<?= $activeSection ?>";
    document.getElementById('type-dropdown').value = active;
    toggleSection();
});
</script>

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
        <div class="user-info">
            <h3>Welcome, <?= htmlspecialchars($fullname) ?></h3>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</header>

<main class="main-wrap">
<!-- Dropdown -->
<div class="dropdowns">
    <label for="type-dropdown">Select:</label>
    <select id="type-dropdown" onchange="toggleSection()">
        <option value="credentials">Credentials</option>
        <option value="application">Job Post</option>
    </select>
</div>

<?php if($message): ?>
<div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Credentials Section -->
<div id="credentials-section">
<form method="POST" enctype="multipart/form-data" class="submission-form">
    <div class="form-card">
        <h3>Upload Documents</h3>
        <div class="file-row">
            <?php
            $fields = [
                'hiring_doc' => 'Hiring & Onboarding Docs',
                'termination_doc' => 'Termination & Exit Docs',
                'contracts_doc' => 'Employment Contracts',
                'privacy_doc' => 'Privacy & Compliance Docs'
            ];
            foreach ($fields as $name => $label): ?>
            <div class="file-div">
                <label><?= $label ?></label>
                <img class="upload_file" id="<?= $name ?>-preview" src="uploads/upload_image.jpg" alt="Click to upload" onclick="document.getElementById('<?= $name ?>').click();">
                <input type="file" name="<?= $name ?>" id="<?= $name ?>" accept=".pdf,.doc,.docx,.jpg,.png" required onchange="previewFile(this,'<?= $name ?>-preview')" hidden>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-card contact-information">
        <h3>Contact Info</h3>
        <label>Telephone Number</label>
        <input type="text" name="telephone" placeholder="e.g. 02-1234567" required>
        <label>Mobile Number</label>
        <input type="text" name="mobile" placeholder="e.g. 09171234567" required>
    </div>

    <div style="flex-basis:100%;text-align:center;margin-top:20px;">
        <button type="submit" name="credentials_submit" class="submit-btn">Submit</button>
    </div>
</form>
</div>

<!-- Job Post Section -->
<div id="application-section" class="app-section">
    <div class="form-card">
        <h3>Post a Job</h3>
        <form method="POST" class="submission-form">
            <label>Job Title</label>
            <input type="text" name="job_title" required>
            <label>Job Type</label>
            <select name="job_type" required>
                <option value="Part-time">Part-time</option>
                <option value="Freelance">Freelance</option>
            </select>
            <label>Job Description</label>
            <textarea name="job_description" rows="5" required></textarea>
            <div style="flex-basis:100%;text-align:center;margin-top:20px;">
                <button type="submit" name="job_submit" class="submit-btn">Post Job</button>
            </div>
        </form>
    </div>

    <div class="form-card" style="padding-top:0px;max-height:462px; overflow-y:auto; position:relative;">
    <h3 style="position:sticky; top:0; background:#fff; padding:20px 0; z-index:10;">Submitted Job Posts</h3>
    
    <?php if(count($jobs) > 0): ?>
        <?php foreach($jobs as $job): ?>
          <div style="border:1px solid #ccc;padding:15px;margin-bottom:12px;
            border-radius:10px;position:relative;">

    <a href="view_applicants.php?job_id=<?= $job['id'] ?>"
       style="position:absolute;bottom:10px;right:10px;
              background:#7a0000;color:#fff;
              padding:6px 12px;border-radius:8px;
              text-decoration:none;font-size:0.85rem;">
        View Applicants (<?= $job['applicant_count'] ?>)
    </a>

    <strong><?= htmlspecialchars($job['job_title']) ?></strong>
    (<?= htmlspecialchars($job['job_type']) ?>)

    <p><?= htmlspecialchars($job['job_description']) ?></p>

    <small style="color:#888;">
        <?= date("F j, Y", strtotime($job['posted_at'])) ?>
    </small>
</div>

        <?php endforeach; ?>
    <?php else: ?>
        <div style="height:150px;background:#f0f0f0;border-radius:12px;display:flex;align-items:center;justify-content:center;">
            No job posts submitted yet.
        </div>
    <?php endif; ?>
</div>

</div>
</main>
</body>
</html>
