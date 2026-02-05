<?php
session_start();
include "../db_conn.php"; // Keeps your connection logic separate (recommended)

// --- BACKEND LOGIC STARTS HERE ---
if (isset($_POST['save_publication'])) {
    
    // 1. Get Data
    // Use a default ID (e.g., 15) if no user is logged in yet
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 15; 
    
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $authors = mysqli_real_escape_string($conn, $_POST['authors']);
    $year = $_POST['year'];
    $indexing = $_POST['indexing'];
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);

    // 2. Insert into DB
    $sql = "INSERT INTO publications (user_id, title, authors, year, indexing_type, venue, status) 
            VALUES ('$user_id', '$title', '$authors', '$year', '$indexing', '$venue', 'Pending Upload')";

    if (mysqli_query($conn, $sql)) {
        // 3. Success -> Go to Upload Page
        $last_id = mysqli_insert_id($conn);
        header("Location: upload_documents.php?id=$last_id");
        exit();
    } else {
        $error_msg = "Database Error: " . mysqli_error($conn);
    }
}
// --- BACKEND LOGIC ENDS ---
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
    <a href="add_publication.php" class="active">Add New Publication</a>
    <a href="my_publications.php">My Publications</a>
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
                    <input type="text" name="authors" placeholder="e.g. Ali, Siti" required>
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