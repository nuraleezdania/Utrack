Project Name: UTrack - Academic Publication and Research Tracking System
Group Number: 1

--- INSTALLATION INSTRUCTIONS ---
1. Copy the "utrack" folder into your XAMPP "htdocs" folder.
2. Open phpMyAdmin (http://localhost/phpmyadmin).
3. Create a new database named "utrack_db".
4. Click "Import" and select the "utrack_db.sql" file found inside this folder.
5. Open your browser and go to: http://localhost/utrack/auth/login.php

--- LOGIN CREDENTIALS ---

1. ADMIN (Default Account)
   Email: admin@utrack.com  
   Password: [Enter Your Admin Password] <-- (CHECK YOUR DB FOR THE ACTUAL PASSWORD)

--- HOW TO TEST THE SYSTEM (FOR GRADING PURPOSE BY LECTURER)---
Since this is a clean installation, please follow these steps:

STEP 1: Register Test Users
1. Go to the Registration Page: http://localhost/utrack/auth/register.php
2. Register a new user with Role: "Coordinator".
3. Register another new user with Role: "Main Author"
4. Register another last new user with Role: "Co-Author"

STEP 2: Approve Users (Admin)
1. Log in as ADMIN using the credentials above.
2. Go to "Manage Users" on the dashboard.
3. You will see the new registrations with status "Pending".
4. Click "Accept" or "Approve" for all users.
5. Log out.

STEP 3: Test User Features
1. Log in as the new "Coordinator", "Main Author" and "Co-Author" to see the implementation

---------------------------------------------------------------------------------------------
