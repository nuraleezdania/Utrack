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

    // --- HANDLE ROLE UPDATE ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
        $dbID = $_POST['id'];
        $newRole = $_POST['role'];
        $updateStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $updateStmt->execute([$newRole, $dbID]);
        header("Location: manage_users.php?msg=role_updated");
        exit();
    }

    // --- HANDLE ACTIONS (Activate / Deactivate / Delete) ---
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $dbID = $_GET['id'];
        $action = $_GET['action'];
        
        if ($action === 'deactivate') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'deactivated' WHERE id = ?");
            $stmt->execute([$dbID]);
        } elseif ($action === 'activate') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'accepted' WHERE id = ?");
            $stmt->execute([$dbID]);
        } elseif ($action === 'delete') {
            // PERMANENT REMOVAL
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$dbID]);
        }

        header("Location: manage_users.php?msg=success");
        exit();
    }

    // 3. Fetch Data
    $stmtPending = $pdo->prepare("SELECT * FROM users WHERE status = 'pending' AND role != 'admin' ORDER BY id DESC");
    $stmtPending->execute();
    $pendingUsers = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

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
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 10% auto; padding: 30px; border-radius: 8px; width: 50%; max-width: 500px; position: relative; }
        .close-btn { position: absolute; right: 20px; top: 15px; font-size: 1.5rem; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .readonly-field { background: #f4f4f4; color: #777; cursor: not-allowed; }
        .btn-small { padding: 5px 10px; font-size: 0.75rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 5px; border: none; cursor: pointer; }
        .btn-delete { background-color: #000; color: #fff; } /* Distinct color for Delete */
        .btn-delete:hover { background-color: #444; }
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
                <h2>Pending User Requests</h2>
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
            <div class="section-header"><h2>Existing Users (Edit/Deactivate/Delete)</h2></div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($existingUsers as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['stID']); ?></td>
                        <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td>
                            <span class="badge <?php echo ($u['status']=='accepted') ? 'status-verified' : 'status-rejected'; ?>">
                                <?php echo ucfirst($u['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-secondary btn-small" onclick="openEditModal('<?php echo $u['fullname']; ?>', '<?php echo $u['role']; ?>', '<?php echo $u['stID']; ?>', '<?php echo $u['id']; ?>')">Edit</button>
                            
                            <?php if($u['status'] === 'accepted'): ?>
                                <a href="manage_users.php?action=deactivate&id=<?php echo $u['id']; ?>" class="btn-danger btn-small" onclick="return confirm('Deactivate this user?')">Deactivate</a>
                            <?php else: ?>
                                <a href="manage_users.php?action=activate&id=<?php echo $u['id']; ?>" class="btn-primary btn-small">Activate</a>
                            <?php endif; ?>

                            <a href="manage_users.php?action=delete&id=<?php echo $u['id']; ?>" class="btn-small btn-delete" onclick="return confirm('WARNING: This will permanently DELETE the user. This cannot be undone. Continue?')">Delete</a>
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
<script>
    function openRejectModal(id, name) {
        document.getElementById('reject-id').value = id;
        document.getElementById('reject-user-text').innerText = "Are you sure you want to reject the request from " + name + "?";
        document.getElementById('rejectModal').style.display = 'block';
    }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
        <h3>Update Role</h3>
        <form method="POST">
            <input type="hidden" name="id" id="edit-db-id">
            <input type="hidden" name="update_role" value="1">
            <div class="form-group"><label>Name</label><input type="text" id="edit-name" readonly class="readonly-field"></div>
            <div class="form-group">
                <label>New Role</label>
                <select name="role" id="edit-role">
                    <option value="Main Author(Student)">Main Author(Student)</option>
                    <option value="Main Author(Lecturer)">Main Author(Lecturer)</option>
                    <option value="Co-Author(Student)">Co-Author(Student)</option>
                    <option value="Co-Author(Lecturer)">Co-Author(Lecturer)</option>
                    <option value="Coordiantor">Coordinator</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:100%">Save Changes</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(name, role, stID, dbID) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-db-id').value = dbID;
        document.getElementById('edit-role').value = role;
        document.getElementById('editModal').style.display = 'block';
    }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
</script>
</body>
</html>