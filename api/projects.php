<?php
/**
 * Projects API
 * Handles CRUD operations for projects
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Require authentication for all operations
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            // Get project(s)
            if (isset($_GET['id'])) {
                // Get single project
                $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $project = $stmt->fetch();
                
                if ($project) {
                    $project['activation_date_formatted'] = formatDate($project['activation_date']);
                    sendJsonResponse(true, $project);
                } else {
                    sendJsonResponse(false, null, 'Project not found', []);
                }
            } elseif (isset($_GET['action']) && $_GET['action'] === 'stats') {
                // Get statistics
                $stats = [
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0,
                    'provinces' => 0
                ];
                
                // Total count
                $result = $db->query("SELECT COUNT(*) as count FROM projects");
                $stats['total'] = (int)$result->fetch()['count'];
                
                // Completed count
                $result = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'Done'");
                $stats['completed'] = (int)$result->fetch()['count'];
                
                // Pending count
                $result = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'Pending'");
                $stats['pending'] = (int)$result->fetch()['count'];
                
                // Province count
                $result = $db->query("SELECT COUNT(DISTINCT province) as count FROM projects");
                $stats['provinces'] = (int)$result->fetch()['count'];
                
                sendJsonResponse(true, $stats);
            } else {
                // Get all projects with filters
                $where = [];
                $params = [];
                
                if (!empty($_GET['status']) && $_GET['status'] !== 'all') {
                    $where[] = "status = ?";
                    $params[] = $_GET['status'];
                }
                
                if (!empty($_GET['province']) && $_GET['province'] !== 'all') {
                    $where[] = "province = ?";
                    $params[] = $_GET['province'];
                }
                
                if (!empty($_GET['search'])) {
                    $where[] = "(site_code LIKE ? OR project_name LIKE ? OR site_name LIKE ? OR barangay LIKE ? OR municipality LIKE ?)";
                    $search = "%{$_GET['search']}%";
                    $params = array_merge($params, [$search, $search, $search, $search, $search]);
                }
                
                if (!empty($_GET['date_from'])) {
                    $where[] = "activation_date >= ?";
                    $params[] = $_GET['date_from'];
                }
                
                if (!empty($_GET['date_to'])) {
                    $where[] = "activation_date <= ?";
                    $params[] = $_GET['date_to'];
                }
                
                $sql = "SELECT * FROM projects";
                if (!empty($where)) {
                    $sql .= " WHERE " . implode(" AND ", $where);
                }
                $sql .= " ORDER BY created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $projects = $stmt->fetchAll();
                
                // Format dates and coordinates
                foreach ($projects as &$project) {
                    $project['activation_date_formatted'] = formatDate($project['activation_date']);
                    // Ensure coordinates are formatted with 6 decimal places
                    $project['latitude'] = number_format((float)$project['latitude'], 6, '.', '');
                    $project['longitude'] = number_format((float)$project['longitude'], 6, '.', '');
                }
                
                sendJsonResponse(true, $projects);
            }
            break;
            
        case 'POST':
            // Create project
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                sendJsonResponse(false, null, 'Invalid request data', []);
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
                sendJsonResponse(false, null, 'Missing required fields: ' . implode(', ', $missing), []);
            }
            
            // Validate coordinates
            $coordErrors = validateCoordinates($data['latitude'], $data['longitude']);
            if (!empty($coordErrors)) {
                sendJsonResponse(false, null, 'Validation failed', $coordErrors);
            }
            
            // Check for duplicate site code
            $stmt = $db->prepare("SELECT id FROM projects WHERE site_code = ?");
            $stmt->execute([$data['site_code']]);
            if ($stmt->fetch()) {
                sendJsonResponse(false, null, 'Site code already exists', []);
            }
            
            // Sanitize and prepare data
            $siteCode = sanitize($data['site_code']);
            $projectName = sanitize($data['project_name']);
            $siteName = sanitize($data['site_name']);
            $barangay = standardizeLocation($data['barangay']);
            $municipality = standardizeLocation($data['municipality']);
            $province = standardizeLocation($data['province']);
            $district = standardizeLocation($data['district']);
            $latitude = (float)$data['latitude'];
            $longitude = (float)$data['longitude'];
            $activationDate = $data['activation_date'];
            $status = normalizeStatus($data['status']);
            $notes = !empty($data['notes']) ? sanitize($data['notes']) : '';
            
            // Insert project
            $stmt = $db->prepare("INSERT INTO projects (site_code, project_name, site_name, barangay, municipality, province, district, latitude, longitude, activation_date, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$siteCode, $projectName, $siteName, $barangay, $municipality, $province, $district, $latitude, $longitude, $activationDate, $status, $notes, $_SESSION['user_id']]);
            
            $projectId = $db->lastInsertId();
            
            // Log activity
            logActivity('create', 'project', $projectId, null, [
                'site_code' => $siteCode,
                'project_name' => $projectName,
                'site_name' => $siteName,
                'province' => $province,
                'status' => $status
            ]);
            
            sendJsonResponse(true, ['id' => $projectId], 'Project created successfully');
            break;
            
        case 'PUT':
            // Update project
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || empty($data['id'])) {
                sendJsonResponse(false, null, 'Project ID required', []);
            }
            
            $id = (int)$data['id'];
            
            // Check if project exists
            $stmt = $db->prepare("SELECT id FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                sendJsonResponse(false, null, 'Project not found', []);
            }
            
            // Validate coordinates if provided
            if (isset($data['latitude']) && isset($data['longitude'])) {
                $coordErrors = validateCoordinates($data['latitude'], $data['longitude']);
                if (!empty($coordErrors)) {
                    sendJsonResponse(false, null, 'Validation failed', $coordErrors);
                }
            }
            
            // Check for duplicate site code if changed
            if (!empty($data['site_code'])) {
                $stmt = $db->prepare("SELECT id FROM projects WHERE site_code = ? AND id != ?");
                $stmt->execute([$data['site_code'], $id]);
                if ($stmt->fetch()) {
                    sendJsonResponse(false, null, 'Site code already exists', []);
                }
            }
            
            // Build update query
            $fields = [];
            $params = [];
            
            $updatableFields = ['site_code', 'project_name', 'site_name', 'barangay', 'municipality', 'province', 'district', 'latitude', 'longitude', 'activation_date', 'status', 'notes'];
            
            foreach ($updatableFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    
                    if (in_array($field, ['barangay', 'municipality', 'province', 'district'])) {
                        $params[] = standardizeLocation($data[$field]);
                    } elseif ($field === 'status') {
                        $params[] = normalizeStatus($data[$field]);
                    } elseif (in_array($field, ['latitude', 'longitude'])) {
                        $params[] = (float)$data[$field];
                    } elseif ($field === 'notes') {
                        $params[] = sanitize($data[$field]);
                    } else {
                        $params[] = sanitize($data[$field]);
                    }
                }
            }
            
            if (empty($fields)) {
                sendJsonResponse(false, null, 'No fields to update', []);
            }
            
            // Get old values before update
            $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $oldProject = $stmt->fetch();
            
            $params[] = $id;
            $sql = "UPDATE projects SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Log activity
            logActivity('update', 'project', $id, $oldProject, $data);
            
            sendJsonResponse(true, null, 'Project updated successfully');
            break;
            
        case 'DELETE':
            // Delete project
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$id) {
                sendJsonResponse(false, null, 'Project ID required', []);
            }
            
            // Get project data before delete
            $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch();
            
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                // Log activity
                logActivity('delete', 'project', $id, $project, null);
                
                sendJsonResponse(true, null, 'Project deleted successfully');
            } else {
                sendJsonResponse(false, null, 'Project not found', []);
            }
            break;
            
        default:
            sendJsonResponse(false, null, 'Method not allowed', []);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error occurred', []);
}
