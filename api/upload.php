<?php
/**
 * File Upload API - Photos & Documents
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$uploadDir = __DIR__ . '/../uploads/';

// Create uploads directory if not exists
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
            $type = isset($_GET['type']) ? $_GET['type'] : 'all';
            
            if (!$projectId) {
                sendJsonResponse(false, null, 'Project ID required');
            }
            
            $files = [];
            
            if ($type === 'all' || $type === 'photos') {
                $stmt = $db->prepare("SELECT * FROM project_photos WHERE project_id = ? ORDER BY is_primary DESC, created_at DESC");
                $stmt->execute([$projectId]);
                $files['photos'] = $stmt->fetchAll();
            }
            
            if ($type === 'all' || $type === 'documents') {
                $stmt = $db->prepare("SELECT * FROM project_documents WHERE project_id = ? ORDER BY created_at DESC");
                $stmt->execute([$projectId]);
                $files['documents'] = $stmt->fetchAll();
            }
            
            sendJsonResponse(true, $files);
            break;
            
        case 'POST':
            $action = isset($_POST['action']) ? $_POST['action'] : 'upload';
            
            switch ($action) {
                case 'upload_photo':
                    if (empty($_FILES['file']) || empty($_POST['project_id'])) {
                        sendJsonResponse(false, null, 'File and project ID required');
                    }
                    
                    $projectId = (int)$_POST['project_id'];
                    $file = $_FILES['file'];
                    $isPrimary = isset($_POST['is_primary']) && $_POST['is_primary'] === '1';
                    $caption = sanitize($_POST['caption'] ?? '');
                    
                    // Validate file
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($file['type'], $allowedTypes)) {
                        sendJsonResponse(false, null, 'Invalid file type. Only JPG, PNG, GIF, WebP allowed');
                    }
                    
                    $maxSize = 5 * 1024 * 1024; // 5MB
                    if ($file['size'] > $maxSize) {
                        sendJsonResponse(false, null, 'File too large. Max 5MB');
                    }
                    
                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'photo_' . $projectId . '_' . uniqid() . '.' . $extension;
                    $filepath = $uploadDir . 'photos/' . $filename;
                    
                    // Create photos directory
                    if (!file_exists($uploadDir . 'photos/')) {
                        mkdir($uploadDir . 'photos/', 0755, true);
                    }
                    
                    // Move uploaded file
                    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                        sendJsonResponse(false, null, 'Failed to save file');
                    }
                    
                    // If setting as primary, unset other primary photos
                    if ($isPrimary) {
                        $stmt = $db->prepare("UPDATE project_photos SET is_primary = 0 WHERE project_id = ?");
                        $stmt->execute([$projectId]);
                    }
                    
                    // Save to database
                    $stmt = $db->prepare("INSERT INTO project_photos (project_id, filename, original_name, file_size, mime_type, caption, is_primary, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$projectId, $filename, $file['name'], $file['size'], $file['type'], $caption, $isPrimary, $_SESSION['user_id']]);
                    
                    $photoId = $db->lastInsertId();
                    
                    logActivity('upload_photo', 'project', $projectId, null, ['filename' => $filename]);
                    
                    sendJsonResponse(true, ['id' => $photoId, 'filename' => $filename], 'Photo uploaded successfully');
                    break;
                    
                case 'upload_document':
                    if (empty($_FILES['file']) || empty($_POST['project_id'])) {
                        sendJsonResponse(false, null, 'File and project ID required');
                    }
                    
                    $projectId = (int)$_POST['project_id'];
                    $file = $_FILES['file'];
                    $docType = in_array($_POST['document_type'] ?? '', ['contract', 'permit', 'report', 'other']) ? $_POST['document_type'] : 'other';
                    $description = sanitize($_POST['description'] ?? '');
                    
                    // Validate file
                    $allowedTypes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg',
                        'image/png'
                    ];
                    
                    if (!in_array($file['type'], $allowedTypes)) {
                        sendJsonResponse(false, null, 'Invalid file type. Only PDF, DOC, DOCX, XLS, XLSX, JPG, PNG allowed');
                    }
                    
                    $maxSize = 10 * 1024 * 1024; // 10MB
                    if ($file['size'] > $maxSize) {
                        sendJsonResponse(false, null, 'File too large. Max 10MB');
                    }
                    
                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'doc_' . $projectId . '_' . uniqid() . '.' . $extension;
                    $filepath = $uploadDir . 'documents/' . $filename;
                    
                    // Create documents directory
                    if (!file_exists($uploadDir . 'documents/')) {
                        mkdir($uploadDir . 'documents/', 0755, true);
                    }
                    
                    // Move uploaded file
                    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                        sendJsonResponse(false, null, 'Failed to save file');
                    }
                    
                    // Save to database
                    $stmt = $db->prepare("INSERT INTO project_documents (project_id, filename, original_name, file_size, mime_type, document_type, description, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$projectId, $filename, $file['name'], $file['size'], $file['type'], $docType, $description, $_SESSION['user_id']]);
                    
                    $docId = $db->lastInsertId();
                    
                    logActivity('upload_document', 'project', $projectId, null, ['filename' => $filename, 'type' => $docType]);
                    
                    sendJsonResponse(true, ['id' => $docId, 'filename' => $filename], 'Document uploaded successfully');
                    break;
                    
                default:
                    sendJsonResponse(false, null, 'Unknown action');
            }
            break;
            
        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $type = isset($_GET['type']) ? $_GET['type'] : null;
            
            if (!$id || !$type) {
                sendJsonResponse(false, null, 'ID and type required');
            }
            
            if ($type === 'photo') {
                // Get photo info
                $stmt = $db->prepare("SELECT * FROM project_photos WHERE id = ?");
                $stmt->execute([$id]);
                $photo = $stmt->fetch();
                
                if ($photo) {
                    // Delete file
                    $filepath = $uploadDir . 'photos/' . $photo['filename'];
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                    
                    // Delete from database
                    $stmt = $db->prepare("DELETE FROM project_photos WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    logActivity('delete_photo', 'project', $photo['project_id'], ['filename' => $photo['filename']], null);
                }
            } elseif ($type === 'document') {
                // Get document info
                $stmt = $db->prepare("SELECT * FROM project_documents WHERE id = ?");
                $stmt->execute([$id]);
                $doc = $stmt->fetch();
                
                if ($doc) {
                    // Delete file
                    $filepath = $uploadDir . 'documents/' . $doc['filename'];
                    if (file_exists($filepath)) {
                        unlink($filepath);
                    }
                    
                    // Delete from database
                    $stmt = $db->prepare("DELETE FROM project_documents WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    logActivity('delete_document', 'project', $doc['project_id'], ['filename' => $doc['filename']], null);
                }
            }
            
            sendJsonResponse(true, null, 'File deleted successfully');
            break;
            
        default:
            sendJsonResponse(false, null, 'Method not allowed');
    }
    
} catch (Exception $e) {
    error_log("Upload API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error');
}
