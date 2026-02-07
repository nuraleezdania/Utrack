<?php
$sname = "localhost";
$uname = "root";
$password = ""; // Default XAMPP password is empty
$db_name = "utrack_db";

// 1. MySQLi Connection (Keep this for your existing files)
$conn = mysqli_connect($sname, $uname, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. PDO Connection (ADD THIS PART)
// This is required for the new Signup page and Admin features
try {
    $dsn = "mysql:host=$sname;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $uname, $password);
    
    // Enable error reporting for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}
?>