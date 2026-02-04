<?php
// Database connection details
$host = 'localhost';
$db   = 'utrack_db';
$db_user = 'root'; // Database username
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $db_user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // 1. Capture all form data (including the new register_as)
        $fullname    = $_POST['fullname'];
        $stID        = $_POST['stID'];
        $email       = $_POST['email'];
        $password    = $_POST['password']; 
        $register_as = $_POST['register_as']; // NEW FIELD
        $role        = $_POST['role'];

        // 2. Check if email or Staff/Student ID already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR stID = ?");
        $check->execute([$email, $stID]);

        if ($check->rowCount() > 0) {
            display_message("Registration Failed", "Email or ID already exists.", "danger", "../signup.html", "Try Again");
        } else {
            // 3. Updated SQL to include 'register_as' column
            // Make sure the number of ? matches the number of variables (6 total)
            $sql = "INSERT INTO users (fullname, stID, email, password, register_as, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$fullname, $stID, $email, $password, $register_as, $role])) {
                display_message("Account Created!", "Welcome, " . htmlspecialchars($fullname) . ". Your account is currently <strong>pending approval</strong> from the Admin.", "success", "../index.html", "Go to Login");
            }
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// Helper function for the Interface
function display_message($title, $text, $type, $link, $btnText) {
    $color = ($type == 'success') ? '#28a745' : '#dc3545';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $title; ?> - UTrack</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <style>
            body { background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            .msg-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; max-width: 450px; width: 90%; }
            .icon { font-size: 50px; color: <?php echo $color; ?>; margin-bottom: 20px; }
            h1 { color: #333; margin-bottom: 10px; font-size: 24px; }
            p { color: #666; margin-bottom: 30px; line-height: 1.5; }
            .btn { background: #003366; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; }
            .btn:hover { background: #002244; }
        </style>
    </head>
    <body>
        <div class="msg-card">
            <div class="icon"><?php echo ($type == 'success') ? '✔' : '✖'; ?></div>
            <h1><?php echo $title; ?></h1>
            <p><?php echo $text; ?></p>
            <a href="<?php echo $link; ?>" class="btn"><?php echo $btnText; ?></a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
?>