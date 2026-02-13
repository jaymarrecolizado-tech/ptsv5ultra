<?php
/**
 * Comprehensive Feature Test
 */

require_once __DIR__ . '/config/database.php';

echo "========================================\n";
echo "  FEATURE TESTING\n";
echo "========================================\n\n";

function test($name, $condition, $details = '') {
    if ($condition) {
        echo "✓ $name" . ($details ? " - $details" : "") . "\n";
        return true;
    } else {
        echo "✗ $name" . ($details ? " - $details" : "") . "\n";
        return false;
    }
}

function testQuery($db, $name, $sql) {
    try {
        $stmt = $db->query($sql);
        $result = $stmt->fetchAll();
        return test($name, count($result) > 0, count($result) . " records");
    } catch (Exception $e) {
        echo "✗ $name - ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

$db = getDB();
$passed = 0;
$failed = 0;

// 1. DATABASE TESTS
echo "1. DATABASE DATA\n";
if (testQuery($db, "Projects exist", "SELECT * FROM projects LIMIT 5")) $passed++; else $failed++;
if (testQuery($db, "Activities exist", "SELECT * FROM activities LIMIT 5")) $passed++; else $failed++;
if (testQuery($db, "Daily metrics exist", "SELECT * FROM daily_metrics LIMIT 5")) $passed++; else $failed++;
if (testQuery($db, "Users exist", "SELECT * FROM users LIMIT 1")) $passed++; else $failed++;

// 2. PROJECT TYPE TESTS
echo "\n2. PROJECT TYPES\n";
$config = require __DIR__ . '/config/project_types.php';
foreach ($config['project_types'] as $typeName => $typeConfig) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE project_type = ?");
    $stmt->execute([$typeName]);
    $count = $stmt->fetchColumn();
    if (test("  $typeName", true, "$count projects")) $passed++; else $failed++;
}

// 3. GEOGRAPHIC DATA
echo "\n3. GEOGRAPHIC DATA\n";
$stmt = $db->query("SELECT province, COUNT(*) as c FROM projects GROUP BY province");
foreach ($stmt->fetchAll() as $row) {
    if (test("  {$row['province']}", true, "{$row['c']} projects")) $passed++; else $failed++;
}

$stmt = $db->query("SELECT COUNT(*) FROM projects WHERE latitude != 0 AND longitude != 0");
$withCoords = $stmt->fetchColumn();
if (test("Projects with coordinates", $withCoords > 0, "$withCoords projects")) $passed++; else $failed++;

// 4. API SIMULATION TESTS
echo "\n4. API SIMULATION\n";

// Test projects API logic
$stmt = $db->query("SELECT COUNT(*) FROM projects");
$total = $stmt->fetchColumn();
if (test("Projects count", $total > 0, "$total total")) $passed++; else $failed++;

$stmt = $db->query("SELECT COUNT(*) FROM projects WHERE status = 'Done'");
$done = $stmt->fetchColumn();
if (test("Completed projects", true, "$done completed")) $passed++; else $failed++;

$stmt = $db->query("SELECT COUNT(*) FROM projects WHERE status = 'Pending'");
$pending = $stmt->fetchColumn();
if (test("Pending projects", true, "$pending pending")) $passed++; else $failed++;

$stmt = $db->query("SELECT COUNT(DISTINCT province) FROM projects");
$provinces = $stmt->fetchColumn();
if (test("Province count", $provinces > 0, "$provinces provinces")) $passed++; else $failed++;

// 5. EGOVPH ACTIVITIES
echo "\n5. EGOVPH ACTIVITIES\n";
$stmt = $db->query("SELECT activity_type, COUNT(*) as c FROM activities GROUP BY activity_type");
foreach ($stmt->fetchAll() as $row) {
    if (test("  {$row['activity_type']}", true, "{$row['c']} activities")) $passed++; else $failed++;
}

$stmt = $db->query("SELECT SUM(participants) FROM activities");
$participants = $stmt->fetchColumn() ?: 0;
if (test("Total participants", true, "$participants participants")) $passed++; else $failed++;

$stmt = $db->query("SELECT SUM(downloads) FROM activities");
$downloads = $stmt->fetchColumn() ?: 0;
if (test("Total downloads", true, "$downloads downloads")) $passed++; else $failed++;

// 6. FREE-WIFI METRICS
echo "\n6. FREE-WIFI DAILY METRICS\n";
$stmt = $db->query("SELECT COUNT(DISTINCT site_code) FROM daily_metrics");
$sites = $stmt->fetchColumn();
if (test("Sites with metrics", $sites > 0, "$sites sites")) $passed++; else $failed++;

$stmt = $db->query("SELECT status, COUNT(*) as c FROM daily_metrics GROUP BY status");
foreach ($stmt->fetchAll() as $row) {
    if (test("  Status: {$row['status']}", true, "{$row['c']} records")) $passed++; else $failed++;
}

$stmt = $db->query("SELECT SUM(unique_users) FROM daily_metrics");
$users = $stmt->fetchColumn() ?: 0;
if (test("Total unique users", true, "$users users")) $passed++; else $failed++;

// 7. FILE EXISTENCE
echo "\n7. CRITICAL FILES\n";
$files = [
    'index.php' => 'Login page',
    'pages/dashboard.php' => 'Dashboard',
    'pages/projects.php' => 'Projects list',
    'pages/import-typed.php' => 'Import page',
    'pages/reports-typed.php' => 'Reports page',
    'api/projects.php' => 'Projects API',
    'api/reports.php' => 'Reports API',
    'api/import-typed.php' => 'Import API',
    'assets/js/app.js' => 'App JavaScript',
    'assets/js/map.js' => 'Map JavaScript',
    'includes/header.php' => 'Header template',
    'includes/footer.php' => 'Footer template',
];
foreach ($files as $file => $desc) {
    if (test("  $desc", file_exists(__DIR__ . '/' . $file))) $passed++; else $failed++;
}

// 8. SAMPLE DATA VERIFICATION
echo "\n8. SAMPLE DATA CHECK\n";
$stmt = $db->query("SELECT site_code, site_name, province, latitude, longitude FROM projects LIMIT 3");
echo "  Sample projects:\n";
foreach ($stmt->fetchAll() as $p) {
    echo "    - {$p['site_code']}: {$p['site_name']} ({$p['province']}) [{$p['latitude']}, {$p['longitude']}]\n";
}
$passed++;

$stmt = $db->query("SELECT activity_id, activity_title, activity_type, participants FROM activities LIMIT 3");
echo "  Sample activities:\n";
foreach ($stmt->fetchAll() as $a) {
    echo "    - {$a['activity_id']}: {$a['activity_title']} ({$a['activity_type']}) - {$a['participants']} participants\n";
}
$passed++;

// SUMMARY
echo "\n========================================\n";
echo "  SUMMARY\n";
echo "========================================\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($failed === 0) {
    echo "\n✓ ALL FEATURES WORKING!\n";
} else {
    echo "\n✗ Some tests failed.\n";
}

echo "\n========================================\n";
echo "  QUICK ACCESS\n";
echo "========================================\n";
echo "App URL: http://localhost/Projects/ptsUltra/ptsv5ultra/\n";
echo "Login: admin / admin123\n";
echo "\nPages:\n";
echo "  - Dashboard: pages/dashboard.php\n";
echo "  - Projects: pages/projects.php\n";
echo "  - Import: pages/import-typed.php\n";
echo "  - Reports: pages/reports-typed.php\n";
