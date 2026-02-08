<?php
// --- 1. ROBUST 24-HOUR SESSION SETUP ---
ini_set('session.gc_maxlifetime', 86400); // Server keeps session data for 24h
session_set_cookie_params(86400);         // Browser keeps cookie for 24h
session_start();

// Manual Timeout Check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) {
    session_unset();     
    session_destroy();   
}
$_SESSION['LAST_ACTIVITY'] = time(); 

include '../db_conn.php';

// --- 2. DATABASE CONNECTION FALLBACK ---
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=utrack_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { $pdo = null; }
}

$isCoordinator = (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Coordinator');

// Default Values
$total_citations = 0; $scopus_count = 0;
$app_rate = 0; $rev_rate = 0; $rej_rate = 0;
$monthly_data = array_fill(0, 12, 0); 
$analysis_results = []; // Array for insights

if ($isCoordinator && $pdo) {
    try {
        // --- A. BASIC KPI LOGIC ---
        $scopus_count = $pdo->query("SELECT COUNT(*) FROM publications WHERE indexing_type = 'Scopus' AND status = 'Approved'")->fetchColumn() ?: 0;
        $citation_stmt = $pdo->query("SELECT SUM(citations) FROM publications");
        $total_citations = $citation_stmt->fetchColumn() ?: 0;
        
        $total_pubs = $pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn() ?: 0;
        if ($total_pubs == 0) $total_pubs = 1; 
        
        $approved = $pdo->query("SELECT COUNT(*) FROM publications WHERE status = 'Approved'")->fetchColumn();
        $rejected = $pdo->query("SELECT COUNT(*) FROM publications WHERE status = 'Rejected'")->fetchColumn();
        $pending = $pdo->query("SELECT COUNT(*) FROM publications WHERE status = 'Pending Verification'")->fetchColumn();

        $app_rate = round(($approved / $total_pubs) * 100);
        $rev_rate = round(($pending / $total_pubs) * 100);
        $rej_rate = round(($rejected / $total_pubs) * 100);

        // --- B. CHART DATA (Group by Month) ---
        $sql_chart = "SELECT MONTH(created_at) as month, COUNT(*) as count 
                      FROM publications 
                      WHERE YEAR(created_at) = YEAR(CURDATE()) 
                      GROUP BY MONTH(created_at)";
        $stmt_chart = $pdo->query($sql_chart);
        while ($row = $stmt_chart->fetch(PDO::FETCH_ASSOC)) {
            $monthly_data[$row['month'] - 1] = $row['count'];
        }

        // --- C. NEW AUTOMATED INSIGHTS (No Topic Gaps) ---

        // Insight 1: COLLABORATION HEALTH
        // Logic: Check if 'authors' string contains a comma. Comma = Co-authored.
        $solo_papers = 0;
        $collab_papers = 0;
        $stmt_collab = $pdo->query("SELECT authors FROM publications WHERE YEAR(created_at) = YEAR(CURDATE())");
        while ($row = $stmt_collab->fetch(PDO::FETCH_ASSOC)) {
            if (strpos($row['authors'], ',') !== false) {
                $collab_papers++;
            } else {
                $solo_papers++;
            }
        }
        $total_checked = $solo_papers + $collab_papers;
        
        if ($total_checked > 0) {
            $solo_ratio = ($solo_papers / $total_checked) * 100;
            if ($solo_ratio > 40) {
                $analysis_results[] = [
                    'title' => 'Low Collaboration',
                    'text' => round($solo_ratio) . "% of papers are single-author. Encourage group research.",
                    'color' => '#e67e22' // Orange
                ];
            } else {
                $analysis_results[] = [
                    'title' => 'Healthy Collaboration',
                    'text' => "Strong teamwork detected. Most papers are co-authored.",
                    'color' => '#27ae60' // Green
                ];
            }
        }

        // Insight 2: IMPACT RATIO (Indexing Quality)
        $wos_count = $pdo->query("SELECT COUNT(*) FROM publications WHERE indexing_type = 'WoS' AND status = 'Approved'")->fetchColumn() ?: 0;
        $high_quality = $scopus_count + $wos_count;
        
        if ($approved > 0) {
            $quality_rate = ($high_quality / $approved) * 100;
            if ($quality_rate < 50) {
                $analysis_results[] = [
                    'title' => 'Quality Alert',
                    'text' => "Only " . round($quality_rate) . "% of approved papers are Scopus/WoS indexed.",
                    'color' => '#c0392b' // Red
                ];
            } else {
                $analysis_results[] = [
                    'title' => 'High Impact',
                    'text' => round($quality_rate) . "% of research is published in high-impact journals.",
                    'color' => '#27ae60'
                ];
            }
        }

        // Insight 3: WORKFLOW BOTTLENECK
        if ($total_pubs > 0) {
            $pending_ratio = ($pending / $total_pubs) * 100;
            if ($pending_ratio > 30) {
                $analysis_results[] = [
                    'title' => 'Verification Backlog',
                    'text' => "Attention needed: " . round($pending_ratio) . "% of submissions are waiting for review.",
                    'color' => '#d35400' // Dark Orange
                ];
            }
        }

    } catch (PDOException $e) { }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coordinator Dashboard - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .monitor-panel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
        .monitor-panel h3 { margin-top: 0; border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; color: var(--primary-color); }
        .chart-container { position: relative; height: 250px; width: 100%; flex-grow: 1; }
        
        /* Insight Styling */
        .insight-item { border-left: 4px solid #ddd; padding-left: 15px; margin-bottom: 15px; }
        .insight-title { font-weight: bold; display: block; margin-bottom: 2px; }
        .insight-text { font-size: 0.9rem; color: #666; margin: 0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php" class="active">Monitor Output</a>
        <a href="verify_list.php">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Research Output Monitor</h1>
        
        <?php if(!$isCoordinator): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Notice:</strong> Your session has expired. Data is hidden. Please refresh or login again.
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="monitor-panel">
                <h3>KPI Performance</h3>
                <div class="stats-grid" style="margin-bottom:0;">
                    <div class="card" style="padding:10px;">
                        <h3 style="font-size:1.5rem;"><?php echo $total_citations; ?></h3>
                        <p>Total Citations</p>
                    </div>
                    <div class="card" style="padding:10px;">
                        <h3 style="font-size:1.5rem;"><?php echo $scopus_count; ?></h3>
                        <p>Scopus Indexed</p>
                    </div>
                </div>
            </div>

            <div class="monitor-panel">
                <h3>Approval Rates</h3>
                <div style="display:flex; justify-content:space-between; align-items:center; height:100%;">
                    <div style="text-align:center;">
                        <span style="font-size:2rem; color:green; font-weight:bold;"><?php echo $app_rate; ?>%</span>
                        <p>Approved</p>
                    </div>
                    <div style="text-align:center;">
                        <span style="font-size:2rem; color:orange; font-weight:bold;"><?php echo $rev_rate; ?>%</span>
                        <p>Pending</p>
                    </div>
                    <div style="text-align:center;">
                        <span style="font-size:2rem; color:red; font-weight:bold;"><?php echo $rej_rate; ?>%</span>
                        <p>Rejected</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="monitor-panel">
                <h3>Publication Timeline (<?php echo date("Y"); ?>)</h3>
                <div class="chart-container">
                    <canvas id="pubsChart"></canvas>
                </div>
            </div>

            <div class="monitor-panel">
                <h3>System Insights</h3>
                <?php if(empty($analysis_results)): ?>
                    <p style="color:#666;">Not enough data for insights yet.</p>
                <?php else: ?>
                    <?php foreach($analysis_results as $item): ?>
                        <div class="insight-item" style="border-left-color: <?php echo $item['color']; ?>;">
                            <span class="insight-title" style="color: <?php echo $item['color']; ?>;">
                                <?php echo $item['title']; ?>
                            </span>
                            <p class="insight-text"><?php echo $item['text']; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // RENDER CHART
    const ctx = document.getElementById('pubsChart').getContext('2d');
    const monthlyData = <?php echo json_encode(array_values($monthly_data)); ?>;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Submissions',
                data: monthlyData,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
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