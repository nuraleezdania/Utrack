<?php
$host = 'localhost';
$db   = 'utrack_db';
$user = 'root';
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email    = $_POST['email'];
        $password = $_POST['password']; // In a real app, use password_hash()
        $role     = $_POST['role'];

        // 1. Check if email already exists
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);

        if ($checkEmail->rowCount() > 0) {
            echo "Error: This email is already registered. <a href='../signup.html'>Try again</a>";
        } else {
            // 2. Insert new user
            $sql = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$email, $password, $role])) {
                echo "<h1>Registration Successful!</h1>";
                echo "Account created for: " . htmlspecialchars($email);
                echo "<br><a href='../index.html'>Click here to Login</a>";
            }
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>