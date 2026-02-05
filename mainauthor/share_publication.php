<?php
session_start();
include "../db_conn.php";
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 15;

// Only fetch APPROVED publications
$sql = "SELECT title FROM publications WHERE user_id='$user_id' AND status='Approved'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Share Publication - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .platform-btn { width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid #ccc; background: white; cursor: pointer; border-radius: 8px; font-size: 1.1rem; opacity: 0.5; pointer-events: none; }
        .platform-btn.active { opacity: 1; pointer-events: auto; }
        .linkedin { color: #0077b5; border-color: #0077b5; }
        .researchgate { color: #00ccbb; border-color: #00ccbb; }
        .orcid { color: #a6ce39; border-color: #a6ce39; }
    </style>
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
        <h1>Share Research</h1>
        
        <div class="share-container" style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="form-group" style="text-align: left; margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
                <label style="font-weight: bold; font-size: 1.1rem; margin-bottom: 10px; display: block;">Step 1: Select Approved Publication</label>
                
                <select id="pubSelect" class="form-control" onchange="enableSharing()" style="font-size: 1rem; padding: 10px; width:100%;">
                    <option value="">-- Choose a publication --</option>
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="'.htmlspecialchars($row['title']).'">'.htmlspecialchars($row['title']).'</option>';
                        }
                    } else {
                        echo '<option disabled>No Approved Publications found</option>';
                    }
                    ?>
                </select>
            </div>

            <div id="platformSection" style="opacity: 0.5; transition: opacity 0.3s;">
                <h3 style="margin-bottom: 20px;">Step 2: Choose Platform</h3>
                <button class="platform-btn linkedin" onclick="shareToPlatform('linkedin')">Share on LinkedIn</button>
                <button class="platform-btn researchgate" onclick="shareToPlatform('researchgate')">Add to ResearchGate</button>
                <button class="platform-btn orcid" onclick="shareToPlatform('orcid')">Sync to ORCID Record</button>
            </div>
        </div>
    </div>
</div>

<script>
    function enableSharing() {
        const select = document.getElementById('pubSelect');
        const section = document.getElementById('platformSection');
        const buttons = document.querySelectorAll('.platform-btn');

        if (select.value !== "") {
            section.style.opacity = "1";
            buttons.forEach(btn => btn.classList.add('active'));
        } else {
            section.style.opacity = "0.5";
            buttons.forEach(btn => btn.classList.remove('active'));
        }
    }

    function shareToPlatform(platform) {
        const pubTitle = document.getElementById('pubSelect').value;
        let url = "";
        switch(platform) {
            case 'linkedin': url = "https://www.linkedin.com/sharing/share-offsite/"; break;
            case 'researchgate': url = "https://www.researchgate.net/upload"; break;
            case 'orcid': url = "https://orcid.org/signin"; break;
        }
        window.open(url, '_blank');
        alert("Redirecting to " + platform + " for: " + pubTitle);
    }
</script>
</body>
</html>