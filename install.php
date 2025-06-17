<?php
session_start();

// --- Configuration ---
$configFilePath = __DIR__ . '/api/config.php';
$sqlSchemaPath = __DIR__ . '/migrations/001_initial_schema.sql';
$db = null;
$installationComplete = false;
$currentStep = 1; // Start with step 1

// --- Helper Functions ---
function connectDb($host, $dbname, $username, $password) {
    try {
        $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        return null; // Or handle error more gracefully
    }
}

function areTablesInstalled($pdo) {
    try {
        $result = $pdo->query("SELECT 1 FROM settings LIMIT 1");
        $result2 = $pdo->query("SELECT 1 FROM users LIMIT 1");
        return ($result !== false && $result2 !== false);
    } catch (PDOException $e) {
        return false;
    }
}

function isInstallationComplete($pdo) {
    if (!$pdo) return false;
    try {
        $stmt = $pdo->prepare("SELECT option_value FROM settings WHERE option_name = 'installation_complete'");
        $stmt->execute();
        $value = $stmt->fetchColumn();
        return $value === 'true';
    } catch (PDOException $e) {
        // This might happen if settings table doesn't exist yet, which is fine during installation
        return false;
    }
}

// --- Main Logic ---

// Check if config.php exists and try to connect
if (file_exists($configFilePath)) {
    require_once $configFilePath; // Defines DB_HOST, DB_NAME, DB_USER, DB_PASS
    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER') && defined('DB_PASS')) {
        $db = connectDb(DB_HOST, DB_NAME, DB_USER, DB_PASS);
        if ($db) {
            $installationComplete = isInstallationComplete($db);
        }
    }
}

// If installation is complete, lock the installer
if ($installationComplete) {
    echo "<h1>Installation Already Completed</h1>";
    echo "<p>The application has already been installed. For security reasons, please <strong>DELETE THIS FILE (install.php)</strong> from your server immediately.</p>";
    exit;
}

// Determine current step based on state
if (!file_exists($configFilePath)) {
    $currentStep = 1; // DB Config
} elseif ($db && !areTablesInstalled($db)) {
    $currentStep = 2; // Install Tables
} elseif ($db && areTablesInstalled($db)) {
    // Check if admin user exists
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $adminExists = $stmt->fetchColumn() > 0;
    if (!$adminExists) {
        $currentStep = 3; // Create Admin
    } else {
        $currentStep = 4; // Finalize (should ideally not be reachable if installation_complete is false and admin exists)
    }
} elseif (!$db && file_exists($configFilePath)) {
    // Config exists but connection failed
    $db_error_message = "Error: Could not connect to the database using the details in api/config.php. Please check the credentials and database server.";
    // We'll let it fall through to step 1 to allow re-configuration or show error
    $currentStep = 1;
}


// --- Step 1: Database Configuration ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step1_submit'])) {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];

    $configContent = "<?php
";
    $configContent .= "define('DB_HOST', '" . addslashes($db_host) . "');
";
    $configContent .= "define('DB_NAME', '" . addslashes($db_name) . "');
";
    $configContent .= "define('DB_USER', '" . addslashes($db_user) . "');
";
    $configContent .= "define('DB_PASS', '" . addslashes($db_pass) . "');
";

    if (file_put_contents($configFilePath, $configContent)) {
        // Try to connect with new credentials
        $db = connectDb($db_host, $db_name, $db_user, $db_pass);
        if ($db) {
            if (!areTablesInstalled($db)) {
                $currentStep = 2;
            } else {
                 // Check if admin user exists
                $stmt = $db->query("SELECT COUNT(*) FROM users");
                $adminExists = $stmt->fetchColumn() > 0;
                if (!$adminExists) {
                    $currentStep = 3;
                } else {
                    // This case implies tables exist, admin exists.
                    // We should mark installation as complete if not already.
                    try {
                        $stmtUpdate = $db->prepare("UPDATE settings SET option_value = 'true' WHERE option_name = 'installation_complete'");
                        $stmtUpdate->execute();
                        $installationComplete = true; // Force exit at the top
                        header("Location: " . $_SERVER['PHP_SELF']); // Refresh to show locked message
                        exit;
                    } catch (PDOException $e) {
                        $step1_error = "Error finalizing installation: " . $e->getMessage();
                    }
                }
            }
        } else {
            $step1_error = "Database connection failed with the provided credentials. Please check them and try again. The file api/config.php was created/updated, but the connection failed.";
            // Keep currentStep = 1 to allow re-submission
        }
    } else {
        $step1_error = "Error: Could not write to api/config.php. Please check file permissions.";
    }
}

