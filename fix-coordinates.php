<?php
/**
 * Fix mapping coordinates for accuracy
 */

require_once 'config/database.php';

echo "Fixing mapping coordinates for accuracy...\n\n";

try {
    $db = getDB();
    
    // Accurate coordinates for Philippine provinces in Region II
    $provinceCoordinates = [
        'Batanes' => [
            'center' => ['lat' => 20.78, 'lng' => 121.85],
            'municipalities' => [
                'Basco' => ['lat' => 20.45, 'lng' => 121.97],
                'Itbayat' => ['lat' => 20.78, 'lng' => 121.84],
                'Ivana' => ['lat' => 20.37, 'lng' => 121.92],
                'Mahatao' => ['lat' => 20.42, 'lng' => 121.94],
                'Sabtang' => ['lat' => 20.34, 'lng' => 121.87],
                'Uyugan' => ['lat' => 20.35, 'lng' => 121.93]
            ]
        ],
        'Cagayan' => [
            'center' => ['lat' => 17.60, 'lng' => 121.80],
            'municipalities' => [
                'Tuguegarao' => ['lat' => 17.61, 'lng' => 121.73],
                'Aparri' => ['lat' => 18.36, 'lng' => 121.64],
                'Baggao' => ['lat' => 17.93, 'lng' => 121.96],
                'Solana' => ['lat' => 17.65, 'lng' => 121.68],
                'Lal-lo' => ['lat' => 18.20, 'lng' => 122.02],
                'Gattaran' => ['lat' => 18.06, 'lng' => 121.64]
            ]
        ],
        'Isabela' => [
            'center' => ['lat' => 16.98, 'lng' => 121.99],
            'municipalities' => [
                'Ilagan' => ['lat' => 17.15, 'lng' => 121.89],
                'Cauayan' => ['lat' => 16.94, 'lng' => 121.78],
                'Santiago' => ['lat' => 16.69, 'lng' => 121.55],
                'Alicia' => ['lat' => 16.78, 'lng' => 121.70],
                'Cabarroguis' => ['lat' => 16.52, 'lng' => 121.52]
            ]
        ],
        'Nueva Vizcaya' => [
            'center' => ['lat' => 16.48, 'lng' => 121.15],
            'municipalities' => [
                'Bayombong' => ['lat' => 16.49, 'lng' => 121.15],
                'Bambang' => ['lat' => 16.39, 'lng' => 121.11],
                'Solano' => ['lat' => 16.52, 'lng' => 121.18],
                'Kasibu' => ['lat' => 16.42, 'lng' => 121.29],
                'Diadi' => ['lat' => 16.66, 'lng' => 121.36]
            ]
        ],
        'Quirino' => [
            'center' => ['lat' => 16.30, 'lng' => 121.70],
            'municipalities' => [
                'Cabarroguis' => ['lat' => 16.52, 'lng' => 121.52],
                'Diffun' => ['lat' => 16.60, 'lng' => 121.45],
                'Maddela' => ['lat' => 16.34, 'lng' => 121.70],
                'Aglipay' => ['lat' => 16.40, 'lng' => 121.64]
            ]
        ]
    ];
    
    // Get all projects
    $stmt = $db->query("SELECT id, province, municipality FROM projects");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $db->prepare("UPDATE projects SET latitude = ?, longitude = ? WHERE id = ?");
    $updated = 0;
    
    foreach ($projects as $project) {
        $province = $project['province'];
        $municipality = $project['municipality'];
        $id = $project['id'];
        
        // Get base coordinates for province
        if (isset($provinceCoordinates[$province])) {
            $provData = $provinceCoordinates[$province];
            
            // If municipality has specific coordinates, use them
            if (isset($provData['municipalities'][$municipality])) {
                $baseLat = $provData['municipalities'][$municipality]['lat'];
                $baseLng = $provData['municipalities'][$municipality]['lng'];
            } else {
                // Use province center
                $baseLat = $provData['center']['lat'];
                $baseLng = $provData['center']['lng'];
            }
            
            // Add small random offset (±0.02 degrees = ~2km) for visual spread
            $lat = $baseLat + (mt_rand(-20, 20) / 1000);
            $lng = $baseLng + (mt_rand(-20, 20) / 1000);
            
            $updateStmt->execute([$lat, $lng, $id]);
            $updated++;
        }
    }
    
    echo "✓ Updated $updated projects with accurate coordinates\n\n";
    
    // Show sample of Batanes projects
    echo "Sample Batanes Projects (should now be correctly placed):\n";
    echo str_repeat('-', 70) . "\n";
    $stmt = $db->query("SELECT site_code, project_type, municipality, latitude, longitude 
                        FROM projects 
                        WHERE province = 'Batanes' 
                        LIMIT 10");
    $batanesProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($batanesProjects as $proj) {
        printf("%-15s %-25s %-15s %8.4f, %8.4f\n", 
            $proj['site_code'], 
            substr($proj['project_type'], 0, 25),
            $proj['municipality'],
            $proj['latitude'], 
            $proj['longitude']
        );
    }
    
    echo "\nProvince Coordinate Centers:\n";
    echo str_repeat('-', 50) . "\n";
    foreach ($provinceCoordinates as $prov => $data) {
        printf("%-20s %8.4f, %8.4f\n", $prov, $data['center']['lat'], $data['center']['lng']);
    }
    
    echo "\n✓ Coordinates fixed! Refresh the map to see accurate locations.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
