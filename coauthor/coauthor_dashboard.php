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

include '../db_conn.php';

if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=utrack_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { $pdo = null; }
}

$isLoggedIn = isset($_SESSION['user_id']);
$my_name = $isLoggedIn ? $_SESSION['fullname'] : "Guest";

$total = 0; $approved = 0; $pending = 0;
$my_pubs = [];
$monthly_data = array_fill(0, 12, 0);

if ($isLoggedIn && $pdo) {
    // 1. Fetch List
    $sql = "SELECT * FROM publications WHERE authors LIKE ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$my_name%"]);
    $my_pubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Calculate Stats
    $total = count($my_pubs);
    foreach ($my_pubs as $pub) {
        if ($pub['status'] === 'Approved') $approved++;
        elseif ($pub['status'] === 'Pending Verification' || $pub['status'] === 'Pending Upload') $pending++;
    }

    // 3. --- NEW: MONTHLY GRAPH DATA ---
    // We reuse the same logic: Find papers with my name, grouped by month
    $sql_chart = "SELECT MONTH(created_at) as month, COUNT(*) as count 
                  FROM publications 
                  WHERE authors LIKE ? AND YEAR(created_at) = YEAR(CURDATE()) 
                  GROUP BY MONTH(created_at)";
    $stmt_chart = $pdo->prepare($sql_chart);
    $stmt_chart->execute(["%$my_name%"]);
    
    while ($row = $stmt_chart->fetch(PDO::FETCH_ASSOC)) {
        $monthly_data[$row['month'] - 1] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Co-Author Dashboard - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .timeline-preview { font-size: 0.85rem; color: #666; display: block; margin-top: 4px; }
        .chart-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Co-Author</h2>
        <a href="coauthor_dashboard.php" class="active">My Co-Authored List</a>
        <a href="share_publication.php">Share Publication</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Co-Authored Publications</h1>
        <p style="color:#666; margin-bottom: 30px;">
            Welcome, <strong><?php echo htmlspecialchars($my_name); ?></strong>. 
            Track the status of research papers where you are listed as a contributor.
        </p>

        <?php if(!$isLoggedIn): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Notice:</strong> Your session has expired. Please refresh.
            </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="card">
                <h3><?php echo $total; ?></h3>
                <p>Total Contributions</p>
            </div>
            <div class="card">
                <h3 style="color: var(--success-color);"><?php echo $approved; ?></h3>
                <p>Approved & Published</p>
            </div>
            <div class="card">
                <h3 style="color: var(--accent-color);"><?php echo $pending; ?></h3>
                <p>Pending Verification</p>
            </div>
        </div>

        <div class="chart-section">
            <h3 style="margin-top:0;">Contribution Timeline (<?php echo date("Y"); ?>)</h3>
            <div style="height: 250px; width: 100%;">
                <canvas id="coAuthChart"></canvas>
            </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <div class="header-flex" style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                <h2 style="margin:0;">Publication Status</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Title & Venue</th>
                        <th>Authors</th>
                        <th>Submission Date</th>
                        <th>Current Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_pubs)): ?>
                        <tr><td colspan="5" style="text-align:center;">
                            <?php echo $isLoggedIn ? "No records found." : "Session Expired"; ?>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($my_pubs as $row): 
                             $status_class = 'status-pending';
                             if ($row['status'] == 'Approved') $status_class = 'status-approved';
                             if ($row['status'] == 'Rejected') $status_class = 'status-rejected';
                             if ($row['status'] == 'Resubmitted') $status_class = 'status-resubmitted';
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                <span class="timeline-preview"><?php echo htmlspecialchars($row['venue']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($row['authors']); ?></td>
                            <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                            <td><span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span></td>
                            <td>
                                <a href="view_publication.php?id=<?php echo $row['id']; ?>" class="btn-secondary" style="font-size:0.8rem; padding: 6px 12px; text-decoration:none;">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('coAuthChart').getContext('2d');
    const monthlyData = <?php echo json_encode(array_values($monthly_data)); ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Contributions',
                data: monthlyData,
                backgroundColor: 'rgba(75, 192, 192, 0.6)', // Teal color
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });
</script>
</body>
</html>