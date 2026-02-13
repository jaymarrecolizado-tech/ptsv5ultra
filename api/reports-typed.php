<?php
/**
 * API: Type-Specific Report Generator
 * Generates reports tailored to each project type
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Load project type configuration
$config = require __DIR__ . '/../config/project_types.php';

/**
 * Get report data for a specific project type
 */
function getTypeReport($projectType, $filters = []) {
    global $config;
    
    $typeConfig = $config['project_types'][$projectType] ?? null;
    if (!$typeConfig) {
        return ['success' => false, 'error' => 'Unknown project type'];
    }
    
    $db = getDB();
    
    // Base query
    $whereClause = "WHERE project_type = ?";
    $params = [$projectType];
    
    if (!empty($filters['province'])) {
        $whereClause .= " AND province = ?";
        $params[] = $filters['province'];
    }
    if (!empty($filters['status'])) {
        $whereClause .= " AND status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['date_from'])) {
        $whereClause .= " AND activation_date >= ?";
        $params[] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $whereClause .= " AND activation_date <= ?";
        $params[] = $filters['date_to'];
    }
    
    // Get all projects
    $stmt = $db->prepare("SELECT * FROM projects $whereClause ORDER BY created_at DESC");
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse custom_data JSON
    foreach ($projects as &$project) {
        $project['custom_data'] = json_decode($project['custom_data'], true) ?? [];
    }
    
    // Generate type-specific metrics
    $report = [
        'project_type' => $projectType,
        'summary' => generateSummary($projects, $typeConfig),
        'charts' => generateCharts($projects, $typeConfig),
        'details' => generateDetails($projects, $typeConfig),
        'projects' => $projects
    ];
    
    return ['success' => true, 'data' => $report];
}

/**
 * Generate summary statistics
 */
function generateSummary($projects, $typeConfig) {
    $summary = [
        'total_projects' => count($projects),
        'completed' => 0,
        'pending' => 0,
        'by_province' => [],
        'custom_metrics' => []
    ];
    
    foreach ($projects as $project) {
        // Status counts
        if ($project['status'] === 'Done') {
            $summary['completed']++;
        } else {
            $summary['pending']++;
        }
        
        // Province counts
        $province = $project['province'];
        if (!isset($summary['by_province'][$province])) {
            $summary['by_province'][$province] = ['count' => 0, 'completed' => 0, 'pending' => 0];
        }
        $summary['by_province'][$province]['count']++;
        if ($project['status'] === 'Done') {
            $summary['by_province'][$province]['completed']++;
        } else {
            $summary['by_province'][$province]['pending']++;
        }
    }
    
    // Calculate custom metrics based on project type
    $summary['custom_metrics'] = calculateCustomMetrics($projects, $typeConfig);
    
    return $summary;
}

/**
 * Calculate custom metrics for each project type
 */
