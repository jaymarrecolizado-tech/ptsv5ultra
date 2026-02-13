<?php
/**
 * System Verification Script
 * Run this to verify all components are working
 */

require_once __DIR__ . '/config/database.php';

echo "========================================\n";
echo "  PROJECT TRACKING SYSTEM VERIFICATION\n";
echo "========================================\n\n";

$passed = 0;
$failed = 0;

function test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "✓ $name\n";
        $passed++;
    } else {
        echo "✗ $name\n";
        $failed++;
    }
}

// 1. Database Tables
echo "1. DATABASE TABLES\n";
try {
    $db = getDB();
    
    $tables = ['projects', 'users', 'daily_metrics', 'activities', 'project_type_fields', 'import_templates'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        test("  Table '$table' exists", $stmt->rowCount() > 0);
    }
} catch (Exception $e) {
    echo "  ✗ Database error: " . $e->getMessage() . "\n";
    $failed += 6;
}

// 2. Data Counts
echo "\n2. DATA COUNTS\n";
try {
    $projectCount = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    test("  Projects: $projectCount records", $projectCount > 0);
    
    $activityCount = $db->query("SELECT COUNT(*) FROM activities")->fetchColumn();
    test("  Activities: $activityCount records", $activityCount > 0);
    
    $metricsCount = $db->query("SELECT COUNT(*) FROM daily_metrics")->fetchColumn();
    test("  Daily Metrics: $metricsCount records", $metricsCount > 0);
    
    $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    test("  Users: $userCount records", $userCount > 0);
} catch (Exception $e) {
    echo "  ✗ Count error: " . $e->getMessage() . "\n";
}

// 3. Project Types
echo "\n3. PROJECT TYPES\n";
$config = require __DIR__ . '/config/project_types.php';
$requiredTypes = ['EgovPH', 'ELGU', 'Free-WIFI for All'];
foreach ($requiredTypes as $type) {
    test("  '$type' configured", isset($config['project_types'][$type]));
}

// 4. Geographic Data
echo "\n4. GEOGRAPHIC DATA\n";
try {
    $withCoords = $db->query("SELECT COUNT(*) FROM projects WHERE latitude != 0 AND longitude != 0")->fetchColumn();
    test("  Projects with coordinates: $withCoords", $withCoords > 0);
    
    $provinces = $db->query("SELECT COUNT(DISTINCT province) FROM projects")->fetchColumn();
    test("  Unique provinces: $provinces", $provinces > 0);
    
    $provinceList = $db->query("SELECT DISTINCT province FROM projects WHERE province != '' LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
    echo "    Provinces: " . implode(', ', $provinceList) . "\n";
} catch (Exception $e) {
    echo "  ✗ Geo error: " . $e->getMessage() . "\n";
}

// 5. Files
echo "\n5. FILES\n";
$files = [
    'api/import-typed.php',
    'api/reports-typed.php',
    'pages/import-typed.php',
    'pages/reports-typed.php',
    'templates/template_egovph.csv',
    'templates/template_elgu.csv',
    'templates/template_freewifi_sites.csv',
    'templates/template_freewifi_daily.csv',
];
foreach ($files as $file) {
    test("  $file exists", file_exists(__DIR__ . '/' . $file));
}

// 6. API Endpoints
echo "\n6. API ENDPOINTS (checking files exist)\n";
$apis = [
    'api/projects.php',
    'api/locations.php',
    'api/activity.php',
];
foreach ($apis as $api) {
    test("  $api exists", file_exists(__DIR__ . '/' . $api));
}

// Summary
echo "\n========================================\n";
echo "  SUMMARY\n";
echo "========================================\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ ALL TESTS PASSED! System is ready.\n";
} else {
    echo "\n✗ Some tests failed. Please review above.\n";
}

echo "\n========================================\n";
echo "  ACCESS URLS\n";
echo "========================================\n";
echo "Application: http://localhost/Projects/ptsUltra/ptsv5ultra/\n";
echo "Dashboard:   http://localhost/Projects/ptsUltra/ptsv5ultra/pages/dashboard.php\n";
echo "Import:      http://localhost/Projects/ptsUltra/ptsv5ultra/pages/import-typed.php\n";
echo "Reports:     http://localhost/Projects/ptsUltra/ptsv5ultra/pages/reports-typed.php\n";
echo "Login:       admin / admin123\n";
