<?php
include 'db_config.php';

if (!isset($_GET['id'])) { header("Location: verify_list.php"); exit; }
$id = $_GET['id'];

// Using 'id' to find the specific publication
$stmt = $pdo->prepare("SELECT * FROM publications WHERE id = ?");
$stmt->execute([$id]);
$pub = $stmt->fetch();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status']; 
    $remarks = $_POST['remarks'];

    // Update using 'id'
    $update = $pdo->prepare("UPDATE publications SET status = ?, remarks = ? WHERE id = ?");
    $update->execute([$status, $remarks, $id]);

    echo "<script>alert('Review Submitted: $status'); window.location='verify_list.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Publication - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .split-view { display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px; }
        .doc-preview { height: 500px; background: #525659; display: flex; align-items: center; justify-content: center; border-radius: 8px; color: white; flex-direction: column;}
        .checklist-box { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php">Monitor Output</a>
        <a href="verify_list.php">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../index.html" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Review Submission #<?php echo $pub['id']; ?></h1>
        <div class="split-view">
            <div>
                <div class="card" style="text-align: left; margin-bottom: 20px;">
                    <h3>Publication Details</h3>
                    <p><strong>Title:</strong> <?php echo htmlspecialchars($pub['title']); ?></p>
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($pub['authors']); ?></p>
                    <p><strong>Indexing:</strong> <?php echo $pub['indexing_type']; ?></p>
                </div>

                <form method="POST">
                    <div class="checklist-box">
                        <label><input type="checkbox" required> Information matches document</label><br>
                        <label><input type="checkbox" required> DOI/Link is valid</label>
                    </div>
                    <textarea name="remarks" rows="4" style="width:100%;" placeholder="Add remarks..." required></textarea>
                    <div style="margin-top: 10px; display: flex; gap: 10px;">
                        <button type="submit" name="status" value="accepted" class="btn-primary" style="background: green; flex:1;">Approve</button>
                        <button type="submit" name="status" value="rejected" class="btn-danger" style="flex:1;">Reject</button>
                    </div>
                </form>
            </div>

            <div class="doc-preview">
                <p>File: <?php echo $pub['file_path']; ?></p>
                <a href="../uploads/<?php echo $pub['file_path']; ?>" target="_blank" class="btn-secondary">Open Document</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>