<?php
require_once 'auth.php'; // Ensures user is authenticated and session is started

$pageTitle = "Interface Settings"; // Title for the layout
$db = null;
$errorMessage = '';
$successMessage = '';

// Current settings values
$settings = [
    'site_title' => 'Squadra SEO Analyzer', // Default
    'main_color' => '#3B82F6' // Default (Tailwind blue-500)
];

// Database connection
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

        // Fetch current settings from DB
        $stmtSettings = $db->query("SELECT option_name, option_value FROM settings WHERE option_name IN ('site_title', 'main_color')");
        while ($row = $stmtSettings->fetch(PDO::FETCH_ASSOC)) {
            if (isset($settings[$row['option_name']])) {
                $settings[$row['option_name']] = htmlspecialchars($row['option_value']);
            }
        }

    } catch (PDOException $e) {
        $errorMessage = "Database connection or query error: " . $e->getMessage();
    } catch (Exception $e) {
        $errorMessage = "Configuration error: " . $e->getMessage();
    }
}

// Handle Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ui_settings']) && $db) {
    $new_site_title = trim($_POST['site_title']);
    $new_main_color = trim($_POST['main_color']);

    if (empty($new_site_title)) {
        $errorMessage = "Site Title cannot be empty.";
    } elseif (!preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $new_main_color)) {
        $errorMessage = "Main Color must be a valid hex code (e.g., #RRGGBB or #RGB).";
    } else {
        try {
            $db->beginTransaction();

            // Update site_title (insert if not exists, update if exists)
            $stmtTitle = $db->prepare("INSERT INTO settings (option_name, option_value) VALUES ('site_title', :value)
                                       ON DUPLICATE KEY UPDATE option_value = :value");
            $stmtTitle->bindParam(':value', $new_site_title);
            $stmtTitle->execute();

            // Update main_color (insert if not exists, update if exists)
            $stmtColor = $db->prepare("INSERT INTO settings (option_name, option_value) VALUES ('main_color', :value)
                                       ON DUPLICATE KEY UPDATE option_value = :value");
            $stmtColor->bindParam(':value', $new_main_color);
            $stmtColor->execute();

            $db->commit();
            $successMessage = "Interface settings saved successfully!";
            // Update current values for display
            $settings['site_title'] = htmlspecialchars($new_site_title);
            $settings['main_color'] = htmlspecialchars($new_main_color);

        } catch (PDOException $e) {
            $db->rollBack();
            $errorMessage = "Error saving settings: " . $e->getMessage();
        }
    }
}

// Start output buffering to capture content for the layout
ob_start();
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Manage Interface Settings</h2>

    <?php if (!empty($successMessage)): ?>
        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (!file_exists($configFilePath) || !$db): ?>
        <p class="text-gray-600">Database connection not available. Please check configuration.</p>
    <?php else: ?>
        <form action="manage_ui.php" method="POST">
            <div class="mb-6">
                <label for="site_title" class="block text-sm font-medium text-gray-700 mb-1">Site Title</label>
                <input type="text" name="site_title" id="site_title" value="<?php echo $settings['site_title']; ?>"
                       class="mt-1 block w-full px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                <p class="mt-1 text-xs text-gray-500">This title will be used in the browser tab and potentially other places in the UI.</p>
            </div>

            <div class="mb-8">
                <label for="main_color" class="block text-sm font-medium text-gray-700 mb-1">Main Color (Hex Code)</label>
                <div class="flex items-center">
                    <input type="color" name="main_color_picker" id="main_color_picker" value="<?php echo $settings['main_color']; ?>"
                           class="h-10 w-10 p-1 border border-gray-300 rounded-md shadow-sm cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="text" name="main_color" id="main_color_text" value="<?php echo $settings['main_color']; ?>"
                           pattern="^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$"
                           class="ml-3 mt-1 block w-full px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <p class="mt-1 text-xs text-gray-500">Choose a main theme color for the application (e.g., for buttons, highlights).</p>
            </div>

            <button type="submit" name="save_ui_settings"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Save Changes
            </button>
        </form>
        <script>
            // Sync color picker and text input
            const colorPicker = document.getElementById('main_color_picker');
            const colorText = document.getElementById('main_color_text');
            if (colorPicker && colorText) {
                colorPicker.addEventListener('input', function() {
                    colorText.value = this.value;
                });
                colorText.addEventListener('input', function() {
                    if (/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/.test(this.value)) {
                        colorPicker.value = this.value;
                    }
                });
            }
        </script>
    <?php endif; ?>
</div>

<?php
$pageContent = ob_get_clean(); // Get content from buffer
require_once 'layout.php';    // Include the layout
?>
