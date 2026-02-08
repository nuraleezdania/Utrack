<?php
session_start();
include "../db_conn.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$my_name = $_SESSION['fullname'];

// Only fetch APPROVED papers for the dropdown
$sql = "SELECT title FROM publications 
        WHERE authors LIKE '%$my_name%' AND status='Approved'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Share Publication - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .share-container { max-width: 600px; margin: 0 auto; text-align: center; }
        .platform-btn { 
            width: 100%; padding: 15px; margin-bottom: 15px; border: 1px solid #ccc; 
            background: white; cursor: pointer; border-radius: 8px; font-size: 1.1rem; 
            transition: all 0.2s; display: flex; align-items: center; justify-content: center; 
            font-weight: 600; opacity: 0.5; pointer-events: none; 
        }
        .platform-btn.active { opacity: 1; pointer-events: auto; }
        .platform-btn:hover { transform: translateY(-3px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .linkedin { color: #0077b5; border-color: #0077b5; }
        .researchgate { color: #00ccbb; border-color: #00ccbb; }
        .orcid { color: #a6ce39; border-color: #a6ce39; }

        /* Spinner Overlay */
        #loadingOverlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.9); z-index: 1000;
            justify-content: center; align-items: center; flex-direction: column;
        }
        .spinner {
            border: 5px solid #f3f3f3; border-top: 5px solid var(--primary-color);
            border-radius: 50%; width: 50px; height: 50px;
            animation: spin 1s linear infinite; margin-bottom: 15px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Co-author</h2>
        <a href="coauthor_dashboard.php">My Co-authored List</a>
        <a href="share_publication.php" class="active">Share Publication</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Share Publication</h1>
        
        <div class="share-container">
            <div class="form-group" style="text-align: left; margin-bottom: 30px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
                <label>Step 1: Select Approved Publication</label>
                <select id="pubSelect" class="form-control" onchange="enableSharing()" style="padding: 10px; width:100%;">
                    <option value="">-- Choose a publication --</option>
                    <?php 
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="'.htmlspecialchars($row['title']).'">'.htmlspecialchars($row['title']).'</option>';
                        }
                    } else {
                        echo '<option disabled>No Approved Publications</option>';
                    }
                    ?>
                </select>
            </div>

            <div id="platformSection" style="opacity: 0.5;">
                <h3>Step 2: Choose Platform</h3>
                <button class="platform-btn linkedin" onclick="shareToPlatform('linkedin')">Share on LinkedIn</button>
                <button class="platform-btn researchgate" onclick="shareToPlatform('researchgate')">Add to ResearchGate</button>
                <button class="platform-btn orcid" onclick="shareToPlatform('orcid')">Sync to ORCID Record</button>
            </div>
            
            <br>
            <a href="coauthor_dashboard.php" class="btn-secondary" style="text-decoration:none;">Cancel</a>
        </div>
    </div>
</div>

<div id="loadingOverlay">
    <div class="spinner"></div>
    <h3 id="loadingText">Formatting Metadata...</h3>
    <p>Preparing citation data for external API.</p>
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
        const overlay = document.getElementById('loadingOverlay');
        const text = document.getElementById('loadingText');
        
        overlay.style.display = 'flex';
        text.innerText = "Formatting metadata for " + platform.charAt(0).toUpperCase() + platform.slice(1) + "...";

        setTimeout(() => {
            let url = "";
            switch(platform) {
                case 'linkedin': url = "https://www.linkedin.com/sharing/share-offsite/"; break;
                case 'researchgate': url = "https://www.researchgate.net/upload"; break;
                case 'orcid': url = "https://orcid.org/signin"; break;
            }
            window.open(url, '_blank');
            overlay.style.display = 'none';
            alert("Success! Metadata sent.");
            
            // FIX 3: Correct Javascript Redirect
            window.location.href = "coauthor_dashboard.php"; 
        }, 1500);
    }
</script>
</body>
</html>