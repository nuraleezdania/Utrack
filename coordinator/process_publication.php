<?php
// --- ROBUST 24-HOUR SESSION ---
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);

session_start();

// Manual Timeout Check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) {
    session_unset(); session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

include "../db_conn.php";

// Connection Fallback
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}

if (!isset($_GET['id'])) {
    header("Location: verify_list.php");
    exit();
}
$pub_id = $_GET['id'];

// Fetch Data
$sql = "SELECT * FROM publications WHERE id='$pub_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "Publication not found.";
    exit();
}

// Handle Approve/Reject
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    // --- 1. NEW VALIDATION LOGIC ---
    if ($action === 'approve') {
        // Check if both boxes are ticked
        if (!isset($_POST['check_info']) || !isset($_POST['check_doi'])) {
            echo "<script>
                    alert('You must tick both checkboxes to verify the document before Approving.'); 
                    window.history.back();
                  </script>";
            exit(); // Stop execution
        }
        $status = 'Approved';
    } else {
        // Rejection does not require checkboxes
        $status = 'Rejected';
    }
    
    $update_sql = "UPDATE publications SET status='$status', remarks='$remarks' WHERE id='$pub_id'";
    mysqli_query($conn, $update_sql);
    
    echo "<script>alert('Publication $status!'); window.location.href='verify_list.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Submission - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .details-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .document-viewer { background: #555; height: 500px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; flex-direction: column; }
        .label { font-weight: bold; color: #555; display: block; margin-top: 15px; }
        .value { color: #333; font-size: 1.05rem; display: block; margin-bottom: 5px; }
        .checklist { margin: 20px 0; background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php">Monitor Output</a>
        <a href="verify_list.php" class="active">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Review Submission #<?php echo $row['id']; ?></h1>

        <div class="review-grid">
            <div class="details-card">
                <h2 style="margin-top:0; color:var(--primary-color);">Publication Details</h2>
                
                <span class="label">Title:</span>
                <span class="value"><?php echo htmlspecialchars($row['title']); ?></span>

                <span class="label">Authors:</span>
                <span class="value"><?php echo htmlspecialchars($row['authors']); ?></span>
                
                <span class="label">Year:</span>
                <span class="value"><?php echo htmlspecialchars($row['year']); ?></span>

                <span class="label">Indexing:</span>
                <span class="value"><?php echo htmlspecialchars($row['indexing_type']); ?></span>

                <span class="label">Venue:</span>
                <span class="value"><?php echo htmlspecialchars($row['venue']); ?></span>
                
                <span class="label">Current Citations:</span>
                <span class="value"><?php echo htmlspecialchars($row['citations']); ?></span>

                <span class="label">DOI:</span>
                <span class="value">
                    <?php 
                    if (!empty($row['doi'])) {
                        $doi_link = strpos($row['doi'], 'http') === 0 ? $row['doi'] : "https://doi.org/" . $row['doi'];
                        echo '<a href="' . $doi_link . '" target="_blank" style="color:var(--primary-color);">' . htmlspecialchars($row['doi']) . '</a>';
                    } else {
                        echo '<span style="color:#999;">N/A</span>';
                    }
                    ?>
                </span>

                <hr style="margin: 20px 0;">

                <form method="POST">
                    <div class="checklist">
                        <label><input type="checkbox" name="check_info" value="1"> Information matches document</label><br>
                        <label><input type="checkbox" name="check_doi" value="1"> DOI/Link is valid</label>
                    </div>

                    <textarea name="remarks" class="form-control" placeholder="Add remarks (optional)..." style="height:80px; margin-bottom:15px;"></textarea>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="action" value="approve" class="btn-primary" style="background-color: #28a745; width: 100%;">Approve</button>
                        <button type="submit" name="action" value="reject" class="btn-secondary" style="background-color: #dc3545; color: white; width: 100%;">Reject</button>
                    </div>
                </form>
            </div>

            <div class="document-viewer">
                <?php if($row['file_path']): ?>
                    <p>File: <?php echo htmlspecialchars($row['file_path']); ?></p>
                    <a href="/utrack/uploads/<?php echo $row['file_path']; ?>" target="_blank" class="btn-primary" style="background: white; color: #333; text-decoration:none;">Open Document</a>
                <?php else: ?>
                    <p>No document uploaded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>