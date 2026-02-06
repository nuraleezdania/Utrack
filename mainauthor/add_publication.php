<?php
session_start();
include "../db_conn.php"; 

// --- Connection Safety Check ---
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}

// --- Strict Security Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_POST['save_publication'])) {
    
    $user_id = $_SESSION['user_id']; 
    
    // Sanitize Inputs
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $authors  = mysqli_real_escape_string($conn, $_POST['authors']);
    $year     = mysqli_real_escape_string($conn, $_POST['year']);
    $indexing = mysqli_real_escape_string($conn, $_POST['indexing']);
    $venue    = mysqli_real_escape_string($conn, $_POST['venue']);
    $doi      = mysqli_real_escape_string($conn, $_POST['doi']);
    
    // NEW: Capture Citation Count
    $citations = (int)$_POST['citations'];

    // Insert with Citations
    $sql = "INSERT INTO publications (user_id, title, authors, year, indexing_type, venue, doi, citations, status) 
            VALUES ('$user_id', '$title', '$authors', '$year', '$indexing', '$venue', '$doi', '$citations', 'Pending Upload')";

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
        <a href="mainauthor_dashboard.php">Dashboard</a>
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
                    <label>DOI (Optional)</label>
                    <input type="text" name="doi" placeholder="e.g. 10.1109/ACCESS.2025.12345">
                </div>
                
                <div class="form-group">
                    <label>Current Citations</label>
                    <input type="number" name="citations" value="0" min="0">
                </div>

                <div class="form-group">
                    <label>Indexing</label>
                    <select name="indexing">
                        <option value="Scopus">Scopus</option>
                        <option value="WoS">Web of Science</option>
                        <option value="ERA">ERA</option>
                        <option value="Other">Other</option>
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