<?php
// Use the central connection file
include '../db_conn.php';

// Fallback if db_conn.php connection variable ($pdo) isn't set
if (!isset($pdo)) {
    $host = 'localhost';
    $db   = 'utrack_db';
    $db_user = 'root'; 
    $pass = ''; 
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $db_user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname    = $_POST['fullname'];
    $stID        = $_POST['stID'];
    $email       = $_POST['email'];
    $faculty     = $_POST['faculty'];
    $password    = $_POST['password']; 
    
    // FIX: We only use 'role', we don't need 'register_as'
    // If your HTML form sends 'register_as', we can just ignore it or map it to role if needed.
    // Assuming 'role' contains the value like "Main Author(Student)"
    $role        = $_POST['role']; 

    try {
        // Check for duplicates
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR stID = ?");
        $check->execute([$email, $stID]);

        if ($check->rowCount() > 0) {
            display_message("Registration Failed", "Email or ID already exists.", "danger", "../signup.html", "Try Again");
        } else {
            // FIX: Removed 'register_as' from this list
            $sql = "INSERT INTO users (fullname, stID, email, faculty, password, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $pdo->prepare($sql);
            
            // FIX: Removed $register_as from execution array
            if ($stmt->execute([$fullname, $stID, $email, $faculty, $password, $role])) {
                display_message("Account Created!", "Welcome, " . htmlspecialchars($fullname) . ". Your account is pending admin approval.", "success", "../index.html", "Go to Login");
            }
        }
    } catch (PDOException $e) {
        display_message("System Error", "Database error: " . $e->getMessage(), "danger", "../signup.html", "Back");
    }
}

function display_message($title, $text, $type, $link, $btnText) {
    $color = ($type == 'success') ? '#28a745' : '#dc3545';
    echo "<!DOCTYPE html><html><head><title>$title</title><link rel='stylesheet' href='../assets/css/style.css'><style>
        body { background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .msg-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; max-width: 450px; }
        .icon { font-size: 50px; color: $color; margin-bottom: 20px; }
        .btn { background: #003366; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; display: inline-block; }
    </style></head><body><div class='msg-card'>
        <div class='icon'>".($type == 'success' ? '✔' : '✖')."</div>
        <h1>$title</h1><p>$text</p><a href='$link' class='btn'>$btnText</a>
    </div></body></html>";
    exit();
}
?>