<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.html");
    exit();
}

$host = 'localhost'; $db = 'utrack_db'; $user = 'root'; $pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Fetch all users EXCLUDING admins
    $stmt = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - UTrack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .flow-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .action-link { text-decoration: none; padding: 6px 12px; font-size: 0.8rem; border-radius: 4px; display: inline-block; color: white; margin-right: 5px; }
        .bg-approve { background-color: #28a745; }
        .bg-reject { background-color: #ffc107; color: #000; }
        .bg-delete { background-color: #dc3545; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php" class="active">Manage Users</a>
        <a href="manage_programme.php">Manage Programmes</a>
        <a href="system_settings.php">System Settings</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>User Account Management</h1>
        <div class="flow-section">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['stID']); ?></td>
                        <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo ucfirst($u['status'] ?? 'pending'); ?></td>
                        <td>
                            <a href="update_status.php?id=<?php echo $u['id']; ?>&status=accepted" class="action-link bg-approve">Approve</a>
                            <a href="update_status.php?id=<?php echo $u['id']; ?>&status=rejected" class="action-link bg-reject">Reject</a>
                            <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="action-link bg-delete" onclick="return confirm('Delete user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>