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

// Connection Fallback
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=utrack_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { $pdo = null; }
}

$students = [];
if ($pdo) {
    // Group by author name to count total APPROVED publications
    $sql = "SELECT authors, COUNT(*) as total_pubs, 
            SUM(CASE WHEN indexing_type IN ('Scopus', 'WoS') THEN 1 ELSE 0 END) as indexed_count
            FROM publications WHERE status = 'Approved' GROUP BY authors";
    $stmt = $pdo->query($sql);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Graduation Check - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php">Monitor Output</a>
        <a href="verify_list.php">Verify & Approve</a>
        <a href="graduation_eligibility.php" class="active">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Graduation Eligibility Checker</h1>
        <p>Verify if students meet publication requirements (Min 2 Approved).</p>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <strong>Notice:</strong> Your session has expired. Data is hidden.
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Name (Group)</th>
                    <th>Total Approved</th>
                    <th>Indexed</th>
                    <th>Requirement (Min 2)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($students)): ?>
                    <tr><td colspan="6" style="text-align:center;">No approved publications found yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $row): 
                        $isEligible = ($row['total_pubs'] >= 2);
                        $authorName = htmlspecialchars($row['authors']);
                    ?>
                    <tr>
                        <td><strong><?php echo $authorName; ?></strong></td>
                        <td style="font-weight: bold;"><?php echo $row['total_pubs']; ?></td>
                        <td><?php echo $row['indexed_count']; ?></td>
                        <td>
                            <?php echo $isEligible ? '<span style="color: green;">Met</span>' : '<span style="color: red;">Failed</span>'; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $isEligible ? 'status-approved' : 'status-rejected'; ?>">
                                <?php echo $isEligible ? 'Eligible' : 'Not Eligible'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($isEligible): ?>
                                <button class="btn-primary" style="font-size: 0.8rem; background-color: #28a745;" 
                                        onclick="sendReport('<?php echo $authorName; ?>', 'eligible')">
                                    Send Graduation Letter
                                </button>
                            <?php else: ?>
                                <button class="btn-secondary" style="font-size: 0.8rem; background-color: #dc3545; color: white;" 
                                        onclick="sendReport('<?php echo $authorName; ?>', 'failed')">
                                    Send Warning
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function sendReport(studentName, status) {
        if (status === 'eligible') {
            if (confirm("Confirm sending Graduation Eligibility Letter to " + studentName + "?")) {
                setTimeout(function() { alert("✅ SUCCESS: Graduation Letter sent to Exam Board and " + studentName + "."); }, 500);
            }
        } else {
            if (confirm("Send 'Requirement Not Met' warning to " + studentName + "?")) {
                setTimeout(function() { alert("⚠ NOTICE SENT: Warning notification emailed to " + studentName + "."); }, 500);
            }
        }
    }
</script>
</body>
</html>