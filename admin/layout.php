<?php
// admin/layout.php
require_once __DIR__ . '/../api/helpers.php'; // Ensures t() and $pdo are available.

$currentScriptName = basename($_SERVER['PHP_SELF']);
$version = 'N/A';
$repo_root_path_for_version = realpath(__DIR__ . '/../');

if ($repo_root_path_for_version) {
    $git_command = 'cd ' . escapeshellarg($repo_root_path_for_version) . ' && git rev-parse --short HEAD';
    $version_output = shell_exec($git_command . ' 2>&1');
    if ($version_output !== null) {
        $version_output = trim($version_output);
        if (preg_match('/^[0-9a-f]{7,12}$/', $version_output)) {
            $version = $version_output;
        }
    }
}

$adminUsername = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';

$tailwindCSS = <<<CSS
        .sidebar-custom-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-custom-scroll::-webkit-scrollbar-track {
            background: #1f2937; /* gray-800 */
        }
        .sidebar-custom-scroll::-webkit-scrollbar-thumb {
            background: #4b5563; /* gray-600 */
            border-radius: 3px;
        }
        .sidebar-custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #6b7280; /* gray-500 */
        }
        .main-content-area {
            overflow-y: auto;
            height: calc(100vh - 4rem); /* Adjust if header height changes (h-16 = 4rem) */
        }
        html, body { height: 100%; margin: 0; }
        body { display: flex; }
        .sidebar { width: 16rem; /* w-64 */ flex-shrink: 0; }
        .content-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        .page-header-sticky { position: sticky; top: 0; z-index: 40; }
CSS;

?>
<!DOCTYPE html>
<html lang="<?php echo t('html_lang', [], 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : t('admin_area_default_title', [], 'Admin Area'); ?> - <?php echo t('app_title', [], 'Squadra SEO'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        <?php echo $tailwindCSS; ?>
    </style>
</head>
<body class="bg-gray-100">

    <!-- Sidebar -->
    <aside class="sidebar bg-gray-900 text-gray-200 p-5 space-y-6 fixed top-0 left-0 h-full shadow-xl sidebar-custom-scroll overflow-y-auto">
        <div class="text-2xl font-bold text-white mb-6 border-b border-gray-700 pb-4">
            <?php echo t('app_title', [], 'Squadra SEO'); ?>
            <span class="block text-xs text-gray-400 mt-1"><?php echo t('admin_text', [], 'Admin'); ?> v<?php echo htmlspecialchars($version); ?></span>
        </div>
        <nav class="flex-grow">
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors <?php echo ($currentScriptName === 'index.php') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:text-white'; ?>">
                        <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        <?php echo t('admin_menu_dashboard', [], 'Dashboard'); ?>
                    </a>
                </li>
                <li>
                    <a href="manage_users.php" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors <?php echo ($currentScriptName === 'manage_users.php') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:text-white'; ?>">
                        <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.004 3.004 0 013.75-2.906z"></path></svg>
                        <?php echo t('admin_menu_user_management', [], 'User Management'); ?>
                    </a>
                </li>
                <li>
                    <a href="manage_ui.php" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors <?php echo ($currentScriptName === 'manage_ui.php') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:text-white'; ?>">
                         <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"></path></svg>
                        <?php echo t('admin_menu_interface_settings', [], 'Interface Settings'); ?>
                    </a>
                </li>
                <li>
                    <a href="manage_translations.php" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors <?php echo ($currentScriptName === 'manage_translations.php') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:text-white'; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707a1 1 0 001.414 1.414L6 12.414V8a4 4 0 118 0v4.414l.293.293a1 1 0 001.414-1.414L14 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                        </svg>
                        <?php echo t('admin_menu_translations_link', [], 'Text Management'); ?>
                    </a>
                </li>
                <li>
                    <a href="changelog.php" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors <?php echo ($currentScriptName === 'changelog.php') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:text-white'; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                        </svg>
                        <?php echo t('admin_menu_changelog', [], 'Changelog'); ?>
                    </a>
                </li>
                <li>
                    <a href="update_app.php" id="updateAppLink" class="flex items-center px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors <?php echo ($currentScriptName === 'update_app.php') ? 'bg-blue-600 text-white font-semibold shadow-md' : 'hover:text-white'; ?>">
                        <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <?php echo t('admin_menu_update_application', [], 'Update Application'); ?>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="mt-auto border-t border-gray-700 pt-4">
             <a href="logout.php" class="flex items-center px-3 py-2.5 rounded-lg text-red-400 hover:bg-red-600 hover:text-white transition-colors">
                <svg class="h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path></svg>
                <?php echo t('admin_menu_logout_param', ['username' => $adminUsername], 'Logout ({username})'); ?>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="content-wrapper ml-64"> <!-- ml-64 to offset sidebar width -->
        <header class="bg-white shadow p-4 page-header-sticky h-16 flex items-center"> <!-- h-16 for fixed height -->
            <h1 class="text-2xl font-semibold text-gray-800"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : t('admin_area_default_title', [], 'Admin Area'); ?></h1>
        </header>

        <main class="p-6 bg-gray-100 flex-grow main-content-area">
            <?php
            if (isset($_SESSION['update_message'])) {
                echo '<div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">' . htmlspecialchars($_SESSION['update_message']) . '</div>';
                unset($_SESSION['update_message']);
            }
            if (isset($_SESSION['update_error'])) {
                echo '<div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">' . htmlspecialchars($_SESSION['update_error']) . '</div>';
                unset($_SESSION['update_error']);
            }
            if (isset($pageContent)) {
                echo $pageContent;
            } else {
                echo '<div class="bg-white p-6 rounded-lg shadow-md"><p class="text-red-500">' . t('layout_page_content_not_loaded', [], 'Error: Page content was not provided to the layout.') . '</p></div>';
            }
            ?>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateAppLink = document.getElementById('updateAppLink');
        if (updateAppLink) {
            updateAppLink.addEventListener('click', function(event) {
                event.preventDefault();
                if (confirm(<?php echo json_encode(t('admin_update_confirm_message', [], 'Are you sure you want to attempt to update the application? This will run `git pull`. Make sure you have a backup if needed.')); ?>)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'update_app.php';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    });
    </script>

</body>
</html>
