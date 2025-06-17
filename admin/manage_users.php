<?php
require_once 'auth.php'; // Ensures user is authenticated and session is started

$pageTitle = "User Management"; // Title for the layout
$db = null;
$users = [];
$errorMessage = '';
$successMessage = '';

// Database connection (reuse logic from login.php or install.php if possible, or simplify)
$configFilePath = __DIR__ . '/../api/config.php';
if (!file_exists($configFilePath)) {
    $errorMessage = "Configuration file missing. Cannot connect to database.";
} else {
    require_once $configFilePath;
    try {
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            throw new Exception("Database configuration constants are not defined in api/config.php.");
        }
        $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $errorMessage = "Database connection error: " . $e->getMessage();
    } catch (Exception $e) {
        $errorMessage = "Configuration error: " . $e->getMessage();
    }
}

// Handle Add New User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user']) && $db) {
    $new_username = trim($_POST['new_username']);
    $new_password = $_POST['new_password'];

    if (empty($new_username) || empty($new_password)) {
        $errorMessage = "New username and password cannot be empty.";
    } elseif (strlen($new_password) < 8) {
        $errorMessage = "New password must be at least 8 characters long.";
    } else {
        // Check if username already exists
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmtCheck->bindParam(':username', $new_username);
        $stmtCheck->execute();
        if ($stmtCheck->fetchColumn() > 0) {
            $errorMessage = "Username '{$new_username}' already exists. Please choose a different one.";
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            if (!$password_hash) {
                $errorMessage = "Failed to hash password.";
            } else {
                try {
                    $stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
                    $stmt->bindParam(':username', $new_username);
                    $stmt->bindParam(':password_hash', $password_hash);
                    $stmt->execute();
                    $successMessage = "User '{$new_username}' added successfully!";
                } catch (PDOException $e) {
                    $errorMessage = "Error adding user: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch existing users
if ($db) {
    try {
        $stmt = $db->query("SELECT id, username, created_at FROM users ORDER BY username ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Error fetching users: " . $e->getMessage();
        $users = []; // Ensure users is an array even on error
    }
}

// Start output buffering to capture content for the layout
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Add New User Form -->
    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Administrator</h2>
        <?php if (!empty($successMessage) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])): // Show success only on POST success ?>
            <div class="mb-4 p-3 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])): // Show error only on POST error ?>
            <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form action="manage_users.php" method="POST">
            <div class="mb-4">
                <label for="new_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="new_username" id="new_username" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
            </div>
            <div class="mb-6">
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password (min 8 chars)</label>
                <input type="password" name="new_password" id="new_password" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required minlength="8">
            </div>
            <button type="submit" name="add_user" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Add User
            </button>
        </form>
    </div>

    <!-- Existing Users Table -->
    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Existing Administrators</h2>
        <?php if (empty($users) && empty($errorMessage) && $db): ?>
            <p class="text-gray-600">No administrator users found. Add one using the form.</p>
        <?php elseif (!empty($errorMessage) && empty($users)): // Show general error if user fetching failed ?>
             <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php elseif (!empty($users)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                            <!-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th> -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($user['created_at']))); ?></td>
                                <!-- <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                                </td> -->
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$pageContent = ob_get_clean(); // Get content from buffer and assign to $pageContent
require_once 'layout.php'; // Include the layout
?>
