<?php
// --- FIX: ROBUST 24-HOUR SESSION ---
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);

session_start();

// Manual Timeout Check (Overrides server defaults)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) {
    session_unset();     // Unset session variables
    session_destroy();   // Destroy session data
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time stamp

// --- DATABASE CONNECTION ---
include '../db_conn.php';
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=utrack_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $pdo = null; 
    }
}

// --- SAFE MODE LOGIC ---
// Check if user is logged in AND is Admin
$isAdmin = (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin');
$fullname = $isAdmin ? $_SESSION['fullname'] : "Guest (Session Expired)";

// Default Values (Safe Mode)
$totalUsers = 0;
$totalProgrammes = 0;
$totalPublications = 0;
$recentUsers = [];

if ($isAdmin && $pdo) {
    try {
        // 1. Fetch Totals
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'Admin'")->fetchColumn();
        $totalProgrammes = $pdo->query("SELECT COUNT(*) FROM programmes")->fetchColumn();
        $totalPublications = $pdo->query("SELECT COUNT(*) FROM publications")->fetchColumn();

        // 2. Fetch Recent Users
        $stmt = $pdo->query("SELECT fullname, stID, role, status 
                             FROM users 
                             WHERE role != 'Admin' 
                             ORDER BY id DESC LIMIT 5");    
        $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Silent fail
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="wrapper">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_programme.php">Manage Programmes</a>
        <a href="system_settings.php">System Settings</a>
        <a href="system_reports.php">System Reports</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($fullname); ?></h1>
        
        <?php if(!$isAdmin): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Notice:</strong> Your session has expired. The data below is hidden. Please refresh or login again.
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="card">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Managed Users</p>
            </div>
            <div class="card">
                <h3><?php echo $totalProgrammes; ?></h3>
                <p>Active Programmes</p>
            </div>
            <div class="card">
                <h3><?php echo $totalPublications; ?></h3>
                <p>Publications</p>
            </div>
        </div>

        <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
            <h2>Recent User Registrations</h2>
            <a href="manage_users.php" class="btn-secondary">View All</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>ID</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['stID']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <?php 
                                $status = strtolower($user['status'] ?? 'pending');
                                if ($status == 'accepted') echo '<span style="color: #28a745; font-weight: bold;">Accepted</span>';
                                elseif ($status == 'rejected') echo '<span style="color: #dc3545; font-weight: bold;">Rejected</span>';
                                else echo '<span style="color: #ffc107; font-weight: bold;">Pending</span>';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">
                        <?php echo ($isAdmin) ? "No recent registrations." : "No data available (Session Expired)"; ?>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>