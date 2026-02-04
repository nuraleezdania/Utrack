<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.html"); 
    exit();
}

// 2. Database Connection
$host = 'localhost';
$db   = 'utrack_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $reportData = [];
    $metrics = ['total' => 0, 'pending' => 0, 'rate' => 0];
    $showResults = false;
    $tableHeader = "Category";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['report_type'])) {
        $showResults = true;
        $reportType = $_POST['report_type'];
        // Convert technical slug to a formal title for the heading
        $reportTitle = "System Analysis Report";
        if ($reportType === 'publication') $reportTitle = "Publication Analysis Report";
        elseif ($reportType === 'user') $reportTitle = "User Activity & Registration Report";
        elseif ($reportType === 'kpi') $reportTitle = "KPI Performance & Impact Report";
        $faculty = $_POST['faculty'] ?? 'All Faculties';

        $whereClause = " WHERE 1=1 ";
        $params = [];
        
        if ($faculty !== 'All Faculties') {
            $whereClause .= " AND u.faculty = :faculty ";
            $params[':faculty'] = $faculty;
        }

        if ($reportType === 'publication') {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN r.status = 'accepted' THEN 1 ELSE 0 END) as accepted
                    FROM reports r 
                    JOIN users u ON r.user_id = u.id" . $whereClause;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            $metrics['total'] = $res['total'] ?? 0;
            $metrics['pending'] = $res['pending'] ?? 0;
            $metrics['rate'] = ($metrics['total'] > 0) ? round(($res['accepted'] / $metrics['total']) * 100) : 0;

            $sqlTable = "SELECT r.pub_type as category, COUNT(*) as count 
                         FROM reports r 
                         JOIN users u ON r.user_id = u.id " . $whereClause . " GROUP BY r.pub_type";
            $stmtTable = $pdo->prepare($sqlTable);
            $stmtTable->execute($params);
            $reportData = $stmtTable->fetchAll(PDO::FETCH_ASSOC);
            $tableHeader = "Publication Type";

        } elseif ($reportType === 'user') {
            $userWhere = ($faculty !== 'All Faculties') ? " WHERE faculty = :faculty AND role != 'admin'" : " WHERE role != 'admin'";
            $sql = "SELECT COUNT(*) as total,
                           SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                           SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as active
                    FROM users" . $userWhere;
            
            $stmt = $pdo->prepare($sql);
            if($faculty !== 'All Faculties') $stmt->execute([':faculty' => $faculty]); else $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            $metrics['total'] = $res['total'] ?? 0;
            $metrics['pending'] = $res['pending'] ?? 0;
            $metrics['rate'] = ($metrics['total'] > 0) ? round(($res['active'] / $metrics['total']) * 100) : 0;

            $sqlTable = "SELECT role as category, COUNT(*) as count FROM users " . $userWhere . " GROUP BY role";
            $stmtTable = $pdo->prepare($sqlTable);
            if($faculty !== 'All Faculties') $stmtTable->execute([':faculty' => $faculty]); else $stmtTable->execute();
            $reportData = $stmtTable->fetchAll(PDO::FETCH_ASSOC);
            $tableHeader = "User Role";

        } elseif ($reportType === 'kpi') {
            $sql = "SELECT COUNT(*) as total,
                           SUM(CASE WHEN r.indexing IN ('Scopus', 'WoS') AND r.status = 'accepted' THEN 1 ELSE 0 END) as indexed
                    FROM reports r
                    JOIN users u ON r.user_id = u.id" . $whereClause;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            $metrics['total'] = $res['total'] ?? 0;
            $metrics['pending'] = $metrics['total'] - ($res['indexed'] ?? 0); 
            $metrics['rate'] = ($metrics['total'] > 0) ? round(($res['indexed'] / $metrics['total']) * 100) : 0;

            $sqlTable = "SELECT r.indexing as category, COUNT(*) as count FROM reports r JOIN users u ON r.user_id = u.id " . $whereClause . " GROUP BY r.indexing";
            $stmtTable = $pdo->prepare($sqlTable);
            $stmtTable->execute($params);
            $reportData = $stmtTable->fetchAll(PDO::FETCH_ASSOC);
            $tableHeader = "Indexing Status";
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports - UTrack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-type-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .type-card { background: white; border: 2px solid transparent; padding: 20px; border-radius: 10px; text-align: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.3s; }
        .type-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .type-card.active { border-color: #003366; background-color: #f0f7fb; }
        .filter-panel { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .filter-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: end; }
        .summary-cards { display: flex; gap: 20px; margin-bottom: 25px; }
        .summary-metric { flex: 1; background: white; padding: 20px; border-radius: 8px; border-left: 5px solid #28a745; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .value { font-size: 2.2rem; font-weight: bold; color: #333; }
        .icon { font-size: 2.5rem; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #eee; text-align: left; }
        th { background: #003366; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_programme.php">Manage Programmes</a>
        <a href="system_settings.php">System Settings</a>
        <a href="system_reports.php" class="active">System Reports</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Generate System Reports</h1>
        <p style="color:#666; margin-bottom: 30px;">Analyze system metrics and faculty performance.</p>

        <form method="POST" id="reportForm">
            <input type="hidden" name="report_type" id="report_type_input" value="<?= $_POST['report_type'] ?? '' ?>">
            
            <div class="report-type-grid">
                <div class="type-card <?= (isset($_POST['report_type']) && $_POST['report_type'] == 'publication') ? 'active' : '' ?>" onclick="setReportType('publication', this)">
                    <div class="icon">📄</div>
                    <h3>Publication Report</h3>
                    <p>Status, Volume & Indexing</p>
                </div>
                <div class="type-card <?= (isset($_POST['report_type']) && $_POST['report_type'] == 'user') ? 'active' : '' ?>" onclick="setReportType('user', this)">
                    <div class="icon">👥</div>
                    <h3>User Activity</h3>
                    <p>Registrations & Roles</p>
                </div>
                <div class="type-card <?= (isset($_POST['report_type']) && $_POST['report_type'] == 'kpi') ? 'active' : '' ?>" onclick="setReportType('kpi', this)">
                    <div class="icon">📈</div>
                    <h3>KPI Performance</h3>
                    <p>Targets vs Achievement</p>
                </div>
            </div>

            <div id="filterSection" class="filter-panel" style="<?= (isset($_POST['report_type'])) ? 'display:block;' : 'display:none;' ?>">
                <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Configuration</h3>
                <div class="filter-grid">
                    <div class="form-group">
                        <label>Timeframe</label>
                        <select name="timeframe" class="form-control">
                            <option value="all">All Time</option>
                            <option value="last30days">Last 30 Days</option>
                            <option value="currentquarter">Current Quarter (Q1 2026)</option>
                        </select>
                    </div>

                    <div class="form-group" id="secondaryFilter">
                        <label id="filterLabel">Faculty</label>
                        <select name="faculty" class="form-control">
                            <option value="All Faculties">All Faculties</option>
                            <option value="Faculty of Computing" <?= (isset($_POST['faculty']) && $_POST['faculty'] == 'Faculty of Computing') ? 'selected' : '' ?>>Faculty of Computing</option>
                            <option value="Faculty of Management" <?= (isset($_POST['faculty']) && $_POST['faculty'] == 'Faculty of Management') ? 'selected' : '' ?>>Faculty of Management</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-primary" style="width:100%; height:42px;">Generate Analysis</button>
                    </div>
                </div>
            </div>
        </form>

        <?php if ($showResults): ?>

            <div id="resultsSection" class="results-container" style="background: white; padding: 20px; border-radius: 10px;">
            <h1 style="color: #003366; margin-bottom: 5px; font-size: 1.8rem;"><?= $reportTitle ?></h1>
            
            <div style="border-bottom: 2px solid #003366; margin-bottom: 25px; padding-bottom: 10px;">
                <span style="color: #666; font-weight: bold;">Faculty:</span> <?= htmlspecialchars($faculty) ?> 
            </div>
            
            <div class="summary-cards">
                <div class="summary-metric">
                    <h4>Total Records</h4>
                    <div class="value"><?= $metrics['total'] ?></div>
                </div>
                <div class="summary-metric" style="border-color: #ffc107;">
                    <h4>Pending Review</h4>
                    <div class="value"><?= $metrics['pending'] ?></div>
                </div>
                <div class="summary-metric" style="border-color: #007bff;">
                    <h4>Success Rate</h4>
                    <div class="value"><?= $metrics['rate'] ?>%</div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><?= $tableHeader ?></th>
                        <th>Count</th>
                        <th>Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reportData)): ?>
                        <?php foreach ($reportData as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['category'] ?: 'Uncategorized') ?></strong></td>
                            <td><?= $row['count'] ?></td>
                            <td><?= ($metrics['total'] > 0) ? round(($row['count'] / $metrics['total']) * 100) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center;">No data records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; margin-bottom: 10px;">
            <button onclick="exportToPDF()" style="background: #003366 ; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">
                📕 Export to PDF
            </button>
        </div>

        <?php endif; ?>
    </div>
</div>

<script>
    function setReportType(type, card) {
        document.getElementById('report_type_input').value = type;
        document.querySelectorAll('.type-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        document.getElementById('filterSection').style.display = 'block';
    }

    function exportToPDF() {
        const element = document.getElementById('resultsSection');
        const opt = {
            margin:       10,
            filename:     `UTrack_Report_${new Date().toLocaleDateString()}.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
</body>
</html>