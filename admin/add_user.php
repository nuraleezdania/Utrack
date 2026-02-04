<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User - UTrack Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.html">Dashboard</a>
        <a href="manage_users.html" class="active">Manage Users</a> <a href="manage_programme.html">Manage Programmes</a>
        <a href="system_settings.html">System Settings</a>
        <a href="../index.html" class="logout-btn">Logout</a>
    </div>

    <div class="main-content">
        <div class="header-flex">
            <h1>Register New User</h1>
            <a href="manage_users.html" class="btn-secondary">Back to List</a>
        </div>

        <div class="form-container">
            <form action="process_add_user.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required>
                </div>

                <div class="form-group">
                    <label>Email Address (Official)</label>
                    <input type="email" name="email" placeholder="@uni.edu.my" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="roleSelect" onchange="toggleFields()">
                        <option value="student">Student</option>
                        <option value="lecturer">Lecturer</option>
                        <option value="coordinator">Programme Coordinator</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>User ID (Staff ID or Student ID)</label>
                    <input type="text" name="user_id" required>
                </div>

                <div class="form-group">
                    <label>Faculty</label>
                    <select name="faculty">
                        <option value="FOC">Faculty of Computing</option>
                        <option value="FOM">Faculty of Management</option>
                        <option value="FCM">Faculty of Creative Multimedia</option>
                    </select>
                </div>

                <div class="form-group" id="progField">
                    <label>Programme</label>
                    <select name="programme">
                        <option value="BCS-SE">Bachelor of Computer Science (SE)</option>
                        <option value="BIT-CYB">Bachelor of IT (Cybersecurity)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Temporary Password</label>
                    <input type="text" name="password" value="UTrack2026!" readonly style="background:#eee;">
                    <small>User will be prompted to change this upon first login.</small>
                </div>

                <button type="submit" class="btn-primary">Create User Account</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Simple script to toggle Programme field based on Role
    function toggleFields() {
        const role = document.getElementById('roleSelect').value;
        const progField = document.getElementById('progField');
        if (role === 'student') {
            progField.style.display = 'block';
        } else {
            progField.style.display = 'none';
        }
    }
</script>
</body>
</html>