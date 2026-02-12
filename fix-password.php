<?php
/**
 * Fix Admin Password
 * Run this file to reset admin password to 'admin123'
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // Generate correct hash for admin123
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update admin user
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$passwordHash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Admin password updated successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    } else {
        // If no rows updated, user might not exist, create it
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $passwordHash, 'admin']);
        echo "✓ Admin user created successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nMake sure you have:\n";
    echo "1. Created the database 'project_tracking'\n";
    echo "2. Imported sql/schema.sql\n";
    echo "3. Configured database credentials in config/database.php\n";
}
