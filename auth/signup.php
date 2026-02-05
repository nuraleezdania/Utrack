<?php
// 1. Enable error reporting to find issues easily
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'utrack_db';
$db_user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $db_user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fullname = $_POST['fullname'];
        $stID     = $_POST['stID'];
        $email    = $_POST['email'];
        $faculty  = $_POST['faculty'];
        $password = $_POST['password']; 
        $role     = $_POST['role']; 

        $status = ($role === 'Admin') ? 'accepted' : 'pending';

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR stID = ?");
        $check->execute([$email, $stID]);

        if ($check->rowCount() > 0) {
            display_message("Registration Failed", "Email or ID already exists.", "danger", "signup.php", "Try Again");
        } else {
            $sql = "INSERT INTO users (fullname, stID, email, faculty, password, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$fullname, $stID, $email, $faculty, $password, $role, $status])) {
                $msg = ($status === 'accepted') ? "Admin account active. You can log in now." : "Account pending approval.";
                // Using ./index.html is safer for local development
                display_message("Account Created!", $msg, "success", "../index.html", "Go to Login");
            }
        }
    } // <-- THIS WAS THE MISSING BRACKET
} catch (PDOException $e) {
    // This helps you see if your database column names are wrong
    die("Database Error: " . $e->getMessage());
}

function display_message($title, $text, $type, $link, $btnText) {
    $color = ($type == 'success') ? '#28a745' : '#dc3545';
    echo "<!DOCTYPE html><html><head><title>$title</title><style>
        body { background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .msg-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; max-width: 450px; }
        .icon { font-size: 50px; color: $color; margin-bottom: 20px; }
        .btn { background: #003366; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold; }
    </style></head><body><div class='msg-card'><div class='icon'>".($type == 'success' ? '✔' : '✖')."</div><h1>$title</h1><p>$text</p><a href='$link' class='btn'>$btnText</a></div></body></html>";
    exit();
}
?>