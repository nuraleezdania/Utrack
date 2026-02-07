<?php
// 1. Include Database Connection
include 'db_conn.php';

// Fallback: Create local connection if global one fails
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=utrack_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }
}

// 2. Fetch Programmes for the Dropdown
$programmes = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM programmes ORDER BY name ASC");
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* Ignore */ }

// 3. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $stID     = $_POST['stID'];
    $email    = $_POST['email'];
    $faculty  = $_POST['faculty'];
    $password = $_POST['password'];
    $role     = $_POST['role'];
    
    // Capture Programme ID (Allow NULL)
    $programme_id = !empty($_POST['programme_id']) ? $_POST['programme_id'] : NULL;

    try {
        // Check for duplicates
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR stID = ?");
        $check->execute([$email, $stID]);

        if ($check->rowCount() > 0) {
             echo "<script>alert('Email or ID already exists!'); window.history.back();</script>";
        } else {
            $sql = "INSERT INTO users (fullname, stID, email, faculty, password, role, status, programme_id) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$fullname, $stID, $email, $faculty, $password, $role, $programme_id])) {
                 echo "<script>alert('Registration Successful! Please wait for Admin approval.'); window.location.href='index.html';</script>";
            }
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - UTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background-color: #003366; font-family: Arial, sans-serif; }
        .signup-container {
            width: 450px; margin: 50px auto; background: white; padding: 30px;
            border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 12px; background: #003366; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-primary:hover { background: #002244; }
    </style>
</head>
<body>

<div class="signup-container">
    <h2 style="text-align:center; color:#003366;">UTrack Registration</h2>
    
    <form action="" method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" required class="form-control">
        </div>
        
        <div class="form-group">
            <label>Student/Staff ID</label>
            <input type="text" name="stID" required class="form-control">
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required class="form-control">
        </div>

        <div class="form-group">
            <label>Faculty</label>
            <select name="faculty" class="form-control">
                <option>Faculty of Computing</option>
                <option>Faculty of Management</option>
                <option>Faculty of Engineering</option>
            </select>
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control" id="roleSelect" onchange="toggleProgramme()">
                <option value="Main Author(Student)">Student (Main Author)</option>
                <option value="Co-Author(Student)">Student (Co-Author)</option>
                <option value="Coordinator">Coordinator</option>
                <option value="Main Author(Lecturer)">Lecturer (Main Author)</option>
                <option value="Co-Author(Lecturer)">Lecturer (Co-Author)</option>
            </select>
        </div>

        <div class="form-group" id="progDiv">
            <label>Select Programme</label>
            <select name="programme_id" class="form-control">
                <option value="">-- Select Programme --</option>
                <?php if (!empty($programmes)): ?>
                    <?php foreach ($programmes as $p): ?>
                        <option value="<?php echo $p['id']; ?>">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No programmes available (Contact Admin)</option>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required class="form-control">
        </div>

        <button type="submit" class="btn-primary">Create Account</button>
        <p style="text-align:center; margin-top:15px; font-size:0.9rem;">
            Already have an account? <a href="../index.html">Login here</a>
        </p>
    </form>
</div>

<script>
    function toggleProgramme() {
        var role = document.getElementById('roleSelect').value;
        var progDiv = document.getElementById('progDiv');
        // Only show Programme dropdown if the role contains "Student"
        if (role.indexOf('Student') !== -1) {
            progDiv.style.display = 'block';
        } else {
            progDiv.style.display = 'none';
        }
    }
    // Run on page load
    toggleProgramme();
</script>

</body>
</html>