<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings - UTrack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .settings-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-top: 4px solid var(--primary-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .settings-box h3 { margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .permission-table td { padding: 8px; border: none; font-size: 0.9rem; }
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
        <h1>System Configuration</h1>

        <form onsubmit="event.preventDefault(); saveAllChanges();">
            <div class="settings-grid">
                
                <div class="settings-box">
                    <h3>1. Publication Types</h3>
                    <p class="small-text">Select allowed categories for submission.</p>
                    <div class="form-group">
                        <label><input type="checkbox" checked> Journal Article</label>
                        <label><input type="checkbox" checked> Conference Proceeding</label>
                        <label><input type="checkbox" checked> Book Chapter</label>
                        <label><input type="checkbox"> Technical Report</label>
                    </div>
                    <div style="display:flex; gap:5px;">
                        <input type="text" placeholder="Add custom type..." style="padding:5px;">
                        <button type="button" class="btn-secondary" style="padding:5px 10px;">Add</button>
                    </div>
                </div>

                <div class="settings-box">
                    <h3>2. Workflow Rules</h3>
                    <p class="small-text">Configure verification and approval steps.</p>
                    <div class="form-group">
                        <label>Verification Required By:</label>
                        <select class="form-control">
                            <option value="coord">Programme Coordinator Only</option>
                            <option value="dual">Coordinator + Admin (Dual Step)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Auto-Reject if incomplete?</label>
                        <input type="radio" name="autoreject" value="yes"> Yes
                        <input type="radio" name="autoreject" value="no" checked> No
                    </div>
                </div>

                <div class="settings-box">
                    <h3>3. User Permissions</h3>
                    <p class="small-text">Modify role-based access controls.</p>
                    <table class="permission-table">
                        <tr>
                            <td><strong>Role</strong></td>
                            <td><strong>Upload</strong></td>
                            <td><strong>Edit</strong></td>
                            <td><strong>Delete</strong></td>
                        </tr>
                        <tr>
                            <td>Student</td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="checkbox"></td>
                        </tr>
                        <tr>
                            <td>Lecturer</td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="checkbox" checked></td>
                            <td><input type="checkbox" checked></td>
                        </tr>
                    </table>
                </div>

                <div class="settings-box">
                    <h3>4. KPI Configuration</h3>
                    <p class="small-text">Set calculation formulas and targets.</p>
                    <div class="form-group">
                        <label>Min. Publications (Graduation)</label>
                        <input type="number" value="2" style="width: 60px;">
                    </div>
                    <div class="form-group">
                        <label>Citation Weightage</label>
                        <input type="range" min="1" max="10" value="5">
                    </div>
                    <div class="form-group">
                        <label>Report Frequency</label>
                        <select>
                            <option>Monthly</option>
                            <option>Quarterly</option>
                            <option>Yearly</option>
                        </select>
                    </div>
                </div>

            </div> <div style="margin-top: 30px; text-align: center;">
                <hr>
                <button type="submit" class="btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">Save All Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function saveAllChanges() {
        // Simulates the "Save all changes" node in your diagram
        alert("System Configuration Saved Successfully!");
    }
</script>

</body>
</html>