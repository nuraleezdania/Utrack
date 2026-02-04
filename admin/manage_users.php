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

    // 3. Fetch Pending Requests (Excluding Admins)
    $stmtPending = $pdo->prepare("SELECT * FROM users WHERE status = 'pending' AND role != 'admin' ORDER BY id DESC");
    $stmtPending->execute();
    $pendingUsers = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

    // 4. Fetch Existing Users (Accepted/Rejected, Excluding Admins)
    $stmtExisting = $pdo->prepare("SELECT * FROM users WHERE status != 'pending' AND role != 'admin' ORDER BY id DESC");
    $stmtExisting->execute();
    $existingUsers = $stmtExisting->fetchAll(PDO::FETCH_ASSOC);

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
        .section-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f4f4f4; padding-bottom: 15px; margin-bottom: 20px; }
        .section-header h2 { margin: 0; color: #333; font-size: 1.2rem; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 10% auto; padding: 30px; border-radius: 8px; width: 50%; max-width: 500px; position: relative; }
        .close-btn { position: absolute; right: 20px; top: 15px; font-size: 1.5rem; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
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
        <h1>User Account Management</h1>

        <div class="flow-section">
            <div class="section-header">
                <h2>1. Pending User Requests</h2>
                <span class="badge status-rejected"><?php echo count($pendingUsers); ?> Requests</span>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Staff/Student ID</th>
                        <th>Name</th>
                        <th>Role Requested</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingUsers as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['stID']); ?></td>
                        <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <a href="update_status.php?id=<?php echo $u['id']; ?>&status=accepted" class="btn-primary" style="text-decoration:none; padding:5px 10px;">Approve</a>
                            <button class="btn-danger" onclick="openRejectModal('<?php echo $u['id']; ?>', '<?php echo $u['fullname']; ?>')">Reject</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="flow-section">
            <div class="section-header">
                <h2>2. Existing Users (Edit / Deactivate)</h2>
            </div>

            <div style="display:flex; gap:10px; margin-bottom:20px;">
                <input type="text" id="userSearch" placeholder="Search by name or ID..." style="flex:1; padding:10px; border-radius:4px; border:1px solid #ddd;">
                <button class="btn-secondary">Search</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Staff/Student ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($existingUsers as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['stID']); ?></td>
                        <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td>
                            <?php if($u['status'] == 'accepted'): ?>
                                <span class="badge status-verified">Active</span>
                            <?php else: ?>
                                <span class="badge status-rejected">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-secondary" onclick="openEditModal('<?php echo $u['fullname']; ?>', '<?php echo $u['role']; ?>', '<?php echo $u['stID']; ?>', '<?php echo $u['id']; ?>')">Edit</button>
                            <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="btn-danger" style="text-decoration:none; padding:5px 10px;" onclick="return confirm('Delete user?')">Deactivate</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="rejectModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('rejectModal')">&times;</span>
        <h3>Reject User Request</h3>
        <p id="reject-user-text"></p>
        <form action="update_status.php" method="GET">
            <input type="hidden" name="id" id="reject-id">
            <input type="hidden" name="status" value="rejected">
            <div class="form-group">
                <label>Reason for Rejection:</label>
                <textarea name="reason" rows="4" placeholder="e.g., Invalid staff ID provided..." required></textarea>
            </div>
            <button type="submit" class="btn-danger" style="width:100%">Confirm Rejection</button>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
        <h3>Edit User Information</h3>
        <form action="edit_user.php" method="POST">
            <input type="hidden" name="id" id="edit-db-id">
            <div class="form-group">
                <label>User ID (Read-only)</label>
                <input type="text" id="edit-id" readonly style="background:#eee;">
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" id="edit-name">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="edit-role">
                    <option value="student">Main Author(Student)</option>
                    <option value="student">Main Author(Lecturer)</option>
                    <option value="student">Co-Author(Student)</option>
                    <option value="student">Co-Author(Lecturer)</option>
                    <option value="coordinator">Coordinator</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:100%">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id, name) {
        document.getElementById('reject-id').value = id;
        document.getElementById('reject-user-text').innerText = "Rejecting request for: " + name;
        document.getElementById('rejectModal').style.display = 'block';
    }

    function openEditModal(name, role, stID, dbID) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-role').value = role.toLowerCase();
        document.getElementById('edit-id').value = stID;
        document.getElementById('edit-db-id').value = dbID;
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
</script>
</body>
</html>