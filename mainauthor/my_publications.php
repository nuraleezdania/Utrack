<?php
// --- FIX: EXTEND SESSION TIME (24 HOURS) ---
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);
session_start();

include "../db_conn.php";

// Fallback
if (!isset($conn) && !isset($pdo)) {
    $conn = mysqli_connect("localhost", "root", "", "utrack_db");
}

// --- NO REDIRECT LOGIC ---
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = 0; // Safe Empty Mode
}

// Delete Logic
if (isset($_GET['delete_id']) && $user_id != 0) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $sql_del = "DELETE FROM publications WHERE id='$del_id' AND user_id='$user_id'";
    mysqli_query($conn, $sql_del);
    header("Location: my_publications.php"); 
    exit();
}

// Fetch Logic
if ($user_id != 0) {
    $sql = "SELECT * FROM publications WHERE user_id='$user_id' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
} else {
    $result = false; // Don't run query if no user
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Publications - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>UTrack Author</h2>
        <a href="mainauthor_dashboard.php">Dashboard</a>
        <a href="my_publications.php" class="active">My Publications</a>
        <a href="add_publication.php">Add New Publication</a>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <h1>My Publications</h1>
        
        <?php if($user_id == 0): ?>
            <p style="color: red;">Session expired. Please refresh the page to log in again.</p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Venue</th>
                    <th>Citations</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <?php 
                            $status_class = 'status-pending'; 
                            if($row['status'] == 'Approved') $status_class = 'status-approved';
                            if($row['status'] == 'Rejected') $status_class = 'status-rejected';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['venue']); ?></td>
                            <td style="font-weight: bold; text-align: center;"><?php echo $row['citations']; ?></td>
                            <td><span class="badge <?php echo $status_class; ?>"><?php echo $row['status']; ?></span></td>
                            <td>
                                <?php if($row['status'] == 'Rejected'): ?>
                                    <a href="resubmit_publication.php?id=<?php echo $row['id']; ?>" style="color:var(--danger-color); font-weight:bold;">Resubmit</a>
                                <?php elseif($row['status'] == 'Approved'): ?>
                                    <a href="share_publication.php" style="color:var(--success-color); font-weight:bold;">Share</a>
                                <?php else: ?>
                                    <a href="edit_publication.php?id=<?php echo $row['id']; ?>" style="color:var(--primary-color); font-weight:bold;">Edit</a>
                                <?php endif; ?>

                                <a href="my_publications.php?delete_id=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Are you sure? This cannot be undone.');" 
                                   class="btn-danger" style="margin-left:10px; text-decoration:none; font-size:0.9rem;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">
                        <?php echo ($user_id == 0) ? "No data available (Session Expired)" : "No publications found."; ?>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>