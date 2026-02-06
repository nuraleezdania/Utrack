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

// Connection Fallback
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}

// Fetch Pending Publications
$sql = "SELECT * FROM publications WHERE status='Pending Verification' OR status='Pending Upload' ORDER BY created_at ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Publications - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php">Monitor Output</a>
        <a href="verify_list.php" class="active">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Verify & Approve Submissions</h1>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <strong>Notice:</strong> Your session has expired. Please refresh the page to login.
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Authors</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['authors']); ?></td>
                        <td><span class="badge status-pending"><?php echo $row['status']; ?></span></td>
                        <td>
                            <a href="process_publication.php?id=<?php echo $row['id']; ?>" class="btn-primary" style="font-size:0.8rem; padding: 6px 12px; text-decoration:none;">Review</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No pending submissions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>