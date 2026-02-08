<?php
// --- 1. SESSION & SECURITY ---
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();

// Manual Timeout Check
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) {
    session_unset(); session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

include '../db_conn.php';

// Fallback Connection
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=utrack_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { die("Database Error"); }
}

$coord_id = $_SESSION['user_id'];
$students = [];

// --- 2. GET COORDINATOR'S PROGRAMMES ---
$progStmt = $pdo->prepare("SELECT id, name FROM programmes WHERE coordinator_id = ?");
$progStmt->execute([$coord_id]);
$my_programmes_data = $progStmt->fetchAll(PDO::FETCH_ASSOC);
$my_programme_ids = array_column($my_programmes_data, 'id');

// --- 3. FILTER STUDENTS (THE FIX) ---
if (!empty($my_programme_ids)) {
    $placeholders = implode(',', array_fill(0, count($my_programme_ids), '?'));

    // Logic:
    // 1. Join Publications (p) with Users (u).
    // 2. Filter where User's Programme ID is in Coordinator's list.
    // 3. Only count 'Approved' publications.
    $sql = "SELECT u.fullname as student_name, u.stID, p.user_id,
            COUNT(*) as total_pubs, 
            SUM(CASE WHEN p.indexing_type IN ('Scopus', 'WoS') THEN 1 ELSE 0 END) as indexed_count
            FROM publications p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'Approved'
            AND u.programme_id IN ($placeholders)
            GROUP BY p.user_id"; // Group by User ID (Unique), not Name

    $stmt = $pdo->prepare($sql);
    $stmt->execute($my_programme_ids);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Graduation Check - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
         .debug-box { background: #333; color: #0f0; padding: 10px; font-family: monospace; margin-bottom: 20px; font-size: 0.8rem; }
    </style>
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
        <p>Verify if your students meet the publication requirements (Min 2 Approved).</p>

        <?php if (empty($my_programme_ids)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Alert:</strong> You are not assigned to manage any programmes.
            </div>
        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Total Approved</th>
                        <th>Indexed (Scopus/WoS)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($students)): ?>
                        <tr><td colspan="6" style="text-align:center;">No students in your programme have approved publications yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $row): 
                            // Rule: Must have at least 2 Approved Publications
                            $isEligible = ($row['total_pubs'] >= 2);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['student_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['stID']); ?></td>
                            <td style="font-weight: bold; text-align: center; font-size: 1.1rem;"><?php echo $row['total_pubs']; ?></td>
                            <td style="text-align: center;"><?php echo $row['indexed_count']; ?></td>
                            <td>
                                <span class="badge <?php echo $isEligible ? 'status-approved' : 'status-rejected'; ?>">
                                    <?php echo $isEligible ? 'Eligible' : 'Not Eligible'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($isEligible): ?>
                                    <button class="btn-primary" style="font-size: 0.8rem; background-color: #28a745;" 
                                            onclick="sendReport('<?php echo htmlspecialchars($row['student_name']); ?>', 'eligible')">
                                        Send Letter
                                    </button>
                                <?php else: ?>
                                    <button class="btn-secondary" style="font-size: 0.8rem; background-color: #dc3545; color: white;" 
                                            onclick="sendReport('<?php echo htmlspecialchars($row['student_name']); ?>', 'failed')">
                                        Send Warning
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>
</div>

<script>
    function sendReport(studentName, status) {
        if (status === 'eligible') {
            if (confirm("Confirm sending Graduation Eligibility Letter to " + studentName + "?")) {
                setTimeout(function() { alert("SUCCESS: Letter sent to Exam Board."); }, 500);
            }
        } else {
            if (confirm("Send 'Requirement Not Met' warning to " + studentName + "?")) {
                setTimeout(function() { alert("NOTICE SENT: Warning emailed to student."); }, 500);
            }
        }
    }
</script>
</body>
</html>