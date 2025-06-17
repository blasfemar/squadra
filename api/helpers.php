<?php
// api/helpers.php

// Include the database connection script first.
// This makes the global $pdo variable available.
require_once __DIR__ . '/db_connect.php';

/**
 * Translates a given language key into the currently selected language.
 *
 * @param string $key The language key to translate (e.g., 'main_title').
 * @param array $params Optional parameters to replace placeholders in the translation.
 *                     Example: t('welcome_user', ['name' => 'John']) for "Welcome, {name}!"
 * @return string The translated string, or the key itself if no translation is found.
 */
function t($key, $params = []) {
    global $pdo; // Access the global PDO object from db_connect.php

    // Determine current language (default to 'es', can be expanded later e.g., from session/URL)
    // For now, hardcoded as 'es' as per prompt for initial implementation.
    $lang = 'es';
    // TODO: Implement dynamic language selection (e.g., based on user preference, session, URL param)
    // Example: if (isset($_SESSION['lang'])) { $lang = $_SESSION['lang']; }
    // Ensure $lang is a valid/expected column name (e.g., 'es', 'en') to prevent SQL injection if dynamic.
    // For now, since it's hardcoded, it's safe. If it becomes dynamic, sanitize/validate it.

    static $translations_cache = []; // Static cache for translations within a single request

    if (!$pdo) {
        // Database connection not available, return the key.
        // error_log("t() function: PDO connection is not available. Returning key '{$key}'.");
        return $key; // Or handle error more explicitly
    }

    // Check cache first for the given language and key
    if (isset($translations_cache[$lang][$key])) {
        $text = $translations_cache[$lang][$key];
    } else {
        try {
            // Ensure $lang is one of the allowed columns to prevent SQL injection if it becomes dynamic
            $allowed_langs = ['es', 'en']; // Define allowed language columns
            if (!in_array($lang, $allowed_langs)) {
                error_log("t() function: Invalid language '{$lang}' selected.");
                return $key; // Fallback to key if language is not allowed
            }

            $stmt = $pdo->prepare("SELECT `{$lang}` FROM `translations` WHERE `lang_key` = :lang_key");
            $stmt->bindParam(':lang_key', $key, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchColumn();

            if ($result !== false) {
                $translations_cache[$lang][$key] = $result; // Cache the result
                $text = $result;
            } else {
                // Key not found in DB for this language, cache the key itself to avoid re-querying
                $translations_cache[$lang][$key] = $key;
                $text = $key;
            }
        } catch (PDOException $e) {
            error_log("t() function database error for key '{$key}': " . $e->getMessage());
            return $key; // Fallback to key on DB error
        }
    }

    // Replace placeholders if params are provided
    if (!empty($params) && is_array($params)) {
        foreach ($params as $placeholder => $value) {
            // Using {placeholder} format, e.g., "Welcome, {name}!"
            $text = str_replace('{' . $placeholder . '}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $text);
        }
    }

    return $text;
}

// Example usage (for testing this file directly, if needed):
// if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
//     // This block will only execute if helpers.php is run directly.
//     // You would need some sample data in your 'translations' table to test.
//     // Example: INSERT INTO translations (lang_key, es, en) VALUES ('test_greeting', 'Hola Mundo', 'Hello World');
//     //          INSERT INTO translations (lang_key, es, en) VALUES ('welcome_user', 'Bienvenido, {name}!', 'Welcome, {name}!');
//
//     echo "Testing t() function:
";
//     echo "Key 'test_greeting': " . t('test_greeting') . "
"; // Expected: Hola Mundo (if 'es' and in DB)
//     echo "Key 'non_existent_key': " . t('non_existent_key') . "
"; // Expected: non_existent_key
//     echo "Key 'welcome_user' with param: " . t('welcome_user', ['name' => 'Jules']) . "
"; // Expected: Bienvenido, Jules!
// }

?>
