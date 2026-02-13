<?php
/**
 * Import/Export API
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

// Require CSRF validation for state-changing operations
if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
    requireCsrfToken();
}

try {
    $db = getDB();
    
    switch ($method) {
        case 'POST':
            // CSV Import
            if (empty($_FILES['csv_file'])) {
                sendJsonResponse(false, null, 'No file uploaded', []);
            }
            
            $file = $_FILES['csv_file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                sendJsonResponse(false, null, 'File upload failed', []);
            }
            
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                sendJsonResponse(false, null, 'Could not read file', []);
            }
            
            // Read headers
            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                sendJsonResponse(false, null, 'Empty CSV file', []);
            }
            
            // Normalize headers
            $headers = array_map(function($h) {
                return strtolower(trim(str_replace([' ', '_'], '', $h)));
            }, $headers);
            
            // Required fields mapping
            $fieldMap = [
                'sitecode' => 'site_code',
                'projectname' => 'project_name',
                'sitename' => 'site_name',
                'barangay' => 'barangay',
                'municipality' => 'municipality',
                'province' => 'province',
                'district' => 'district',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
                'dateofactivation' => 'activation_date',
                'activationdate' => 'activation_date',
                'date' => 'activation_date',
                'status' => 'status',
                'notes' => 'notes'
            ];
            
            $batchId = uniqid('import_');
            $imported = 0;
            $errors = [];
            $rowNumber = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $data = [];
                
                // Map CSV columns to fields
                foreach ($headers as $index => $header) {
                    if (isset($fieldMap[$header]) && isset($row[$index])) {
                        $data[$fieldMap[$header]] = trim($row[$index]);
                    }
                }
                
                // Validate required fields
                $required = ['site_code', 'project_name', 'site_name', 'barangay', 'municipality', 'province', 'district', 'latitude', 'longitude', 'activation_date', 'status'];
                $missing = [];
                
                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        $missing[] = $field;
                    }
                }
                
                if (!empty($missing)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => 'Missing fields: ' . implode(', ', $missing)
                    ];
                    logValidationError($batchId, $rowNumber, 'multiple', 'Missing required fields', '', '');
                    continue;
                }
                
                // Validate coordinates
                if (!is_numeric($data['latitude']) || $data['latitude'] < -90 || $data['latitude'] > 90) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => 'Invalid latitude: ' . $data['latitude']
                    ];
                    logValidationError($batchId, $rowNumber, 'latitude', 'Invalid latitude value', $data['latitude'], '');
                    continue;
                }
                
                if (!is_numeric($data['longitude']) || $data['longitude'] < -180 || $data['longitude'] > 180) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => 'Invalid longitude: ' . $data['longitude']
                    ];
                    logValidationError($batchId, $rowNumber, 'longitude', 'Invalid longitude value', $data['longitude'], '');
                    continue;
                }
                
                // Parse and validate date
                $parsedDate = parseDate($data['activation_date']);
                if (!$parsedDate) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => 'Invalid date format: ' . $data['activation_date']
                    ];
                    logValidationError($batchId, $rowNumber, 'activation_date', 'Invalid date format', $data['activation_date'], '');
                    continue;
                }
                
                // Check for duplicate site code
                $stmt = $db->prepare("SELECT id FROM projects WHERE site_code = ?");
                $stmt->execute([$data['site_code']]);
                if ($stmt->fetch()) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => 'Duplicate site code: ' . $data['site_code']
                    ];
                    logValidationError($batchId, $rowNumber, 'site_code', 'Duplicate site code', $data['site_code'], '');
                    continue;
                }
                
                // Auto-correct province
                $originalProvince = $data['province'];
                $correctedProvince = findClosestProvince($data['province']);
                if ($correctedProvince && $correctedProvince !== $originalProvince) {
                    $data['province'] = $correctedProvince;
                    logValidationError($batchId, $rowNumber, 'province', 'Province auto-corrected', $originalProvince, $correctedProvince);
                }
                
                // Insert project
                try {
                    $stmt = $db->prepare("INSERT INTO projects (site_code, project_name, site_name, barangay, municipality, province, district, latitude, longitude, activation_date, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        sanitize($data['site_code']),
                        sanitize($data['project_name']),
                        sanitize($data['site_name']),
                        standardizeLocation($data['barangay']),
                        standardizeLocation($data['municipality']),
                        standardizeLocation($data['province']),
                        standardizeLocation($data['district']),
                        (float)$data['latitude'],
                        (float)$data['longitude'],
                        $parsedDate,
                        normalizeStatus($data['status']),
                        !empty($data['notes']) ? sanitize($data['notes']) : '',
                        $_SESSION['user_id']
                    ]);
                    $imported++;
                } catch (Exception $e) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => 'Database error: ' . $e->getMessage()
                    ];
                }
            }
            
            fclose($handle);
            
            sendJsonResponse(true, [
                'imported' => $imported,
                'errors' => $errors,
                'total_rows' => $rowNumber - 1
            ], "Imported {$imported} projects successfully");
            break;
            
        case 'GET':
            // CSV Export or Template Download
            $action = isset($_GET['action']) ? $_GET['action'] : 'export';
            
            if ($action === 'template') {
                // Download CSV template
                $headers = ['Site Code', 'Project Name', 'Site Name', 'Barangay', 'Municipality', 'Province', 'District', 'Latitude', 'Longitude', 'Date of Activation', 'Status', 'Notes'];
                $sampleData = [
                    ['UNDP-GI-0001', 'Free-WIFI for All', 'Sample Site', 'Sample Barangay', 'Sample Municipality', 'Batanes', 'District I', '20.728794', '121.804235', '2024-04-30', 'Done', 'Sample notes']
                ];
                
                $csv = generateCSV($sampleData, $headers);
                
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="project_template.csv"');
                echo $csv;
                exit;
            } else {
                // Export all projects
                $stmt = $db->query("SELECT * FROM projects ORDER BY created_at DESC");
                $projects = $stmt->fetchAll();
                
                $data = [];
                foreach ($projects as $project) {
                    $data[] = [
                        $project['site_code'],
                        $project['project_name'],
                        $project['site_name'],
                        $project['barangay'],
                        $project['municipality'],
                        $project['province'],
                        $project['district'],
                        $project['latitude'],
                        $project['longitude'],
                        $project['activation_date'],
                        $project['status'],
                        $project['notes']
                    ];
                }
                
                $headers = ['Site Code', 'Project Name', 'Site Name', 'Barangay', 'Municipality', 'Province', 'District', 'Latitude', 'Longitude', 'Date of Activation', 'Status', 'Notes'];
                $csv = generateCSV($data, $headers);
                
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="projects_export_' . date('Y-m-d') . '.csv"');
                echo $csv;
                exit;
            }
            break;
            
        default:
            sendJsonResponse(false, null, 'Method not allowed', []);
    }
    
} catch (Exception $e) {
    error_log("Import/Export API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error occurred', []);
}
