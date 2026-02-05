<?php 
include 'db_config.php';

$stmt = $pdo->query("SELECT * FROM publications WHERE status = 'pending' OR status = 'Resubmitted' ORDER BY created_at DESC");
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Publications - UTrack</title>
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
        <h1>Verification Queue</h1>
        <p>Review and validate pending research submissions.</p>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)): ?>
                    <tr><td colspan="6" style="text-align:center;">No pending submissions.</td></tr>
                <?php else: ?>
                    <?php foreach ($submissions as $row): ?>
                    <tr>
                        <td><?php echo $row['date_submitted']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['author_name']); ?></td>
                        <td><?php echo $row['type']; ?> (<?php echo $row['indexing']; ?>)</td>
                        <td><span class="badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <a href="process_publication.php?id=<?php echo $row['publication_id']; ?>" class="btn-primary" style="text-decoration: none; font-size: 0.9rem;">Review</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>