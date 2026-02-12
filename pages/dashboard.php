<?php
/**
 * Dashboard Page
 */

$pageTitle = 'Dashboard';
$activeTab = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Projects</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1" id="stat-total">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-3xl font-bold text-green-600 mt-1" id="stat-completed">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending</p>
                    <p class="text-3xl font-bold text-orange-600 mt-1" id="stat-pending">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Provinces</p>
                    <p class="text-3xl font-bold text-purple-600 mt-1" id="stat-provinces">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map and Status Overview - 70/30 Split -->
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Map Section - 70% -->
        <div class="w-full lg:w-[70%] bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                    <div class="flex items-center gap-4 flex-wrap">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            Project Implementation Map
                        </h3>
                        <div class="flex flex-wrap gap-1" id="project-legend">
                            <!-- Legend will be populated by JavaScript -->
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select id="map-province-filter" onchange="filterMapByProvince(this.value)" 
                                class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="all">All Provinces</option>
                        </select>
                        <button onclick="filterMap('all')" class="px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded transition-colors">All</button>
                        <button onclick="filterMap('Done')" class="px-3 py-2 text-sm bg-green-200 hover:bg-green-300 text-green-800 rounded transition-colors">Done</button>
                        <button onclick="filterMap('Pending')" class="px-3 py-2 text-sm bg-orange-200 hover:bg-orange-300 text-orange-800 rounded transition-colors">Pending</button>
                        <button onclick="toggleMapView()" class="px-3 py-2 text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 rounded transition-colors" title="Toggle Satellite View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div id="map" class="h-[650px] w-full"></div>
        </div>
        
        <!-- Status Overview - 30% -->
        <div class="w-full lg:w-[30%] bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Map Filters
                </h3>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Province</label>
                    <select id="map-filter-province" onchange="applyMapFilters()" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All Provinces</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                    <div class="flex gap-2">
                        <button onclick="setMapStatusFilter('all')" id="btn-status-all" class="flex-1 px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded transition-colors font-medium">All</button>
                        <button onclick="setMapStatusFilter('Done')" id="btn-status-done" class="flex-1 px-3 py-2 text-sm bg-green-100 hover:bg-green-200 text-green-800 rounded transition-colors">Done</button>
                        <button onclick="setMapStatusFilter('Pending')" id="btn-status-pending" class="flex-1 px-3 py-2 text-sm bg-orange-100 hover:bg-orange-200 text-orange-800 rounded transition-colors">Pending</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Project</label>
                    <select id="map-filter-project" onchange="applyMapFilters()" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All Projects</option>
                    </select>
                </div>
                <button onclick="resetMapFilters()" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm font-medium">
                    Reset Filters
                </button>
                <div class="pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        <span id="map-filtered-count" class="font-bold text-blue-600">0</span> projects shown
                    </p>
                </div>
            </div>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Status Overview
                </h3>
            </div>
            <div class="flex-1 min-h-[200px]">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Timeline
                </h3>
                <div class="h-[200px]">
                    <canvas id="timelineChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Projects Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Recent Projects
            </h3>
            <a href="/projects/newPTS/pages/projects.php" class="text-blue-600 hover:text-blue-700 font-medium">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Site Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Site Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody id="recent-projects-body" class="divide-y divide-gray-200">
                    <!-- Filled by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions & Activity Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Quick Actions
            </h3>
            <div class="space-y-3">
                <a href="/projects/newPTS/pages/project-form.php" class="flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="text-blue-700 font-medium">Add New Project</span>
                </a>
                <a href="/projects/newPTS/pages/import.php" class="flex items-center gap-3 p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="text-green-700 font-medium">Import CSV Data</span>
                </a>
                <button onclick="API.import.export()" class="w-full flex items-center gap-3 p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span class="text-purple-700 font-medium">Export All Data</span>
                </button>
                <a href="/projects/newPTS/pages/reports.php" class="flex items-center gap-3 p-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-orange-700 font-medium">Generate Reports</span>
                </a>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Recent Activity
                </h3>
                <a href="<?php echo isAdmin() ? '/projects/newPTS/pages/admin.php' : '#'; ?>" class="text-sm text-blue-600 hover:text-blue-700">
                    <?php echo isAdmin() ? 'View All' : ''; ?>
                </a>
            </div>
            <div class="max-h-80 overflow-y-auto" id="activity-feed">
                <!-- Filled by JavaScript -->
                <div class="p-4 text-center text-gray-500">Loading activity...</div>
            </div>
        </div>
    </div>
</div>

