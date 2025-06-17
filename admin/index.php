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

// Fetch Git Commit Hash for version
$version = '';
$repo_root_path_for_version = realpath(__DIR__ . '/../'); // Assumes admin is one level below repo root

if ($repo_root_path_for_version) {
    // Command to get the short commit hash
    $git_command = 'cd ' . escapeshellarg($repo_root_path_for_version) . ' && git rev-parse --short HEAD';

    // Execute the command. Redirect stderr to stdout to capture any git errors.
    $version_output = shell_exec($git_command . ' 2>&1');

    if ($version_output !== null) {
        $version_output = trim($version_output);
        // Check if the output looks like a hash (e.g., 7-12 hex chars) and not an error message
        if (preg_match('/^[0-9a-f]{7,12}$/', $version_output)) {
            $version = $version_output;
        } elseif (strpos(strtolower($version_output), 'fatal') !== false || strpos(strtolower($version_output), 'error') !== false) {
            $version = 'N/A (git error)'; // Git command failed
        } else {
            $version = 'N/A (not a repo?)'; // Output doesn't look like a hash or known error
        }
    } else {
        $version = 'N/A (exec failed)'; // shell_exec itself might have failed or returned null
    }
} else {
    $version = 'N/A (path error)'; // Could not determine repo root path
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
        /* .admin-header h1 { margin: 0; font-size: 1.5em; } Ensure this is removed or adapted */
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

        /* CSS for the new structure in the header */
        .admin-header .header-title { /* Container for h1 and small */
            /* Add any specific styling if needed, e.g., flex direction if they were inline */
        }
        .admin-header .header-title h1 {
            margin: 0;
            font-size: 1.5em;
            line-height: 1.2; /* Adjust if needed */
        }
        .admin-header .header-title small {
            font-size: 0.8em;
            color: #ccc;
            display: block; /* Makes it appear on the next line */
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="header-title">
            <h1>Admin Panel</h1>
            <small>Version: <?php echo htmlspecialchars($version); ?></small>
        </div>
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
