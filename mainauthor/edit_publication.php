<?php
session_start();
include "../db_conn.php";

// Check ID
if (!isset($_GET['id'])) {
    header("Location: my_publications.php");
    exit();
}
$id = $_GET['id'];

// --- UPDATE LOGIC ---
if (isset($_POST['update_btn'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $authors = mysqli_real_escape_string($conn, $_POST['authors']);
    $year = $_POST['year'];

    // Update Metadata
    $sql_update = "UPDATE publications SET title='$title', authors='$authors', year='$year' WHERE id='$id'";
    mysqli_query($conn, $sql_update);

    // Handle File Replacement
    if (!empty($_FILES['newFile']['name'])) {
        $file_name = $_FILES['newFile']['name'];
        $file_tmp = $_FILES['newFile']['tmp_name'];
        // Use ../uploads/ because this file is in mainauthor/
        $new_name = uniqid("PUB-", true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        
        if (move_uploaded_file($file_tmp, "../uploads/" . $new_name)) {
            $sql_file = "UPDATE publications SET file_path='$new_name' WHERE id='$id'";
            mysqli_query($conn, $sql_file);
        }
    }

    echo "<script>alert('Changes saved successfully.'); window.location.href='my_publications.php';</script>";
}

// --- FETCH EXISTING DATA ---
$sql_fetch = "SELECT * FROM publications WHERE id='$id'";
$result = mysqli_query($conn, $sql_fetch);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Publication - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="../auth/author_dashboard.php">Dashboard</a>
        <a href="add_publication.php">Add New Publication</a>
        <a href="my_publications.php" class="active">My Publications</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="header-flex">
            <h1>Edit Publication Details</h1>
            <a href="my_publications.php" class="btn-secondary">Back</a>
        </div>

        <div class="form-container">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Publication Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Authors</label>
                    <p style="font-size: 0.8rem; color: #666; margin: 2px 0 5px;">*Ensure names match exactly to link Co-Authors.</p>
                    <input type="text" name="authors" value="<?php echo htmlspecialchars($row['authors']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" value="<?php echo $row['year']; ?>">
                </div>

                <div class="form-group" style="background-color: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px dashed #ccc;">
                    <label>Current File</label>
                    <p style="margin: 5px 0 10px 0; font-size: 0.9rem; color: #555;">
                        <?php if($row['file_path']): ?>
                            <a href="../uploads/<?php echo $row['file_path']; ?>" target="_blank">View Current Document</a>
                        <?php else: ?>
                            No file uploaded.
                        <?php endif; ?>
                    </p>
                    
                    <label style="color: var(--primary-color);">Replace Document (Optional)</label>
                    <input type="file" name="newFile" accept=".pdf,.doc,.docx">
                </div>

                <button type="submit" name="update_btn" class="btn-primary" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>