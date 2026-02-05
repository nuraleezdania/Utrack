<?php
session_start();
include "db_conn.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 15;

// Helper function to get counts
function getCount($conn, $uid, $status = null) {
    $sql = "SELECT COUNT(*) as total FROM publications WHERE user_id='$uid'";
    if ($status) {
        $sql .= " AND status='$status'";
    }
    $res = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($res);
    return $data['total'];
}

$total_pubs = getCount($conn, $user_id);
$pending_pubs = getCount($conn, $user_id, 'Pending Verification');
$approved_pubs = getCount($conn, $user_id, 'Approved');
// Note: Citations column doesn't exist in your table yet, so I'll leave it static or 0
$citations = 0; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Author Dashboard - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .welcome-banner { background: linear-gradient(135deg, var(--primary-color) 0%, #002244 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; border-bottom: 3px solid transparent; }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-right: 15px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="mainauthor_dashboard.php" class="active">Dashboard</a>
        <a href="my_publications.php">My Publications</a>
        <a href="add_publication.php">Add New Publication</a>
        <a href="../index.html" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-banner">
            <div class="welcome-text">
                <h1>Welcome, Main Author</h1>
                <p>Track your research impact and manage submissions.</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
            <div class="stat-card" style="border-color: var(--primary-color);">
                <div style="display:flex; align-items:center;">
                    <div class="stat-icon" style="background:#eef2f6; color:var(--primary-color);">📚</div>
                    <div class="stat-info">
                        <h3><?php echo $total_pubs; ?></h3>
                        <p>Publications</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card" style="border-color: #9b59b6;">
                <div style="display:flex; align-items:center;">
                    <div class="stat-icon" style="background:#f5eef8; color:#9b59b6;">❝</div>
                    <div class="stat-info">
                        <h3><?php echo $citations; ?></h3>
                        <p>Total Citations</p>
                    </div>
                </div>
            </div>

            <div class="stat-card" style="border-color: var(--accent-color);">
                <div style="display:flex; align-items:center;">
                    <div class="stat-icon" style="background:#fff8e1; color:var(--accent-color);">⏳</div>
                    <div class="stat-info">
                        <h3><?php echo $pending_pubs; ?></h3>
                        <p>In Review</p>
                    </div>
                </div>
            </div>

            <div class="stat-card" style="border-color: var(--success-color);">
                <div style="display:flex; align-items:center;">
                    <div class="stat-icon" style="background:#e8f5e9; color:var(--success-color);">✔</div>
                    <div class="stat-info">
                        <h3><?php echo $approved_pubs; ?></h3>
                        <p>Approved</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>