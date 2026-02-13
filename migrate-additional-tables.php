<?php
/**
 * Migration: Add daily_metrics and activities tables
 * Required for Free-WiFi daily tracking and EgovPH/ELGU activities
 */

require_once __DIR__ . '/config/database.php';

echo "Creating additional tables for project tracking...\n\n";

try {
    $db = getDB();
    
    // 1. Create daily_metrics table for Free-WiFi
    echo "1. Creating daily_metrics table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS daily_metrics (
        id INT PRIMARY KEY AUTO_INCREMENT,
        site_code VARCHAR(50) NOT NULL,
        metric_date DATE NOT NULL,
        status VARCHAR(20) DEFAULT 'UP',
        bandwidth_utilization DECIMAL(10, 4) NULL,
        unique_users INT DEFAULT 0,
        remarks TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_site_date (site_code, metric_date),
        INDEX idx_site_code (site_code),
        INDEX idx_metric_date (metric_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "   ✓ daily_metrics table created\n";
    
    // 2. Create activities table for EgovPH/ELGU
    echo "\n2. Creating activities table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS activities (
        id INT PRIMARY KEY AUTO_INCREMENT,
        project_id INT NULL,
        project_type VARCHAR(100) NOT NULL,
        activity_id VARCHAR(50) NOT NULL,
        activity_title VARCHAR(500) NOT NULL,
        activity_type VARCHAR(100) NOT NULL,
        activity_date DATE NOT NULL,
        participants INT DEFAULT 0,
        downloads INT DEFAULT 0,
        province VARCHAR(100) NULL,
        municipality VARCHAR(100) NULL,
        district VARCHAR(50) NULL,
        status VARCHAR(50) DEFAULT 'Done',
        facilitator VARCHAR(100) NULL,
        latitude DECIMAL(10, 7) NULL,
        longitude DECIMAL(10, 7) NULL,
        custom_data JSON NULL,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_activity_id (activity_id),
        INDEX idx_project_type (project_type),
        INDEX idx_activity_date (activity_date),
        INDEX idx_province (province)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "   ✓ activities table created\n";
    
    // 3. Update import_templates to include new project types
    echo "\n3. Updating import templates...\n";
    $config = require __DIR__ . '/config/project_types.php';
    
    $templateStmt = $db->prepare("INSERT INTO import_templates 
        (project_type, template_name, template_description, csv_headers, field_mapping, sample_data, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        template_description = VALUES(template_description),
        csv_headers = VALUES(csv_headers),
        field_mapping = VALUES(field_mapping),
        sample_data = VALUES(sample_data)");
    
    $adminId = 1;
    $newTemplates = 0;
    
    foreach ($config['project_types'] as $projectType => $typeConfig) {
        // Check if this project type already has a template
        $checkStmt = $db->prepare("SELECT id FROM import_templates WHERE project_type = ?");
        $checkStmt->execute([$projectType]);
        
        // Build CSV headers and field mapping
        $csvHeaders = [];
        $fieldMapping = [];
        $sampleData = [];
        
        foreach ($typeConfig['fields'] as $fieldName => $fieldConfig) {
            $csvHeaders[] = $fieldConfig['label'];
            $fieldMapping[$fieldConfig['label']] = $fieldName;
            
            // Generate sample data based on field type
            switch ($fieldConfig['type']) {
                case 'text':
                    if ($fieldName === 'site_code' || $fieldName === 'activity_id') {
                        $prefix = 'EGV';
                        if (strpos($projectType, 'ELGU') !== false) $prefix = 'ELG';
                        if (strpos($projectType, 'Free-WIFI') !== false || strpos($projectType, 'Free-WiFi') !== false) $prefix = 'SINAG';
                        $sampleData[$fieldName] = $prefix . '-R2-0001A';
                    } else {
                        $sampleData[$fieldName] = 'Sample ' . $fieldConfig['label'];
                    }
                    break;
                case 'number':
                    $min = isset($fieldConfig['min']) ? $fieldConfig['min'] : 0;
                    $max = isset($fieldConfig['max']) ? $fieldConfig['max'] : 100;
                    $sampleData[$fieldName] = rand($min, min($max, $min + 10));
                    break;
                case 'select':
                    $options = isset($fieldConfig['options']) ? $fieldConfig['options'] : ['Option 1'];
                    $sampleData[$fieldName] = $options[0];
                    break;
                case 'date':
                    $sampleData[$fieldName] = date('Y-m-d');
                    break;
                case 'multiselect':
                    $options = isset($fieldConfig['options']) ? $fieldConfig['options'] : ['Option 1'];
                    $sampleData[$fieldName] = implode(', ', array_slice($options, 0, 2));
                    break;
                default:
                    $sampleData[$fieldName] = 'Sample Data';
            }
        }
        
        $templateStmt->execute([
            $projectType,
            $projectType . ' Import Template',
            'Standard import template for ' . $projectType,
            json_encode($csvHeaders),
            json_encode($fieldMapping),
            json_encode($sampleData),
            $adminId
        ]);
        
        $newTemplates++;
    }
    
    echo "   ✓ Updated $newTemplates import templates\n";
    
    // 4. Update project_type_fields
    echo "\n4. Updating project type fields...\n";
    
    $stmt = $db->prepare("INSERT INTO project_type_fields 
        (project_type, field_name, field_label, field_type, is_required, validation_rules, field_options, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        field_label = VALUES(field_label),
        field_type = VALUES(field_type),
        is_required = VALUES(is_required),
        validation_rules = VALUES(validation_rules),
        field_options = VALUES(field_options),
        sort_order = VALUES(sort_order)");
    
    $totalFields = 0;
    
    foreach ($config['project_types'] as $projectType => $typeConfig) {
        $sortOrder = 0;
        foreach ($typeConfig['fields'] as $fieldName => $fieldConfig) {
            if (in_array($fieldName, $config['common_fields'] ?? ['site_code', 'project_name', 'site_name', 'barangay', 'municipality', 'province', 'district', 'latitude', 'longitude', 'activation_date', 'status', 'notes'])) {
                continue;
            }
            
            $validationRules = [];
            if (isset($fieldConfig['min'])) $validationRules['min'] = $fieldConfig['min'];
            if (isset($fieldConfig['max'])) $validationRules['max'] = $fieldConfig['max'];
            if (isset($fieldConfig['pattern'])) $validationRules['pattern'] = $fieldConfig['pattern'];
            
            $fieldOptions = [];
            if (isset($fieldConfig['options'])) $fieldOptions['options'] = $fieldConfig['options'];
            
            $stmt->execute([
                $projectType,
                $fieldName,
                $fieldConfig['label'],
                $fieldConfig['type'],
                $fieldConfig['required'] ? 1 : 0,
                json_encode($validationRules),
                json_encode($fieldOptions),
                $sortOrder++
            ]);
            
            $totalFields++;
        }
    }
    
    echo "   ✓ Updated $totalFields custom fields\n";
    
    echo "\n========================================\n";
    echo "✓ Additional tables created successfully!\n";
    echo "========================================\n\n";
    
    echo "Summary:\n";
    echo "  - Created daily_metrics table\n";
    echo "  - Created activities table\n";
    echo "  - Updated import templates\n";
    echo "  - Updated project type fields\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
