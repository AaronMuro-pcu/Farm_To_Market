<?php
// ---------------------------------------------------------------
// FarmLinked admin settings (XAMPP defaults)
// ---------------------------------------------------------------
date_default_timezone_set('Asia/Manila');

// Database: use the name you see in phpMyAdmin
define('DB_HOST', 'localhost');
define('DB_NAME', 'farmer_buyer');
define('DB_USER', 'root');
define('DB_PASS', '');

// Where people who are not logged in as admin get sent.
// Path of your sign-in page, relative to the admin/ folder.
define('LOGIN_URL', '../auth/login.php');

// A user counts as "online" if they were active in the last N minutes.
define('ONLINE_MINUTES', 15);

// Rows per page on the Users, Approvals and Login Activity lists.
define('PER_PAGE', 20);
