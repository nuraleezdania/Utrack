<?php 
include "../db_conn.php";

// 1. Get the ID from URL (to know which publication we are editing)
if(isset($_GET['id'])) {
    $pub_id = $_GET['id'];
} else {
    // If no ID provided, redirect back or show error
    header("Location: add_publication.php");
    exit();
}

// --- BACKEND LOGIC: FILE UPLOAD ---
if (isset($_POST['upload_btn'])) {
    
    // File Details
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $error = $_FILES['file']['error'];

    if ($error === 0) {
        if ($file_size > 20000000) { // 20MB Limit
            $msg = "File too large!";
        } else {
            // Generate unique name
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_name = uniqid("PUB-", true) . '.' . $file_ext;
            $path = '../uploads/' . $new_name;

            // Move File & Update DB
            if(move_uploaded_file($file_tmp, $path)) {
                $sql = "UPDATE publications 
                        SET file_path='$new_name', status='Pending Verification' 
                        WHERE id='$pub_id'";
                mysqli_query($conn, $sql);
                
                // Done! Go to list
                echo "<script>alert('Success!'); window.location.href='../mainauthor/my_publications.php';</script>";
            } else {
                $msg = "Failed to move file to folder.";
            }
        }
    } else {
        $msg = "Error uploading file.";
    }
}
// --- END LOGIC ---

// Fetch Title for display (Optional visual touch)
$sql_fetch = "SELECT title FROM publications WHERE id='$pub_id'";
$res = mysqli_query($conn, $sql_fetch);
$row = mysqli_fetch_assoc($res);
$pub_title = $row['title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Documents - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="../auth/author_dashboard.php">Dashboard</a>
        <a href="add_publication.php" class="active">Add New Publication</a>
        <a href="my_publications.php">My Publications</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Step 2: Upload Supporting Documents</h1>
        
        <?php if(isset($msg)) { echo "<p style='color:red;'>$msg</p>"; } ?>

        <div class="form-container">
            <div class="card" style="text-align:left; margin-bottom:20px; background:#eef2f6; padding:15px;">
                <p><strong>Publication:</strong> <?php echo $pub_title; ?></p>
                <p><strong>Status:</strong> <span class="badge status-pending">Pending Upload</span></p>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Select Document (PDF/Word)</label>
                    <input type="file" name="file" accept=".pdf,.doc,.docx" required>
                    <small>Max size: 20MB</small>
                </div>

                <button type="submit" name="upload_btn" class="btn-primary">Upload & Submit</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>