function calculateCustomMetrics($projects, $typeConfig) {
    $metrics = [];
    
    switch ($typeConfig['icon']) {
        case 'wifi': // Free-WIFI for All
            $totalAPs = 0;
            $bandwidths = [];
            $providers = [];
            
            foreach ($projects as $project) {
                $totalAPs += intval($project['custom_data']['ap_count'] ?? 0);
                $bandwidth = intval($project['custom_data']['bandwidth'] ?? 0);
                if ($bandwidth > 0) $bandwidths[] = $bandwidth;
                
                $provider = $project['custom_data']['internet_provider'] ?? 'Unknown';
                if (!isset($providers[$provider])) $providers[$provider] = 0;
                $providers[$provider]++;
            }
            
            $metrics = [
                'total_access_points' => $totalAPs,
                'average_bandwidth' => count($bandwidths) > 0 ? round(array_sum($bandwidths) / count($bandwidths), 2) : 0,
                'provider_distribution' => $providers,
                'wifi_coverage_score' => count($projects) > 0 ? round(($metrics['completed'] / count($projects)) * 100, 1) : 0
            ];
            break;
            
        case 'computer': // Tech4ED
            $totalComputers = 0;
            $totalBeneficiaries = 0;
            $courses = [];
            
            foreach ($projects as $project) {
                $totalComputers += intval($project['custom_data']['computer_count'] ?? 0);
                $totalBeneficiaries += intval($project['custom_data']['beneficiaries_count'] ?? 0);
                
                $courseList = $project['custom_data']['courses_offered'] ?? '';
                if (!empty($courseList)) {
                    foreach (explode(',', $courseList) as $course) {
                        $course = trim($course);
                        if (!isset($courses[$course])) $courses[$course] = 0;
                        $courses[$course]++;
                    }
                }
            }
            
            $metrics = [
                'total_computers' => $totalComputers,
                'total_beneficiaries' => $totalBeneficiaries,
                'avg_computers_per_center' => count($projects) > 0 ? round($totalComputers / count($projects), 1) : 0,
                'popular_courses' => $courses
            ];
            break;
            
        case 'broadcast': // National Broadband Program
            $totalFiberKm = 0;
            $institutions = 0;
            $infrastructureTypes = [];
            
            foreach ($projects as $project) {
                $totalFiberKm += floatval($project['custom_data']['fiber_length_km'] ?? 0);
                $institutions += intval($project['custom_data']['connected_institutions'] ?? 0);
                
                $type = $project['custom_data']['infrastructure_type'] ?? 'Unknown';
                if (!isset($infrastructureTypes[$type])) $infrastructureTypes[$type] = 0;
                $infrastructureTypes[$type]++;
            }
            
            $metrics = [
                'total_fiber_km' => round($totalFiberKm, 2),
                'total_institutions_connected' => $institutions,
                'avg_institutions_per_site' => count($projects) > 0 ? round($institutions / count($projects), 1) : 0,
                'infrastructure_breakdown' => $infrastructureTypes
            ];
            break;
            
        case 'city': // Digital Cities Program
            $totalCCTV = 0;
            $totalSensors = 0;
            $facilityTypes = [];
            
            foreach ($projects as $project) {
                $totalCCTV += intval($project['custom_data']['cctv_cameras'] ?? 0);
                $totalSensors += intval($project['custom_data']['sensors_deployed'] ?? 0);
                
                $type = $project['custom_data']['facility_type'] ?? 'Unknown';
                if (!isset($facilityTypes[$type])) $facilityTypes[$type] = 0;
                $facilityTypes[$type]++;
            }
            
            $metrics = [
                'total_cctv_cameras' => $totalCCTV,
                'total_iot_sensors' => $totalSensors,
                'facilities_by_type' => $facilityTypes,
                'smart_city_readiness' => count($projects) > 0 ? round(($metrics['completed'] / count($projects)) * 100, 1) : 0
            ];
            break;
            
        case 'library': // PIPOL
            $totalWorkstations = 0;
            $totalEbooks = 0;
            $libraryTypes = [];
            
            foreach ($projects as $project) {
                $totalWorkstations += intval($project['custom_data']['workstations'] ?? 0);
                $totalEbooks += intval($project['custom_data']['ebooks_available'] ?? 0);
                
                $type = $project['custom_data']['library_type'] ?? 'Unknown';
                if (!isset($libraryTypes[$type])) $libraryTypes[$type] = 0;
                $libraryTypes[$type]++;
            }
            
            $metrics = [
                'total_workstations' => $totalWorkstations,
                'total_ebooks' => $totalEbooks,
                'avg_workstations_per_library' => count($projects) > 0 ? round($totalWorkstations / count($projects), 1) : 0,
                'libraries_by_type' => $libraryTypes
            ];
            break;
            
        case 'briefcase': // Rural Impact Sourcing
            $totalWorkstations = 0;
            $totalGraduates = 0;
            $activeWorkers = 0;
            
            foreach ($projects as $project) {
                $totalWorkstations += intval($project['custom_data']['workstations'] ?? 0);
                $totalGraduates += intval($project['custom_data']['graduates_count'] ?? 0);
                $activeWorkers += intval($project['custom_data']['active_workers'] ?? 0);
            }
            
            $metrics = [
                'total_workstations' => $totalWorkstations,
                'total_graduates' => $totalGraduates,
                'active_digital_workers' => $activeWorkers,
                'employment_rate' => $totalGraduates > 0 ? round(($activeWorkers / $totalGraduates) * 100, 1) : 0
            ];
            break;
            
        case 'government': // e-Government Services
            $totalTransactions = 0;
            $serviceTypes = [];
            
            foreach ($projects as $project) {
                $totalTransactions += intval($project['custom_data']['daily_transactions'] ?? 0);
                
                $type = $project['custom_data']['service_type'] ?? 'Unknown';
                if (!isset($serviceTypes[$type])) $serviceTypes[$type] = 0;
                $serviceTypes[$type]++;
            }
            
            $metrics = [
                'total_daily_transactions' => $totalTransactions,
                'avg_transactions_per_center' => count($projects) > 0 ? round($totalTransactions / count($projects), 0) : 0,
                'services_by_type' => $serviceTypes,
                'digital_adoption_rate' => count($projects) > 0 ? round(($metrics['completed'] / count($projects)) * 100, 1) : 0
            ];
            break;
            
        case 'education': // ICT Literacy Programs
            $totalParticipants = 0;
            $totalHours = 0;
            $certifiedCount = 0;
            $programTypes = [];
            
            foreach ($projects as $project) {
                $totalParticipants += intval($project['custom_data']['participants_trained'] ?? 0);
                $totalHours += intval($project['custom_data']['training_hours'] ?? 0);
                if (isset($project['custom_data']['certification_offered']) && 
                    $project['custom_data']['certification_offered'] === 'Yes') {
                    $certifiedCount++;
                }
                
                $type = $project['custom_data']['program_type'] ?? 'Unknown';
                if (!isset($programTypes[$type])) $programTypes[$type] = 0;
                $programTypes[$type]++;
            }
            
            $metrics = [
                'total_participants' => $totalParticipants,
                'total_training_hours' => $totalHours,
                'certified_programs' => $certifiedCount,
                'avg_hours_per_program' => count($projects) > 0 ? round($totalHours / count($projects), 1) : 0,
                'programs_by_type' => $programTypes
            ];
            break;
            
        case 'shield': // Cybersecurity Awareness
            $totalAttendees = 0;
            $totalSessions = 0;
            $centerTypes = [];
            
            foreach ($projects as $project) {
                $totalAttendees += intval($project['custom_data']['attendees_educated'] ?? 0);
                $totalSessions += intval($project['custom_data']['sessions_conducted'] ?? 0);
                
                $type = $project['custom_data']['center_type'] ?? 'Unknown';
                if (!isset($centerTypes[$type])) $centerTypes[$type] = 0;
                $centerTypes[$type]++;
            }
            
            $metrics = [
                'total_attendees' => $totalAttendees,
                'total_sessions' => $totalSessions,
                'avg_attendees_per_session' => $totalSessions > 0 ? round($totalAttendees / $totalSessions, 1) : 0,
                'centers_by_type' => $centerTypes,
                'awareness_coverage' => count($projects) > 0 ? round(($metrics['completed'] / count($projects)) * 100, 1) : 0
            ];
            break;
            
        case 'globe': // Free Internet in Public Places
            $totalRadius = 0;
            $totalExpectedUsers = 0;
            $locationTypes = [];
            
            foreach ($projects as $project) {
                $totalRadius += intval($project['custom_data']['coverage_radius_m'] ?? 0);
                $totalExpectedUsers += intval($project['custom_data']['expected_users'] ?? 0);
                
                $type = $project['custom_data']['location_type'] ?? 'Unknown';
                if (!isset($locationTypes[$type])) $locationTypes[$type] = 0;
                $locationTypes[$type]++;
            }
            
            $metrics = [
                'total_coverage_radius_m' => $totalRadius,
                'avg_coverage_radius' => count($projects) > 0 ? round($totalRadius / count($projects), 0) : 0,
                'total_expected_users' => $totalExpectedUsers,
                'locations_by_type' => $locationTypes,
                'public_access_score' => count($projects) > 0 ? round(($metrics['completed'] / count($projects)) * 100, 1) : 0
            ];
            break;
    }
    
    return $metrics;
}

