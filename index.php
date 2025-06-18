<?php
// index.php (Public facing page)

// Helpers are needed for t() function if used directly in $pageContent generation.
// frontend_layout.php also includes helpers.php, so $pdo becomes available there.
require_once __DIR__ . '/api/helpers.php';

// Optional: Set a specific title for this page.
// If not set, frontend_layout.php will use the global site_title from settings.
// $pageTitle = t('home_page_title', [], 'Home'); // Example if a specific title is desired

ob_start(); // Start output buffering
?>

<!-- Main content specific to index.php -->
<div class="pt-20 container mx-auto px-6 py-8"> <!-- Adjust pt-20 if header height in layout changes -->
    <div class="flex flex-col items-center justify-center min-h-[calc(100vh-10rem)]"> <!-- Adjust min-h for footer height -->
        <div class="bg-white p-8 sm:p-10 rounded-xl shadow-xl w-full max-w-xl text-center">
            <h1 class="text-3xl font-bold mb-4 text-gray-800"><?php echo t('frontend_analyze_form_title', [], 'Analyze SEO Performance'); ?></h1>
            <p class="text-gray-600 mb-8"><?php echo t('frontend_form_subtitle', [], 'Enter a URL to get started with your SEO analysis.'); ?></p>

            <form action="<?php echo t('analyze_form_action_url', [], 'api/analyze.php'); ?>" method="POST" class="space-y-6">
                <div>
                    <label for="url" class="sr-only"><?php echo t('frontend_url_label_sr', [], 'Website URL'); ?></label>
                    <input type="url" name="url" id="url"
                           placeholder="<?php echo t('frontend_url_placeholder', [], 'Enter website URL (e.g., https://www.example.com)'); ?>"
                           class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <button type="submit"
                        class="w-full text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition-colors duration-150 text-lg hover:opacity-90"
                        style="background-color: var(--main-color, #10B981);"> <!-- Default to a green if var not set -->
                    <?php echo t('analyze_button', [], 'Analyze'); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean(); // Store the buffered content in $pageContent

require_once 'frontend_layout.php'; // This will include the full HTML structure, header, footer, and echo $pageContent.
?>
