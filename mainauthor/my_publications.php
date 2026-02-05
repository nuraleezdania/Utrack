<?php
session_start();
include "../db_conn.php";

// Set user ID (Hardcoded for testing, use session in real app)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 15;

// --- DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    
    // Security check: Ensure the user owns this publication before deleting
    $sql_del = "DELETE FROM publications WHERE id='$del_id' AND user_id='$user_id'";
    
    if (mysqli_query($conn, $sql_del)) {
        echo "<script>alert('Publication deleted successfully.'); window.location.href='my_publications.php';</script>";
    } else {
        echo "<script>alert('Error deleting record.');</script>";
    }
}

// --- FETCH LOGIC ---
// Get all publications for this user, ordered by newest first
$sql = "SELECT * FROM publications WHERE user_id='$user_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
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
    <a href="../auth/author_dashboard.php">Dashboard</a>
    <a href="add_publication.php">Add New Publication</a>
    <a href="my_publications.php" class="active">My Publications</a>
    <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

    <div class="main-content">
        <h1>My Publications</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Venue / Journal</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <?php 
                            // Determine Badge Color based on status
                            $status_class = 'status-pending'; // default
                            if($row['status'] == 'Approved') $status_class = 'status-approved';
                            if($row['status'] == 'Rejected') $status_class = 'status-rejected';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['venue']); ?></td>
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
                    <tr><td colspan="4" style="text-align:center;">No publications found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>