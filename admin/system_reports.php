<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports - UTrack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-types { display: flex; gap: 20px; margin-bottom: 20px; }
        .type-card {
            flex: 1;
            padding: 15px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            color: #666;
        }
        .type-card.selected {
            border-color: var(--primary-color);
            background-color: #eef2f6;
            color: var(--primary-color);
        }
        
        .filter-panel { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none; }
        
        .results-panel { background: white; padding: 20px; border-radius: 8px; display: none; }
        .export-bar { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 15px; }
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
        <h1>Generate Reports</h1>

        <h3>1. Select Report Type</h3>
        <div class="report-types">
            <div class="type-card" onclick="selectType(this, 'pub')">Publication Reports</div>
            <div class="type-card" onclick="selectType(this, 'user')">User Activity Reports</div>
            <div class="type-card" onclick="selectType(this, 'kpi')">KPI Performance</div>
        </div>

        <div id="filter-container" class="filter-panel">
            <h3>2. Apply Filters</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Timeframe</label>
                    <select class="form-control">
                        <option>Last 30 Days</option>
                        <option>This Quarter</option>
                        <option>This Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Faculty</label>
                    <select class="form-control">
                        <option>All Faculties</option>
                        <option>FOC</option>
                        <option>FOM</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button class="btn-primary" style="width: 100%;" onclick="generateReport()">Generate Report</button>
                </div>
            </div>
        </div>

        <div id="results-container" class="results-panel">
            <div class="export-bar">
                <button class="btn-secondary" onclick="alert('Exporting to Excel...')">Export Excel</button>
                <button class="btn-danger" onclick="alert('Exporting to PDF...')">Export PDF</button>
            </div>
            
            <h3>Report Results</h3>
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Count / Value</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Publications</td>
                        <td>145</td>
                        <td>+12% vs last month</td>
                    </tr>
                    <tr>
                        <td>Approved</td>
                        <td>120</td>
                        <td>82% Approval Rate</td>
                    </tr>
                    <tr>
                        <td>Rejected</td>
                        <td>25</td>
                        <td>Most common reason: Formatting</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    function selectType(element, type) {
        // Visual selection
        document.querySelectorAll('.type-card').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        
        // Show filters
        document.getElementById('filter-container').style.display = 'block';
        
        // Hide results until generated again
        document.getElementById('results-container').style.display = 'none';
    }

    function generateReport() {
        // Show results (Simulates Diagram Step "View Results")
        document.getElementById('results-container').style.display = 'block';
    }
</script>
</body>
</html>