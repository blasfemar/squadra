<?php
require_once 'auth.php'; // Protects this page

// At this point, $_SESSION['user_id'] is set, and the user is authenticated.
// You can retrieve user details if needed, e.g., from the database or session.
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Administrator';

$updateMessage = '';
$updateError = '';

// Check for update messages from update_app.php (via session or query param)
if (isset($_SESSION['update_message'])) {
    $updateMessage = $_SESSION['update_message'];
    unset($_SESSION['update_message']); // Clear message after displaying
}
if (isset($_SESSION['update_error'])) {
    $updateError = $_SESSION['update_error'];
    unset($_SESSION['update_error']); // Clear error after displaying
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Assuming style.css is in assets/css -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f7f6; color: #333; }
        .admin-header { background-color: #333; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { margin: 0; font-size: 1.5em; }
        .admin-header a { color: #fff; text-decoration: none; padding: 8px 12px; background-color: #555; border-radius: 4px; }
        .admin-header a:hover { background-color: #007bff; }
        .admin-container { padding: 20px; }
        .welcome-message { font-size: 1.2em; margin-bottom: 20px; }
        .action-button { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 1em; }
        .action-button:hover { background-color: #218838; }
        .update-section { margin-top: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background-color: #fff; }
        .update-section h2 { margin-top: 0; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Admin Panel</h1>
        <a href="logout.php">Logout</a>
    </div>

    <div class="admin-container">
        <p class="welcome-message">Welcome, <?php echo $username; ?>!</p>

        <p>This is the main dashboard of your SEO Application. From here, you can manage settings and perform application updates.</p>

        <div class="update-section">
            <h2>Application Update</h2>
            <?php if ($updateMessage): ?>
                <div class="message success"><?php echo htmlspecialchars($updateMessage); ?></div>
            <?php endif; ?>
            <?php if ($updateError): ?>
                <div class="message error"><?php echo htmlspecialchars($updateError); ?></div>
            <?php endif; ?>
            <p>Click the button below to attempt to update the application by pulling the latest changes from the Git repository.</p>
            <form action="update_app.php" method="POST" onsubmit="return confirm('Are you sure you want to attempt to update the application?');">
                <button type="submit" class="action-button">Update Application</button>
            </form>
        </div>

        <!-- Other admin functionalities can be added here -->

    </div>
</body>
</html>
