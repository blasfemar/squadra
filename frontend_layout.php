<?php
// frontend_layout.php

// Ensure helpers are available for t() function and $pdo
require_once __DIR__ . '/api/helpers.php'; // This also includes db_connect.php and makes $pdo global

global $pdo;

// Default settings values (fallbacks)
$ui_settings = [
    'site_title' => 'Squadra SEO Analyzer', // Default from t('app_title') could also be used
    'header_logo_url' => '', // Empty string means no logo by default
    'favicon_url' => '',     // Empty string means no custom favicon by default
    'main_color' => '#3B82F6',  // Tailwind blue-500 as a sensible default
    'link_color' => '#3B82F6',  // Default link color (Tailwind blue-500)
    'h1_font_size' => '2.5rem',
    'h2_font_size' => '2rem',
    'h3_font_size' => '1.75rem',
];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT option_name, option_value FROM settings
                             WHERE option_name IN (
                                 'site_title', 'header_logo_url', 'favicon_url',
                                 'main_color', 'link_color',
                                 'h1_font_size', 'h2_font_size', 'h3_font_size'
                             )");
        $db_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Merge DB settings with defaults. DB values take precedence.
        if ($db_settings) {
            foreach ($ui_settings as $key => $default_value) {
                if (isset($db_settings[$key]) && !empty($db_settings[$key])) {
                    $ui_settings[$key] = htmlspecialchars($db_settings[$key], ENT_QUOTES, 'UTF-8');
                } elseif ($key === 'site_title') { // Ensure site_title always has a value, use app_title if DB one is empty
                     $ui_settings[$key] = t('app_title', [], $default_value);
                }
            }
        } else {
            // If no settings found in DB, ensure site_title still uses t()
            $ui_settings['site_title'] = t('app_title', [], $ui_settings['site_title']);
        }
    } catch (PDOException $e) {
        error_log("Error fetching UI settings for frontend_layout: " . $e->getMessage());
        // Use defaults, but ensure site_title is from t()
        $ui_settings['site_title'] = t('app_title', [], $ui_settings['site_title']);
    }
} else {
    // PDO not available, use defaults, ensure site_title is from t()
    $ui_settings['site_title'] = t('app_title', [], $ui_settings['site_title']);
    error_log("PDO not available in frontend_layout.php. Using default UI settings.");
}

// Prepare CSS variables or direct styles
$custom_styles = ":root {\n";
if (!empty($ui_settings['main_color'])) {
    $custom_styles .= "    --main-color: " . $ui_settings['main_color'] . ";\n";
}
if (!empty($ui_settings['link_color'])) {
    $custom_styles .= "    --link-color: " . $ui_settings['link_color'] . ";\n";
}
$custom_styles .= "}\n";

if (!empty($ui_settings['h1_font_size'])) {
    $custom_styles .= "h1 { font-size: " . $ui_settings['h1_font_size'] . "; }\n";
}
if (!empty($ui_settings['h2_font_size'])) {
    $custom_styles .= "h2 { font-size: " . $ui_settings['h2_font_size'] . "; }\n";
}
if (!empty($ui_settings['h3_font_size'])) {
    $custom_styles .= "h3 { font-size: " . $ui_settings['h3_font_size'] . "; }\n";
}
// Apply link color directly as well, not just as a variable, for broader compatibility
if (!empty($ui_settings['link_color'])) {
    $custom_styles .= "a { color: var(--link-color, " . $ui_settings['link_color'] . "); }\n";
    // Specific for Tailwind-like buttons if they don't use <a> tags or need override
    // .button-link { background-color: var(--link-color); color: white; } // Example
}


// This layout expects $pageContent to be set by the including file (e.g., index.php)
// and $pageTitle (optional, defaults to site_title)
$currentPageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) : $ui_settings['site_title'];

?>
<!DOCTYPE html>
<html lang="<?php echo t('html_lang', [], 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $currentPageTitle; ?></title>
    <?php if (!empty($ui_settings['favicon_url'])): ?>
        <link rel="icon" href="<?php echo htmlspecialchars(ltrim($ui_settings['favicon_url'], '/')); // Path relative to root ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <?php if (!empty(trim($custom_styles))): ?>
    <style>
        <?php echo "
" . trim($custom_styles) . "
"; ?>
    </style>
    <?php endif; ?>
</head>
<body class="bg-gray-100 text-gray-800 flex flex-col min-h-screen">

    <header class="bg-white shadow-md sticky top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="text-xl font-semibold text-gray-700">
                <?php if (!empty($ui_settings['header_logo_url'])): ?>
                    <img src="<?php echo htmlspecialchars(ltrim($ui_settings['header_logo_url'], '/')); ?>" alt="<?php echo t('app_title_logo_alt', [], $ui_settings['site_title']); ?>" class="h-8 md:h-10 max-w-xs inline-block">
                <?php else: ?>
                    <?php echo $ui_settings['site_title']; // Already translated if from t('app_title') ?>
                <?php endif; ?>
            </div>
            <a href="admin/login.php"
               class="text-white font-bold py-2 px-4 rounded inline-flex items-center transition-colors duration-150"
               style="background-color: var(--main-color, <?php echo $ui_settings['main_color']; ?>); color: white;"
               onmouseover="this.style.backgroundColor='<?php echo htmlspecialchars(adjustBrightness($ui_settings['main_color'], -20)); ?>'"
               onmouseout="this.style.backgroundColor='var(--main-color, <?php echo $ui_settings['main_color']; ?>)'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                <?php echo t('admin_login_button', [], 'Admin Login'); ?>
            </a>
        </div>
    </header>

    <main class="flex-grow">
        <?php
        // This is where the actual page content from index.php (or other pages) will be injected
        if (isset($pageContent)) {
            echo $pageContent;
        } else {
            echo '<div class="container mx-auto px-6 py-8"><p class="text-red-500">' . t('layout_page_content_not_loaded', [], 'Error: Page content was not provided to the layout.') . '</p></div>';
        }
        ?>
    </main>

    <footer class="text-center py-8 text-gray-600 text-sm bg-gray-200 mt-auto">
        <?php
        echo t('footer_copyright', ['year' => date('Y'), 'app_name' => $ui_settings['site_title']], '&copy; {year} {app_name}. All rights reserved.');
        ?>
    </footer>
    <?php
    // Helper function for hover effect on button (can be moved to helpers.php if used elsewhere)
    if (!function_exists('adjustBrightness')) {
        function adjustBrightness($hex, $steps) {
            $steps = max(-255, min(255, $steps));
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
            }
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
            $r = max(0,min(255,$r + $steps));
            $g = max(0,min(255,$g + $steps));
            $b = max(0,min(255,$b + $steps));
            return '#'.str_pad(dechex($r),2,'0',STR_PAD_LEFT).str_pad(dechex($g),2,'0',STR_PAD_LEFT).str_pad(dechex($b),2,'0',STR_PAD_LEFT);
        }
    }
    ?>
    <script src="assets/js/script.js" defer></script>
</body>
</html>
