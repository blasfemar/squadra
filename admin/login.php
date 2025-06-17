<?php
require_once __DIR__ . '/../api/helpers.php'; // Include helpers for t() and $pdo

// session_start() is called in auth.php, which is included by helpers.php -> db_connect.php (potentially)
// or should be called here if not guaranteed.
// For login page, session is mainly for redirecting if already logged in, or setting session on success.
// helpers.php includes db_connect.php. db_connect.php does NOT start a session.
// auth.php starts a session. login.php does not require auth.php.
// So, login.php needs to start its own session.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect to admin index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$configFilePath = __DIR__ . '/../api/config.php';
$errorMessage = '';
// $db is already globally available via db_connect.php (included by helpers.php)
global $pdo; // Explicitly bring $pdo into local scope if not already via auto-globals or specific setup

if (!$pdo && file_exists($configFilePath)) {
    // This case means db_connect.php was included, config.php exists, but $pdo is still null.
    // This implies an error during PDO connection attempt within db_connect.php.
    // db_connect.php logs errors, so we provide a generic message here.
    $errorMessage = t('login_error_db_connection_specific', [], 'Database connection failed. Please check server logs and api/config.php.');
} elseif (!file_exists($configFilePath)) {
    // This is checked before $pdo, as $pdo would be null if config.php is missing.
    $errorMessage = t('login_error_config_missing', [], 'Configuration file missing. Please run the installer (install.php).');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo) {
        // If $pdo is still null at this point (e.g. config file existed but connection failed, or config file missing)
        // $errorMessage would have been set above. If it's somehow empty, set a generic one.
        if (empty($errorMessage)) {
             $errorMessage = t('login_error_cannot_process_no_db', [], 'Cannot process login: Database connection not established.');
        }
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $errorMessage = t('login_error_credentials_required', [], 'Username and password are required.');
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header('Location: index.php');
                    exit();
                } else {
                    $errorMessage = t('login_error_invalid_credentials', [], 'Invalid username or password.');
                }
            } catch (PDOException $e) {
                error_log("Login page DB query error: " . $e->getMessage()); // Log detailed error
                $errorMessage = t('login_error_db_query', [], 'Database query error. Please try again later.');
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?php echo t('html_lang', [], 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo t('admin_login_page_title', [], 'Admin Login'); ?></title>
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
        <h1><?php echo t('admin_login_form_title', [], 'Admin Panel Login'); ?></h1>

        <?php if (!file_exists($configFilePath)): ?>
            <div class="info-message">
                <?php echo t('login_app_not_installed_message', [], 'Application not fully installed or configuration is missing.'); ?>
                <a href="../install.php"><?php echo t('login_run_installer_link_text', [], 'Please run the installation script.'); ?></a>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (file_exists($configFilePath) && $pdo): // Only show form if config exists and DB connection is OK ?>
        <form method="POST" action="login.php">
            <div>
                <label for="username"><?php echo t('username_label', [], 'Username:'); ?></label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password"><?php echo t('password_label', [], 'Password:'); ?></label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit"><?php echo t('login_button', [], 'Login'); ?></button>
        </form>
        <?php elseif (file_exists($configFilePath) && !$pdo && empty($errorMessage)):
            // This state implies config exists, db_connect.php was included, but $pdo is null.
            // An error message should have been set by the initial $pdo check. If not, this is a fallback.
        ?>
            <div class="error-message"><?php echo t('login_error_db_connection_verify_settings', [], 'Could not connect to the database. Please verify settings in api/config.php or run the installer.'); ?></div>
        <?php endif; ?>

        <?php if (!file_exists($configFilePath)): ?>
        <div class="install-notice">
            <?php echo t('login_footer_installer_notice', [], "If you haven't installed the application yet, please"); ?> <a href="../install.php"><?php echo t('login_run_installer_link_text', [], 'run the installer'); ?></a>.
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
