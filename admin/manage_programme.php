<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.html?error=unauthorized"); 
    exit();
}

$host = 'localhost';
$db   = 'utrack_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- HANDLE FORM SUBMISSIONS ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        // A. Add New Programme
        if (isset($_POST['action']) && $_POST['action'] == 'add') {
            $stmt = $pdo->prepare("INSERT INTO programmes (name, code, faculty, level) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['code'], $_POST['faculty'], $_POST['level']]);
            $msg = "Programme added successfully!";
        }

        // B. Update Details
        if (isset($_POST['action']) && $_POST['action'] == 'edit') {
            $stmt = $pdo->prepare("UPDATE programmes SET name = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['id']]);
            $msg = "Programme updated!";
        }

        // C. Assign Coordinator
        if (isset($_POST['action']) && $_POST['action'] == 'assign') {
            $stmt = $pdo->prepare("UPDATE programmes SET coordinator_id = ? WHERE id = ?");
            $stmt->execute([$_POST['coord_id'], $_POST['prog_id']]);
            $msg = "Coordinator assigned!";
        }

        // D. Set KPIs
        if (isset($_POST['action']) && $_POST['action'] == 'kpi') {
            $indexes = isset($_POST['indexes']) ? implode(", ", $_POST['indexes']) : "";
            $stmt = $pdo->prepare("UPDATE programmes SET min_publications = ?, indexing_req = ? WHERE id = ?");
            $stmt->execute([$_POST['min_pub'], $indexes, $_POST['prog_id']]);
            $msg = "KPI Requirements updated!";
        }
    }

    // --- FETCH DATA FOR DROPDOWNS ---
    $programmes = $pdo->query("
    SELECT p.*, u.fullname AS coordinator_name 
    FROM programmes p 
    LEFT JOIN users u ON p.coordinator_id = u.id 
    ORDER BY p.name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);    
    $coordinators = $pdo->query("SELECT id, fullname FROM users WHERE role = 'coordinator'")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Programmes - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .action-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .action-card { background: white; padding: 20px; border-radius: 8px; text-align: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: 0.2s; }
        .action-card:hover { transform: translateY(-3px); }
        .action-card.active { border: 2px solid #003366; background: #eef2f6; }
        .workflow-section { display: none; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Admin Panel</h2>
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="manage_programme.php">Manage Programmes</a>
                <a href="system_settings.php">System Settings</a>
                <a href="system_reports.php">System Reports</a>
                <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Programme Management</h1>

        <?php if(isset($msg)): ?>
            <div class="alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="action-grid">
            <div class="action-card" id="card-add" onclick="showSection('add')"><h3>+ Add New Programme</h3></div>
            <div class="action-card" id="card-edit" onclick="showSection('edit')"><h3>Edit Details</h3></div>
            <div class="action-card" id="card-coord" onclick="showSection('coord')"><h3>Assign Coordinator</h3></div>
            <div class="action-card" id="card-reqs" onclick="showSection('reqs')"><h3>Set KPIs</h3></div>
        </div>

        <div id="section-add" class="workflow-section">
            <h2>Add New Programme</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group"><label>Programme Name</label><input type="text" name="name" placeholder="e.g. Master of Data Science" required></div>
                <div class="form-group"><label>Programme Code</label><input type="text" name="code" placeholder="e.g. MDS-25" required></div>
                <div class="form-group">
                    <label>Faculty</label>
                    <select name="faculty">
                        <option value="">-- Select Faculty --</option>
                        <option>Faculty of Computing</option>
                        <option>Faculty of Management</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Level</label>
                    <select name="level">
                        <option value="">-- Select Level --</option>
                        <option>Bachelor's Degree</option>
                        <option>Master's Degree</option>
                        <option>PhD</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Save Programme</button>
            </form>
        </div>

        <div id="section-edit" class="workflow-section">
            <h2>Edit Programme Details</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <div class="form-group">
                    <label>Select Programme</label>
                    <select name="id" required>
                        <option value="">-- Select Programme --</option>
                        <?php foreach($programmes as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['name'] ?> (<?= $p['code'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>New Name</label><input type="text" name="name" required></div>
                <button type="submit" class="btn-primary">Update Information</button>
            </form>
        </div>

        <div id="section-coord" class="workflow-section">
            <h2>Assign Programme Coordinator</h2>
            <form method="POST">
                <input type="hidden" name="action" value="assign">
                
                <div class="form-group">
                    <label>Select Programme</label>
                    <select name="prog_id" required>
                        <option value="">-- Select Programme --</option>
                        <?php foreach($programmes as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Coordinator</label>
                    <select name="coord_id" required>
                        <option value="">-- Select Available Coordinator --</option>
                        <?php if (empty($coordinators)): ?>
                            <option disabled>No coordinators found. Update a user role first.</option>
                        <?php else: ?>
                            <?php foreach($coordinators as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['fullname']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Confirm Assignment</button>
            </form>
        </div>

        <div id="section-reqs" class="workflow-section">
            <h2>Set Publication Targets</h2>
            <form method="POST">
                <input type="hidden" name="action" value="kpi">
                <div class="form-group">
                    <label>Select Programme</label>
                    <select name="prog_id">
                        <option value="">-- Select Programme --</option>
                        <?php foreach($programmes as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Min. Publications</label><input type="number" name="min_pub" value="1"></div>
                <div class="form-group">
                    <label>Required Indexing</label><br>
                    <input type="checkbox" name="indexes[]" value="Scopus"> Scopus 
                    <input type="checkbox" name="indexes[]" value="WoS"> WoS 
                    <input type="checkbox" name="indexes[]" value="ERA"> ERA
                </div>
                <button type="submit" class="btn-primary">Define Requirements</button>
            </form>
        </div>
    </div>
</div>

<script>
    function showSection(id) {
        document.querySelectorAll('.workflow-section').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.action-card').forEach(el => el.classList.remove('active'));
        document.getElementById('section-' + id).style.display = 'block';
        document.getElementById('card-' + id).classList.add('active');
    }
    showSection('add');
</script>
</body>
</html>