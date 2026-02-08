<?php
session_start();
include "../db_conn.php";

// --- CONNECTION FALLBACK ---
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}

// --- STRICT SECURITY CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) { header("Location: my_publications.php"); exit(); }
$id = $_GET['id'];

// --- RESUBMIT LOGIC ---
if (isset($_POST['resubmit_btn'])) {
    $doi = mysqli_real_escape_string($conn, $_POST['doi']);
    
    // File Upload is Mandatory for Resubmission
    $file_name = $_FILES['revisedFile']['name'];
    $file_tmp = $_FILES['revisedFile']['tmp_name'];
    $new_name = uniqid("RESUB-", true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
    
    // Correct Path
    if (move_uploaded_file($file_tmp, "../uploads/" . $new_name)) {
        // Update: Set status back to Pending, Add DOI, Update File Path
        $sql = "UPDATE publications 
                SET doi='$doi', file_path='$new_name', status='Pending Verification' 
                WHERE id='$id'";
        
        if(mysqli_query($conn, $sql)) {
            echo "<script>alert('Resubmitted successfully!'); window.location.href='my_publications.php';</script>";
        }
    } else {
        echo "<script>alert('File upload failed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resubmit - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="mainauthor_dashboard.php">Dashboard</a>
        <a href="add_publication.php">Add New Publication</a>
        <a href="my_publications.php" class="active">My Publications</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Resubmit Publication</h1>

        <div class="feedback-box" style="background: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin-top:0; color:#856404;">Coordinator Remarks</h3>
            <p><em>"Please address the previous issues (e.g. Missing DOI or Corrupt PDF) and re-upload."</em></p>
        </div>

        <div class="form-container">
            <h3>Make Corrections</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>DOI (Required Correction)</label>
                    <input type="text" name="doi" placeholder="Enter DOI..." required>
                </div>

                <div class="form-group">
                    <label>Upload Revised Document</label>
                    <input type="file" name="revisedFile" required>
                </div>

                <button type="submit" name="resubmit_btn" class="btn-primary">Resubmit Publication</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>