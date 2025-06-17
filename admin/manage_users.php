<?php
require_once 'auth.php'; // Ensures user is authenticated and session is started
require_once __DIR__ . '/../api/helpers.php'; // Include helpers for t() and $pdo

// $pageTitle is used by layout.php
$pageTitle = t('admin_users_title', [], 'User Management'); // Already seeded

// $db is already globally available via db_connect.php (included by helpers.php)
global $pdo;

$users = [];
$errorMessage = ''; // This will be set using t()
$successMessage = ''; // This will be set using t()

if (!$pdo) {
    // This means db_connect.php failed to establish a connection.
    // db_connect.php itself logs the detailed error.
    // We set a generic error message for the UI here.
    $errorMessage = t('db_connection_not_available', [], 'Database connection not available. Please check configuration.');
} else {
    // Handle Add New User
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
        $new_username = trim($_POST['new_username']);
        $new_password = $_POST['new_password'];

        if (empty($new_username) || empty($new_password)) {
            $errorMessage = t('admin_users_error_empty_fields', [], 'New username and password cannot be empty.');
        } elseif (strlen($new_password) < 8) {
            $errorMessage = t('admin_users_error_password_short', [], 'New password must be at least 8 characters long.');
        } else {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmtCheck->bindParam(':username', $new_username);
            $stmtCheck->execute();
            if ($stmtCheck->fetchColumn() > 0) {
                $errorMessage = t('admin_users_error_username_exists', ['username' => $new_username], "Username '{username}' already exists. Please choose a different one.");
            } else {
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                if (!$password_hash) {
                    $errorMessage = t('admin_users_error_hash_failed', [], 'Failed to hash password.');
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
                        $stmt->bindParam(':username', $new_username);
                        $stmt->bindParam(':password_hash', $password_hash);
                        $stmt->execute();
                        $successMessage = t('admin_users_add_success', ['username' => $new_username], "User '{username}' added successfully!");
                    } catch (PDOException $e) {
                        error_log("Add user DB error: " . $e->getMessage());
                        $errorMessage = t('admin_users_error_add_failed', ['error' => $e->getMessage()], 'Error adding user: {error}');
                    }
                }
            }
        }
    }

    // Fetch existing users (only if $pdo is valid)
    try {
        $stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY username ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Fetch users DB error: " . $e->getMessage());
        $errorMessage = t('admin_users_error_fetch_failed', ['error' => $e->getMessage()], 'Error fetching users: {error}');
        $users = [];
    }
}

// Start output buffering to capture content for the layout
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4"><?php echo t('admin_users_add_admin_title', [], 'Add New Administrator'); // Already seeded ?></h2>

        <?php if (!empty($successMessage) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])): ?>
            <div class="mb-4 p-3 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($successMessage); // Already from t() ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])): ?>
            <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($errorMessage); // Already from t() ?>
            </div>
        <?php endif; ?>

        <?php if (!$pdo): // If $pdo is null (connection failed), show general error instead of form ?>
            <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($errorMessage); // Display the DB connection error ?>
            </div>
        <?php else: ?>
            <form action="manage_users.php" method="POST">
                <div class="mb-4">
                    <label for="new_username" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('username_label', [], 'Username'); // Already seeded ?></label>
                    <input type="text" name="new_username" id="new_username" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <div class="mb-6">
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('password_label_min_chars', [], 'Password (min 8 chars)'); // Already seeded ?></label>
                    <input type="password" name="new_password" id="new_password" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required minlength="8">
                </div>
                <button type="submit" name="add_user" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?php echo t('add_user_button', [], 'Add User'); // Already seeded ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-700 mb-4"><?php echo t('admin_users_existing_admins_title', [], 'Existing Administrators'); // Already seeded ?></h2>

        <?php if (!$pdo && empty($users)): // Show general error if user fetching failed due to no DB ?>
             <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($errorMessage); // Display the DB connection error ?>
            </div>
        <?php elseif ($pdo && !empty($errorMessage) && empty($users)): // Show specific fetch error if DB was fine initially but fetch failed ?>
             <div class="mb-4 p-3 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($errorMessage); // Error from fetching users ?>
            </div>
        <?php elseif (empty($users) && $pdo): ?>
            <p class="text-gray-600"><?php echo t('admin_users_none_found', [], 'No administrator users found. Add one using the form.'); // Already seeded ?></p>
        <?php elseif (!empty($users)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo t('table_header_id', [], 'ID'); // Already seeded ?></th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo t('table_header_username', [], 'Username'); // Already seeded ?></th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo t('table_header_created_at', [], 'Created At'); // Already seeded ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($user['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once 'layout.php';
?>
