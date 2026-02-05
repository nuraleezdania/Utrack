<?php
session_start();

$host = 'localhost';
$db   = 'utrack_db'; 
$db_user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $db_user, $pass);
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
            
            // --- STATUS CHECK ---
            if ($user['role'] !== 'admin') {
                if ($user['status'] === 'pending') {
                    // Note: We use ../index.html here because index.html IS in the parent folder
                    echo "<script>alert('Account pending approval.'); window.location.href='../index.html';</script>";
                    exit();
                } elseif ($user['status'] === 'rejected') {
                    echo "<script>alert('Registration rejected.'); window.location.href='../index.html';</script>";
                    exit();
                }
            }

            // 3. Set Session variables
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['stID']     = $user['stID']; 

            // 4. Redirect based on role
            // FIX: Removed "../" because the dashboard files are in the same 'auth' folder as login.php
            
            if ($user['role'] === 'admin') {
                // Matches 'admin_d.php' in your screenshot
                header("Location: admin_d.php"); 
            } elseif ($user['role'] === 'coordinator') {
                // Matches 'coordinator_dashboard.php' in your screenshot
                header("Location: coordinator_dashboard.php");
            } else {
                // For Main Author (Student/Lecturer)
                // Matches 'author_dashboard.php' in your screenshot
                header("Location: author_dashboard.php");
            }
            exit(); 
            
        } else {
            echo "<script>alert('Invalid Email or Password'); window.location.href='../index.html';</script>";
        }
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>