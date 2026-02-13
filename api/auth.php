<?php
/**
 * Authentication API
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['action'])) {
                sendJsonResponse(false, null, 'Action required', []);
            }
            
            switch ($data['action']) {
                case 'login':
                    if (empty($data['username']) || empty($data['password'])) {
                        sendJsonResponse(false, null, 'Username and password required', []);
                    }
                    
                    // Rate limiting check
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $db = getDB();
                    
                    // Check if IP is locked
                    $stmt = $db->prepare("SELECT * FROM login_attempts WHERE ip_address = ? AND locked_until > NOW()");
                    $stmt->execute([$ipAddress]);
                    $locked = $stmt->fetch();
                    
                    if ($locked) {
                        $remaining = strtotime($locked['locked_until']) - time();
                        sendJsonResponse(false, null, "Too many failed attempts. Try again in " . ceil($remaining/60) . " minutes", []);
                    }
                    
                    // Check failed attempts for username
                    $stmt = $db->prepare("SELECT * FROM login_attempts WHERE username = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
                    $stmt->execute([$data['username']]);
                    $attempts = $stmt->fetch();
                    
                    if ($attempts && $attempts['attempts'] >= 5) {
                        // Lock the account for 15 minutes
                        $stmt = $db->prepare("UPDATE login_attempts SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE username = ?");
                        $stmt->execute([$data['username']]);
                        sendJsonResponse(false, null, "Too many failed attempts. Account locked for 15 minutes", []);
                    }
                    
                    if (login($data['username'], $data['password'])) {
                        // Clear failed attempts on successful login
                        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ? OR username = ?");
                        $stmt->execute([$ipAddress, $data['username']]);
                        
                        sendJsonResponse(true, [
                            'user' => getCurrentUser(),
                            'csrf_token' => generateCsrfToken()
                        ], 'Login successful');
                    } else {
                        // Record failed attempt
                        $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, username, attempts) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
                        $stmt->execute([$ipAddress, $data['username']]);
                        
                        sendJsonResponse(false, null, 'Invalid credentials', []);
                    }
                    break;
                    
                case 'logout':
                    // Require CSRF validation for logout
                    requireCsrfToken();
                    logout();
                    sendJsonResponse(true, null, 'Logout successful');
                    break;
                    
                default:
                    sendJsonResponse(false, null, 'Unknown action', []);
            }
            break;
            
        case 'GET':
            // Check authentication status
            if (isLoggedIn()) {
                sendJsonResponse(true, [
                    'authenticated' => true,
                    'user' => getCurrentUser()
                ]);
            } else {
                sendJsonResponse(true, [
                    'authenticated' => false
                ]);
            }
            break;
            
        default:
            sendJsonResponse(false, null, 'Method not allowed', []);
    }
    
} catch (Exception $e) {
    error_log("Auth API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error occurred', []);
}
