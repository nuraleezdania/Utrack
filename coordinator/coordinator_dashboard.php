<?php
include 'db_config.php';

// Since 'kpi' table is missing, we'll set citations to 0 for now to stop the error
$total_citations = 0; 

// Using 'indexing_type' and 'accepted' to match the new DB
$scopus_count = $pdo->query("SELECT COUNT(*) FROM publications WHERE indexing_type = 'Scopus' AND status = 'accepted'")->fetchColumn() ?: 0;

$total_pubs = $pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn() ?: 1;
$approved = $pdo->query("SELECT COUNT(*) FROM publications WHERE status = 'accepted'")->fetchColumn();
$rejected = $pdo->query("SELECT COUNT(*) FROM publications WHERE status = 'rejected'")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM publications WHERE status = 'pending'")->fetchColumn();

$app_rate = round(($approved / $total_pubs) * 100);
$rev_rate = round(($pending / $total_pubs) * 100);
$rej_rate = round(($rejected / $total_pubs) * 100);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coordinator Dashboard - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .monitor-panel { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .monitor-panel h3 { margin-top: 0; border-bottom: 2px solid #f4f4f4; padding-bottom: 10px; color: var(--primary-color); }
        .chart-placeholder { background: #eef2f6; height: 150px; display: flex; align-items: center; justify-content: center; color: #888; border-radius: 4px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php">Monitor Output</a>
        <a href="verify_list.php">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../index.html" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Research Output Monitor</h1>
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
                        <p>Revision</p>
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
                <h3>Publication Timeline</h3>
                <div class="chart-placeholder">[Bar Chart: Monthly Submissions Jan-Dec]</div>
            </div>
            <div class="monitor-panel">
                <h3>Research Gap Analysis</h3>
                <p><strong>Identified Gaps:</strong></p>
                <ul style="list-style-type: circle; padding-left: 20px;">
                    <li style="margin-bottom:10px; color:#c0392b;">Low output in Cybersecurity field.</li>
                    <li style="margin-bottom:10px; color:#e67e22;">Decreasing trend in WoS journals.</li>
                    <li style="color:green;">AI/ML publications exceed targets.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>