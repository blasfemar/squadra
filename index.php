<?php require_once __DIR__ . '/api/helpers.php'; // Include helpers for t() ?>
<!DOCTYPE html>
<html lang="<?php echo t('html_lang', [], 'en'); // Add a key for html lang if needed, default to 'en' for now ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('app_title', [], 'Squadra SEO Analyzer'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <?php
        // Placeholder for later dynamic styles from frontend_layout.php (Part 2 of Prompt 3)
        // For now, no dynamic styles here, just text i18n.
    ?>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <div class="text-xl font-semibold text-gray-700"><?php echo t('app_title', [], 'Squadra SEO Analyzer'); ?></div>
            <a href="admin/login.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                <?php echo t('admin_login_button', [], 'Admin Login'); ?>
            </a>
        </div>
    </header>

    <main class="pt-20 container mx-auto px-6 py-8">
        <div class="flex flex-col items-center justify-center">
            <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg text-center">
                <h2 class="text-2xl font-bold mb-6 text-gray-800"><?php echo t('frontend_analyze_form_title', [], 'Analyze SEO Performance'); ?></h2>
                <form action="<?php echo t('analyze_form_action_url', [], 'api/analyze.php'); // Make form action translatable if needed or dynamic ?>" method="POST">
                    <div class="mb-4">
                        <label for="url" class="sr-only"><?php echo t('frontend_url_label_sr', [], 'Website URL'); ?></label>
                        <input type="url" name="url" id="url" placeholder="<?php echo t('frontend_url_placeholder', [], 'Enter website URL (e.g., https://www.example.com)'); ?>"
                               class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded focus:outline-none focus:shadow-outline w-full">
                        <?php echo t('analyze_button', [], 'Analyze'); ?>
                    </button>
                </form>
                <p class="mt-4 text-sm text-gray-600"><?php echo t('frontend_form_subtitle', [], 'Enter a URL to get started with your SEO analysis.'); ?></p>
            </div>
        </div>
    </main>

    <footer class="text-center py-8 text-gray-600 text-sm">
        <?php
        // Note: For the copyright, if app_title is fetched by t('app_title'), it might be redundant to pass it as a param.
        // Simpler: t('footer_copyright_simple', ['year' => date('Y')]) which would be "&copy; {year} Squadra SEO Analyzer..."
        // Or, if you want app_name to also be from a t() call:
        // $appNameForFooter = t('app_title');
        // echo t('footer_copyright', ['year' => date('Y'), 'app_name' => $appNameForFooter]);
        // For now, using the key as defined previously.
        echo t('footer_copyright', ['year' => date('Y'), 'app_name' => t('app_title', [], 'Squadra SEO Analyzer')], '&copy; {year} {app_name}. All rights reserved.');
        ?>
    </footer>

</body>
</html>
