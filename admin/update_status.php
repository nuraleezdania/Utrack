<?php
session_start();

// 1. Security: Only Admin can run this script
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.html?error=unauthorized"); 
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

    // 3. Check if ID and Status are provided via GET
    if (isset($_GET['id']) && isset($_GET['status'])) {
        $id = $_GET['id'];
        $status = $_GET['status'];

        // Update the user status
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // 4. Redirect back to manage_users.php with a success message
        header("Location: manage_users.php?msg=success");
        exit();
    } else {
        echo "Missing parameters.";
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>