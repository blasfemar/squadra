<?php
session_start();

// If user is already logged in, redirect to admin index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$configFilePath = __DIR__ . '/../api/config.php';
$errorMessage = '';
$db = null;

if (!file_exists($configFilePath)) {
    $errorMessage = "Configuration file missing. Please run the installer (install.php).";
} else {
    require_once $configFilePath; // Defines DB_HOST, DB_NAME, DB_USER, DB_PASS
    try {
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            throw new Exception("Database configuration constants are not defined in api/config.php.");
        }
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $errorMessage = "Database connection error: " . $e->getMessage() . ". Ensure the details in api/config.php are correct and the database server is running.";
    } catch (Exception $e) {
        $errorMessage = "Configuration error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errorMessage = "Username and password are required.";
    } else {
        try {
            $stmt = $db->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username']; // Optional: store username too
                header('Location: index.php');
                exit();
            } else {
                $errorMessage = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Database query error: " . $e->getMessage();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$db && empty($errorMessage)) {
    // This case should ideally be caught by the initial DB connection check
    $errorMessage = "Cannot process login: Database connection not established.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Assuming style.css is in assets/css -->
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 320px; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: bold; }
        input[type="text"], input[type="password"] { width: calc(100% - 20px); padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
        button:hover { background-color: #0056b3; }
        .error-message { background-color: #ffecec; color: #c00; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .info-message { background-color: #e6f7ff; color: #006080; border: 1px solid #b3e0ff; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .install-notice { margin-top: 20px; text-align: center; font-size: 0.9em; color: #666; }
        .install-notice a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Panel Login</h1>

        <?php if (!file_exists($configFilePath) || (defined('DB_INSTALLATION_NEEDED') && DB_INSTALLATION_NEEDED)): ?>
            <!-- This specific check for DB_INSTALLATION_NEEDED is illustrative; primary check is file_exists -->
            <div class="info-message">
                Application not fully installed or configuration is missing.
                Please run the <a href="../install.php">installation script</a>.
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (file_exists($configFilePath) && $db): // Only show form if config exists and DB connection was attempted ?>
        <form method="POST" action="login.php">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <?php elseif (file_exists($configFilePath) && !$db && empty($errorMessage)):
            // This case indicates config exists, but $db is null, and no specific $errorMessage was set prior for this.
            // This might happen if the initial PDO connection itself threw an error not caught by the first $errorMessage assignment.
        ?>
            <div class="error-message">Could not connect to the database. Please verify settings in api/config.php or run the installer.</div>
        <?php endif; ?>

        <?php if (!file_exists($configFilePath)): ?>
        <div class="install-notice">
            If you haven't installed the application yet, please <a href="../install.php">run the installer</a>.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
