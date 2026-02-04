<?php
session_start(); // Starts a session to keep the user logged in

$host = 'localhost';
$db   = 'utrack_db'; // Ensure this matches your DB name
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // 1. Fetch user by email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Check if user exists and password matches
        if ($user && $password === $user['password']) {
            
            // 3. Set Session variables to use in the Dashboard
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role']     = $user['role'];

            // 4. Redirect based on role
            // Ensure these values match exactly what is in your database 'role' column
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } elseif ($user['role'] === 'coordinator') {
                header("Location: ../coordinator_dashboard.php");
            } else {
                // For student/lecturer roles
                header("Location: ../author_dashboard.php");
            }
            exit(); 
            
        } else {
            // Error handling
            echo "<script>alert('Invalid Email or Password'); window.location.href='../index.html';</script>";
        }
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>