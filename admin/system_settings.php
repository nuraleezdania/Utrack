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

    // --- HANDLE FORM SUBMISSION ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        foreach ($_POST['settings'] as $key => $value) {
            
            // If it's the permissions array, store as JSON string
            if ($key === 'permissions') {
                $value = json_encode($value);
            } 
            // If it's other arrays (like pub_types), store as Comma Separated string
            elseif (is_array($value)) {
                $value = implode(", ", $value);
            }

            $stmt = $pdo->prepare("REPLACE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
        $msg = "System configuration and permissions saved successfully!";
    }

    // --- FETCH CURRENT SETTINGS ---
    $settingsRaw = $pdo->query("SELECT * FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Set Defaults if keys don't exist in DB yet
    $settings = array_merge([
        'pub_types' => 'Journal Article, Conference Proceeding',
        'verification_workflow' => 'coord',
        'auto_reject' => 'no',
        'min_publications' => '2',
        'report_frequency' => 'Monthly',
        'permissions' => json_encode([
            'student' => ['upload' => 1, 'edit' => 1, 'delete' => 0],
            'lecturer' => ['upload' => 1, 'edit' => 1, 'delete' => 1]
        ])
    ], $settingsRaw);

    $current_types = explode(", ", $settings['pub_types']);
    $perms = json_decode($settings['permissions'], true);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings - UTrack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .settings-box { background: white; padding: 20px; border-radius: 8px; border-top: 4px solid #003366; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .settings-box h3 { margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .permission-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .permission-table th { text-align: left; padding: 8px; border-bottom: 2px solid #eee; }
        .permission-table td { padding: 10px 8px; border-bottom: 1px solid #f9f9f9; }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb; }
        .small-text { font-size: 0.8rem; color: #777; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_users.php">Manage Users</a>
        <a href="manage_programme.php">Manage Programmes</a>
        <a href="system_settings.php" class="active">System Settings</a>
        <a href="system_reports.php">System Reports</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>System Configuration</h1>

        <?php if(isset($msg)): ?>
            <div class="alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="settings-grid">
                
                <div class="settings-box">
                    <h3>1. Publication Types</h3>
                    <span class="small-text">Select allowed categories for submission</span>
                    <div class="form-group">
                        <?php 
                        $types = ["Journal Article", "Conference Proceeding", "Book Chapter", "Technical Report"];
                        foreach($types as $t): ?>
                            <label>
                                <input type="checkbox" name="settings[pub_types][]" value="<?= $t ?>" 
                                <?= in_array($t, $current_types) ? 'checked' : '' ?>> <?= $t ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="settings-box">
                    <h3>2. Workflow Rules</h3>
                    <span class="small-text">Configure verification and approval steps.</span>
                    <div class="form-group">
                        <label>Approval Flow:</label>
                        <select name="settings[verification_workflow]" style="width:100%; padding:8px;">
                            <option value="coord" <?= ($settings['verification_workflow'] == 'coord') ? 'selected' : ''; ?>>Coordinator Only</option>
                            <option value="dual" <?= ($settings['verification_workflow'] == 'dual') ? 'selected' : ''; ?>>Coordinator + Admin</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label>Auto-Reject Incomplete Data?</label><br>
                        <input type="radio" name="settings[auto_reject]" value="yes" <?= ($settings['auto_reject'] == 'yes') ? 'checked' : ''; ?>> Yes
                        <input type="radio" name="settings[auto_reject]" value="no" <?= ($settings['auto_reject'] == 'no') ? 'checked' : ''; ?>> No
                    </div>
                </div>

                <div class="settings-box">
                    <h3>3. User Permissions</h3>
                    <span class="small-text">Modify role-based access controls.</span>
                    <table class="permission-table">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Upload</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(['student', 'lecturer'] as $role): ?>
                            <tr>
                                <td style="text-transform: capitalize; font-weight: bold;"><?= $role ?></td>
                                <td><input type="checkbox" name="settings[permissions][<?= $role ?>][upload]" value="1" <?= isset($perms[$role]['upload']) ? 'checked' : '' ?>></td>
                                <td><input type="checkbox" name="settings[permissions][<?= $role ?>][edit]" value="1" <?= isset($perms[$role]['edit']) ? 'checked' : '' ?>></td>
                                <td><input type="checkbox" name="settings[permissions][<?= $role ?>][delete]" value="1" <?= isset($perms[$role]['delete']) ? 'checked' : '' ?>></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="settings-box">
                    <h3>4. KPI & Reporting</h3>
                    <span class="small-text">Set calculations formulas and targets.</span>
                    <div class="form-group">
                        <label>Min. Publications:(Graduations)</label>
                        <input type="number" name="settings[min_publications]" value="<?= $settings['min_publications'] ?>" style="width:60px;">
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <label>Report Frequency:</label>
                        <select name="settings[report_frequency]" style="width:100%; padding:8px;">
                            <option <?= ($settings['report_frequency'] == 'Monthly') ? 'selected' : '' ?>>Monthly</option>
                            <option <?= ($settings['report_frequency'] == 'Quarterly') ? 'selected' : '' ?>>Quarterly</option>
                            <option <?= ($settings['report_frequency'] == 'Yearly') ? 'selected' : '' ?>>Yearly</option>
                        </select>
                    </div>
                </div>

            </div>

            <div style="margin-top: 30px; text-align: center; border-top: 1px solid #ddd; padding-top: 20px;">
                <button type="submit" class="btn-primary" style="padding: 12px 50px;">Apply All Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>