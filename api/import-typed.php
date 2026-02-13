<?php
/**
 * API: Enhanced Dynamic Import Handler with Project Type Support
 * Supports different CSV formats for different project types
 * Handles: Projects, Activities, and Daily Metrics
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$config = require __DIR__ . '/../config/project_types.php';

function getProjectTypeConfig($projectType) {
    global $config;
    return $config['project_types'][$projectType] ?? null;
}

function getAllProjectTypes() {
    global $config;
    return array_keys($config['project_types']);
}

function getImportTemplate($projectType) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM import_templates WHERE project_type = ? AND is_active = TRUE LIMIT 1");
        $stmt->execute([$projectType]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

function parseDate($dateStr) {
    if (empty($dateStr)) return null;
    
    $dateStr = trim($dateStr);
    
    $formats = [
        'Y-m-d',
        'm/d/Y',
        'd/m/Y',
        'F j, Y',
        'M j, Y',
        'j-M-y',
        'Y-m-d',
    ];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateStr);
        if ($date) {
            return $date->format('Y-m-d');
        }
    }
    
    if (strtotime($dateStr)) {
        return date('Y-m-d', strtotime($dateStr));
    }
    
    return null;
}

function validateField($fieldName, $value, $fieldConfig, &$errors) {
    $isValid = true;
    
    if ($fieldConfig['required'] && (empty($value) || $value === '')) {
        $errors[] = "{$fieldConfig['label']} is required";
        return false;
    }
    
    if (empty($value) && !$fieldConfig['required']) {
        return true;
    }
    
    switch ($fieldConfig['type']) {
        case 'number':
            if (!is_numeric($value)) {
                $errors[] = "{$fieldConfig['label']} must be a number";
                $isValid = false;
            } else {
                $num = floatval($value);
                if (isset($fieldConfig['min']) && $num < $fieldConfig['min']) {
                    $errors[] = "{$fieldConfig['label']} must be at least {$fieldConfig['min']}";
                    $isValid = false;
                }
                if (isset($fieldConfig['max']) && $num > $fieldConfig['max']) {
                    $errors[] = "{$fieldConfig['label']} must be at most {$fieldConfig['max']}";
                    $isValid = false;
                }
            }
            break;
            
        case 'select':
            if (isset($fieldConfig['options']) && !in_array($value, $fieldConfig['options'])) {
                $errors[] = "{$fieldConfig['label']} must be one of: " . implode(', ', $fieldConfig['options']);
                $isValid = false;
            }
            break;
            
        case 'text':
            if (isset($fieldConfig['pattern']) && !preg_match($fieldConfig['pattern'], $value)) {
                $errors[] = "{$fieldConfig['label']} format is invalid";
                $isValid = false;
            }
            break;
            
        case 'date':
            if (!parseDate($value)) {
                $errors[] = "{$fieldConfig['label']} must be a valid date";
                $isValid = false;
            }
            break;
    }
    
    return $isValid;
}

function mapCsvToFields($row, $headers, $typeConfig) {
    $data = [];
    $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
    
    foreach ($row as $index => $value) {
        if (!isset($normalizedHeaders[$index])) continue;
        
        $header = $normalizedHeaders[$index];
        $fieldName = null;
        
        foreach ($typeConfig['fields'] as $fname => $fconfig) {
            if (strtolower($fconfig['label']) === $header) {
                $fieldName = $fname;
                break;
            }
        }
        
        if ($fieldName) {
            $data[$fieldName] = trim($value);
        }
    }
    
    return $data;
}

function processEgovPHImport($csvData, $headers, $typeConfig) {
    $results = ['success' => true, 'imported' => 0, 'errors' => [], 'warnings' => []];
    
    $db = getDB();
    $db->beginTransaction();
    
    try {
        $insertStmt = $db->prepare("INSERT INTO activities 
            (project_type, activity_id, activity_title, activity_type, activity_date, 
             participants, downloads, province, municipality, district, status, facilitator, 
             latitude, longitude, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $rowNum = 0;
        foreach ($csvData as $row) {
            $rowNum++;
            $rowErrors = [];
            
            $data = mapCsvToFields($row, $headers, $typeConfig);
            
            foreach ($typeConfig['fields'] as $fieldName => $fieldConfig) {
                $value = $data[$fieldName] ?? '';
                validateField($fieldName, $value, $fieldConfig, $rowErrors);
            }
            
            if (!empty($rowErrors)) {
                $results['errors'][] = ['row' => $rowNum, 'errors' => $rowErrors, 'data' => $row];
                continue;
            }
            
            $activityId = 'EGV-' . str_pad($rowNum, 4, '0', STR_PAD_LEFT);
            
            $insertStmt->execute([
                'EgovPH',
                $activityId,
                $data['activity_title'] ?? '',
                $data['activity_type'] ?? 'Orientation',
                parseDate($data['activity_date'] ?? ''),
                intval($data['participants'] ?? 0),
                intval($data['downloads'] ?? 0),
                $data['province'] ?? '',
                $data['municipality'] ?? '',
                $data['district'] ?? '',
                $data['status'] ?? 'Done',
                $data['facilitator'] ?? '',
                floatval($data['latitude'] ?? 0),
                floatval($data['longitude'] ?? 0),
                $_SESSION['user_id'] ?? 1
            ]);
            
            $results['imported']++;
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $results['success'] = false;
        $results['error'] = $e->getMessage();
    }
    
    return $results;
}

function processELGUImport($csvData, $headers, $typeConfig) {
    $results = ['success' => true, 'imported' => 0, 'errors' => [], 'warnings' => []];
    
    $db = getDB();
    $db->beginTransaction();
    
    try {
        $insertStmt = $db->prepare("INSERT INTO projects 
            (site_code, project_name, project_type, site_name, barangay, municipality, province, district, 
             latitude, longitude, activation_date, status, custom_data, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $rowNum = 0;
        foreach ($csvData as $row) {
            $rowNum++;
            $rowErrors = [];
            
            $data = mapCsvToFields($row, $headers, $typeConfig);
            
            foreach ($typeConfig['fields'] as $fieldName => $fieldConfig) {
                $value = $data[$fieldName] ?? '';
                validateField($fieldName, $value, $fieldConfig, $rowErrors);
            }
            
            if (!empty($rowErrors)) {
                $results['errors'][] = ['row' => $rowNum, 'errors' => $rowErrors, 'data' => $row];
                continue;
            }
            
            $customData = [
                'lgu_name' => $data['lgu_name'] ?? '',
                'lgu_type' => $data['lgu_type'] ?? '',
                'services_digitalized' => $data['services_digitalized'] ?? '',
                'eboss_compliance' => $data['eboss_compliance'] ?? '',
            ];
            
            $insertStmt->execute([
                $data['site_code'] ?? '',
                'ELGU',
                'ELGU',
                $data['lgu_name'] ?? '',
                $data['barangay'] ?? '',
                $data['municipality'] ?? '',
                $data['province'] ?? '',
                $data['district'] ?? '',
                floatval($data['latitude'] ?? 0),
                floatval($data['longitude'] ?? 0),
                parseDate($data['date_digitalized'] ?? ''),
                $data['status'] ?? 'Pending',
                json_encode($customData),
                $_SESSION['user_id'] ?? 1
            ]);
            
            $results['imported']++;
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $results['success'] = false;
        $results['error'] = $e->getMessage();
    }
    
    return $results;
}

function processFreeWiFiImport($csvData, $headers, $typeConfig) {
    $results = ['success' => true, 'imported' => 0, 'errors' => [], 'warnings' => []];
    
    $db = getDB();
    $db->beginTransaction();
    
    try {
        $insertStmt = $db->prepare("INSERT INTO projects 
            (site_code, project_name, project_type, site_name, barangay, municipality, province, district, 
             latitude, longitude, activation_date, status, custom_data, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            site_name = VALUES(site_name),
            municipality = VALUES(municipality),
            province = VALUES(province),
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            activation_date = VALUES(activation_date),
            status = VALUES(status),
            custom_data = VALUES(custom_data)");
        
        $rowNum = 0;
        foreach ($csvData as $row) {
            $rowNum++;
            $rowErrors = [];
            
            $data = mapCsvToFields($row, $headers, $typeConfig);
            
            foreach ($typeConfig['fields'] as $fieldName => $fieldConfig) {
                if (!$fieldConfig['required']) continue;
                $value = $data[$fieldName] ?? '';
                validateField($fieldName, $value, $fieldConfig, $rowErrors);
            }
            
            if (!empty($rowErrors)) {
                $results['errors'][] = ['row' => $rowNum, 'errors' => $rowErrors, 'data' => $row];
                continue;
            }
            
            $customData = [
                'location_name' => $data['location_name'] ?? '',
                'site_type' => $data['site_type'] ?? '',
                'cms_provider' => $data['cms_provider'] ?? '',
                'link_provider' => $data['link_provider'] ?? '',
                'technology' => $data['technology'] ?? '',
                'bandwidth' => $data['bandwidth'] ?? '',
            ];
            
            $insertStmt->execute([
                $data['site_code'] ?? '',
                'Free-WIFI for All',
                'Free-WIFI for All',
                $data['site_name'] ?? $data['location_name'] ?? '',
                $data['barangay'] ?? '',
                $data['municipality'] ?? '',
                $data['province'] ?? '',
                $data['district'] ?? '',
                floatval($data['latitude'] ?? 0),
                floatval($data['longitude'] ?? 0),
                parseDate($data['activation_date'] ?? ''),
                $data['status'] ?? 'Pending',
                json_encode($customData),
                $_SESSION['user_id'] ?? 1
            ]);
            
            $results['imported']++;
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $results['success'] = false;
        $results['error'] = $e->getMessage();
    }
    
    return $results;
}

function processDailyMetricsImport($csvData, $headers) {
    $results = ['success' => true, 'imported' => 0, 'errors' => [], 'warnings' => []];
    
    $db = getDB();
    $db->beginTransaction();
    
    try {
        $insertStmt = $db->prepare("INSERT INTO daily_metrics 
            (site_code, metric_date, status, bandwidth_utilization, unique_users, remarks)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            bandwidth_utilization = VALUES(bandwidth_utilization),
            unique_users = VALUES(unique_users),
            remarks = VALUES(remarks)");
        
        $rowNum = 0;
        foreach ($csvData as $row) {
            $rowNum++;
            
            $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
            $data = [];
            
            foreach ($row as $index => $value) {
                if (!isset($normalizedHeaders[$index])) continue;
                $data[$normalizedHeaders[$index]] = trim($value);
            }
            
            $siteCode = $data['site code'] ?? '';
            $metricDate = parseDate($data['date'] ?? '');
            
            if (empty($siteCode) || empty($metricDate)) {
                $results['errors'][] = ['row' => $rowNum, 'errors' => ['Site Code and Date are required'], 'data' => $row];
                continue;
            }
            
            $insertStmt->execute([
                $siteCode,
                $metricDate,
                $data['status'] ?? 'UP',
                floatval($data['bandwidth utilization'] ?? 0),
                intval($data['unique users'] ?? 0),
                $data['remarks'] ?? ''
            ]);
            
            $results['imported']++;
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $results['success'] = false;
        $results['error'] = $e->getMessage();
    }
    
    return $results;
}

function processRealFreeWiFiCSV($csvData, $headers) {
    $results = ['success' => true, 'imported' => 0, 'errors' => [], 'warnings' => [], 'metrics_imported' => 0];
    
    $db = getDB();
    $db->beginTransaction();
    
    try {
        $projectStmt = $db->prepare("INSERT INTO projects 
            (site_code, project_name, project_type, site_name, barangay, municipality, province, district, 
             latitude, longitude, activation_date, status, custom_data, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            site_name = VALUES(site_name),
            municipality = VALUES(municipality),
            province = VALUES(province),
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            activation_date = VALUES(activation_date),
            status = VALUES(status),
            custom_data = VALUES(custom_data)");
        
        $metricsStmt = $db->prepare("INSERT INTO daily_metrics 
            (site_code, metric_date, status, bandwidth_utilization, unique_users)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            bandwidth_utilization = VALUES(bandwidth_utilization),
            unique_users = VALUES(unique_users)");
        
        $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
        
        $dateColumns = [];
        foreach ($normalizedHeaders as $idx => $header) {
            if (preg_match('/^\d+-[a-z]+-\d+$/', $header) || preg_match('/^[a-z]+ \d+-\d+$/', $header)) {
                $dateColumns[] = ['index' => $idx, 'header' => $header];
            }
        }
        
        $columnMap = [
            'ap site code' => 'site_code',
            'ap site name' => 'site_name',
            'location name' => 'location_name',
            'site type' => 'site_type',
            'cms provider' => 'cms_provider',
            'link provider' => 'link_provider',
            'last mile technology' => 'technology',
            'bw download (cir)' => 'bandwidth',
            'barangay' => 'barangay',
            'locality' => 'municipality',
            'province' => 'province',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'date of activation' => 'activation_date',
            'status' => 'status',
        ];
        
        $rowNum = 0;
        foreach ($csvData as $row) {
            $rowNum++;
            
            $data = [];
            foreach ($row as $index => $value) {
                if (!isset($normalizedHeaders[$index])) continue;
                $header = $normalizedHeaders[$index];
                
                if (isset($columnMap[$header])) {
                    $data[$columnMap[$header]] = trim($value);
                }
            }
            
            if (empty($data['site_code'])) {
                continue;
            }
            
            $customData = [
                'location_name' => $data['location_name'] ?? '',
                'site_type' => $data['site_type'] ?? '',
                'cms_provider' => $data['cms_provider'] ?? '',
                'link_provider' => $data['link_provider'] ?? '',
                'technology' => $data['technology'] ?? '',
                'bandwidth' => $data['bandwidth'] ?? '',
            ];
            
            $projectStmt->execute([
                $data['site_code'],
                'Free-WIFI for All',
                'Free-WIFI for All',
                $data['site_name'] ?? $data['location_name'] ?? '',
                $data['barangay'] ?? '',
                $data['municipality'] ?? '',
                $data['province'] ?? '',
                '',
                floatval($data['latitude'] ?? 0),
                floatval($data['longitude'] ?? 0),
                parseDate($data['activation_date'] ?? ''),
                $data['status'] ?? 'Pending',
                json_encode($customData),
                $_SESSION['user_id'] ?? 1
            ]);
            
            $results['imported']++;
            
            foreach ($dateColumns as $dateCol) {
                $colIdx = $dateCol['index'];
                $dateHeader = $dateCol['header'];
                
                if (!isset($row[$colIdx])) continue;
                
                $status = trim($row[$colIdx]);
                if (empty($status)) continue;
                
                $dateParts = preg_split('/[\s-]+/', $dateHeader);
                if (count($dateParts) >= 3) {
                    $monthMap = ['jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04', 'may' => '05', 'jun' => '06',
                                 'jul' => '07', 'aug' => '08', 'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12'];
                    $month = $monthMap[strtolower($dateParts[0])] ?? '01';
                    $day = str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
                    $year = $dateParts[2] ?? '2026';
                    $metricDate = "$year-$month-$day";
                    
                    $usersCol = $colIdx + 1;
                    $users = isset($row[$usersCol]) ? intval($row[$usersCol]) : 0;
                    
                    $bwUtil = 0;
                    if (in_array(strtoupper($status), ['UP', 'DOWN', 'NO NMS'])) {
                        $bwUtil = 0;
                    } else {
                        $bwUtil = floatval($status);
                        $status = 'UP';
                    }
                    
                    try {
                        $metricsStmt->execute([
                            $data['site_code'],
                            $metricDate,
                            strtoupper($status),
                            $bwUtil,
                            $users
                        ]);
                        $results['metrics_imported']++;
                    } catch (Exception $e) {
                        // Skip duplicate or invalid metrics
                    }
                }
            }
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $results['success'] = false;
        $results['error'] = $e->getMessage();
    }
    
    return $results;
}

function processRealEgovPHCSV($csvData, $headers) {
    $results = ['success' => true, 'imported' => 0, 'errors' => [], 'warnings' => []];
    
    $db = getDB();
    $db->beginTransaction();
    
    try {
        $insertStmt = $db->prepare("INSERT INTO activities 
            (project_type, activity_id, activity_title, activity_type, activity_date, 
             participants, downloads, province, municipality, district, status, facilitator, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
        
        $columnMap = [
            'date' => 'activity_date',
            'title of activity' => 'activity_title',
            'type of activity' => 'activity_type',
            '#participants' => 'participants',
            '#downloads' => 'downloads',
            'province/' => 'province',
            'province' => 'province',
            'municipality/city' => 'municipality',
            'municipality' => 'municipality',
            'district' => 'district',
            'status' => 'status',
            'facilitator' => 'facilitator',
        ];
        
        $rowNum = 0;
        foreach ($csvData as $row) {
            $rowNum++;
            
            $data = [];
            foreach ($row as $index => $value) {
                if (!isset($normalizedHeaders[$index])) continue;
                $header = $normalizedHeaders[$index];
                
                foreach ($columnMap as $mapKey => $mapField) {
                    if (strpos($header, $mapKey) !== false) {
                        $data[$mapField] = trim($value);
                        break;
                    }
                }
            }
            
            if (empty($data['activity_title'])) {
                continue;
            }
            
            $activityId = 'EGV-' . str_pad($rowNum, 4, '0', STR_PAD_LEFT);
            
            $insertStmt->execute([
                'EgovPH',
                $activityId,
                $data['activity_title'] ?? '',
                $data['activity_type'] ?? 'Orientation',
                parseDate($data['activity_date'] ?? ''),
                intval($data['participants'] ?? 0),
                intval($data['downloads'] ?? 0),
                $data['province'] ?? '',
                $data['municipality'] ?? '',
                $data['district'] ?? '',
                $data['status'] ?? 'Done',
                $data['facilitator'] ?? '',
                $_SESSION['user_id'] ?? 1
            ]);
            
            $results['imported']++;
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $results['success'] = false;
        $results['error'] = $e->getMessage();
    }
    
    return $results;
}

function generateTemplate($projectType) {
    $template = getImportTemplate($projectType);
    if (!$template) {
        return null;
    }
    
    $headers = json_decode($template['csv_headers'], true);
    $sampleData = json_decode($template['sample_data'], true);
    
    $output = fopen('php://temp', 'r+');
    fputcsv($output, $headers);
    
    $row = [];
    foreach ($headers as $header) {
        $row[] = $sampleData[$header] ?? '';
    }
    fputcsv($output, $row);
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-project-types':
        $types = [];
        foreach ($config['project_types'] as $typeName => $typeConfig) {
            $types[] = [
                'name' => $typeName,
                'description' => $typeConfig['description'],
                'icon' => $typeConfig['icon'],
                'color' => $typeConfig['color']
            ];
        }
        echo json_encode(['success' => true, 'data' => $types]);
        break;
        
    case 'get-template':
        $projectType = $_GET['project_type'] ?? '';
        $template = getImportTemplate($projectType);
        
        if ($template) {
            echo json_encode([
                'success' => true, 
                'data' => [
                    'project_type' => $template['project_type'],
                    'headers' => json_decode($template['csv_headers'], true),
                    'field_mapping' => json_decode($template['field_mapping'], true),
                    'sample_data' => json_decode($template['sample_data'], true)
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Template not found']);
        }
        break;
        
    case 'download-template':
        $projectType = $_GET['project_type'] ?? '';
        $csv = generateTemplate($projectType);
        
        if ($csv) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $projectType . '_template.csv"');
            echo $csv;
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Template not found']);
        }
        break;
        
    case 'import':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
        }
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
            break;
        }
        
        $projectType = $_POST['project_type'] ?? '';
        $importFormat = $_POST['import_format'] ?? 'template';
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            echo json_encode(['success' => false, 'error' => 'Could not read file']);
            break;
        }
        
        $headers = fgetcsv($handle);
        if (!$headers) {
            echo json_encode(['success' => false, 'error' => 'Could not read CSV headers']);
            break;
        }
        
        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (array_filter($row)) {
                $data[] = $row;
            }
        }
        fclose($handle);
        
        $typeConfig = getProjectTypeConfig($projectType);
        
        if ($projectType === 'EgovPH') {
            if ($importFormat === 'real') {
                $results = processRealEgovPHCSV($data, $headers);
            } else {
                $results = processEgovPHImport($data, $headers, $typeConfig);
            }
        } elseif ($projectType === 'ELGU') {
            $results = processELGUImport($data, $headers, $typeConfig);
        } elseif ($projectType === 'Free-WIFI for All') {
            if ($importFormat === 'real') {
                $results = processRealFreeWiFiCSV($data, $headers);
            } else {
                $results = processFreeWiFiImport($data, $headers, $typeConfig);
            }
        } elseif ($projectType === 'Free-WiFi Daily Metrics') {
            $results = processDailyMetricsImport($data, $headers);
        } else {
            $results = processFreeWiFiImport($data, $headers, $typeConfig);
        }
        
        echo json_encode($results);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
