<?php
include 'db_config.php';

$sql = "SELECT authors, COUNT(*) as total_pubs, 
        SUM(CASE WHEN indexing_type IN ('Scopus', 'WoS') THEN 1 ELSE 0 END) as indexed_count
        FROM publications WHERE status = 'accepted' GROUP BY authors";
$stmt = $pdo->query($sql);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Graduation Check - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <h1>Graduation Eligibility Checker</h1>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Total Approved</th>
                    <th>Indexed</th>
                    <th>Requirement (Min 2)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $row): 
                    $isEligible = ($row['total_pubs'] >= 2);
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['authors']); ?></strong></td>
                    <td style="font-weight: bold;"><?php echo $row['total_pubs']; ?></td>
                    <td><?php echo $row['indexed_count']; ?></td>
                    <td><?php echo $isEligible ? '<span style="color: green;">Met</span>' : '<span style="color: red;">Failed</span>'; ?></td>
                    <td>
                        <span class="badge <?php echo $isEligible ? 'status-verified' : 'status-rejected'; ?>">
                            <?php echo $isEligible ? 'Eligible' : 'Not Eligible'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-primary" style="font-size: 0.8rem;">Send Report</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>