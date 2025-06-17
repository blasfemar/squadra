<?php
require_once 'auth.php'; // Ensures user is authenticated and session is started

$pageTitle = "Dashboard"; // Title for the layout

// Any dashboard-specific data fetching can go here in the future
// For example, stats, quick links, etc.
$adminUsername = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Administrator';

// Start output buffering to capture content for the layout
ob_start();
?>

<!-- Welcome Message -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Welcome back, <?php echo $adminUsername; ?>!</h2>
    <p class="text-gray-600">This is your Squadra SEO Analyzer dashboard. From here, you can manage users, customize interface settings, and update the application.</p>
</div>

<!-- Placeholder for Dashboard Widgets/Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Quick Stats (Placeholder)</h3>
        <p class="text-gray-600">Total Users: <span class="font-bold">
            <?php
            // Example: Fetch user count if $db is available
            // This is just a placeholder, actual DB query would be needed
            // For now, we'll assume this part of index.php might not have $db initialized
            // unless we explicitly add it. For a simple welcome, it might not be needed.
            // If $db was initialized:
            // try { $stmt = $db->query("SELECT COUNT(*) FROM users"); echo $stmt->fetchColumn(); } catch(Exception $e) { echo 'N/A'; }
            echo 'N/A'; // Placeholder
            ?>
        </span></p>
        <p class="text-gray-600">Analyses Performed: <span class="font-bold">N/A</span></p>
        <p class="text-gray-600">Site Health: <span class="font-bold text-green-500">Good</span></p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">Quick Links</h3>
        <ul class="space-y-2">
            <li><a href="manage_users.php" class="text-blue-600 hover:text-blue-800 hover:underline">- Manage Users</a></li>
            <li><a href="manage_ui.php" class="text-blue-600 hover:text-blue-800 hover:underline">- Customize Interface</a></li>
            <li><a href="update_app.php" class="text-blue-600 hover:text-blue-800 hover:underline" title="Uses POST from sidebar, this is a GET link for now">- Check for Updates (via Sidebar)</a></li>
        </ul>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold text-gray-700 mb-3">System Information (Placeholder)</h3>
        <p class="text-gray-600 text-sm">PHP Version: <?php echo htmlspecialchars(phpversion()); ?></p>
        <p class="text-gray-600 text-sm">App Version: <span class="font-bold">
             <?php
                // Re-use version fetching logic if needed, or rely on layout's version
                // For simplicity, could just echo what layout already gets, or fetch again
                $app_version = 'N/A';
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
                echo $app_version;
             ?>
        </span></p>
        <!-- Add more system info if relevant -->
    </div>

</div>

<?php
$pageContent = ob_get_clean(); // Get content from buffer
require_once 'layout.php';    // Include the layout
?>
