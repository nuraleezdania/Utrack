<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { exit("Unauthorized"); }

$host = 'localhost'; $db = 'utrack_db'; $user = 'root'; $pass = '';
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

if (isset($_GET['id']) && isset($_GET['status'])) {
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$_GET['status'], $_GET['id']]);
    header("Location: manage_users.php"); // Go back to the table
}
?>