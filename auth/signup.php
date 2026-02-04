<?php
// Database connection details
$host = 'localhost';
$db   = 'utrack_db';
$user = 'root';
$pass = ''; // Leave empty for default XAMPP on Mac

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 1. Capture data from HTML form names
        $fullname = $_POST['fullname'];
        $stID     = $_POST['stID'];
        $email    = $_POST['email'];
        $password = $_POST['password']; 
        $role     = $_POST['role'];

        // 2. Check if email or Staff/Student ID already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR stID = ?");
        $check->execute([$email, $stID]);

        if ($check->rowCount() > 0) {
            echo "Error: Email or Staff/Student ID already registered! <a href='../signup.html'>Try again</a>";
        } else {
            // 3. Insert into database (Matching your exact table structure)
            $sql = "INSERT INTO users (fullname, stID, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$fullname, $stID, $email, $password, $role])) {
                echo "<h1>Account Created Successfully!</h1>";
                echo "Welcome, " . htmlspecialchars($fullname) . ".<br>";
                echo "<a href='../index.html'>Click here to Login</a>";
            }
        }
    }
} catch (PDOException $e) {
    // This will help you if the connection fails
    die("Database Error: " . $e->getMessage());
}
?>