<?php
/**
 * Common utility functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Send JSON response
 */
function sendJsonResponse($success, $data = null, $message = '', $errors = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'errors' => $errors
    ]);
    exit;
}

/**
 * Sanitize input string
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate coordinates
 */
function validateCoordinates($latitude, $longitude) {
    $errors = [];
    
    if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
        $errors[] = 'Latitude must be between -90 and 90';
    }
    
    if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
        $errors[] = 'Longitude must be between -180 and 180';
    }
    
    return $errors;
}

/**
 * Format date for display
 */
function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    return date('F d, Y', $timestamp);
}

/**
 * Parse date from various formats
 */
function parseDate($dateString) {
    if (empty($dateString)) return null;
    
    $formats = [
        'Y-m-d',
        'm/d/Y',
        'd/m/Y',
        'M d, Y',
        'F d, Y',
        'Y/m/d',
    ];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, trim($dateString));
        if ($date && $date->format($format) === trim($dateString)) {
            return $date->format('Y-m-d');
        }
    }
    
    // Try strtotime as fallback
    $timestamp = strtotime($dateString);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    return null;
}

/**
 * Normalize status value
 */
function normalizeStatus($status) {
    $status = strtolower(trim($status));
    if (in_array($status, ['done', 'completed', 'finish', 'finished', 'complete'])) {
        return 'Done';
    }
    return 'Pending';
}

/**
 * Standardize location names
 */
function standardizeLocation($name) {
    $name = trim($name);
    // Capitalize first letter of each word
    return ucwords(strtolower($name));
}

/**
 * Generate CSV from data
 */
function generateCSV($data, $headers) {
    $output = fopen('php://temp', 'r+');
    
    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write headers
    fputcsv($output, $headers);
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    
    return $csv;
}

/**
 * Get provinces list for validation
 */
function getProvincesList() {
    return [
        'Abra', 'Agusan del Norte', 'Agusan del Sur', 'Aklan', 'Albay', 'Antique',
        'Apayao', 'Aurora', 'Basilan', 'Bataan', 'Batanes', 'Batangas', 'Benguet',
        'Biliran', 'Bohol', 'Bukidnon', 'Bulacan', 'Cagayan', 'Camarines Norte',
        'Camarines Sur', 'Camiguin', 'Capiz', 'Catanduanes', 'Cavite', 'Cebu',
        'Compostela Valley', 'Cotabato', 'Davao del Norte', 'Davao del Sur',
        'Davao Occidental', 'Davao Oriental', 'Dinagat Islands', 'Eastern Samar',
        'Guimaras', 'Ifugao', 'Ilocos Norte', 'Ilocos Sur', 'Iloilo', 'Isabela',
        'Kalinga', 'La Union', 'Laguna', 'Lanao del Norte', 'Lanao del Sur',
        'Leyte', 'Maguindanao', 'Marinduque', 'Masbate', 'Metro Manila',
        'Misamis Occidental', 'Misamis Oriental', 'Mountain Province',
        'Negros Occidental', 'Negros Oriental', 'Northern Samar', 'Nueva Ecija',
        'Nueva Vizcaya', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan',
        'Pampanga', 'Pangasinan', 'Quezon', 'Quirino', 'Rizal', 'Romblon',
        'Samar', 'Sarangani', 'Siquijor', 'Sorsogon', 'South Cotabato',
        'Southern Leyte', 'Sultan Kudarat', 'Sulu', 'Surigao del Norte',
        'Surigao del Sur', 'Tarlac', 'Tawi-Tawi', 'Zambales', 'Zamboanga del Norte',
        'Zamboanga del Sur', 'Zamboanga Sibugay'
    ];
}

/**
 * Find closest matching province
 */
function findClosestProvince($input) {
    $provinces = getProvincesList();
    $input = strtolower(trim($input));
    
    // Direct match
    foreach ($provinces as $province) {
        if (strtolower($province) === $input) {
            return $province;
        }
    }
    
    // Similarity match
    $bestMatch = null;
    $bestScore = 0;
    
    foreach ($provinces as $province) {
        similar_text(strtolower($province), $input, $percent);
        if ($percent > $bestScore && $percent > 70) {
            $bestScore = $percent;
            $bestMatch = $province;
        }
    }
    
    return $bestMatch;
}

/**
 * Log validation error
 */
function logValidationError($batchId, $rowNumber, $fieldName, $errorMessage, $originalValue = '', $correctedValue = '') {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO validation_logs (import_batch_id, row_number, field_name, error_message, original_value, corrected_value) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$batchId, $rowNumber, $fieldName, $errorMessage, $originalValue, $correctedValue]);
    } catch (Exception $e) {
        error_log("Failed to log validation error: " . $e->getMessage());
    }
}

/**
 * Log activity
 */
function logActivity($action, $entityType, $entityId = null, $oldValues = null, $newValues = null) {
    try {
        $db = getDB();
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            $userAgent
        ]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Get setting value
 */
function getSetting($key, $default = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Update setting
 */
function updateSetting($key, $value) {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to update setting: " . $e->getMessage());
        return false;
    }
}
