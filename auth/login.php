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
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Check if user exists and password matches
        if ($user && $password === $user['password']) {
            
            // --- STATUS CHECK ---
            // Allow Admins through even if status is pending, others must be accepted
            if ($user['role'] !== 'Admin') {
                if ($user['status'] === 'pending') {
                    echo "<script>alert('Your account is still pending approval.'); window.location.href='../index.html';</script>";
                    exit();
                } elseif ($user['status'] === 'rejected') {
                    echo "<script>alert('Your account has been rejected.Please contact Admin and login again.'); window.location.href='../index.html';</script>";
                    exit();
                }
            }

            // 3. Set Session variables (CRITICAL: 'Admin' must be capitalized)
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role']     = $user['role']; // Stores "Admin"
            $_SESSION['stID']     = $user['stID']; 

            // 4. Redirect based on role
            if ($user['role'] === 'Admin') {
                header("Location: ../admin/admin_dashboard.php");
            } elseif ($user['role'] === 'Coordinator') {
                header("Location: ../coordinator_dashboard.php");
            } else {
                header("Location: ../author_dashboard.php");
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