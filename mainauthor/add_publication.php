<?php
session_start();
include "../db_conn.php"; 

if (isset($_POST['save_publication'])) {
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 15; 
    
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $authors = mysqli_real_escape_string($conn, $_POST['authors']);
    $year = $_POST['year'];
    $indexing = $_POST['indexing'];
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);

    $sql = "INSERT INTO publications (user_id, title, authors, year, indexing_type, venue, status) 
            VALUES ('$user_id', '$title', '$authors', '$year', '$indexing', '$venue', 'Pending Upload')";

    if (mysqli_query($conn, $sql)) {
        $last_id = mysqli_insert_id($conn);
        header("Location: upload_documents.php?id=$last_id");
        exit();
    } else {
        $error_msg = "Database Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Publication - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="../auth/author_dashboard.php">Dashboard</a>
        <a href="my_publications.php">My Publications</a>
        <a href="add_publication.php" class="active">Add New Publication</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Step 1: Publication Details</h1>

        <?php if(isset($error_msg)) { echo "<p style='color:red;'>$error_msg</p>"; } ?>

        <div class="form-container">
            <form action="" method="POST">
                <div class="form-group">
                    <label>Publication Title</label>
                    <input type="text" name="title" required>
                </div>
                
                <div class="form-group">
                    <label>Authors</label>
                    <p style="font-size: 0.85rem; color: #666; margin-top: 2px; margin-bottom: 8px; background: #eef2f6; padding: 8px; border-radius: 4px;">
                        <strong>💡 How to Connect Co-Authors:</strong><br>
                        Type the <strong>Exact Full Name</strong> of your co-authors (as registered in UTrack) separated by commas.<br>
                        <em>Example: Ali Bin Ahmad, Siti Sarah</em>
                    </p>
                    <input type="text" name="authors" placeholder="e.g. Ali Bin Ahmad, Siti Sarah" required>
                </div>

                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" value="2026" required>
                </div>
                <div class="form-group">
                    <label>Indexing</label>
                    <select name="indexing">
                        <option value="Scopus">Scopus</option>
                        <option value="WoS">Web of Science</option>
                        <option value="ERA">ERA</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Venue (Journal/Conference)</label>
                    <input type="text" name="venue" required>
                </div>

                <button type="submit" name="save_publication" class="btn-primary">Save & Proceed to Upload</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>