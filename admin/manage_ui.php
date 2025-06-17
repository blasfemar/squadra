<?php
require_once 'auth.php'; // Ensures user is authenticated and session is started
require_once __DIR__ . '/../api/helpers.php'; // Include helpers for t() and $pdo

$pageTitle = t('admin_ui_title', [], 'Interface Settings');

global $pdo;
$errorMessage = '';
$successMessage = '';

// Define upload directory and ensure it exists (attempt to create if not)
$uploadDirRelative = 'assets/uploads/'; // Relative to project root
$uploadDirAbsolute = realpath(__DIR__ . '/../') . '/' . $uploadDirRelative;

if (!is_dir($uploadDirAbsolute)) {
    if (mkdir($uploadDirAbsolute, 0755, true)) {
        // Directory created
    } else {
        // Error creating directory - this might be an issue for uploads
        // $errorMessage .= t('admin_ui_error_upload_dir_creation_failed', ['dir' => $uploadDirRelative], 'Failed to create upload directory: {dir}. Please check permissions. ');
        // This error might be too disruptive if shown all the time. File upload logic will handle writability.
    }
}


// Default settings values
$settings = [
    'site_title' => t('app_title', [], 'Squadra SEO Analyzer'),
    'main_color' => '#3B82F6', // Tailwind blue-500
    'header_logo_url' => '',
    'favicon_url' => '',
    'link_color' => '#007bff', // Bootstrap default blue
    'h1_font_size' => '2.5rem',
    'h2_font_size' => '2rem',
    'h3_font_size' => '1.75rem'
];

$setting_keys_to_fetch = array_keys($settings);

