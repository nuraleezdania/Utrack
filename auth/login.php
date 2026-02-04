<?php
session_start(); // Starts a session to keep the user logged in

$host = 'localhost';
$db   = 'utrack_db'; // Ensure this matches your DB name
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Check if form was submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Fetch user from DB
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check if user exists and password matches
        // Note: In production, use password_verify($password, $user['password'])
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            
            echo "Login successful! Welcome " . $user['email'];
            // header("Location: ../dashboard.php"); // Redirect to dashboard
        } else {
            echo "Invalid email or password.";
        }
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>