<?php
require_once 'auth.php'; // Authenticates and starts session
require_once __DIR__ . '/../api/helpers.php'; // For t() function and $pdo from db_connect.php

$pageTitle = "Translation Management"; // Will be translated later if a key is made for it
$errorMessage = '';
$successMessage = '';

global $pdo; // From db_connect.php included via helpers.php

$translations = [];

// Ensure $pdo is available
if (!$pdo) {
    $errorMessage = "Database connection is not available. Cannot manage translations.";
} else {
    // Handle Save All Changes
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all_translations'])) {
        if (isset($_POST['translation_ids']) && is_array($_POST['translation_ids'])) {
            try {
                $pdo->beginTransaction();
                $stmtUpdate = $pdo->prepare("UPDATE translations SET es = :es, en = :en WHERE id = :id");

                foreach ($_POST['translation_ids'] as $id) {
                    $id = filter_var($id, FILTER_VALIDATE_INT);
                    if ($id === false) continue; // Skip invalid IDs

                    $es_text = $_POST['es'][$id] ?? '';
                    $en_text = $_POST['en'][$id] ?? '';

                    $stmtUpdate->bindParam(':es', $es_text, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':en', $en_text, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmtUpdate->execute();
                }
                $pdo->commit();
                $successMessage = "All changes saved successfully!";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errorMessage = "Error saving translations: " . $e->getMessage();
            }
        }
    }
    // Handle Add New Translation
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_translation'])) {
        $new_lang_key = trim($_POST['new_lang_key']);
        $new_es = trim($_POST['new_es']);
        $new_en = trim($_POST['new_en']);

        if (empty($new_lang_key)) {
            $errorMessage = "Language Key cannot be empty.";
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $new_lang_key)) {
            $errorMessage = "Language Key can only contain letters, numbers, underscores, dots, and hyphens.";
        } else {
            try {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM translations WHERE lang_key = :lang_key");
                $stmtCheck->bindParam(':lang_key', $new_lang_key);
                $stmtCheck->execute();
                if ($stmtCheck->fetchColumn() > 0) {
                    $errorMessage = "Language Key '{$new_lang_key}' already exists.";
                } else {
                    $stmtInsert = $pdo->prepare("INSERT INTO translations (lang_key, es, en) VALUES (:lang_key, :es, :en)");
                    $stmtInsert->bindParam(':lang_key', $new_lang_key);
                    $stmtInsert->bindParam(':es', $new_es);
                    $stmtInsert->bindParam(':en', $new_en);
                    $stmtInsert->execute();
                    $successMessage = "Translation for '{$new_lang_key}' added successfully!";
                }
            } catch (PDOException $e) {
                $errorMessage = "Error adding new translation: " . $e->getMessage();
            }
        }
    }

    // Fetch all translations for display
    try {
        $stmtFetch = $pdo->query("SELECT id, lang_key, es, en FROM translations ORDER BY lang_key ASC");
        $translations = $stmtFetch->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Error fetching translations: " . $e->getMessage();
        $translations = [];
    }
}


// Start output buffering
ob_start();
?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6"><?php echo t('admin_translations_title', [], $pageTitle); // Example of using t() for the title itself ?></h2>

    <?php if ($successMessage): ?>
        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <?php if (!$pdo): ?>
        <p class="text-gray-600">Database connection not available. Cannot proceed.</p>
    <?php else: ?>
        <!-- Add New Translation Form -->
        <div class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-700 mb-4"><?php echo t('admin_translations_add_new_title', [], 'Add New Translation Key'); ?></h3>
            <form action="manage_translations.php" method="POST" class="space-y-4">
                <div>
                    <label for="new_lang_key" class="block text-sm font-medium text-gray-700"><?php echo t('admin_translations_key_label', [], 'Language Key'); ?></label>
                    <input type="text" name="new_lang_key" id="new_lang_key" required pattern="^[a-zA-Z0-9_.-]+$" title="Only letters, numbers, underscores, dots, hyphens."
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="new_es" class="block text-sm font-medium text-gray-700"><?php echo t('admin_translations_es_label', [], 'Spanish (es)'); ?></label>
                    <textarea name="new_es" id="new_es" rows="2"
                              class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
                <div>
                    <label for="new_en" class="block text-sm font-medium text-gray-700"><?php echo t('admin_translations_en_label', [], 'English (en)'); ?></label>
                    <textarea name="new_en" id="new_en" rows="2"
                              class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
                <button type="submit" name="add_translation" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <?php echo t('admin_translations_add_button', [], 'Add Translation'); ?>
                </button>
            </form>
        </div>

        <!-- Existing Translations Table -->
        <h3 class="text-xl font-semibold text-gray-700 mb-4 mt-8"><?php echo t('admin_translations_existing_title', [], 'Existing Translations'); ?></h3>
        <?php if (empty($translations)): ?>
            <p class="text-gray-500"><?php echo t('admin_translations_none_found', [], 'No translations found. Add some using the form above.'); ?></p>
        <?php else: ?>
            <form action="manage_translations.php" method="POST">
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo t('admin_translations_key_header', [], 'Key'); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo t('admin_translations_es_header', [], 'Spanish (es)'); ?></th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo t('admin_translations_en_header', [], 'English (en)'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($translations as $trans): ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($trans['lang_key']); ?>
                                        <input type="hidden" name="translation_ids[]" value="<?php echo $trans['id']; ?>">
                                    </td>
                                    <td class="px-4 py-3">
                                        <textarea name="es[<?php echo $trans['id']; ?>]" rows="2" class="w-full p-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($trans['es']); ?></textarea>
                                    </td>
                                    <td class="px-4 py-3">
                                        <textarea name="en[<?php echo $trans['id']; ?>]" rows="2" class="w-full p-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($trans['en']); ?></textarea>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    <button type="submit" name="save_all_translations" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <?php echo t('admin_translations_save_all_button', [], 'Save All Changes'); ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; // end if $pdo check ?>
</div>

<?php
// Assign the buffered content to $pageContent and include the layout
$pageContent = ob_get_clean();
require_once 'layout.php';
?>
