<?php
require_once 'auth.php'; // Authenticates and starts session
require_once __DIR__ . '/../api/helpers.php'; // For t() function

$pageTitle = t('admin_changelog_page_title', [], 'Update History / Changelog');

$log_output = null;
$commits = [];
$git_error = '';

// Determine repository root path
$repo_root_path = realpath(__DIR__ . '/../');

if ($repo_root_path) {
    // Command to get git log with a specific format
    // Format: short_hash|date_short|subject
    $git_command = 'cd ' . escapeshellarg($repo_root_path) . ' && git log --pretty=format:"%h|%ad|%s" --date=short -n 50'; // Limit to 50 commits for performance

    // Execute the command. Redirect stderr to stdout to capture any git errors.
    $log_output = shell_exec($git_command . ' 2>&1');

    if ($log_output === null) {
        $git_error = t('admin_changelog_error_exec_failed', [], 'Failed to execute git log command. Ensure shell_exec is enabled and git is installed.');
    } elseif (strpos(strtolower($log_output), 'fatal:') !== false || strpos(strtolower($log_output), 'error:') !== false) {
        // Git command itself returned an error
        $git_error = t('admin_changelog_error_git_command', [], 'Error executing git log command:') . '<pre class="mt-2 p-2 bg-gray-100 text-red-700 border border-red-300 rounded">' . htmlspecialchars($log_output) . '</pre>';
    } else {
        $lines = explode("
", trim($log_output));
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (empty(trim($line))) continue; // Skip empty lines

                $parts = explode('|', $line, 3); // Split into 3 parts: hash, date, subject
                if (count($parts) === 3) {
                    $commits[] = [
                        'hash' => htmlspecialchars(trim($parts[0])),
                        'date' => htmlspecialchars(trim($parts[1])),
                        'subject' => htmlspecialchars(trim($parts[2]))
                    ];
                } else {
                    // Line doesn't match expected format, might be a git message or warning interspersed
                    // For simplicity, we can log it or add it as a raw line if needed.
                    // error_log("Changelog: Unexpected git log line format: " . $line);
                }
            }
        }
        if (empty($commits) && empty($git_error) && !empty(trim($log_output))) {
             $git_error = t('admin_changelog_error_no_commits_parsed', [], 'Could not parse commit history, or no commits found in the expected format.');
        } elseif (empty($commits) && empty($git_error) && empty(trim($log_output))) {
            $git_error = t('admin_changelog_no_history', [], 'No commit history found or git log output was empty.');
        }
    }
} else {
    $git_error = t('admin_changelog_error_repo_path', [], 'Error: Could not determine the repository root path.');
}


// Start output buffering
ob_start();
?>

<div class="bg-white p-6 md:p-8 rounded-lg shadow-md">
    <h2 class="text-2xl sm:text-3xl font-semibold text-gray-800 mb-6">
        <?php echo $pageTitle; ?>
    </h2>

    <?php if ($git_error): ?>
        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg" role="alert">
            <?php echo $git_error; // Already translated and includes <pre> if needed ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($commits)): ?>
        <div class="space-y-3 changelog-accordion">
            <?php foreach ($commits as $index => $commit): ?>
                <details class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm overflow-hidden" <?php echo ($index === 0) ? 'open' : ''; // Open the first commit by default ?>>
                    <summary class="px-5 py-3 cursor-pointer hover:bg-gray-100 focus:outline-none focus-visible:ring focus-visible:ring-blue-500 focus-visible:ring-opacity-75">
                        <strong class="text-gray-700"><?php echo $commit['date']; ?>:</strong>
                        <span class="ml-2 text-gray-800"><?php echo $commit['subject']; ?></span>
                    </summary>
                    <div class="px-5 py-3 border-t border-gray-200 bg-white">
                        <p class="text-sm text-gray-600">
                            <?php echo t('admin_changelog_commit_hash_label', [], 'Commit hash:'); ?>
                            <code class="text-xs bg-gray-100 p-1 rounded"><?php echo $commit['hash']; ?></code>
                        </p>
                        <?php /* Add more details here if needed, like author or full commit message link */ ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
        <p class="mt-6 text-sm text-gray-500">
            <?php echo t('admin_changelog_displaying_commits', ['count' => count($commits)], 'Displaying last {count} commits.'); ?>
        </p>
    <?php elseif (!$git_error): ?>
        <p class="text-gray-600"><?php echo t('admin_changelog_no_commits_display', [], 'No commits to display.'); ?></p>
    <?php endif; ?>
</div>

<style>
/* Optional: Style for the summary marker, if desired */
details summary::-webkit-details-marker {
  /* display: none; /* To hide default marker */
}
details summary {
  position: relative;
  /* padding-left: 1.5rem; /* If hiding default marker and adding custom */
}
/* Custom marker example (optional) */
/*
details summary::before {
  content: '▶';
  position: absolute;
  left: 0.5rem;
  top: 50%;
  transform: translateY(-50%) rotate(0deg);
  transition: transform 0.2s;
}
details[open] summary::before {
  transform: translateY(-50%) rotate(90deg);
}
*/
</style>

<?php
$pageContent = ob_get_clean();
require_once 'layout.php';
?>
