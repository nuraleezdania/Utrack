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

include "../db_conn.php";

// Fallback for PDO connection
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=utrack_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }
}

$coord_id = $_SESSION['user_id'];
$rows = []; // Start with empty list

// --- 2. GET COORDINATOR'S ASSIGNED PROGRAMMES ---
// This finds out: "Which programmes does THIS coordinator manage?"
$progStmt = $pdo->prepare("SELECT id, name FROM programmes WHERE coordinator_id = ?");
$progStmt->execute([$coord_id]);
$my_programmes_data = $progStmt->fetchAll(PDO::FETCH_ASSOC);

// Extract just the IDs for the query
$my_programme_ids = array_column($my_programmes_data, 'id');

// --- 3. FILTER PUBLICATIONS (THE FIX) ---
if (!empty($my_programme_ids)) {
    // Create placeholders (?,?,?) based on how many programmes they manage
    $placeholders = implode(',', array_fill(0, count($my_programme_ids), '?'));

    // FILTER LOGIC:
    // 1. Join Publications with Users
    // 2. Check if the User's programme_id matches the Coordinator's list
    $sql = "SELECT p.*, u.fullname as student_name, u.programme_id 
            FROM publications p
            JOIN users u ON p.user_id = u.id
            WHERE (p.status='Pending Verification' OR p.status='Pending Upload')
            AND u.programme_id IN ($placeholders) 
            ORDER BY p.created_at ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($my_programme_ids);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Publications - UTrack</title>
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
        <a href="verify_list.php" class="active">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Verify & Approve Submissions</h1>

        <?php if (empty($my_programme_ids)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Alert:</strong> You are not assigned to manage any programmes. Please contact Admin to assign you to a programme.
            </div>
        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Student / Programme</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): ?>
                        <?php foreach($rows as $row): ?>
                        <tr>
                            <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                                <small style="color:#666;">Prog ID: <?php echo $row['programme_id']; ?></small>
                            </td>
                            <td><span class="badge status-pending"><?php echo $row['status']; ?></span></td>
                            <td>
                                <a href="process_publication.php?id=<?php echo $row['id']; ?>" class="btn-primary" style="font-size:0.8rem; padding: 6px 12px; text-decoration:none;">Review</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No pending submissions found for your programme(s).</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        
        <?php endif; ?>
    </div>
</div>
</body>
</html>