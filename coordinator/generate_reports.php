<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports & KPI - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .report-section { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .grid-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .option-card { border: 2px solid #eee; padding: 15px; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.2s; }
        .option-card:hover, .option-card.selected { border-color: var(--primary-color); background: #f0f4f8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Coordinator</h2>
        <a href="coordinator_dashboard.php">Monitor Output</a>
        <a href="verify_list.php">Verify & Approve</a>
        <a href="graduation_eligibility.php">Graduation Check</a>
        <a href="generate_reports.php">Reports & KPI</a>
        <a href="../index.html" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>Generate Reports</h1>

        <div class="report-section">
            <h3>1. Select Report Type</h3>
            <div class="grid-options">
                <div class="option-card selected">Faculty KPI Report</div>
                <div class="option-card">Lecturer Performance</div>
                <div class="option-card">Publication List</div>
            </div>
        </div>

        <div class="report-section">
            <h3>2. Configuration & Filters</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Timeframe</label>
                    <select class="form-control">
                        <option>Current Year (2026)</option>
                        <option>Last Year (2025)</option>
                        <option>Last 5 Years</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Department/Faculty</label>
                    <select class="form-control">
                        <option>Faculty of Computing</option>
                        <option>Faculty of Management</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h3>3. Export Format</h3>
            <div style="margin-bottom: 20px;">
                <label style="margin-right: 20px;"><input type="radio" name="format" checked> PDF Document</label>
                <label><input type="radio" name="format"> Excel Spreadsheet</label>
            </div>
            <button class="btn-primary" style="width: 100%; padding: 15px;" onclick="document.getElementById('preview-area').style.display='block'">Generate Report</button>
        </div>

        <div id="preview-area" class="report-section" style="display: none; border-top: 4px solid var(--success-color);">
            <div class="header-flex">
                <h3>Report Preview: Faculty KPI 2026</h3>
                <div>
                    <button class="btn-secondary">Print</button>
                    <button class="btn-primary">Download</button>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Publications</td>
                        <td>100</td>
                        <td>112</td>
                        <td><span style="color: green;">Exceeded</span></td>
                    </tr>
                    <tr>
                        <td>Scopus Indexed</td>
                        <td>50</td>
                        <td>45</td>
                        <td><span style="color: orange;">On Track</span></td>
                    </tr>
                    <tr>
                        <td>Student Participation</td>
                        <td>30%</td>
                        <td>45%</td>
                        <td><span style="color: green;">Exceeded</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    // Simple script to handle selection visuals
    document.querySelectorAll('.option-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
</script>
</body>
</html>