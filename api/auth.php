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
                    
                    if (login($data['username'], $data['password'])) {
                        sendJsonResponse(true, [
                            'user' => getCurrentUser(),
                            'csrf_token' => generateCsrfToken()
                        ], 'Login successful');
                    } else {
                        sendJsonResponse(false, null, 'Invalid credentials', []);
                    }
                    break;
                    
                case 'logout':
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
