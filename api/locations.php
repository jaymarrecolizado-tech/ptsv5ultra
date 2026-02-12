<?php
/**
 * Location Filter API
 * Returns hierarchical location data for cascading filters
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    $type = isset($_GET['type']) ? $_GET['type'] : 'all';
    
    switch ($type) {
        case 'provinces':
            // Get all provinces with project counts
            $stmt = $db->query("SELECT DISTINCT province, COUNT(*) as project_count FROM projects GROUP BY province ORDER BY province");
            $provinces = $stmt->fetchAll();
            sendJsonResponse(true, ['provinces' => $provinces]);
            break;
            
        case 'districts':
            $province = isset($_GET['province']) ? $_GET['province'] : null;
            if (!$province) {
                sendJsonResponse(false, null, 'Province required');
            }
            
            $stmt = $db->prepare("SELECT DISTINCT district, COUNT(*) as project_count FROM projects WHERE province = ? GROUP BY district ORDER BY district");
            $stmt->execute([$province]);
            $districts = $stmt->fetchAll();
            sendJsonResponse(true, ['districts' => $districts]);
            break;
            
        case 'municipalities':
            $province = isset($_GET['province']) ? $_GET['province'] : null;
            $district = isset($_GET['district']) ? $_GET['district'] : null;
            
            if (!$province) {
                sendJsonResponse(false, null, 'Province required');
            }
            
            $sql = "SELECT DISTINCT municipality, COUNT(*) as project_count FROM projects WHERE province = ?";
            $params = [$province];
            
            if ($district && $district !== 'all') {
                $sql .= " AND district = ?";
                $params[] = $district;
            }
            
            $sql .= " GROUP BY municipality ORDER BY municipality";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $municipalities = $stmt->fetchAll();
            sendJsonResponse(true, ['municipalities' => $municipalities]);
            break;
            
        case 'barangays':
            $province = isset($_GET['province']) ? $_GET['province'] : null;
            $district = isset($_GET['district']) ? $_GET['district'] : null;
            $municipality = isset($_GET['municipality']) ? $_GET['municipality'] : null;
            
            if (!$province) {
                sendJsonResponse(false, null, 'Province required');
            }
            
            $sql = "SELECT DISTINCT barangay, COUNT(*) as project_count FROM projects WHERE province = ?";
            $params = [$province];
            
            if ($district && $district !== 'all') {
                $sql .= " AND district = ?";
                $params[] = $district;
            }
            
            if ($municipality && $municipality !== 'all') {
                $sql .= " AND municipality = ?";
                $params[] = $municipality;
            }
            
            $sql .= " GROUP BY barangay ORDER BY barangay";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $barangays = $stmt->fetchAll();
            sendJsonResponse(true, ['barangays' => $barangays]);
            break;
            
        case 'hierarchy':
            // Get complete hierarchy
            $stmt = $db->query("
                SELECT DISTINCT province, district, municipality, barangay,
                    COUNT(*) as project_count,
                    SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed_count
                FROM projects
                GROUP BY province, district, municipality, barangay
                ORDER BY province, district, municipality, barangay
            ");
            $hierarchy = $stmt->fetchAll();
            sendJsonResponse(true, ['hierarchy' => $hierarchy]);
            break;
            
        default:
            sendJsonResponse(false, null, 'Invalid type');
    }
    
} catch (Exception $e) {
    error_log("Location Filter API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error');
}
