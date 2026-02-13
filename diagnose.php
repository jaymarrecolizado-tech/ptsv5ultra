<?php
/**
 * Login Diagnostic & Fix Tool
 * 
 * SECURITY: This file should be deleted or protected in production!
 * For now, we'll require authentication to access it.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Check authentication - only allow logged-in users
if (!isLoggedIn()) {
    die('Authentication required. Please <a href="index.php">login</a> first.');
}

// Check admin role - only admins can run diagnostics
if (!isAdmin()) {
    die('Access denied. Admin privileges required.');
}

// Only allow access in development environment
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
    die('This diagnostic tool is only accessible from localhost in development.');
}

$message = '';
$error = '';

try {
    $db = getDB();
    
    // Check if users table exists
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        $error .= "❌ Users table does not exist!<br>";
        
        // Create users table
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        $message .= "✅ Users table created<br>";
    } else {
        $message .= "✅ Users table exists<br>";
    }
    
    // Check if admin user exists
    $stmt = $db->query("SELECT * FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch();
    
    if (!$admin) {
        $error .= "❌ Admin user does not exist!<br>";
        
        // Create admin user
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $passwordHash, 'admin']);
        
        $message .= "✅ Admin user created with password: admin123<br>";
    } else {
        $message .= "✅ Admin user exists (ID: {$admin['id']})<br>";
        
        // Only reset password if explicitly requested via GET parameter with confirmation
        if (isset($_GET['reset_password']) && $_GET['reset_password'] === 'confirm') {
            $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
            $stmt->execute([$passwordHash]);
            $message .= "✅ Admin password reset to: admin123<br>";
        } elseif (isset($_GET['reset_password'])) {
            $message .= "⚠️ Use ?reset_password=confirm to confirm reset<br>";
        } else {
            $message .= "ℹ️ Password unchanged (add ?reset_password=confirm to reset)<br>";
        }
    }
    
    // Test password verification
    $stmt = $db->query("SELECT password_hash FROM users WHERE username = 'admin'");
    $user = $stmt->fetch();
    
    if (password_verify('admin123', $user['password_hash'])) {
        $message .= "✅ Password verification test: PASSED<br>";
    } else {
        $error .= "❌ Password verification test: FAILED<br>";
    }
    
    // Check session settings
    $message .= "<br><strong>Server Settings:</strong><br>";
    $message .= "PHP Version: " . phpversion() . "<br>";
    $message .= "Session save path: " . session_save_path() . "<br>";
    
    // Test session
    session_start();
    $_SESSION['test'] = 'working';
    if (isset($_SESSION['test'])) {
        $message .= "✅ Session test: WORKING<br>";
        session_destroy();
    } else {
        $error .= "❌ Session test: FAILED<br>";
    }
    
    // Check for projects table
    $stmt = $db->query("SHOW TABLES LIKE 'projects'");
    if ($stmt->rowCount() === 0) {
        $error .= "❌ Projects table does not exist! Please run: http://localhost/Projects/ptsUltra/ptsv5ultra/setup.php<br>";
    } else {
        $message .= "✅ Projects table exists<br>";
        
        // Count projects
        $stmt = $db->query("SELECT COUNT(*) as count FROM projects");
        $count = $stmt->fetch()['count'];
        $message .= "📊 Total projects: {$count}<br>";
    }
    
} catch (Exception $e) {
    $error .= "❌ Database Error: " . $e->getMessage() . "<br>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Diagnostic - Project Tracking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Login Diagnostic Tool</h1>
            
            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <h2 class="text-red-700 font-semibold mb-2">Issues Found:</h2>
                <div class="text-red-600 text-sm"><?php echo $error; ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <h2 class="text-green-700 font-semibold mb-2">Diagnostic Results:</h2>
                <div class="text-green-600 text-sm"><?php echo $message; ?></div>
            </div>
            <?php endif; ?>
            
            <div class="mt-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-blue-700 font-semibold mb-2">Try Login Again:</h3>
                    <p class="text-sm text-blue-600 mb-3">Use these credentials:</p>
                    <div class="bg-white rounded p-3 font-mono text-sm">
                        <p><strong>Username:</strong> admin</p>
                        <p><strong>Password:</strong> admin123</p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="/Projects/ptsUltra/ptsv5ultra/index.php" 
                       class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors text-center font-medium">
                        Go to Login Page
                    </a>
                    <a href="/Projects/ptsUltra/ptsv5ultra/setup.php" 
                       class="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition-colors text-center font-medium">
                        Run Full Setup
                    </a>
                </div>
            </div>
            
            <div class="mt-6 text-sm text-gray-500 border-t border-gray-200 pt-4">
                <p><strong>Troubleshooting Tips:</strong></p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Make sure MySQL is running in XAMPP Control Panel</li>
                    <li>Clear your browser cache and cookies</li>
                    <li>Try using a different browser</li>
                    <li>Check PHP error logs in XAMPP</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
