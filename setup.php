<?php
/**
 * Database Setup Script
 * Creates database and imports schema automatically
 */

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';  // Change if you have a password

echo "Setting up Project Tracking System Database...\n\n";

try {
    // Connect without database selected
    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    echo "1. Creating database 'project_tracking'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS project_tracking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Database created\n\n";
    
    // Select database
    $pdo->exec("USE project_tracking");
    
    // Read and execute schema
    echo "2. Importing database schema...\n";
    $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
    
    // Split by semicolons to execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--|^\/\*/', $statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }
    echo "   ✓ Schema imported\n\n";
    
    // Verify admin user exists with correct password
    echo "3. Setting up admin user...\n";
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
        $stmt->execute([$passwordHash]);
        echo "   ✓ Admin user updated\n";
    } else {
        // Create new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $passwordHash, 'admin']);
        echo "   ✓ Admin user created\n";
    }
    
    echo "\n========================================\n";
    echo "✓ Setup completed successfully!\n";
    echo "========================================\n\n";
    echo "You can now login with:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    echo "Access the application at:\n";
    echo "  http://localhost/projects/newPTS/\n";
    
} catch (PDOException $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n\n";
    echo "Common fixes:\n";
    echo "1. Make sure MySQL is running in XAMPP Control Panel\n";
    echo "2. Check your MySQL username/password in this file\n";
    echo "3. Verify database server is accessible\n";
    exit(1);
}
