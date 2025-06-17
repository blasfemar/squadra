<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in.
// If not, redirect them to login.php.
if (!isset($_SESSION['user_id'])) {
    // Determine the correct path to login.php relative to the admin directory
    // Assumes auth.php is in the admin directory.
    // If login.php is also in admin/, then 'login.php' is fine.
    // If login.php is outside admin/, path needs adjustment.
    // Based on the structure, login.php is in admin/.
    header('Location: login.php');
    exit();
}

// Optional: You might want to add further checks here,
// e.g., verify that $_SESSION['user_id'] corresponds to a valid user in the database,
// or check for session hijacking. For now, this is a basic check.
?>
