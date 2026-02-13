<?php
/**
 * Database Configuration
 * Update these settings for your environment
 */

define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'project_tracking');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return PDO
 */
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $db = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $db;
}

/**
 * Close database connection
 * Note: PDO connections are automatically closed when the script ends.
 * This function is kept for backward compatibility but is effectively a no-op.
 * The static $db in getDB() cannot be unset from this function context.
 */
function closeDB() {
    // Connection will be closed automatically by PHP at script end
    // This is by design in modern PHP applications using PDO
}
