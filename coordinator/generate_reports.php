<?php
// --- FIX: ROBUST 24-HOUR SESSION ---
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);

session_start();

// Manual Timeout Check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

include '../db_conn.php';

// --- CONNECTION FALLBACK ---
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}

// Security Check
$isCoordinator = (isset($_SESSION['user_id']) && $_SESSION['role'] === 'Coordinator');

// --- REPORT GENERATION LOGIC ---
$report_generated = false;
$data = [];
$summary = [
    'total' => 0,
    'approved' => 0,
    'scopus' => 0,
    'wos' => 0
];

if (isset($_POST['generate_btn']) && $isCoordinator) {
    $year = $_POST['year'];
    $faculty = $_POST['faculty'];
    // $report_type is now just a label since we generate everything together
    $report_title = "Faculty KPI & Detailed Publication List"; 

    // 1. Build the Query
    $sql = "SELECT p.*, u.fullname 
            FROM publications p 
            JOIN users u ON p.user_id = u.id 
            WHERE u.faculty = '$faculty' 
            AND (p.year = '$year' OR YEAR(p.created_at) = '$year')
            ORDER BY p.created_at DESC";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $report_generated = true;
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
            
            // Calculate KPIs
            $summary['total']++;
            if ($row['status'] == 'Approved') $summary['approved']++;
            if ($row['indexing_type'] == 'Scopus') $summary['scopus']++;
            if ($row['indexing_type'] == 'WoS') $summary['wos']++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Reports - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-section { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .kpi-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #ddd; }
        .kpi-card h3 { margin: 0; font-size: 2rem; color: var(--primary-color); }
        .kpi-card p { margin: 5px 0 0; color: #666; font-size: 0.9rem; }
        
        @media print {
            .sidebar, .no-print { display: none; }
            .wrapper { grid-template-columns: 1fr; }
            .main-content { padding: 0; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php">Monitor Output</a>
        <a href="verify_list.php">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php" class="active">Reports & KPI</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Generate Reports</h1>

        <?php if(!$isCoordinator): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Notice:</strong> Your session has expired. Please refresh or login again to generate reports.
            </div>
        <?php endif; ?>

        <div class="report-section no-print">
            <form method="POST" action="">
                <h3>Report Configuration</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                    
                    <div class="form-group">
                        <label>Report Type</label>
                        <select name="report_type" class="form-control">
                            <option value="Full Report">Faculty KPI & Detailed Publication List</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Target Year</label>
                        <input type="number" name="year" value="2026" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Faculty</label>
                        <select name="faculty" class="form-control">
                            <option value="Faculty of Computing">Faculty of Computing</option>
                            <option value="Faculty of Management">Faculty of Management</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="generate_btn" class="btn-primary" style="width: 100%;">Generate Report</button>
            </form>
        </div>

        <?php if ($report_generated): ?>
            <div class="report-section">
                <div class="header-flex">
                    <div>
                        <h2 style="margin:0;">Report: <?php echo htmlspecialchars($_POST['faculty']); ?></h2>
                        <p><?php echo $report_title; ?> | Year: <?php echo htmlspecialchars($_POST['year']); ?></p>
                    </div>
                    <div class="no-print">
                        <button onclick="window.print()" class="btn-secondary">🖨 Print / Save as PDF</button>
                    </div>
                </div>

                <hr style="margin: 20px 0;">

                <h3>Executive Summary (KPIs)</h3>
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <h3><?php echo $summary['total']; ?></h3>
                        <p>Total Submissions</p>
                    </div>
                    <div class="kpi-card">
                        <h3 style="color: green;"><?php echo $summary['approved']; ?></h3>
                        <p>Approved Papers</p>
                    </div>
                    <div class="kpi-card">
                        <h3 style="color: orange;"><?php echo $summary['scopus']; ?></h3>
                        <p>Scopus Indexed</p>
                    </div>
                    <div class="kpi-card">
                        <h3 style="color: #d35400;"><?php echo $summary['wos']; ?></h3>
                        <p>WoS Indexed</p>
                    </div>
                </div>

                <h3>Detailed Publication List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Authors</th>
                            <th>Venue</th>
                            <th>Indexing</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="5" style="text-align:center;">No records found for this year/faculty.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['authors']); ?></td>
                                <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                <td><?php echo htmlspecialchars($row['indexing_type']); ?></td>
                                <td>
                                    <?php 
                                    $color = 'black';
                                    if($row['status']=='Approved') $color='green';
                                    if($row['status']=='Rejected') $color='red';
                                    if($row['status']=='Pending Verification') $color='orange';
                                    echo "<span style='color:$color; font-weight:bold;'>".$row['status']."</span>";
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>