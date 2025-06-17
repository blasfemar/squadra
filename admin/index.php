<?php
require_once 'auth.php'; // Ensures user is authenticated and session is started
require_once __DIR__ . '/../api/helpers.php'; // Include helpers for t()

// $pageTitle is used by layout.php
$pageTitle = t('admin_dashboard_title', [], 'Dashboard'); // Already seeded

$adminUsername = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : t('admin_text', [], 'Admin'); // 'admin_text' already seeded

// Start output buffering to capture content for the layout
ob_start();
?>

<!-- Welcome Message -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-2"><?php echo t('admin_dashboard_welcome', ['username' => $adminUsername], 'Welcome back, {username}!'); // Already seeded ?></h2>
    <p class="text-gray-600"><?php echo t('admin_dashboard_intro_text', [], 'This is your Squadra SEO Analyzer dashboard. From here, you can manage users, customize interface settings, and update the application.'); // Already seeded ?></p>
</div>

<!-- Placeholder for Dashboard Widgets/Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700 mb-3"><?php echo t('admin_dashboard_stats_title', [], 'Quick Stats (Placeholder)'); // Already seeded ?></h3>
        <p class="text-gray-600"><?php echo t('admin_dashboard_total_users_label', [], 'Total Users:'); ?> <span class="font-bold">
            <?php
            // Example: Fetch user count if $db is available (global $pdo;)
            // For now, using placeholder as db connection for this specific stat is not established here.
            echo t('status_not_available', [], 'N/A'); // Already seeded
            ?>
        </span></p>
        <p class="text-gray-600"><?php echo t('admin_dashboard_analyses_label', [], 'Analyses Performed:'); ?> <span class="font-bold"><?php echo t('status_not_available', [], 'N/A'); ?></span></p>
        <p class="text-gray-600"><?php echo t('admin_dashboard_site_health_label', [], 'Site Health:'); ?> <span class="font-bold text-green-500"><?php echo t('site_health_good', [], 'Good'); // Already seeded ?></span></p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700 mb-3"><?php echo t('admin_dashboard_quick_links_title', [], 'Quick Links'); // Already seeded ?></h3>
        <ul class="space-y-2">
            <li><a href="manage_users.php" class="text-blue-600 hover:text-blue-800 hover:underline">- <?php echo t('admin_dashboard_manage_users_link', [], 'Manage Users'); // Already seeded ?></a></li>
            <li><a href="manage_ui.php" class="text-blue-600 hover:text-blue-800 hover:underline">- <?php echo t('admin_dashboard_customize_interface_link', [], 'Customize Interface'); // Already seeded ?></a></li>
            <li><a href="update_app.php" class="text-blue-600 hover:text-blue-800 hover:underline" title="<?php echo t('admin_dashboard_check_updates_link_title', [], 'Uses POST from sidebar, this is a GET link for now'); ?>">- <?php echo t('admin_dashboard_check_updates_link', [], 'Check for Updates (via Sidebar)'); // Already seeded. New title key needed. ?></a></li>
        </ul>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700 mb-3"><?php echo t('admin_dashboard_sysinfo_title', [], 'System Information (Placeholder)'); // Already seeded ?></h3>
        <p class="text-gray-600 text-sm"><?php echo t('admin_dashboard_php_version_label', [], 'PHP Version:'); ?> <?php echo htmlspecialchars(phpversion()); // PHP version is dynamic, not translated ?></p>
        <p class="text-gray-600 text-sm"><?php echo t('admin_dashboard_app_version_label', [], 'App Version:'); ?> <span class="font-bold">
             <?php
                $app_version = t('status_not_available', [], 'N/A');
                $repo_root_path = realpath(__DIR__ . '/../');
                if ($repo_root_path) {
                    $git_command = 'cd ' . escapeshellarg($repo_root_path) . ' && git rev-parse --short HEAD';
                    $version_output = shell_exec($git_command . ' 2>&1');
                    if ($version_output !== null) {
                        $version_output_trimmed = trim($version_output);
                        if (preg_match('/^[0-9a-f]{7,12}$/', $version_output_trimmed)) {
                            $app_version = $version_output_trimmed;
                        }
                    }
                }
                echo htmlspecialchars($app_version); // Version string itself is not translated
             ?>
        </span></p>
        <!-- Add more system info if relevant -->
    </div>

</div>

<?php
$pageContent = ob_get_clean(); // Get content from buffer
require_once 'layout.php';    // Include the layout
?>
