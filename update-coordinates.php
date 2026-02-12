<?php
/**
 * Coordinate Precision Update
 * Run this to ensure all coordinates are stored with 6 decimal precision
 */

require_once __DIR__ . '/config/database.php';

echo "Updating coordinate precision...\n\n";

try {
    $db = getDB();
    
    // Update existing projects to ensure 6 decimal precision
    $stmt = $db->query("SELECT id, latitude, longitude FROM projects");
    $projects = $stmt->fetchAll();
    
    $updated = 0;
    
    foreach ($projects as $project) {
        $lat = number_format((float)$project['latitude'], 6, '.', '');
        $lng = number_format((float)$project['longitude'], 6, '.', '');
        
        if ($lat != $project['latitude'] || $lng != $project['longitude']) {
            $stmt = $db->prepare("UPDATE projects SET latitude = ?, longitude = ? WHERE id = ?");
            $stmt->execute([$lat, $lng, $project['id']]);
            $updated++;
        }
    }
    
    echo "✓ Updated {$updated} projects with 6-decimal precision\n";
    echo "✓ Total projects processed: " . count($projects) . "\n";
    echo "\nCoordinate precision is now set to:\n";
    echo "- Latitude: 6 decimal places (0.000001 = 0.1 meter precision)\n";
    echo "- Longitude: 6 decimal places (0.000001 = 0.1 meter precision)\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
