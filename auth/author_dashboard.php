<?php
session_start();

include "../db_conn.php"; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. GET STATS FROM DATABASE
// Helper function to count publications by status
function getCount($conn, $uid, $status = null) {
    $sql = "SELECT COUNT(*) as total FROM publications WHERE user_id='$uid'";
    if ($status) {
        $sql .= " AND status='$status'";
    }
    // Error handling in case query fails
    $res = mysqli_query($conn, $sql);
    if (!$res) { return 0; } 
    $data = mysqli_fetch_assoc($res);
    return $data['total'];
}

// Fetch the numbers
$total_pubs = getCount($conn, $user_id);
$pending_pubs = getCount($conn, $user_id, 'Pending Verification');
$approved_pubs = getCount($conn, $user_id, 'Approved');
$rejected_pubs = getCount($conn, $user_id, 'Rejected');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Author Dashboard - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Dashboard specific styling */
        .welcome-banner { 
            background: linear-gradient(135deg, var(--primary-color) 0%, #002244 100%); 
            color: white; 
            padding: 30px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
        }
        .stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            display: flex; 
            align-items: center; 
            border-bottom: 3px solid transparent; 
        }
        .stat-icon { 
            width: 50px; height: 50px; 
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.5rem; margin-right: 15px; 
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="author_dashboard.php" class="active">Dashboard</a>
        <a href="../mainauthor/my_publications.php">My Publications</a>
        <a href="../mainauthor/add_publication.php">Add New Publication</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-banner">
            <div class="welcome-text">
                <h1>Welcome, Author</h1>
                <p>Track your research impact and manage submissions.</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
            
            <div class="stat-card" style="border-color: var(--primary-color);">
                <div style="display:flex; align-items:center;">
                    <div class="stat-icon" style="background:#eef2f6; color:var(--primary-color);">📚</div>
                    <div class="stat-info">
                        <h3><?php echo $total_pubs; ?></h3>
                        <p>Total Publications</p>
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

             <div class="stat-card" style="border-color: var(--danger-color);">
                <div style="display:flex; align-items:center;">
                    <div class="stat-icon" style="background:#fdeded; color:var(--danger-color);">✖</div>
                    <div class="stat-info">
                        <h3><?php echo $rejected_pubs; ?></h3>
                        <p>Rejected</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>