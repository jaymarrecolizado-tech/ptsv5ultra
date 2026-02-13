<?php
/**
 * Project Form Page (Add/Edit)
 */

$pageTitle = isset($_GET['id']) ? 'Edit Project' : 'Add New Project';
$activeTab = 'manual-entry';
require_once __DIR__ . '/../includes/header.php';

$projectId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$project = null;

if ($projectId) {
    // Fetch project for editing
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        if (!$project) {
            header('Location: ./pages/projects.php');
            exit;
        }
    } catch (Exception $e) {
        header('Location: ./pages/projects.php');
        exit;
    }
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <?php echo $projectId ? 'Edit Project' : 'Manual Project Entry'; ?>
            </h2>
        </div>
        
        <form id="project-form" class="p-6 space-y-6">
            <input type="hidden" id="project-id" value="<?php echo $projectId ? $projectId : ''; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Code *</label>
                    <input type="text" id="site-code" required 
                           value="<?php echo $project ? htmlspecialchars($project['site_code']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="e.g., UNDP-GI-0009A">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Name *</label>
                    <input type="text" id="project-name" required 
                           value="<?php echo $project ? htmlspecialchars($project['project_name']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="e.g., Free-WIFI for All">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Name *</label>
                    <input type="text" id="site-name" required 
                           value="<?php echo $project ? htmlspecialchars($project['site_name']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="e.g., Raele Barangay Hall - AP 1">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barangay *</label>
                    <input type="text" id="barangay" required 
                           value="<?php echo $project ? htmlspecialchars($project['barangay']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="Enter barangay">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municipality *</label>
                    <input type="text" id="municipality" required 
                           value="<?php echo $project ? htmlspecialchars($project['municipality']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="Enter municipality">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                    <input type="text" id="province" required 
                           value="<?php echo $project ? htmlspecialchars($project['province']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="Type to search provinces..."
                           autocomplete="off">
                    <div id="province-suggestions" class="hidden absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-48 overflow-auto shadow-lg"></div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                    <input type="text" id="district" required 
                           value="<?php echo $project ? htmlspecialchars($project['district']) : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="e.g., District I">
                </div>
                
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Coordinates * <span class="text-xs text-gray-500">(Click on map or enter manually with 6 decimal precision)</span></label>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Latitude (-90 to 90)</label>
                            <input type="number" id="latitude" required step="0.000001" min="-90" max="90"
                                   value="<?php echo $project ? number_format((float)$project['latitude'], 6, '.', '') : ''; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                                   placeholder="e.g., 20.728794">
                            <p class="text-xs text-gray-400 mt-1">Precision: 0.000001 (0.1 meter)</p>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Longitude (-180 to 180)</label>
                            <input type="number" id="longitude" required step="0.000001" min="-180" max="180"
                                   value="<?php echo $project ? number_format((float)$project['longitude'], 6, '.', '') : ''; ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                                   placeholder="e.g., 121.804235">
                            <p class="text-xs text-gray-400 mt-1">Precision: 0.000001 (0.1 meter)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Coordinate Picker Map -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location Picker <span class="text-xs text-gray-500">(Click on map to set precise coordinates)</span></label>
                    <div id="coordinate-map" class="h-80 w-full rounded-lg border border-gray-300"></div>
                    <p class="text-xs text-gray-500 mt-2">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Click anywhere on the map to set coordinates. Use mouse wheel to zoom in/out for better precision.
                        </span>
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Activation *</label>
                    <input type="date" id="activation-date" required 
                           value="<?php echo $project ? $project['activation_date'] : ''; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select id="status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="Done" <?php echo ($project && $project['status'] === 'Done') ? 'selected' : ''; ?>>Done</option>
                        <option value="Pending" <?php echo ($project && $project['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="notes" rows="3" maxlength="1000"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                          placeholder="Optional notes..."><?php echo $project ? htmlspecialchars($project['notes']) : ''; ?></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="./pages/projects.php" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <?php echo $projectId ? 'Update Project' : 'Create Project'; ?>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Auto-correction Info -->
    <div class="mt-6 bg-blue-50 rounded-xl border border-blue-200 p-6">
        <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Auto-Correction Features
        </h3>
        <ul class="space-y-2 text-blue-700">
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Province/Municipality/Barangay name standardization
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Date format validation and correction
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Coordinate format validation
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Status value normalization
            </li>
        </ul>
    </div>
</div>

<script>
// Initialize coordinate picker map
let coordinateMap;
let coordinateMarker;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const initialLat = parseFloat(document.getElementById('latitude').value) || 17.0;
    const initialLng = parseFloat(document.getElementById('longitude').value) || 121.0;
    const hasCoords = document.getElementById('latitude').value && document.getElementById('longitude').value;
    
    coordinateMap = L.map('coordinate-map').setView([initialLat, initialLng], hasCoords ? 18 : 6);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(coordinateMap);
    
    // If editing existing project, show marker
    if (hasCoords) {
        coordinateMarker = L.marker([initialLat, initialLng], {
            draggable: true,
            title: 'Project Location'
        }).addTo(coordinateMap);
        
        // Update inputs when marker is dragged
        coordinateMarker.on('dragend', function(e) {
            const latLng = e.target.getLatLng();
            document.getElementById('latitude').value = latLng.lat.toFixed(6);
            document.getElementById('longitude').value = latLng.lng.toFixed(6);
        });
    }
    
    // Handle map clicks
    coordinateMap.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // Validate coordinates
        if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
            alert('Invalid coordinates. Please click within valid range.');
            return;
        }
        
        // Update input fields with 6 decimal precision
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
        
        // Update or create marker
        if (coordinateMarker) {
            coordinateMarker.setLatLng([lat, lng]);
        } else {
            coordinateMarker = L.marker([lat, lng], {
                draggable: true,
                title: 'Project Location'
            }).addTo(coordinateMap);
            
            // Update inputs when marker is dragged
            coordinateMarker.on('dragend', function(e) {
                const latLng = e.target.getLatLng();
                document.getElementById('latitude').value = latLng.lat.toFixed(6);
                document.getElementById('longitude').value = latLng.lng.toFixed(6);
            });
        }
        
        // Zoom in for better precision
        coordinateMap.setView([lat, lng], 18);
    });
    
    // Update marker when inputs change
    document.getElementById('latitude').addEventListener('change', updateMarkerFromInputs);
    document.getElementById('longitude').addEventListener('change', updateMarkerFromInputs);
});

function updateMarkerFromInputs() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    
    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
        if (coordinateMarker) {
            coordinateMarker.setLatLng([lat, lng]);
        } else {
            coordinateMarker = L.marker([lat, lng], {
                draggable: true,
                title: 'Project Location'
            }).addTo(coordinateMap);
        }
        coordinateMap.setView([lat, lng], 18);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
