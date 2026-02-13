<?php
/**
 * Database Schema Verification Script for Audit
 * Writes results to audit_result.json
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    $results = [];

    // 1. MySQL version
    $stmt = $db->query("SELECT VERSION() as version");
    $results['mysql_version'] = $stmt->fetch(PDO::FETCH_ASSOC)['version'];

    // 2. Get all tables
    $stmt = $db->query("SHOW TABLES");
    $results['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Check projects table columns
    $stmt = $db->query("SHOW COLUMNS FROM projects");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results['projects_columns'] = array_column($cols, 'Field');

    // 4. Check each critical table
    $criticalTables = ['activities', 'daily_metrics', 'import_templates', 'project_type_fields', 'login_attempts'];
    foreach ($criticalTables as $table) {
        try {
            $stmt = $db->query("SHOW COLUMNS FROM $table");
            $results[$table . '_columns'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
        } catch (Exception $e) {
            $results[$table . '_columns'] = 'MISSING';
        }
    }

    // 5. Summary of audit checks
    $projectCols = $results['projects_columns'];
    $results['audit_checks'] = [
        'C2_updated_by_in_projects' => in_array('updated_by', $projectCols),
        'C6_project_type_in_projects' => in_array('project_type', $projectCols),
        'C6_custom_data_in_projects' => in_array('custom_data', $projectCols),
        'C6_activities_table_exists' => ($results['activities_columns'] !== 'MISSING'),
        'C6_daily_metrics_table_exists' => ($results['daily_metrics_columns'] !== 'MISSING'),
        'C6_import_templates_table_exists' => ($results['import_templates_columns'] !== 'MISSING'),
        'C6_project_type_fields_table_exists' => ($results['project_type_fields_columns'] !== 'MISSING'),
        'H1_login_attempts_table_exists' => ($results['login_attempts_columns'] !== 'MISSING'),
    ];

    file_put_contents(__DIR__ . '/audit_result.json', json_encode($results, JSON_PRETTY_PRINT));
    echo "OK";

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/audit_result.json', json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT));
    echo "ERROR: " . $e->getMessage();
}
