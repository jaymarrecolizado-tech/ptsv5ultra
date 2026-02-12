<?php
/**
 * Admin API - User Management
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

requireAuth();

// Check if user is admin
if (!isAdmin()) {
    sendJsonResponse(false, null, 'Access denied. Admin only.', []);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            $action = isset($_GET['action']) ? $_GET['action'] : 'list';
            
            switch ($action) {
                case 'list':
                    $stmt = $db->query("SELECT u.id, u.username, u.email, u.role, u.created_at, u.updated_at, p.first_name, p.last_name, p.department FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id ORDER BY u.created_at DESC");
                    $users = $stmt->fetchAll();
                    sendJsonResponse(true, ['users' => $users]);
                    break;
                    
                case 'stats':
                    $stats = [
                        'total_users' => 0,
                        'active_today' => 0,
                        'by_role' => []
                    ];
                    
                    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
                    $stats['total_users'] = (int)$stmt->fetch()['count'];
                    
                    $stmt = $db->query("SELECT COUNT(*) as count FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                    $stats['active_today'] = (int)$stmt->fetch()['count'];
                    
                    $stmt = $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
                    $stats['by_role'] = $stmt->fetchAll();
                    
                    sendJsonResponse(true, ['stats' => $stats]);
                    break;
                    
                default:
                    sendJsonResponse(false, null, 'Unknown action');
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['action'])) {
                sendJsonResponse(false, null, 'Action required');
            }
            
            switch ($data['action']) {
                case 'create_user':
                    $username = sanitize($data['username']);
                    $email = sanitize($data['email']);
                    $password = $data['password'];
                    $role = in_array($data['role'], ['admin', 'user']) ? $data['role'] : 'user';
                    
                    if (empty($username) || empty($email) || empty($password)) {
                        sendJsonResponse(false, null, 'All fields required');
                    }
                    
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $passwordHash, $role]);
                    
                    $userId = $db->lastInsertId();
                    
                    // Create empty profile
                    $stmt = $db->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
                    $stmt->execute([$userId]);
                    
                    logActivity('create_user', 'user', $userId, null, ['username' => $username, 'role' => $role]);
                    
                    sendJsonResponse(true, ['id' => $userId], 'User created successfully');
                    break;
                    
                case 'update_user':
                    $userId = (int)$data['user_id'];
                    $updates = [];
                    $params = [];
                    
                    if (!empty($data['email'])) {
                        $updates[] = "email = ?";
                        $params[] = sanitize($data['email']);
                    }
                    
                    if (!empty($data['role'])) {
                        $updates[] = "role = ?";
                        $params[] = $data['role'];
                    }
                    
                    if (!empty($data['password'])) {
                        $updates[] = "password_hash = ?";
                        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
                    }
                    
                    if (empty($updates)) {
                        sendJsonResponse(false, null, 'No fields to update');
                    }
                    
                    $params[] = $userId;
                    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    
                    logActivity('update_user', 'user', $userId);
                    
                    sendJsonResponse(true, null, 'User updated successfully');
                    break;
                    
                case 'delete_user':
                    $userId = (int)$data['user_id'];
                    
                    // Don't allow deleting yourself
                    if ($userId === $_SESSION['user_id']) {
                        sendJsonResponse(false, null, 'Cannot delete your own account');
                    }
                    
                    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    
                    logActivity('delete_user', 'user', $userId);
                    
                    sendJsonResponse(true, null, 'User deleted successfully');
                    break;
                    
                case 'reset_password':
                    $userId = (int)$data['user_id'];
                    $newPassword = bin2hex(random_bytes(4)); // Generate random 8-char password
                    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$passwordHash, $userId]);
                    
                    logActivity('reset_password', 'user', $userId);
                    
                    sendJsonResponse(true, ['password' => $newPassword], 'Password reset successfully');
                    break;
                    
                default:
                    sendJsonResponse(false, null, 'Unknown action');
            }
            break;
            
        default:
            sendJsonResponse(false, null, 'Method not allowed');
    }
    
} catch (Exception $e) {
    error_log("Admin API Error: " . $e->getMessage());
    sendJsonResponse(false, null, 'Server error');
}
