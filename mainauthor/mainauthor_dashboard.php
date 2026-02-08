<?php
// --- ROBUST 24-HOUR SESSION ---
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();

// Manual Timeout Check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) {
    session_unset(); session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

include "../db_conn.php";

// Fallback Connection
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}

// NO REDIRECT LOGIC (Safe Mode)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $fullname = $_SESSION['fullname'];
} else {
    $user_id = 0; 
    $fullname = "Guest (Session Expired)";
}

// 1. Helper function for Stats
function getCount($conn, $uid, $status = null) {
    if ($uid == 0) return 0;
    $sql = "SELECT COUNT(*) as total FROM publications WHERE user_id='$uid'";
    if ($status) $sql .= " AND status='$status'";
    
    $res = mysqli_query($conn, $sql);
    if ($res) {
        $data = mysqli_fetch_assoc($res);
        return $data['total'];
    }
    return 0;
}

// 2. Calculate Stats
$total_pubs = getCount($conn, $user_id);
$pending_pubs = getCount($conn, $user_id, 'Pending Verification');
$approved_pubs = getCount($conn, $user_id, 'Approved');

// 3. Citations
$citations = 0;
if ($user_id != 0) {
    $sql_cite = "SELECT SUM(citations) as total_cites FROM publications WHERE user_id='$user_id'";
    $res_cite = mysqli_query($conn, $sql_cite);
    $row_cite = mysqli_fetch_assoc($res_cite);
    $citations = $row_cite['total_cites'] ? $row_cite['total_cites'] : 0;
}

// 4. --- NEW: MONTHLY GRAPH DATA ---
$monthly_data = array_fill(0, 12, 0); // Init 12 months with 0
if ($user_id != 0) {
    $sql_chart = "SELECT MONTH(created_at) as month, COUNT(*) as count 
                  FROM publications 
                  WHERE user_id='$user_id' AND YEAR(created_at) = YEAR(CURDATE()) 
                  GROUP BY MONTH(created_at)";
    $res_chart = mysqli_query($conn, $sql_chart);
    while($row = mysqli_fetch_assoc($res_chart)) {
        $monthly_data[$row['month'] - 1] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Author Dashboard - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .welcome-banner { background: linear-gradient(135deg, var(--primary-color) 0%, #002244 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; align-items: center; border-bottom: 3px solid transparent; }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-right: 15px; }
        .chart-section { background: white; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="mainauthor_dashboard.php" class="active">Dashboard</a>
        <a href="my_publications.php">My Publications</a>
        <a href="add_publication.php">Add New Publication</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-banner">
            <div class="welcome-text">
                <h1>Welcome, <?php echo htmlspecialchars($fullname); ?></h1>
                <p>Track your research impact and manage submissions.</p>
            </div>
        </div>

        <?php if($user_id == 0): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Notice:</strong> Your session has expired. Please refresh to login.
            </div>
        <?php endif; ?>

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

        <div class="chart-section">
            <h3 style="color: #333; margin-top:0;">My Publication Timeline (<?php echo date("Y"); ?>)</h3>
            <div style="height: 300px; width: 100%;">
                <canvas id="myPubChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('myPubChart').getContext('2d');
    const monthlyData = <?php echo json_encode(array_values($monthly_data)); ?>;

    new Chart(ctx, {
        type: 'line', // Line chart looks better for personal timeline
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'My Submissions',
                data: monthlyData,
                borderColor: '#002244', // Primary Color
                backgroundColor: 'rgba(0, 34, 68, 0.1)',
                borderWidth: 2,
                tension: 0.3, // Curve the line slightly
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: { legend: { display: false } }
        }
    });
</script>
</body>
</html>