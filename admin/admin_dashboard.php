<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.html"); 
    exit();
}

// 2. Database Variables
$host = 'localhost';
$db   = 'utrack_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Total Users (Excluding Admins)
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
    
    // Fetch Recent Users (Excluding Admins)
    $stmt = $pdo->query("SELECT fullname, stID, register_as, role, status 
    FROM users 
    WHERE role != 'admin' 
    ORDER BY id DESC LIMIT 5");    
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
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
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></h1>
        
        <div class="stats-grid">
            <div class="card">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Managed Users</p>
            </div>
            <div class="card"><h3>45</h3><p>Active Programmes</p></div>
            <div class="card"><h3>320</h3><p>Publications</p></div>
            <div class="card"><h3>98%</h3><p>System Uptime</p></div>
        </div>

        <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Recent User Registrations</h2>
            <a href="manage_users.php" class="btn-secondary">View All</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Staff/Student ID</th>
                    <th>Register As</th>
                    <th>Role in Publication</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($user['stID']); ?></td>
                        <td><?php echo htmlspecialchars($user['register_as']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <?php 
                                $status = strtolower($user['status'] ?? 'pending');
                                if ($status == 'accepted') echo '<span style="color: #28a745; font-weight: bold;">Accepted</span>';
                                elseif ($status == 'rejected') echo '<span style="color: #dc3545; font-weight: bold;">Rejected</span>';
                                else echo '<span style="color: #ffc107; font-weight: bold;">Pending Approval</span>';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No recent registrations.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>