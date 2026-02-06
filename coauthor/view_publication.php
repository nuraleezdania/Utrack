<?php
session_start();
include "../db_conn.php";

// --- Connection Fallback ---
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}
// --------------------------

if (!isset($_GET['id'])) { header("Location: coauthor_dashboard.php"); exit(); }
$id = $_GET['id'];

// Fetch Data
$sql = "SELECT p.*, u.fullname as main_author_name 
        FROM publications p
        JOIN users u ON p.user_id = u.id
        WHERE p.id='$id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Check if file physically exists on server
$file_path = $row['file_path'];
$full_file_path = "../uploads/" . $file_path; // Internal path for checking
$file_exists = false;

if (!empty($file_path) && file_exists($full_file_path)) {
    $file_exists = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Details - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .details-container { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .read-only-field { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .read-only-label { font-weight: bold; color: #555; display: block; }
        .read-only-value { font-size: 1.1rem; color: #333; }
        .action-panel { background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd; height: fit-content; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Co-author</h2>
        <a href="coauthor_dashboard.php" class="active">Back to List</a>
    </div>

    <div class="main-content">
        <h1>Publication Details</h1>

        <div class="details-container">
            <div class="form-container" style="max-width: 100%;">
                <h3 style="margin-top:0; color:var(--primary-color);">Metadata</h3>
                
                <div class="read-only-field">
                    <span class="read-only-label">Title</span>
                    <span class="read-only-value"><?php echo htmlspecialchars($row['title']); ?></span>
                </div>
                <div class="read-only-field">
                    <span class="read-only-label">Main Author</span>
                    <span class="read-only-value"><?php echo htmlspecialchars($row['main_author_name']); ?></span>
                </div>
                <div class="read-only-field">
                    <span class="read-only-label">Authors</span>
                    <span class="read-only-value"><?php echo htmlspecialchars($row['authors']); ?></span>
                </div>
                <div class="read-only-field">
                    <span class="read-only-label">Venue</span>
                    <span class="read-only-value"><?php echo htmlspecialchars($row['venue']); ?></span>
                </div>
                <div class="read-only-field">
                    <span class="read-only-label">Status</span>
                    <span class="badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo $row['status']; ?></span>
                </div>
            </div>

            <div class="action-panel">
                <h3>Actions</h3>
                <p>Manage the document file.</p>
                
                <?php if($file_exists): ?>
                    <a href="/utrack/uploads/<?php echo $row['file_path']; ?>" target="_blank" class="btn-primary" style="display:block; text-align:center; text-decoration:none; margin-bottom:10px;">
                        ⬇ Download / View PDF
                    </a>
                <?php else: ?>
                    <button class="btn-secondary" disabled style="width:100%; background:#ccc; cursor:not-allowed;">
                        <?php echo $row['file_path'] ? "File Missing on Server" : "No File Uploaded"; ?>
                    </button>
                    <?php if($row['file_path']): ?>
                        <p style="color:red; font-size:0.8rem; margin-top:5px;">Error: Database has filename but file is not in 'uploads' folder.</p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <button class="btn-secondary" style="width:100%;" onclick="window.print()">🖨 Print Preview</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>