if (!$pdo) {
    $errorMessage = t('db_connection_not_available', [], 'Database connection not available. Please check configuration.');
} else {
    // Fetch current settings from DB
    try {
        $placeholders = implode(',', array_fill(0, count($setting_keys_to_fetch), '?'));
        $stmtSettings = $pdo->prepare("SELECT option_name, option_value FROM settings WHERE option_name IN ($placeholders)");
        $stmtSettings->execute($setting_keys_to_fetch);
        while ($row = $stmtSettings->fetch(PDO::FETCH_ASSOC)) {
            if (array_key_exists($row['option_name'], $settings)) {
                $settings[$row['option_name']] = $row['option_value']; // Apply htmlspecialchars on output
            }
        }
    } catch (PDOException $e) {
        error_log("Manage UI - DB query error for fetching settings: " . $e->getMessage());
        // Non-critical, form will show defaults. $errorMessage might already be set if $pdo failed earlier.
    }

    // Handle Save Settings
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ui_settings'])) {
        try {
            $pdo->beginTransaction();

            // Helper function to save a setting
            $saveSetting = function($option_name, $option_value) use ($pdo) {
                $stmt = $pdo->prepare("INSERT INTO settings (option_name, option_value) VALUES (:name, :value)
                                       ON DUPLICATE KEY UPDATE option_value = :value");
                $stmt->bindParam(':name', $option_name);
                $stmt->bindParam(':value', $option_value);
                $stmt->execute();
            };

            // Site Title and Colors
            $new_site_title = trim($_POST['site_title']);
            $new_main_color = trim($_POST['main_color']);
            $new_link_color = trim($_POST['link_color']);

            if (empty($new_site_title)) {
                throw new Exception(t('admin_ui_error_site_title_empty', [], 'Site Title cannot be empty.'));
            }
            if (!preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $new_main_color)) {
                throw new Exception(t('admin_ui_error_main_color_invalid', [], 'Main Color must be a valid hex code.'));
            }
            if (!preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $new_link_color)) {
                throw new Exception(t('admin_ui_error_invalid_hex_code', ['field' => t('admin_ui_link_color_label', [], 'Link Color')], '{field} must be a valid hex code.'));
            }

            $saveSetting('site_title', $new_site_title);
            $settings['site_title'] = $new_site_title;
            $saveSetting('main_color', $new_main_color);
            $settings['main_color'] = $new_main_color;
            $saveSetting('link_color', $new_link_color);
            $settings['link_color'] = $new_link_color;

            // Font Sizes
            $font_sizes = ['h1_font_size', 'h2_font_size', 'h3_font_size'];
            foreach ($font_sizes as $fs_key) {
                $value = trim($_POST[$fs_key]);
                // Basic validation: not empty, and common CSS units. More complex regex possible.
                if (!empty($value) && preg_match('/^[0-9.]+(px|em|rem|pt|%)$/', $value)) {
                    $saveSetting($fs_key, $value);
                    $settings[$fs_key] = $value;
                } elseif (empty($value) && isset($settings[$fs_key])) { // Allow clearing to use CSS default by removing setting? Or keep old?
                    // For now, if submitted empty, we don't update it, keeps old or default.
                    // Or, to allow clearing: $saveSetting($fs_key, ''); $settings[$fs_key] = '';
                } elseif (!empty($value)) { // Submitted value is not empty but invalid format
                     throw new Exception(t('admin_ui_error_invalid_css_value', ['field' => $fs_key], 'Invalid CSS value for {field}.'));
                }
            }

            // File Uploads
            $allowed_logo_types = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/gif'];
            $allowed_favicon_types = ['image/vnd.microsoft.icon', 'image/x-icon', 'image/png'];
            $max_file_size = 2 * 1024 * 1024; // 2MB

            // Header Logo
            if (isset($_FILES['header_logo']) && $_FILES['header_logo']['error'] == UPLOAD_ERR_OK) {
                if (!is_writable($uploadDirAbsolute)) {
                     throw new Exception(t('admin_ui_error_upload_dir_not_writable', ['dir' => $uploadDirRelative], 'Upload directory {dir} is not writable.'));
                }
                if ($_FILES['header_logo']['size'] > $max_file_size) {
                    throw new Exception(t('admin_ui_error_file_too_large', ['limit' => '2MB'], 'Header logo is too large. Max {limit}.'));
                }
                if (!in_array($_FILES['header_logo']['type'], $allowed_logo_types)) {
                    throw new Exception(t('admin_ui_error_invalid_file_type', [], 'Invalid file type for header logo.'));
                }
                $logo_filename = 'logo_' . time() . '_' . basename($_FILES['header_logo']['name']);
                $logo_path_absolute = $uploadDirAbsolute . $logo_filename;
                $logo_path_relative = $uploadDirRelative . $logo_filename;
                if (move_uploaded_file($_FILES['header_logo']['tmp_name'], $logo_path_absolute)) {
                    $saveSetting('header_logo_url', $logo_path_relative);
                    $settings['header_logo_url'] = $logo_path_relative;
                } else {
                    throw new Exception(t('admin_ui_error_upload_failed', ['file' => 'header logo'], 'Failed to upload {file}.'));
                }
            }

            // Favicon
            if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] == UPLOAD_ERR_OK) {
                 if (!is_writable($uploadDirAbsolute)) {
                     throw new Exception(t('admin_ui_error_upload_dir_not_writable', ['dir' => $uploadDirRelative], 'Upload directory {dir} is not writable.'));
                }
                if ($_FILES['favicon_file']['size'] > $max_file_size) {
                    throw new Exception(t('admin_ui_error_file_too_large', ['limit' => '2MB'], 'Favicon is too large. Max {limit}.'));
                }
                if (!in_array($_FILES['favicon_file']['type'], $allowed_favicon_types)) {
                    throw new Exception(t('admin_ui_error_invalid_file_type', [], 'Invalid file type for favicon.'));
                }
                $favicon_filename = 'favicon_' . time() . '_' . basename($_FILES['favicon_file']['name']);
                $favicon_path_absolute = $uploadDirAbsolute . $favicon_filename;
                $favicon_path_relative = $uploadDirRelative . $favicon_filename;
                if (move_uploaded_file($_FILES['favicon_file']['tmp_name'], $favicon_path_absolute)) {
                    $saveSetting('favicon_url', $favicon_path_relative);
                    $settings['favicon_url'] = $favicon_path_relative;
                } else {
                    throw new Exception(t('admin_ui_error_upload_failed', ['file' => 'favicon'], 'Failed to upload {file}.'));
                }
            }

            $pdo->commit();
            $successMessage = t('admin_ui_save_success', [], 'Interface settings saved successfully!');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("Save UI settings DB error: " . $e->getMessage());
            $errorMessage = t('admin_ui_error_save_failed', ['error' => $e->getMessage()], 'Error saving settings: {error}');
        } catch (Exception $e) { // Catch custom exceptions for validation
             if ($pdo->inTransaction()) $pdo->rollBack();
             $errorMessage = $e->getMessage(); // Already translated
        }
    }
}

