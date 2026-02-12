<?php
/**
 * Reports API
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    $report = isset($_GET['report']) ? $_GET['report'] : 'summary';
    
    switch ($report) {
        case 'summary':
            // Summary report
            $stats = [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'provinces' => 0
            ];
            
            $result = $db->query("SELECT COUNT(*) as count FROM projects");
            $stats['total'] = (int)$result->fetch()['count'];
            
            $result = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'Done'");
            $stats['completed'] = (int)$result->fetch()['count'];
            
            $result = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'Pending'");
            $stats['pending'] = (int)$result->fetch()['count'];
            
            $result = $db->query("SELECT COUNT(DISTINCT province) as count FROM projects");
            $stats['provinces'] = (int)$result->fetch()['count'];
            
            // Projects by province
            $stmt = $db->query("SELECT province, COUNT(*) as count FROM projects GROUP BY province ORDER BY count DESC");
            $byProvince = $stmt->fetchAll();
            
            // Projects by status
            $stmt = $db->query("SELECT status, COUNT(*) as count FROM projects GROUP BY status");
            $byStatus = $stmt->fetchAll();
            
            sendJsonResponse(true, [
                'stats' => $stats,
                'by_province' => $byProvince,
                'by_status' => $byStatus
            ]);
            break;
            
        case 'province':
            // Province analysis
            $where = [];
            $params = [];
            
            if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
                $where[] = "p.status = ?";
                $params[] = $_GET['status'];
            }
            
            if (!empty($_GET['province']) && $_GET['province'] !== 'all') {
                $where[] = "p.province = ?";
                $params[] = $_GET['province'];
            }
            
            $sql = "SELECT 
                province,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                MIN(activation_date) as first_implementation,
                MAX(activation_date) as last_implementation
            FROM projects p";
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " GROUP BY province ORDER BY total DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $provinces = $stmt->fetchAll();
            
            // Calculate completion rates
            foreach ($provinces as &$province) {
                $province['completion_rate'] = $province['total'] > 0 
                    ? round(($province['completed'] / $province['total']) * 100, 1) 
                    : 0;
            }
            
            sendJsonResponse(true, ['provinces' => $provinces]);
            break;
            
        case 'timeline':
            // Timeline report
            $where = [];
            $params = [];
            
            if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $_GET['status'];
            }
            
            if (!empty($_GET['date_from'])) {
                $where[] = "activation_date >= ?";
                $params[] = $_GET['date_from'];
            }
            
            if (!empty($_GET['date_to'])) {
                $where[] = "activation_date <= ?";
                $params[] = $_GET['date_to'];
            }
            
            $sql = "SELECT 
                DATE_FORMAT(activation_date, '%Y-%m') as month,
                COUNT(*) as new_projects,
                SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
            FROM projects";
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " GROUP BY DATE_FORMAT(activation_date, '%Y-%m') ORDER BY month";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $timeline = $stmt->fetchAll();
            
            // Calculate cumulative totals
            $cumulative = 0;
            foreach ($timeline as &$month) {
                $cumulative += $month['new_projects'];
                $month['cumulative'] = $cumulative;
                $month['month_formatted'] = date('M Y', strtotime($month['month'] . '-01'));
            }
            
            sendJsonResponse(true, ['timeline' => $timeline]);
            break;
            
        case 'status':
            // Status report
            $where = [];
            $params = [];
            
            if (!empty($_GET['province']) && $_GET['province'] !== 'all') {
                $where[] = "province = ?";
                $params[] = $_GET['province'];
            }
            
            // Pending projects by duration
            $sql = "SELECT 
                CASE 
                    WHEN DATEDIFF(CURRENT_DATE, activation_date) < 30 THEN 'Less than 30 days'
                    WHEN DATEDIFF(CURRENT_DATE, activation_date) < 60 THEN '30-60 days'
                    WHEN DATEDIFF(CURRENT_DATE, activation_date) < 90 THEN '60-90 days'
                    ELSE '90+ days'
                END as duration_range,
                COUNT(*) as count
            FROM projects 
            WHERE status = 'Pending'";
            
            if (!empty($where)) {
                $sql .= " AND " . implode(" AND ", $where);
            }
            
            $sql .= " GROUP BY duration_range ORDER BY count DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $pendingByDuration = $stmt->fetchAll();
            
            // Completion rate by province
            $sql2 = "SELECT 
                province,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed,
                ROUND((SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as completion_rate
            FROM projects";
            
            if (!empty($where)) {
                $sql2 .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql2 .= " GROUP BY province HAVING total > 0 ORDER BY completion_rate DESC";
            
            $stmt = $db->query($sql2);
            $completionRates = $stmt->fetchAll();
            
            // Pending projects requiring attention
            $sql3 = "SELECT *, DATEDIFF(CURRENT_DATE, activation_date) as pending_days 
            FROM projects 
            WHERE status = 'Pending'";
            
            if (!empty($where)) {
                $sql3 .= " AND " . implode(" AND ", $where);
            }
            
            $sql3 .= " ORDER BY activation_date ASC LIMIT 50";
            
            $stmt = $db->prepare($sql3);
            $stmt->execute($params);
            $pendingProjects = $stmt->fetchAll();
            
            // Format dates
            foreach ($pendingProjects as &$project) {
                $project['activation_date_formatted'] = formatDate($project['activation_date']);
            }
            
            sendJsonResponse(true, [
                'pending_by_duration' => $pendingByDuration,
                'completion_rates' => $completionRates,
                'pending_projects' => $pendingProjects
            ]);
            break;
            
        case 'provinces-list':
            // Get unique provinces for filters
            $stmt = $db->query("SELECT DISTINCT province FROM projects ORDER BY province");
            $provinces = $stmt->fetchAll(PDO::FETCH_COLUMN);
            sendJsonResponse(true, ['provinces' => $provinces]);
            break;
            
        default:
            sendJsonResponse(false, null, 'Unknown report type', []);
    }
    
} catch (Exception $e) {
    error_log("Reports API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error occurred', []);
}
