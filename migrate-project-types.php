<?php
/**
 * Migration: Add Project Type Custom Fields Support
 * Run this to update the database schema
 */

require_once __DIR__ . '/config/database.php';

echo "Migrating database to support project type custom fields...\n\n";

try {
    $db = getDB();
    
    // 1. Add custom_data JSON column to projects table
    echo "1. Adding custom_data column to projects table...\n";
    try {
        $db->exec("ALTER TABLE projects ADD COLUMN custom_data JSON NULL AFTER project_type");
        echo "   ✓ custom_data column added\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ✓ custom_data column already exists\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Create project_type_fields table
    echo "\n2. Creating project_type_fields table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS project_type_fields (
        id INT PRIMARY KEY AUTO_INCREMENT,
        project_type VARCHAR(100) NOT NULL,
        field_name VARCHAR(100) NOT NULL,
        field_label VARCHAR(255) NOT NULL,
        field_type VARCHAR(50) NOT NULL,
        is_required BOOLEAN DEFAULT FALSE,
        validation_rules JSON NULL,
        field_options JSON NULL,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_field_per_type (project_type, field_name),
        INDEX idx_project_type (project_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "   ✓ project_type_fields table created\n";
    
    // 3. Create import_templates table
    echo "\n3. Creating import_templates table...\n";
    $db->exec("CREATE TABLE IF NOT EXISTS import_templates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        project_type VARCHAR(100) NOT NULL,
        template_name VARCHAR(255) NOT NULL,
        template_description TEXT,
        csv_headers JSON NOT NULL,
        field_mapping JSON NOT NULL,
        sample_data JSON NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        UNIQUE KEY unique_template_name (template_name),
        INDEX idx_project_type (project_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "   ✓ import_templates table created\n";
    
    // 4. Populate project_type_fields from configuration
    echo "\n4. Populating project type fields...\n";
    $config = require __DIR__ . '/config/project_types.php';
    
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
    
    $sortOrder = 0;
    $totalFields = 0;
    
    foreach ($config['project_types'] as $projectType => $typeConfig) {
        foreach ($typeConfig['fields'] as $fieldName => $fieldConfig) {
            // Skip common fields that are already in the main table
            if (in_array($fieldName, $config['common_fields'])) {
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
        $sortOrder = 0; // Reset for each project type
    }
    
    echo "   ✓ Inserted/updated $totalFields custom fields\n";
    
    // 5. Create import templates for each project type
    echo "\n5. Creating import templates...\n";
    
    $templateStmt = $db->prepare("INSERT INTO import_templates 
        (project_type, template_name, template_description, csv_headers, field_mapping, sample_data, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        template_description = VALUES(template_description),
        csv_headers = VALUES(csv_headers),
        field_mapping = VALUES(field_mapping),
        sample_data = VALUES(sample_data)");
    
    $adminId = 1; // Assuming admin user has ID 1
    $totalTemplates = 0;
    
    foreach ($config['project_types'] as $projectType => $typeConfig) {
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
                    if ($fieldName === 'site_code') {
                        $pattern = isset($fieldConfig['pattern']) ? $fieldConfig['pattern'] : '';
                        preg_match('/[A-Z]{2,4}/', $pattern, $matches);
                        $prefix = isset($matches[0]) ? $matches[0] : 'XXX';
                        $sampleData[$fieldName] = $prefix . '-0001';
                    } else {
                        $sampleData[$fieldName] = 'Sample ' . $fieldConfig['label'];
                    }
                    break;
                case 'number':
                    $min = isset($fieldConfig['min']) ? $fieldConfig['min'] : 1;
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
                case 'checkbox':
                    $sampleData[$fieldName] = 'Yes';
                    break;
                case 'multiselect':
                    $options = isset($fieldConfig['options']) ? $fieldConfig['options'] : ['Option 1'];
                    $sampleData[$fieldName] = implode(', ', array_slice($options, 0, 2));
                    break;
                case 'textarea':
                    $sampleData[$fieldName] = 'Sample notes for this project';
                    break;
                default:
                    $sampleData[$fieldName] = 'Sample Data';
            }
        }
        
        $templateStmt->execute([
            $projectType,
            $projectType . ' Import Template',
            'Standard import template for ' . $projectType . ' projects',
            json_encode($csvHeaders),
            json_encode($fieldMapping),
            json_encode($sampleData),
            $adminId
        ]);
        
        $totalTemplates++;
    }
    
    echo "   ✓ Created $totalTemplates import templates\n";
    
    // 6. Show summary
    echo "\n========================================\n";
    echo "✓ Migration completed successfully!\n";
    echo "========================================\n\n";
    
    echo "Summary:\n";
    echo "  - Added custom_data JSON column\n";
    echo "  - Created project_type_fields table ($totalFields fields)\n";
    echo "  - Created import_templates table ($totalTemplates templates)\n";
    echo "\nYou can now:\n";
    echo "  1. Import different CSV formats per project type\n";
    echo "  2. Store custom fields in custom_data JSON\n";
    echo "  3. Generate type-specific reports\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