ob_start();
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6"><?php echo t('admin_ui_manage_title', [], 'Manage Interface Settings'); ?></h2>

    <?php if (!empty($successMessage)): ?>
        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <?php if (!$pdo && !file_exists( __DIR__ . '/../api/config.php')): ?>
         <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <?php echo t('login_error_config_missing', [], 'Configuration file missing. Please run the installer (install.php).'); ?>
        </div>
    <?php elseif (!$pdo): ?>
         <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <?php echo t('db_connection_not_available', [], 'Database connection not available. Please check configuration.'); ?>
        </div>
    <?php else: ?>
        <form action="manage_ui.php" method="POST" enctype="multipart/form-data">
            <div class="mb-6">
                <label for="site_title" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('site_title_label', [], 'Site Title'); ?></label>
                <input type="text" name="site_title" id="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>"
                       class="mt-1 block w-full px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                <p class="mt-1 text-xs text-gray-500"><?php echo t('site_title_help_text', [], 'This title will be used in the browser tab and potentially other places in the UI.'); ?></p>
            </div>

            <div class="mb-8">
                <label for="main_color_text" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('main_color_label', [], 'Main Color (Hex Code)'); ?></label>
                <div class="flex items-center">
                    <input type="color" name="main_color_picker" id="main_color_picker" value="<?php echo htmlspecialchars($settings['main_color']); ?>"
                           class="h-10 w-10 p-1 border border-gray-300 rounded-md shadow-sm cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="text" name="main_color" id="main_color_text" value="<?php echo htmlspecialchars($settings['main_color']); ?>"
                           pattern="^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$"
                           class="ml-3 mt-1 block w-full px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                </div>
                <p class="mt-1 text-xs text-gray-500"><?php echo t('main_color_help_text', [], 'Choose a main theme color for the application (e.g., for buttons, highlights).'); ?></p>
            </div>

            <div class="mb-6">
                <label for="link_color_text" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('admin_ui_link_color_label', [], 'Link Color (Hex Code)'); ?></label>
                <div class="flex items-center">
                    <input type="color" name="link_color_picker" id="link_color_picker" value="<?php echo htmlspecialchars($settings['link_color'] ?? '#007bff'); ?>" class="h-10 w-10 p-1 border border-gray-300 rounded-md shadow-sm cursor-pointer">
                    <input type="text" name="link_color" id="link_color_text" value="<?php echo htmlspecialchars($settings['link_color'] ?? '#007bff'); ?>" pattern="^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" class="ml-3 mt-1 block w-full px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm sm:text-sm" required>
                </div>
                <p class="mt-1 text-xs text-gray-500"><?php echo t('admin_ui_link_color_help', [], 'Color for hyperlinks.'); ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="h1_font_size" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('admin_ui_h1_size_label', [], 'H1 Font Size'); ?></label>
                    <input type="text" name="h1_font_size" id="h1_font_size" value="<?php echo htmlspecialchars($settings['h1_font_size'] ?? '2.5rem'); ?>" placeholder="e.g., 2.5rem or 32px" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="h2_font_size" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('admin_ui_h2_size_label', [], 'H2 Font Size'); ?></label>
                    <input type="text" name="h2_font_size" id="h2_font_size" value="<?php echo htmlspecialchars($settings['h2_font_size'] ?? '2rem'); ?>" placeholder="e.g., 2rem or 24px" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="h3_font_size" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('admin_ui_h3_size_label', [], 'H3 Font Size'); ?></label>
                    <input type="text" name="h3_font_size" id="h3_font_size" value="<?php echo htmlspecialchars($settings['h3_font_size'] ?? '1.75rem'); ?>" placeholder="e.g., 1.75rem or 20px" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm sm:text-sm">
                </div>
            </div>
            <p class="mb-6 -mt-2 text-xs text-gray-500"><?php echo t('admin_ui_font_size_help', [], 'Define font sizes using valid CSS units (e.g., rem, em, px). Leave empty to use theme defaults.'); ?></p>


            <div class="mb-6">
                <label for="header_logo" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('admin_ui_header_logo_label', [], 'Header Logo'); ?></label>
                <input type="file" name="header_logo" id="header_logo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <?php if (!empty($settings['header_logo_url'])): ?>
                    <p class="mt-2 text-xs text-gray-500"><?php echo t('admin_ui_current_logo_label', [], 'Current logo:'); ?> <img src="<?php echo htmlspecialchars( '../' . ltrim($settings['header_logo_url'], '/')); ?>" alt="<?php echo t('admin_ui_header_logo_alt', [], 'Header Logo Preview'); ?>" class="inline-block h-8 ml-2 border bg-gray-100 p-1"></p>
                <?php endif; ?>
                <p class="mt-1 text-xs text-gray-500"><?php echo t('admin_ui_header_logo_help', [], 'Upload a logo for the website header (e.g., PNG, JPG, SVG). Max 2MB.'); ?></p>
            </div>

            <div class="mb-6">
                <label for="favicon_file" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('admin_ui_favicon_label', [], 'Favicon'); ?></label>
                <input type="file" name="favicon_file" id="favicon_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <?php if (!empty($settings['favicon_url'])): ?>
                    <p class="mt-2 text-xs text-gray-500"><?php echo t('admin_ui_current_favicon_label', [], 'Current favicon:'); ?> <img src="<?php echo htmlspecialchars('../' . ltrim($settings['favicon_url'], '/')); ?>" alt="<?php echo t('admin_ui_favicon_alt', [], 'Favicon Preview'); ?>" class="inline-block h-6 w-6 ml-2 border"></p>
                <?php endif; ?>
                <p class="mt-1 text-xs text-gray-500"><?php echo t('admin_ui_favicon_help', [], 'Upload a favicon (e.g., ICO, PNG). Recommended size: 32x32 or 16x16. Max 2MB.'); ?></p>
            </div>


            <button type="submit" name="save_ui_settings"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <?php echo t('save_changes_button', [], 'Save Changes'); ?>
            </button>
        </form>
        <script>
            function setupColorPickerSync(pickerId, textId) {
                const colorPicker = document.getElementById(pickerId);
                const colorText = document.getElementById(textId);
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
            }
            setupColorPickerSync('main_color_picker', 'main_color_text');
            setupColorPickerSync('link_color_picker', 'link_color_text');
        </script>
    <?php endif; ?>
</div>

<?php
$pageContent = ob_get_clean();
require_once 'layout.php';
?>
