<?php
/**
 * Activity Log API
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
    
    switch ($action) {
        case 'list':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $sql = "SELECT al.*, u.username 
                    FROM activity_logs al 
                    LEFT JOIN users u ON al.user_id = u.id 
                    ORDER BY al.created_at DESC 
                    LIMIT ? OFFSET ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$limit, $offset]);
            $logs = $stmt->fetchAll();
            
            sendJsonResponse(true, ['logs' => $logs]);
            break;
            
        case 'stats':
            // Get activity stats
            $stats = [
                'total_today' => 0,
                'total_week' => 0,
                'by_action' => [],
                'by_user' => []
            ];
            
            // Today's count
            $stmt = $db->query("SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()");
            $stats['total_today'] = (int)$stmt->fetch()['count'];
            
            // This week's count
            $stmt = $db->query("SELECT COUNT(*) as count FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['total_week'] = (int)$stmt->fetch()['count'];
            
            // By action
            $stmt = $db->query("SELECT action, COUNT(*) as count FROM activity_logs GROUP BY action ORDER BY count DESC LIMIT 10");
            $stats['by_action'] = $stmt->fetchAll();
            
            // By user
            $stmt = $db->query("SELECT u.username, COUNT(*) as count FROM activity_logs al JOIN users u ON al.user_id = u.id GROUP BY al.user_id ORDER BY count DESC LIMIT 10");
            $stats['by_user'] = $stmt->fetchAll();
            
            sendJsonResponse(true, ['stats' => $stats]);
            break;
            
        default:
            sendJsonResponse(false, null, 'Unknown action');
    }
    
} catch (Exception $e) {
    error_log("Activity Log API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error');
}
