<?php
// api/db_connect.php

// This script establishes the database connection using PDO.
// It should be included by any script that needs database access.

// Ensure config.php with DB constants is loaded.
// It's assumed that scripts including db_connect.php are in a context
// where the path to config.php is correctly resolved (e.g., from root or admin).
// For helpers.php in api/, config.php is in the same directory.

$configPath = __DIR__ . '/config.php'; // Assumes config.php is in the same /api directory

if (!file_exists($configPath)) {
    // Handle missing configuration file gracefully.
    // In a real app, this might throw an exception or trigger an error page.
    // For the t() function, it might mean it always returns keys.
    // This message won't be visible if output is suppressed or t() is called early.
    error_log("FATAL ERROR: api/config.php not found. Database connection cannot be established.");
    $pdo = null; // Ensure $pdo is defined, even if null
    return; // Stop further execution of this script if config is missing
}

require_once $configPath;

global $pdo; // Make $pdo available globally or manage scope as needed.

if (!isset($pdo)) { // Check if $pdo is already set (e.g., by multiple includes)
    try {
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            throw new Exception("Database configuration constants (DB_HOST, DB_NAME, DB_USER, DB_PASS) are not all defined in api/config.php.");
        }

        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    } catch (PDOException $e) {
        // Log detailed error, but don't expose it directly to user unless in debug mode.
        error_log("PDO Connection Error: " . $e->getMessage());
        // For the t() function, if $pdo is null, it will just return keys.
        $pdo = null; // Ensure $pdo is null on failure
        // In a web context, you might redirect to an error page or show a user-friendly message.
        // die("Database connection failed. Please check server logs. Error: " . $e->getMessage()); // Or a more graceful death
    } catch (Exception $e) {
        error_log("Configuration Error for DB Connection: " . $e->getMessage());
        $pdo = null;
        // die("Database configuration error. Please check server logs.");
    }
}
?>
