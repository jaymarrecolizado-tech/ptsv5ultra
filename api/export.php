<?php
/**
 * Export API - PDF and Excel Generation
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

$type = isset($_GET['type']) ? $_GET['type'] : 'pdf';

try {
    $db = getDB();
    
    // Build filter conditions
    $where = [];
    $params = [];
    
    if (!empty($_GET['province']) && $_GET['province'] !== 'all') {
        $where[] = "province = ?";
        $params[] = $_GET['province'];
    }
    
    if (!empty($_GET['district']) && $_GET['district'] !== 'all') {
        $where[] = "district = ?";
        $params[] = $_GET['district'];
    }
    
    if (!empty($_GET['municipality']) && $_GET['municipality'] !== 'all') {
        $where[] = "municipality = ?";
        $params[] = $_GET['municipality'];
    }
    
    if (!empty($_GET['barangay']) && $_GET['barangay'] !== 'all') {
        $where[] = "barangay = ?";
        $params[] = $_GET['barangay'];
    }
    
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
    
    // Get projects
    $sql = "SELECT * FROM projects";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= " ORDER BY province, municipality, barangay, site_code";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();
    
    // Get summary stats
    $total = count($projects);
    $completed = count(array_filter($projects, fn($p) => $p['status'] === 'Done'));
    $pending = $total - $completed;
    $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    
    if ($type === 'csv') {
        // Excel/CSV Export
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="project_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        // Headers
        fputcsv($output, [
            'Site Code', 'Project Name', 'Site Name', 'Province', 'District', 
            'Municipality', 'Barangay', 'Latitude', 'Longitude', 
            'Activation Date', 'Status', 'Notes'
        ]);
        
        // Data
        foreach ($projects as $project) {
            fputcsv($output, [
                $project['site_code'],
                $project['project_name'],
                $project['site_name'],
                $project['province'],
                $project['district'],
                $project['municipality'],
                $project['barangay'],
                $project['latitude'],
                $project['longitude'],
                $project['activation_date'],
                $project['status'],
                $project['notes']
            ]);
        }
        
        // Add summary
        fputcsv($output, []);
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Total Projects', $total]);
        fputcsv($output, ['Completed', $completed]);
        fputcsv($output, ['Pending', $pending]);
        fputcsv($output, ['Completion Rate', $completionRate . '%']);
        
        fclose($output);
        
    } else {
        // PDF Export (HTML-based)
        $filterDescription = [];
        if (!empty($_GET['province']) && $_GET['province'] !== 'all') $filterDescription[] = "Province: {$_GET['province']}";
        if (!empty($_GET['district']) && $_GET['district'] !== 'all') $filterDescription[] = "District: {$_GET['district']}";
        if (!empty($_GET['municipality']) && $_GET['municipality'] !== 'all') $filterDescription[] = "Municipality: {$_GET['municipality']}";
        if (!empty($_GET['barangay']) && $_GET['barangay'] !== 'all') $filterDescription[] = "Barangay: {$_GET['barangay']}";
        if (!empty($_GET['status']) && $_GET['status'] !== 'all') $filterDescription[] = "Status: {$_GET['status']}";
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Project Implementation Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2563eb; padding-bottom: 20px; }
                .header h1 { color: #1e40af; margin: 0; font-size: 24px; }
                .header p { color: #6b7280; margin: 5px 0; }
                .summary { background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
                .summary-grid { display: flex; justify-content: space-around; text-align: center; }
                .summary-item { flex: 1; }
                .summary-item h3 { margin: 0; font-size: 28px; color: #2563eb; }
                .summary-item p { margin: 5px 0 0; color: #6b7280; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
                th { background: #2563eb; color: white; padding: 10px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
                tr:nth-child(even) { background: #f9fafb; }
                .status-done { color: #059669; font-weight: bold; }
                .status-pending { color: #d97706; font-weight: bold; }
                .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
                .filters { background: #eff6ff; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 11px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Project Implementation Report</h1>
                <p>Generated on: ' . date('F d, Y h:i A') . '</p>
            </div>
            
            <div class="filters">
                <strong>Filters Applied:</strong> ' . (empty($filterDescription) ? 'None (All Projects)' : implode(' | ', $filterDescription)) . '
            </div>
            
            <div class="summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <h3>' . $total . '</h3>
                        <p>Total Projects</p>
                    </div>
                    <div class="summary-item">
                        <h3 style="color: #059669;">' . $completed . '</h3>
                        <p>Completed</p>
                    </div>
                    <div class="summary-item">
                        <h3 style="color: #d97706;">' . $pending . '</h3>
                        <p>Pending</p>
                    </div>
                    <div class="summary-item">
                        <h3 style="color: #7c3aed;">' . $completionRate . '%</h3>
                        <p>Completion Rate</p>
                    </div>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Site Code</th>
                        <th>Project Name</th>
                        <th>Site Name</th>
                        <th>Province</th>
                        <th>District</th>
                        <th>Municipality</th>
                        <th>Barangay</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($projects as $project) {
            $statusClass = $project['status'] === 'Done' ? 'status-done' : 'status-pending';
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($project['site_code']) . '</td>
                        <td>' . htmlspecialchars($project['project_name']) . '</td>
                        <td>' . htmlspecialchars($project['site_name']) . '</td>
                        <td>' . htmlspecialchars($project['province']) . '</td>
                        <td>' . htmlspecialchars($project['district']) . '</td>
                        <td>' . htmlspecialchars($project['municipality']) . '</td>
                        <td>' . htmlspecialchars($project['barangay']) . '</td>
                        <td>' . formatDate($project['activation_date']) . '</td>
                        <td class="' . $statusClass . '">' . $project['status'] . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <div class="footer">
                <p>Project Tracking System - Confidential Report</p>
                <p>Generated by: ' . ($_SESSION['username'] ?? 'System') . '</p>
            </div>
        </body>
        </html>';
        
        // Output as HTML for browser print-to-PDF
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    sendJsonResponse(false, null, 'Export failed: ' . $e->getMessage());
}
