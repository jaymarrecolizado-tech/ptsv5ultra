<?php
/**
 * Update Database Schema
 */

require_once __DIR__ . '/config/database.php';

echo "Updating database schema...\n\n";

try {
    $db = getDB();
    
    // Read and execute update script
    $sql = file_get_contents(__DIR__ . '/sql/update_schema.sql');
    
    // Remove USE statement since we're already connected
    $sql = preg_replace('/USE\s+\w+;/i', '', $sql);
    
    // Execute statements
    $db->exec($sql);
    
    echo "✓ Database updated successfully!\n";
    echo "✓ New tables created:\n";
    echo "  - user_profiles\n";
    echo "  - password_resets\n";
    echo "  - project_photos\n";
    echo "  - project_documents\n";
    echo "  - project_categories\n";
    echo "  - project_category_relations\n";
    echo "  - activity_logs\n";
    echo "  - settings\n";
    echo "  - map_zones\n";
    echo "  - project_status_history\n";
    echo "  - project_forecasts\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
