<?php
/**
 * Test Import Script - Tests CSV imports for EgovPH and Free-WiFi
 */

require_once __DIR__ . '/config/database.php';

echo "Testing CSV Imports...\n\n";

function parseDate($dateStr) {
    if (empty($dateStr)) return date('Y-m-d');
    $dateStr = trim($dateStr);
    
    // Handle date ranges like "January 7-9, 2026"
    if (preg_match('/([A-Za-z]+)\s+(\d+)-(\d+),?\s*(\d+)/', $dateStr, $matches)) {
        $month = $matches[1];
        $day = $matches[2];
        $year = $matches[4];
        $dateStr = "$month $day, $year";
    }
    
    // Handle "January 9,2026" format
    if (preg_match('/([A-Za-z]+)\s+(\d+),?\s*(\d+)/', $dateStr, $matches)) {
        $dateStr = $matches[1] . ' ' . $matches[2] . ', ' . $matches[3];
    }
    
    if (strtotime($dateStr)) {
        return date('Y-m-d', strtotime($dateStr));
    }
    return date('Y-m-d');
}

try {
    $db = getDB();
    
    // Test 1: Import EgovPH Activities
    echo "1. Testing EgovPH Activities Import...\n";
    $egovFile = __DIR__ . '/report eGovPH Activities.csv';
    
    if (file_exists($egovFile)) {
        $handle = fopen($egovFile, 'r');
        $headers = fgetcsv($handle);
        
        echo "   Headers found: " . count($headers) . " columns\n";
        echo "   First few headers: " . implode(', ', array_slice($headers, 0, 5)) . "\n";
        
        $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
        
        $insertStmt = $db->prepare("INSERT INTO activities 
            (project_type, activity_id, activity_title, activity_type, activity_date, 
             participants, downloads, province, municipality, district, status, facilitator, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $rowNum = 0;
        $imported = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            
            $data = [];
            foreach ($row as $index => $value) {
                if (!isset($normalizedHeaders[$index])) continue;
                $header = $normalizedHeaders[$index];
                
                if (strpos($header, 'date') !== false) $data['activity_date'] = trim($value);
                if (strpos($header, 'title') !== false) $data['activity_title'] = trim($value);
                if (strpos($header, 'type') !== false) $data['activity_type'] = trim($value);
                if (strpos($header, '#participants') !== false) $data['participants'] = intval($value);
                if (strpos($header, '#downloads') !== false) $data['downloads'] = intval($value);
                if (strpos($header, 'province') !== false) $data['province'] = trim($value);
                if (strpos($header, 'municipality') !== false) $data['municipality'] = trim($value);
                if (strpos($header, 'district') !== false && strpos($header, '#') === false) $data['district'] = trim($value);
                if (strpos($header, 'status') !== false) $data['status'] = trim($value);
                if (strpos($header, 'facilitator') !== false) $data['facilitator'] = trim($value);
            }
            
            if (empty($data['activity_title'])) continue;
            
            $activityId = 'EGV-R2-' . str_pad($rowNum, 4, '0', STR_PAD_LEFT);
            
            $insertStmt->execute([
                'EgovPH',
                $activityId,
                $data['activity_title'] ?? '',
                $data['activity_type'] ?? 'Orientation',
                parseDate($data['activity_date'] ?? ''),
                $data['participants'] ?? 0,
                $data['downloads'] ?? 0,
                $data['province'] ?? '',
                $data['municipality'] ?? '',
                $data['district'] ?? '',
                $data['status'] ?? 'Done',
                $data['facilitator'] ?? '',
                1
            ]);
            
            $imported++;
        }
        
        fclose($handle);
        echo "   ✓ Imported $imported EgovPH activities\n\n";
    } else {
        echo "   ✗ File not found: $egovFile\n\n";
    }
    
    // Test 2: Import Free-WiFi Sites
    echo "2. Testing Free-WiFi Sites Import...\n";
    $wifiFile = __DIR__ . '/report REGION II SITE STATUS 2026 - JANUARY.csv';
    
    if (file_exists($wifiFile)) {
        $handle = fopen($wifiFile, 'r');
        $headers = fgetcsv($handle);
        
        echo "   Headers found: " . count($headers) . " columns\n";
        echo "   First few headers: " . implode(', ', array_slice($headers, 0, 5)) . "\n";
        
        $normalizedHeaders = array_map('strtolower', array_map('trim', $headers));
        
        $projectStmt = $db->prepare("INSERT INTO projects 
            (site_code, project_name, project_type, site_name, barangay, municipality, province, district, 
             latitude, longitude, activation_date, status, custom_data, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            site_name = VALUES(site_name), status = VALUES(status)");
        
        $metricsStmt = $db->prepare("INSERT INTO daily_metrics 
            (site_code, metric_date, status, bandwidth_utilization, unique_users)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            status = VALUES(status), bandwidth_utilization = VALUES(bandwidth_utilization), unique_users = VALUES(unique_users)");
        
        $columnMap = [];
        foreach ($normalizedHeaders as $idx => $header) {
            if (strpos($header, 'ap site code') !== false) $columnMap['site_code'] = $idx;
            if (strpos($header, 'ap site name') !== false) $columnMap['site_name'] = $idx;
            if (strpos($header, 'location name') !== false) $columnMap['location_name'] = $idx;
            if (strpos($header, 'site type') !== false) $columnMap['site_type'] = $idx;
            if (strpos($header, 'barangay') !== false) $columnMap['barangay'] = $idx;
            if (strpos($header, 'locality') !== false) $columnMap['municipality'] = $idx;
            if ($header === 'province') $columnMap['province'] = $idx;
            if (strpos($header, 'latitude') !== false) $columnMap['latitude'] = $idx;
            if (strpos($header, 'longitude') !== false) $columnMap['longitude'] = $idx;
            if (strpos($header, 'date of activation') !== false) $columnMap['activation_date'] = $idx;
            if (strpos($header, 'bw download') !== false) $columnMap['bandwidth'] = $idx;
            if (strpos($header, 'last mile technology') !== false) $columnMap['technology'] = $idx;
        }
        
        $sitesImported = 0;
        $metricsImported = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            if (!isset($columnMap['site_code']) || !isset($row[$columnMap['site_code']])) continue;
            
            $siteCode = trim($row[$columnMap['site_code']]);
            if (empty($siteCode)) continue;
            
            $customData = [
                'location_name' => $row[$columnMap['location_name'] ?? $columnMap['site_name']] ?? '',
                'site_type' => $row[$columnMap['site_type'] ?? -1] ?? '',
                'bandwidth' => $row[$columnMap['bandwidth'] ?? -1] ?? '',
                'technology' => $row[$columnMap['technology'] ?? -1] ?? '',
            ];
            
            $projectStmt->execute([
                $siteCode,
                'Free-WIFI for All',
                'Free-WIFI for All',
                $row[$columnMap['site_name'] ?? $columnMap['location_name']] ?? '',
                $row[$columnMap['barangay'] ?? -1] ?? '',
                $row[$columnMap['municipality'] ?? -1] ?? '',
                $row[$columnMap['province'] ?? -1] ?? '',
                '',
                floatval($row[$columnMap['latitude'] ?? -1] ?? 0),
                floatval($row[$columnMap['longitude'] ?? -1] ?? 0),
                parseDate($row[$columnMap['activation_date'] ?? -1] ?? ''),
                'Active',
                json_encode($customData),
                1
            ]);
            
            $sitesImported++;
            
            // Import daily metrics from date columns
            foreach ($headers as $idx => $header) {
                if (preg_match('/^(\d+)-([A-Za-z]+)-(\d+)$/', trim($header), $matches)) {
                    $day = $matches[1];
                    $monthName = strtolower($matches[2]);
                    $year = $matches[3];
                    
                    $monthMap = ['jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04', 'may' => '05', 'jun' => '06',
                                 'jul' => '07', 'aug' => '08', 'sep' => '09', 'oct' => '10', 'nov' => '11', 'dec' => '12'];
                    $month = $monthMap[$monthName] ?? '01';
                    $metricDate = "$year-$month-$day";
                    
                    $status = trim($row[$idx] ?? '');
                    if (empty($status)) continue;
                    
                    $bwUtil = 0;
                    $users = 0;
                    
                    // Next column might be users
                    if (isset($row[$idx + 1])) {
                        $users = intval($row[$idx + 1]);
                    }
                    
                    if (in_array(strtoupper($status), ['UP', 'DOWN', 'NO NMS'])) {
                        $metricStatus = strtoupper($status);
                    } else {
                        $bwUtil = floatval($status);
                        $metricStatus = 'UP';
                    }
                    
                    try {
                        $metricsStmt->execute([$siteCode, $metricDate, $metricStatus, $bwUtil, $users]);
                        $metricsImported++;
                    } catch (Exception $e) {
                        // Skip duplicates
                    }
                }
            }
        }
        
        fclose($handle);
        echo "   ✓ Imported $sitesImported Free-WiFi sites\n";
        echo "   ✓ Imported $metricsImported daily metrics records\n\n";
    } else {
        echo "   ✗ File not found: $wifiFile\n\n";
    }
    
    // Summary
    echo "========================================\n";
    echo "✓ Import test completed!\n";
    echo "========================================\n\n";
    
    // Verify data
    $projectCount = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $activityCount = $db->query("SELECT COUNT(*) FROM activities")->fetchColumn();
    $metricsCount = $db->query("SELECT COUNT(*) FROM daily_metrics")->fetchColumn();
    
    echo "Database Summary:\n";
    echo "  - Projects: $projectCount\n";
    echo "  - Activities: $activityCount\n";
    echo "  - Daily Metrics: $metricsCount\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
