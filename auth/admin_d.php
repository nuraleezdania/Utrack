<?php
session_start();

// 1. Security Check: Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// 2. Role Check: Redirect if the user is NOT an admin
if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Access Denied: Admins Only'); window.location.href='index.html';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - UTrack</title>
    <link rel="stylesheet" href="assets/css/style.css"> <style>
        /* Basic Layout Styling */
        .wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #003366; color: white; padding: 20px; }
        .sidebar a { display: block; color: white; padding: 10px; text-decoration: none; margin-bottom: 5px; border-radius: 4px; }
        .sidebar a.active, .sidebar a:hover { background: #001a33; }
        .main-content { flex: 1; padding: 30px; background: #f4f7f6; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; }
        .status-verified { background: #e6f4ea; color: #1e7e34; }
        .logout-btn { background: #d9534f !important; margin-top: 50px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Admin</h2>
        <p style="font-size: 0.8rem; opacity: 0.8;">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
        <hr>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_programme.php">Manage Programmes</a>
        <a href="auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>System Overview</h1>
        
        <div class="stats-grid">
            <div class="card"><h3>1,250</h3><p>Total Users</p></div>
            <div class="card"><h3>45</h3><p>Active Programmes</p></div>
            <div class="card"><h3>320</h3><p>Publications</p></div>
            <div class="card"><h3>98%</h3><p>Uptime</p></div>
        </div>

        <div class="header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2>Recent User Registrations</h2>
            <button onclick="window.location.reload();" class="btn-secondary">Refresh List</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($_SESSION['email']); ?></td>
                    <td><?php echo htmlspecialchars($_SESSION['role']); ?></td>
                    <td><span class="badge status-verified">You (Active)</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>