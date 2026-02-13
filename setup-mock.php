<?php
/**
 * Complete Database Setup with Mock Data
 */

echo "Setting up Project Tracking System with Mock Data...\n\n";

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';

try {
    // Connect without database selected
    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate database
    echo "1. Creating database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS project_tracking");
    $pdo->exec("CREATE DATABASE project_tracking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE project_tracking");
    echo "   ✓ Database created\n\n";
    
    // Create tables with project_type column included
    echo "2. Creating tables...\n";
    
    $pdo->exec("CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE projects (
        id INT PRIMARY KEY AUTO_INCREMENT,
        site_code VARCHAR(50) UNIQUE NOT NULL,
        project_name VARCHAR(255) NOT NULL,
        project_type VARCHAR(100) DEFAULT 'Free-WIFI for All',
        site_name VARCHAR(255) NOT NULL,
        barangay VARCHAR(100) NOT NULL,
        municipality VARCHAR(100) NOT NULL,
        province VARCHAR(100) NOT NULL,
        district VARCHAR(100) NOT NULL,
        latitude DECIMAL(10, 6) NOT NULL,
        longitude DECIMAL(10, 6) NOT NULL,
        activation_date DATE NOT NULL,
        status ENUM('Done', 'Pending') NOT NULL,
        notes TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_site_code (site_code),
        INDEX idx_province (province),
        INDEX idx_status (status),
        INDEX idx_activation_date (activation_date),
        INDEX idx_project_type (project_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE validation_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        import_batch_id VARCHAR(50),
        `row_number` INT,
        field_name VARCHAR(50),
        error_message TEXT,
        original_value TEXT,
        corrected_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_batch_id (import_batch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "   ✓ Tables created\n\n";
    
    // Insert admin user
    echo "3. Creating admin user...\n";
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@example.com', $passwordHash, 'admin']);
    echo "   ✓ Admin user created\n\n";
    
    // Insert mock data
    echo "4. Inserting mock data with different project types...\n";
    
    $projectTypes = [
        'Free-WIFI for All',
        'Tech4ED',
        'National Broadband Program',
        'Digital Cities Program',
        'PIPOL',
        'Rural Impact Sourcing',
        'e-Government Services',
        'ICT Literacy Programs',
        'Cybersecurity Awareness',
        'Free Internet in Public Places'
    ];
    
    $provinces = [
        ['name' => 'Cagayan', 'districts' => ['District I', 'District II', 'District III']],
        ['name' => 'Isabela', 'districts' => ['District I', 'District II', 'District III', 'District IV']],
        ['name' => 'Nueva Vizcaya', 'districts' => ['District I']],
        ['name' => 'Quirino', 'districts' => ['District I']],
        ['name' => 'Batanes', 'districts' => ['District I']]
    ];
    
    $municipalities = [
        'Tuguegarao', 'Cauayan', 'Ilagan', 'Santiago', 'Solana', 
        'Aparri', 'Bayombong', 'Bambang', 'Basco', 'Itbayat'
    ];
    
    $barangays = [
        'Centro', 'San Vicente', 'Poblacion', 'District I', 'District II',
        'San Gabriel', 'Larion Alto', 'Ugac Sur', 'Banga', 'Mabanguc'
    ];
    
    $siteNames = [
        'Barangay Hall - AP 1', 'Municipal Library', 'Community Center', 
        'Health Center', 'School Campus', 'Public Market', 'Transport Terminal',
        'Municipal Plaza', 'Tech Center', 'Digital Hub'
    ];
    
    $stmt = $pdo->prepare("INSERT INTO projects 
        (site_code, project_name, project_type, site_name, barangay, municipality, province, district, latitude, longitude, activation_date, status, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $counter = 0;
    $id = 1;
    
    foreach ($projectTypes as $typeIndex => $projectType) {
        // Create 5-8 projects per type
        $numProjects = rand(5, 8);
        
        for ($i = 0; $i < $numProjects; $i++) {
            $province = $provinces[array_rand($provinces)];
            $district = $province['districts'][array_rand($province['districts'])];
            $municipality = $municipalities[array_rand($municipalities)];
            $barangay = $barangays[array_rand($barangays)];
            $siteName = $siteNames[array_rand($siteNames)];
            
            $siteCode = sprintf('PRJ-%s-%04d', strtoupper(substr(str_replace(' ', '', $projectType), 0, 3)), $id);
            $projectName = $projectType;
            $latitude = rand(16000, 18000) / 1000; // 16.000 to 18.000
            $longitude = rand(121000, 122000) / 1000; // 121.000 to 122.000
            $year = rand(2023, 2024);
            $month = rand(1, 12);
            $day = rand(1, 28);
            $activationDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $status = rand(1, 100) > 30 ? 'Done' : 'Pending';
            
            $notesList = [
                'Done' => ['Installation complete', 'Fully operational', 'Active and monitoring', 'Successfully activated', 'System tested'],
                'Pending' => ['Equipment delivery pending', 'Awaiting approval', 'Site survey scheduled', 'Power supply upgrade needed', 'Installation in progress']
            ];
            $notes = $notesList[$status][array_rand($notesList[$status])];
            
            $stmt->execute([
                $siteCode,
                $projectName,
                $projectType,
                $siteName,
                $barangay,
                $municipality,
                $province['name'],
                $district,
                $latitude,
                $longitude,
                $activationDate,
                $status,
                $notes
            ]);
            
            $counter++;
            $id++;
        }
    }
    
    echo "   ✓ Inserted $counter projects\n\n";
    
    // Show summary
    echo "5. Project Summary:\n";
    echo str_repeat('-', 75) . "\n";
    printf("%-45s %8s %8s %8s\n", 'Project Type', 'Total', 'Done', 'Pending');
    echo str_repeat('-', 75) . "\n";
    
    $stmt = $pdo->query("SELECT project_type, COUNT(*) as count, 
        SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as done,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
        FROM projects GROUP BY project_type ORDER BY count DESC");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        printf("%-45s %8d %8d %8d\n", $row['project_type'], $row['count'], $row['done'], $row['pending']);
    }
    
    echo str_repeat('-', 75) . "\n";
    $total = array_sum(array_column($results, 'count'));
    $done = array_sum(array_column($results, 'done'));
    $pending = array_sum(array_column($results, 'pending'));
    printf("%-45s %8d %8d %8d\n", 'TOTAL', $total, $done, $pending);
    
    echo "\n========================================\n";
    echo "✓ Setup completed successfully!\n";
    echo "========================================\n\n";
    echo "Login credentials:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n\n";
    echo "Access the application at:\n";
    echo "  http://localhost/Projects/ptsUltra/ptsv5ultra/\n";
    
} catch (PDOException $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n\n";
    echo "Common fixes:\n";
    echo "1. Make sure MySQL is running in XAMPP/WAMP Control Panel\n";
    echo "2. Check your MySQL username/password\n";
    exit(1);
}
