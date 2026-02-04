<?php
$host = 'localhost';
$db   = 'utrack_db';
$db_user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $db_user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fullname    = $_POST['fullname'];
        $stID        = $_POST['stID'];
        $email       = $_POST['email'];
        $faculty     = $_POST['faculty']; // Captured from new dropdown
        $password    = $_POST['password']; 
        $register_as = $_POST['register_as'];
        $role        = $_POST['role'];

        // Check for duplicates
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR stID = ?");
        $check->execute([$email, $stID]);

        if ($check->rowCount() > 0) {
            display_message("Registration Failed", "Email or ID already exists.", "danger", "../signup.html", "Try Again");
        } else {
            // INSERT with 7 values matching the DB structure
            $sql = "INSERT INTO users (fullname, stID, email, faculty, password, register_as, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$fullname, $stID, $email, $faculty, $password, $register_as, $role])) {
                display_message("Account Created!", "Welcome, " . htmlspecialchars($fullname) . ". Your account is pending admin approval.", "success", "../index.html", "Go to Login");
            }
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
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