<script>
// Load activity feed
async function loadActivityFeed() {
    try {
        const response = await fetch('/projects/newPTS/api/activity.php?limit=10');
        const data = await response.json();

        if (data.success && data.data.logs.length > 0) {
            const container = document.getElementById('activity-feed');
            container.innerHTML = data.data.logs.map(log => {
                const icon = log.action === 'create' ? '<span class="w-2 h-2 bg-green-500 rounded-full"></span>' :
                           log.action === 'update' ? '<span class="w-2 h-2 bg-blue-500 rounded-full"></span>' :
                           log.action === 'delete' ? '<span class="w-2 h-2 bg-red-500 rounded-full"></span>' :
                           '<span class="w-2 h-2 bg-gray-500 rounded-full"></span>';

                return `
                    <div class="flex items-start gap-3 p-4 border-b border-gray-100 hover:bg-gray-50">
                        <div class="mt-1">${icon}</div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-800">
                                <span class="font-medium">${log.username || 'System'}</span>
                                <span class="text-gray-600">${log.action}</span>
                                <span class="font-medium">${log.entity_type}</span>
                                ${log.entity_id ? `#${log.entity_id}` : ''}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">${new Date(log.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            document.getElementById('activity-feed').innerHTML = '<div class="p-4 text-center text-gray-500">No recent activity</div>';
        }
    } catch (error) {
        console.error('Failed to load activity:', error);
        document.getElementById('activity-feed').innerHTML = '<div class="p-4 text-center text-gray-500">Failed to load activity</div>';
    }
}

// Load activity on page load
document.addEventListener('DOMContentLoaded', function() {
    loadActivityFeed();
    loadMapFilterOptions();
});

// Map filter state
let mapFilters = {
    province: 'all',
    status: 'all',
    project: 'all'
};

// Load filter options for map
async function loadMapFilterOptions() {
    try {
        // Load provinces
        const response = await fetch('/projects/newPTS/api/locations.php?type=provinces');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('map-filter-province');
            data.data.provinces.forEach(p => {
                const option = document.createElement('option');
                option.value = p.province;
                option.textContent = `${p.province} (${p.project_count})`;
                select.appendChild(option);
            });
        }
        
        // Load unique projects
        const projectsResponse = await fetch('/projects/newPTS/api/projects.php?per_page=1000');
        const projectsData = await projectsResponse.json();
        if (projectsData.success) {
            // Handle paginated response format
            const projects = projectsData.data.projects || projectsData.data || [];
            const uniqueProjects = [...new Set(projects.map(p => p.project_name))];
            const projectSelect = document.getElementById('map-filter-project');
            uniqueProjects.forEach(projectName => {
                const option = document.createElement('option');
                option.value = projectName;
                option.textContent = projectName;
                projectSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load map filters:', error);
    }
}

// Set status filter
function setMapStatusFilter(status) {
    mapFilters.status = status;
    
    // Update button styles
    document.getElementById('btn-status-all').className = status === 'all' 
        ? 'flex-1 px-3 py-2 text-sm bg-gray-600 text-white rounded transition-colors font-medium' 
        : 'flex-1 px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded transition-colors';
    document.getElementById('btn-status-done').className = status === 'Done' 
        ? 'flex-1 px-3 py-2 text-sm bg-green-600 text-white rounded transition-colors font-medium' 
        : 'flex-1 px-3 py-2 text-sm bg-green-100 hover:bg-green-200 text-green-800 rounded transition-colors';
    document.getElementById('btn-status-pending').className = status === 'Pending' 
        ? 'flex-1 px-3 py-2 text-sm bg-orange-600 text-white rounded transition-colors font-medium' 
        : 'flex-1 px-3 py-2 text-sm bg-orange-100 hover:bg-orange-200 text-orange-800 rounded transition-colors';
    
    applyMapFilters();
}

// Apply all map filters
async function applyMapFilters() {
    mapFilters.province = document.getElementById('map-filter-province').value;
    mapFilters.project = document.getElementById('map-filter-project').value;
    
    const params = new URLSearchParams();
    if (mapFilters.province !== 'all') params.append('province', mapFilters.province);
    if (mapFilters.status !== 'all') params.append('status', mapFilters.status);
    params.append('per_page', '1000'); // Load all for map display
    
    try {
        const response = await fetch(`/projects/newPTS/api/projects.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            // Handle paginated response - extract projects array
            let filtered = data.data.projects || data.data || [];
            
            // Filter by project name if selected
            if (mapFilters.project !== 'all') {
                filtered = filtered.filter(p => p.project_name === mapFilters.project);
            }
            
            // Update map
            MapService.addMarkers(filtered);
            
            // Update count
            document.getElementById('map-filtered-count').textContent = filtered.length;
        }
    } catch (error) {
        console.error('Failed to apply filters:', error);
    }
}

// Reset all map filters
function resetMapFilters() {
    document.getElementById('map-filter-province').value = 'all';
    document.getElementById('map-filter-project').value = 'all';
    mapFilters = { province: 'all', status: 'all', project: 'all' };
    setMapStatusFilter('all');
    MapService.loadProjects();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
