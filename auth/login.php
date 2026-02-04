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
            
            // --- DETAILED STATUS CHECK ---
            // Admins are always allowed in. Others must be 'accepted'.
            if ($user['role'] !== 'admin') {
                if ($user['status'] === 'pending') {
                    echo "<script>alert('Your account is still pending approval. Please wait for the Admin to verify your details.'); window.location.href='../index.html';</script>";
                    exit();
                } elseif ($user['status'] === 'rejected') {
                    echo "<script>alert('Your registration request has been rejected. Please contact the Admin for more information.'); window.location.href='../index.html';</script>";
                    exit();
                }
            }

            // 3. Set Session variables
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['stID']     = $user['stID']; 

            // 4. Redirect based on role
            // Using ../ to go back to root from the auth/ folder
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } elseif ($user['role'] === 'coordinator') {
                header("Location: ../coordinator_dashboard.php");
            } else {
                // For student/lecturer (Author role)
                header("Location: ../author_dashboard.php");
            }
            exit(); 
            
        } else {
            // 5. Error handling for wrong credentials
            echo "<script>alert('Invalid Email or Password'); window.location.href='../index.html';</script>";
        }
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>