// --- Step 2: Install Database Tables ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step2_install_db']) && $db) {
    if (!file_exists($sqlSchemaPath)) {
        $step2_error = "Error: SQL schema file not found at {$sqlSchemaPath}.";
    } else {
        try {
            $sql = file_get_contents($sqlSchemaPath);
            $db->exec($sql);
            // Check again if tables were actually created
            if (areTablesInstalled($db)) {
                 $currentStep = 3; // Move to admin creation
            } else {
                $step2_error = "Database tables installed, but could not verify. Please check your database manually.";
                 // To prevent loop, let's assume it worked and move to step 3
                 // Or, add a more robust check. For now, let's be optimistic.
                $currentStep = 3;
            }
        } catch (PDOException $e) {
            $step2_error = "Error installing database schema: " . $e->getMessage();
        }
    }
}

// --- Step 3: Create Admin User ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step3_create_admin']) && $db) {
    $admin_user = $_POST['admin_username'];
    $admin_pass = $_POST['admin_password'];

    if (empty($admin_user) || empty($admin_pass)) {
        $step3_error = "Administrator username and password cannot be empty.";
    } elseif (strlen($admin_pass) < 8) {
        $step3_error = "Password must be at least 8 characters long.";
    } else {
        $password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
        if (!$password_hash) {
             $step3_error = "Failed to hash password.";
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
                $stmt->bindParam(':username', $admin_user);
                $stmt->bindParam(':password_hash', $password_hash);
                $stmt->execute();

                // Finalize installation
                $stmtUpdate = $db->prepare("UPDATE settings SET option_value = 'true' WHERE option_name = 'installation_complete'");
                $stmtUpdate->execute();
                $installationComplete = true; // To trigger lock on next page load/refresh
                $currentStep = 4; // Move to final message screen

            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $step3_error = "Error: Username '{$admin_user}' already exists. Choose a different username.";
                } else {
                    $step3_error = "Error creating admin user: " . $e->getMessage();
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Installation Wizard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        .error { color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px; background-color: #ffecec;}
        .success { color: green; border: 1px solid green; padding: 10px; margin-bottom: 15px; background-color: #e6ffe6;}
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: calc(100% - 22px); padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 3px; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .step { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; background-color: #fff; }
        .step-title { font-size: 1.2em; font-weight: bold; margin-bottom: 10px; color: #007bff; }
        .disabled { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Application Installation Wizard</h1>

        <?php if (isset($db_error_message)): ?>
            <p class="error"><?php echo $db_error_message; ?></p>
        <?php endif; ?>

        <!-- Step 1: Database Configuration -->
        <div class="step <?php echo ($currentStep !== 1 || $installationComplete) ? 'disabled' : ''; ?>">
            <div class="step-title">Step 1: Database Configuration</div>
            <?php if ($currentStep === 1 && !$installationComplete): ?>
                <p>Please enter your database connection details. This will create <code>api/config.php</code>.</p>
                <?php if (isset($step1_error)) echo "<p class='error'>{$step1_error}</p>"; ?>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <label for="db_host">Database Host (e.g., localhost):</label>
                    <input type="text" id="db_host" name="db_host" required value="<?php echo defined('DB_HOST') ? htmlspecialchars(DB_HOST) : 'localhost'; ?>">

                    <label for="db_name">Database Name:</label>
                    <input type="text" id="db_name" name="db_name" required value="<?php echo defined('DB_NAME') ? htmlspecialchars(DB_NAME) : ''; ?>">

                    <label for="db_user">Database Username:</label>
                    <input type="text" id="db_user" name="db_user" required value="<?php echo defined('DB_USER') ? htmlspecialchars(DB_USER) : ''; ?>">

                    <label for="db_pass">Database Password:</label>
                    <input type="password" id="db_pass" name="db_pass" value="<?php echo defined('DB_PASS') ? htmlspecialchars(DB_PASS) : ''; ?>">

                    <button type="submit" name="step1_submit">Save Configuration & Connect</button>
                </form>
            <?php elseif ($currentStep > 1 && !$installationComplete): ?>
                <p>&#10004; Database configuration is present (api/config.php).</p>
                 <?php if (!$db && file_exists($configFilePath)): ?>
                    <p class="error">Connection to database failed with current settings in api/config.php. Please correct it above or delete api/config.php to re-enter.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Step 2: Install Database Tables -->
        <div class="step <?php echo ($currentStep !== 2 || $installationComplete || !$db) ? 'disabled' : ''; ?>">
            <div class="step-title">Step 2: Install Database Tables</div>
            <?php if ($currentStep === 2 && !$installationComplete && $db): ?>
                <p>Database configured. Click the button below to create the necessary tables (<code>settings</code> and <code>users</code>) from <code>migrations/001_initial_schema.sql</code>.</p>
                <?php if (isset($step2_error)) echo "<p class='error'>{$step2_error}</p>"; ?>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <button type="submit" name="step2_install_db">Install Database Tables</button>
                </form>
            <?php elseif ($currentStep > 2 && !$installationComplete && $db && areTablesInstalled($db)): ?>
                 <p>&#10004; Database tables (settings, users) are installed.</p>
            <?php elseif ($currentStep > 2 && !$installationComplete && $db && !areTablesInstalled($db)): ?>
                 <p class="error">Attempted to install tables, but they don't seem to exist. Try again or check manually.</p>
                 <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <button type="submit" name="step2_install_db">Retry Installing Database Tables</button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Step 3: Create Administrator Account -->
        <div class="step <?php echo ($currentStep !== 3 || $installationComplete || !$db || !areTablesInstalled($db)) ? 'disabled' : ''; ?>">
            <div class="step-title">Step 3: Create Your Administrator Account</div>
            <?php if ($currentStep === 3 && !$installationComplete && $db && areTablesInstalled($db)): ?>
                <p>Tables are installed. Now, create your admin user. This will be the only user with access to the admin panel.</p>
                <?php if (isset($step3_error)) echo "<p class='error'>{$step3_error}</p>"; ?>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <label for="admin_username">Admin Username:</label>
                    <input type="text" id="admin_username" name="admin_username" required>

                    <label for="admin_password">Admin Password (min 8 characters):</label>
                    <input type="password" id="admin_password" name="admin_password" required minlength="8">

                    <button type="submit" name="step3_create_admin">Create Admin User & Finalize</button>
                </form>
            <?php elseif ($currentStep > 3 && !$installationComplete): ?>
                 <p>&#10004; Administrator account step reached (should be finalized now).</p>
            <?php endif; ?>
        </div>

        <!-- Step 4: Finalization -->
        <?php if ($currentStep === 4 && $installationComplete): ?>
        <div class="step">
            <div class="step-title">Step 4: Installation Complete!</div>
            <div class="success">
                <p><strong>Congratulations!</strong> The application has been successfully installed and configured.</p>
                <p>Your administrator account has been created.</p>
                <p style="font-weight: bold; color: red;">IMPORTANT SECURITY WARNING: For security reasons, you MUST now DELETE the <code>install.php</code> file from your server.</p>
                <p>You can now proceed to the <a href="admin/login.php">Admin Login Page</a>.</p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
