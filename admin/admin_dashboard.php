<?php
session_start();

// 1. Security Check: If not logged in, send back to login page
// Since we are in the 'admin' folder, the login page is one level up (../)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html"); 
    exit();
}

// 2. Role Check: Only allow 'admin'
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.html?error=unauthorized");
    exit();
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
        
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></h1>
        <p>System Overview Dashboard</p>
        
        <div class="stats-grid">
            <div class="card">
                <h3>1,250</h3>
                <p>Total Users</p>
            </div>
            <div class="card">
                <h3>45</h3>
                <p>Active Programmes</p>
            </div>
            <div class="card">
                <h3>320</h3>
                <p>Publications</p>
            </div>
            <div class="card">
                <h3>98%</h3>
                <p>System Uptime</p>
            </div>
        </div>

        <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Recent User Registrations</h2>
            <a href="manage_users.php" class="btn-secondary">View All</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($_SESSION['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($_SESSION['role']); ?></td>
                    <td><span class="badge status-verified">Active Now</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>