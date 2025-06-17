<?php
require_once 'auth.php'; // Protects this page

// Ensure this script is accessed via POST, typically from the form in index.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, redirect to index or show an error.
    // For simplicity, redirecting. Could also set an error message.
    header('Location: index.php');
    exit;
}

$output = '';
$error = '';
$return_var = 0;

// Attempt to execute git pull
// IMPORTANT:
// 1. Ensure the web server user (e.g., www-data, apache) has the necessary permissions
//    to execute 'git' and write to the repository files. This might involve:
//    - Setting up SSH keys for the web server user if pulling from a private repo.
//    - Ensuring the .git directory and other files are writable by the web server user.
//    - Adding the web server user to the sudoers file (with caution) if git requires elevated privileges for some reason,
//      though this is generally not recommended for `git pull`.
// 2. The command `git pull` should be executed in the root directory of the repository.
//    This script assumes `update_app.php` is in `admin/`, so `../` should go to the repo root.
// 3. Error handling and output capturing are important for diagnostics.

$repo_root_path = realpath(__DIR__ . '/../'); // Get absolute path to repo root

if (!$repo_root_path) {
    $_SESSION['update_error'] = "Error: Could not determine the repository root path.";
    header('Location: index.php');
    exit;
}

// Construct the command. Redirect stderr to stdout to capture all output.
// `cd` to the repo root first, then execute `git pull`.
$command = 'cd ' . escapeshellarg($repo_root_path) . ' && git pull 2>&1';

// For security, it's good to ensure git is available and the path is safe.
// `exec` can be dangerous if not handled carefully.
// Consider alternatives or more robust security measures in a production environment.

// Clear previous messages
unset($_SESSION['update_message']);
unset($_SESSION['update_error']);

exec($command, $output_array, $return_var);
$output = implode("
", $output_array);

if ($return_var === 0) {
    // Check output for common "Already up to date." message
    if (strpos($output, 'Already up to date.') !== false || strpos($output, 'Ya está actualizado.') !== false) {
        $_SESSION['update_message'] = "Application is already up to date. <pre>" . htmlspecialchars($output) . "</pre>";
    } else {
        $_SESSION['update_message'] = "Application updated successfully! Output: <pre>" . htmlspecialchars($output) . "</pre>";
    }
} else {
    $_SESSION['update_error'] = "Error updating application (return code: {$return_var}). Output: <pre>" . htmlspecialchars($output) . "</pre>";
}

// Redirect back to the admin index page to show the message.
header('Location: index.php');
exit;
?>