/**
 * Generate chart data
 */
function generateCharts($projects, $typeConfig) {
    $charts = [];
    
    // Status pie chart
    $statusData = ['Done' => 0, 'Pending' => 0];
    foreach ($projects as $project) {
        $statusData[$project['status'] ?? 'Pending']++;
    }
    $charts['status_distribution'] = [
        'type' => 'pie',
        'labels' => array_keys($statusData),
        'data' => array_values($statusData),
        'colors' => ['#22C55E', '#F59E0B']
    ];
    
    // Province bar chart
    $provinceData = [];
    foreach ($projects as $project) {
        $province = $project['province'];
        if (!isset($provinceData[$province])) $provinceData[$province] = 0;
        $provinceData[$province]++;
    }
    $charts['by_province'] = [
        'type' => 'bar',
        'labels' => array_keys($provinceData),
        'data' => array_values($provinceData)
    ];
    
    // Timeline chart
    $timelineData = [];
    foreach ($projects as $project) {
        $month = date('Y-m', strtotime($project['activation_date']));
        if (!isset($timelineData[$month])) $timelineData[$month] = ['Done' => 0, 'Pending' => 0];
        $timelineData[$month][$project['status'] ?? 'Pending']++;
    }
    ksort($timelineData);
    $charts['timeline'] = [
        'type' => 'line',
        'labels' => array_keys($timelineData),
        'datasets' => [
            ['label' => 'Done', 'data' => array_map(fn($m) => $m['Done'], $timelineData)],
            ['label' => 'Pending', 'data' => array_map(fn($m) => $m['Pending'], $timelineData)]
        ]
    ];
    
    return $charts;
}

