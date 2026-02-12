<?php
/**
 * Fix Double URLs Script
 * Reverts /projects/projects/ back to /projects/
 */

$files = [
    'includes/header.php',
    'includes/footer.php', 
    'pages/dashboard.php',
    'pages/projects.php',
    'pages/project-form.php',
    'pages/import.php',
    'pages/reports.php',
    'pages/admin.php',
    'pages/profile.php',
    'pages/register.php',
    'api/projects.php',
    'api/admin.php',
    'api/activity.php',
    'api/upload.php',
    'api/reports.php',
    'api/import.php',
    'api/auth.php',
    'assets/js/api.js',
    'assets/js/app.js',
    'assets/js/map.js',
    'logout.php',
    'diagnose.php',
    'setup.php',
    'index.php',
    'fix-urls.php'
];

$count = 0;

foreach ($files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        $content = file_get_contents($filepath);
        // Fix double projects
        $newContent = str_replace('/projects/projects/', '/projects/', $content);
        if ($content !== $newContent) {
            file_put_contents($filepath, $newContent);
            echo "✓ Fixed: $file\n";
            $count++;
        }
    }
}

echo "\n========================================\n";
echo "✓ Fixed $count files!\n";
echo "========================================\n";
echo "\nAccess the application at:\n";
echo "http://localhost/projects/newPTS/\n";