/**
 * Generate detailed breakdown
 */
function generateDetails($projects, $typeConfig) {
    // Group by municipality
    $byMunicipality = [];
    foreach ($projects as $project) {
        $key = $project['municipality'] . ', ' . $project['province'];
        if (!isset($byMunicipality[$key])) {
            $byMunicipality[$key] = ['count' => 0, 'completed' => 0, 'projects' => []];
        }
        $byMunicipality[$key]['count']++;
        if ($project['status'] === 'Done') {
            $byMunicipality[$key]['completed']++;
        }
        $byMunicipality[$key]['projects'][] = [
            'site_code' => $project['site_code'],
            'site_name' => $project['site_name'],
            'status' => $project['status']
        ];
    }
    
    return $byMunicipality;
}

// Handle API requests
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-report':
        $projectType = $_GET['project_type'] ?? '';
        $filters = [
            'province' => $_GET['province'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        if ($projectType === 'EgovPH') {
            $result = getEgovPHReport($filters);
        } elseif ($projectType === 'Free-WIFI for All') {
            $result = getFreeWiFiReport($filters);
        } else {
            $result = getTypeReport($projectType, array_filter($filters));
        }
        echo json_encode($result);
        break;
        
    case 'get-summary':
        $db = getDB();
        $summary = [];
        
        foreach ($config['project_types'] as $typeName => $typeConfig) {
            if ($typeName === 'EgovPH') {
                $stmt = $db->query("SELECT COUNT(*) as total FROM activities WHERE project_type = 'EgovPH'");
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                $summary[$typeName] = [
                    'total' => intval($stats['total']),
                    'completed' => intval($stats['total']),
                    'pending' => 0,
                    'icon' => $typeConfig['icon'],
                    'color' => $typeConfig['color']
                ];
            } else {
                $stmt = $db->prepare("SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Done' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
                    FROM projects WHERE project_type = ?");
                $stmt->execute([$typeName]);
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $summary[$typeName] = [
                    'total' => intval($stats['total']),
                    'completed' => intval($stats['completed']),
                    'pending' => intval($stats['pending']),
                    'icon' => $typeConfig['icon'],
                    'color' => $typeConfig['color']
                ];
            }
        }
        
        echo json_encode(['success' => true, 'data' => $summary]);
        break;
        
    case 'get-activities':
        $result = getEgovPHReport($_GET);
        echo json_encode($result);
        break;
        
    case 'get-daily-metrics':
        $siteCode = $_GET['site_code'] ?? '';
        $result = getDailyMetricsReport($siteCode, $_GET);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

function getEgovPHReport($filters = []) {
    $db = getDB();
    
    $whereClause = "WHERE project_type = 'EgovPH'";
    $params = [];
    
    if (!empty($filters['province'])) {
        $whereClause .= " AND province = ?";
        $params[] = $filters['province'];
    }
    if (!empty($filters['date_from'])) {
        $whereClause .= " AND activity_date >= ?";
        $params[] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $whereClause .= " AND activity_date <= ?";
        $params[] = $filters['date_to'];
    }
    
    $stmt = $db->prepare("SELECT * FROM activities $whereClause ORDER BY activity_date DESC");
    $stmt->execute($params);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalParticipants = 0;
    $totalDownloads = 0;
    $byType = [];
    $byProvince = [];
    $byFacilitator = [];
    
    foreach ($activities as $activity) {
        $totalParticipants += intval($activity['participants']);
        $totalDownloads += intval($activity['downloads']);
        
        $type = $activity['activity_type'] ?? 'Unknown';
        if (!isset($byType[$type])) $byType[$type] = 0;
        $byType[$type]++;
        
        $province = $activity['province'] ?? 'Unknown';
        if (!isset($byProvince[$province])) $byProvince[$province] = ['count' => 0, 'participants' => 0, 'downloads' => 0];
        $byProvince[$province]['count']++;
        $byProvince[$province]['participants'] += intval($activity['participants']);
        $byProvince[$province]['downloads'] += intval($activity['downloads']);
        
        $facilitator = $activity['facilitator'] ?? 'Unknown';
        if (!isset($byFacilitator[$facilitator])) $byFacilitator[$facilitator] = 0;
        $byFacilitator[$facilitator]++;
    }
    
    return [
        'success' => true,
        'data' => [
            'project_type' => 'EgovPH',
            'summary' => [
                'total_activities' => count($activities),
                'total_participants' => $totalParticipants,
                'total_downloads' => $totalDownloads,
                'avg_participants' => count($activities) > 0 ? round($totalParticipants / count($activities), 1) : 0,
            ],
            'charts' => [
                'by_type' => ['labels' => array_keys($byType), 'data' => array_values($byType)],
                'by_province' => ['labels' => array_keys($byProvince), 'data' => array_map(fn($p) => $p['count'], $byProvince)],
                'by_facilitator' => ['labels' => array_keys($byFacilitator), 'data' => array_values($byFacilitator)],
            ],
            'by_province' => $byProvince,
            'activities' => $activities
        ]
    ];
}

function getFreeWiFiReport($filters = []) {
    $db = getDB();
    
    $whereClause = "WHERE project_type = 'Free-WIFI for All'";
    $params = [];
    
    if (!empty($filters['province'])) {
        $whereClause .= " AND province = ?";
        $params[] = $filters['province'];
    }
    if (!empty($filters['status'])) {
        $whereClause .= " AND status = ?";
        $params[] = $filters['status'];
    }
    
    $stmt = $db->prepare("SELECT * FROM projects $whereClause ORDER BY created_at DESC");
    $stmt->execute($params);
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sites as &$site) {
        $site['custom_data'] = json_decode($site['custom_data'], true) ?? [];
    }
    
    $byProvince = [];
    $bySiteType = [];
    $byTechnology = [];
    $totalBandwidth = 0;
    
    foreach ($sites as $site) {
        $province = $site['province'] ?? 'Unknown';
        if (!isset($byProvince[$province])) $byProvince[$province] = 0;
        $byProvince[$province]++;
        
        $siteType = $site['custom_data']['site_type'] ?? 'Unknown';
        if (!isset($bySiteType[$siteType])) $bySiteType[$siteType] = 0;
        $bySiteType[$siteType]++;
        
        $tech = $site['custom_data']['technology'] ?? 'Unknown';
        if (!isset($byTechnology[$tech])) $byTechnology[$tech] = 0;
        $byTechnology[$tech]++;
        
        $totalBandwidth += floatval($site['custom_data']['bandwidth'] ?? 0);
    }
    
    $metricsStmt = $db->prepare("SELECT 
        COUNT(*) as total_records,
        AVG(bandwidth_utilization) as avg_utilization,
        SUM(unique_users) as total_users,
        SUM(CASE WHEN status = 'UP' THEN 1 ELSE 0 END) as up_count,
        SUM(CASE WHEN status = 'DOWN' THEN 1 ELSE 0 END) as down_count
        FROM daily_metrics");
    $metricsStmt->execute();
    $metricsSummary = $metricsStmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'success' => true,
        'data' => [
            'project_type' => 'Free-WIFI for All',
            'summary' => [
                'total_sites' => count($sites),
                'avg_bandwidth' => count($sites) > 0 ? round($totalBandwidth / count($sites), 2) : 0,
                'total_bandwidth' => $totalBandwidth,
                'metrics_records' => intval($metricsSummary['total_records']),
                'avg_utilization' => round(floatval($metricsSummary['avg_utilization']), 4),
                'total_users' => intval($metricsSummary['total_users']),
                'uptime_rate' => $metricsSummary['total_records'] > 0 
                    ? round((intval($metricsSummary['up_count']) / intval($metricsSummary['total_records'])) * 100, 1) 
                    : 0,
            ],
            'charts' => [
                'by_province' => ['labels' => array_keys($byProvince), 'data' => array_values($byProvince)],
                'by_site_type' => ['labels' => array_keys($bySiteType), 'data' => array_values($bySiteType)],
                'by_technology' => ['labels' => array_keys($byTechnology), 'data' => array_values($byTechnology)],
            ],
            'by_province' => $byProvince,
            'sites' => $sites
        ]
    ];
}

function getDailyMetricsReport($siteCode, $filters = []) {
    $db = getDB();
    
    $whereClause = "WHERE 1=1";
    $params = [];
    
    if (!empty($siteCode)) {
        $whereClause .= " AND site_code = ?";
        $params[] = $siteCode;
    }
    if (!empty($filters['date_from'])) {
        $whereClause .= " AND metric_date >= ?";
        $params[] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $whereClause .= " AND metric_date <= ?";
        $params[] = $filters['date_to'];
    }
    
    $stmt = $db->prepare("SELECT * FROM daily_metrics $whereClause ORDER BY metric_date DESC LIMIT 1000");
    $stmt->execute($params);
    $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $byDate = [];
    foreach ($metrics as $metric) {
        $date = $metric['metric_date'];
        if (!isset($byDate[$date])) {
            $byDate[$date] = ['up' => 0, 'down' => 0, 'users' => 0, 'utilization' => 0];
        }
        if ($metric['status'] === 'UP') $byDate[$date]['up']++;
        if ($metric['status'] === 'DOWN') $byDate[$date]['down']++;
        $byDate[$date]['users'] += intval($metric['unique_users']);
        $byDate[$date]['utilization'] += floatval($metric['bandwidth_utilization']);
    }
    
    return [
        'success' => true,
        'data' => [
            'site_code' => $siteCode,
            'metrics' => $metrics,
            'by_date' => $byDate,
            'chart_data' => [
                'labels' => array_keys($byDate),
                'users' => array_map(fn($d) => $d['users'], $byDate),
                'utilization' => array_map(fn($d) => $d['utilization'], $byDate),
            ]
        ]
    ];
}
