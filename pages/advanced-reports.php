<?php
/**
 * Advanced Reports Page with Location Filtering
 */

$pageTitle = 'Advanced Reports';
$activeTab = 'reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Implementation Reports</h2>
        <p class="text-gray-600">Generate detailed reports by Province, District, and Municipality</p>
    </div>

    <!-- Filter Panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filter by Location
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Province Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <select id="filter-province" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="all">All Provinces</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="province-count"></p>
            </div>
            
            <!-- District Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                <select id="filter-district" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" disabled>
                    <option value="all">All Districts</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="district-count"></p>
            </div>
            
            <!-- Municipality Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                <select id="filter-municipality" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" disabled>
                    <option value="all">All Municipalities</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="municipality-count"></p>
            </div>
            
            <!-- Barangay Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <select id="filter-barangay" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" disabled>
                    <option value="all">All Barangays</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="barangay-count"></p>
            </div>
        </div>
        
        <!-- Additional Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="filter-status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="all">All Status</option>
                    <option value="Done">Completed</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" id="filter-date-from" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" id="filter-date-to" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-gray-200">
            <button onclick="generateReport()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generate Report
            </button>
            <button onclick="exportPDF()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button>
            <button onclick="exportExcel()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
            <button onclick="resetFilters()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Reset Filters
            </button>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="summary-stats">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-blue-600" id="stat-total">-</p>
            <p class="text-sm text-gray-500 mt-1">Total Projects</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-green-600" id="stat-completed">-</p>
            <p class="text-sm text-gray-500 mt-1">Completed</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-orange-600" id="stat-pending">-</p>
            <p class="text-sm text-gray-500 mt-1">Pending</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-purple-600" id="stat-completion">-</p>
            <p class="text-sm text-gray-500 mt-1">Completion Rate</p>
        </div>
    </div>

    <!-- Report Results -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Report Results</h3>
            <span id="result-count" class="text-sm text-gray-500"></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Site Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">District</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody id="report-results" class="divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Select filters and click "Generate Report" to see results
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Location Breakdown -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Location Breakdown</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <canvas id="locationChart"></canvas>
            </div>
            <div id="breakdown-table" class="overflow-x-auto">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Load location hierarchy on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProvinces();
});

// Load provinces
async function loadProvinces() {
    try {
        const response = await fetch('/projects/newPTS/api/locations.php?type=provinces');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('filter-province');
            select.innerHTML = '<option value="all">All Provinces</option>';
            
            data.data.provinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province.province;
                option.textContent = `${province.province} (${province.project_count})`;
                select.appendChild(option);
            });
            
            // Add change listener
            select.addEventListener('change', function() {
                loadDistricts(this.value);
                document.getElementById('filter-district').disabled = this.value === 'all';
                document.getElementById('filter-municipality').disabled = true;
                document.getElementById('filter-barangay').disabled = true;
            });
        }
    } catch (error) {
        console.error('Failed to load provinces:', error);
    }
}

// Load districts
async function loadDistricts(province) {
    if (province === 'all') {
        document.getElementById('filter-district').innerHTML = '<option value="all">All Districts</option>';
        return;
    }
    
    try {
        const response = await fetch(`/projects/newPTS/api/locations.php?type=districts&province=${encodeURIComponent(province)}`);
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('filter-district');
            select.innerHTML = '<option value="all">All Districts</option>';
            
            data.data.districts.forEach(district => {
                const option = document.createElement('option');
                option.value = district.district;
                option.textContent = `${district.district} (${district.project_count})`;
                select.appendChild(option);
            });
            
            select.addEventListener('change', function() {
                loadMunicipalities(province, this.value);
                document.getElementById('filter-municipality').disabled = false;
            });
        }
    } catch (error) {
        console.error('Failed to load districts:', error);
    }
}

// Load municipalities
async function loadMunicipalities(province, district) {
    try {
        const url = `/projects/newPTS/api/locations.php?type=municipalities&province=${encodeURIComponent(province)}&district=${encodeURIComponent(district)}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('filter-municipality');
            select.innerHTML = '<option value="all">All Municipalities</option>';
            
            data.data.municipalities.forEach(municipality => {
                const option = document.createElement('option');
                option.value = municipality.municipality;
                option.textContent = `${municipality.municipality} (${municipality.project_count})`;
                select.appendChild(option);
            });
            
            select.addEventListener('change', function() {
                loadBarangays(province, district, this.value);
                document.getElementById('filter-barangay').disabled = false;
            });
        }
    } catch (error) {
        console.error('Failed to load municipalities:', error);
    }
}

// Load barangays
async function loadBarangays(province, district, municipality) {
    try {
        const url = `/projects/newPTS/api/locations.php?type=barangays&province=${encodeURIComponent(province)}&district=${encodeURIComponent(district)}&municipality=${encodeURIComponent(municipality)}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('filter-barangay');
            select.innerHTML = '<option value="all">All Barangays</option>';
            
            data.data.barangays.forEach(barangay => {
                const option = document.createElement('option');
                option.value = barangay.barangay;
                option.textContent = `${barangay.barangay} (${barangay.project_count})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Failed to load barangays:', error);
    }
}

// Generate report
async function generateReport() {
    const filters = getCurrentFilters();
    
    try {
        // Build query string
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            if (filters[key] && filters[key] !== 'all') {
                params.append(key, filters[key]);
            }
        });
        
        const response = await fetch(`/projects/newPTS/api/projects.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            displayResults(data.data);
            updateStats(data.data);
            updateLocationChart(data.data);
        }
    } catch (error) {
        console.error('Failed to generate report:', error);
        alert('Failed to generate report');
    }
}

// Update location breakdown chart
function updateLocationChart(projects) {
    // Group by province
    const byProvince = {};
    projects.forEach(p => {
        byProvince[p.province] = (byProvince[p.province] || 0) + 1;
    });
    
    // Create chart
    const ctx = document.getElementById('locationChart');
    if (window.locationChartInstance) {
        window.locationChartInstance.destroy();
    }
    
    window.locationChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(byProvince),
            datasets: [{
                label: 'Projects',
                data: Object.values(byProvince),
                backgroundColor: '#3B82F6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    
    // Update breakdown table
    const tableDiv = document.getElementById('breakdown-table');
    let tableHTML = '<table class="w-full text-sm"><thead><tr class="bg-gray-50"><th class="px-3 py-2 text-left">Province</th><th class="px-3 py-2 text-right">Projects</th></tr></thead><tbody>';
    
    Object.entries(byProvince)
        .sort((a, b) => b[1] - a[1])
        .forEach(([province, count]) => {
            tableHTML += `<tr class="border-b"><td class="px-3 py-2">${province}</td><td class="px-3 py-2 text-right font-semibold">${count}</td></tr>`;
        });
    
    tableHTML += '</tbody></table>';
    tableDiv.innerHTML = tableHTML;
}

// Display results
function displayResults(projects) {
    const tbody = document.getElementById('report-results');
    document.getElementById('result-count').textContent = `${projects.length} projects found`;
    
    if (projects.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    No projects found for selected filters
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = projects.map(project => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-medium text-gray-900">${project.site_code}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${project.project_name}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.barangay}, ${project.municipality}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.district}</td>
            <td class="px-4 py-3">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${project.status === 'Done' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">
                    ${project.status}
                </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.activation_date_formatted}</td>
        </tr>
    `).join('');
}

// Update statistics
function updateStats(projects) {
    const total = projects.length;
    const completed = projects.filter(p => p.status === 'Done').length;
    const pending = projects.filter(p => p.status === 'Pending').length;
    const completionRate = total > 0 ? ((completed / total) * 100).toFixed(1) : 0;
    
    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-completed').textContent = completed;
    document.getElementById('stat-pending').textContent = pending;
    document.getElementById('stat-completion').textContent = completionRate + '%';
}

// Reset filters
function resetFilters() {
    document.getElementById('filter-province').value = 'all';
    document.getElementById('filter-district').innerHTML = '<option value="all">All Districts</option>';
    document.getElementById('filter-municipality').innerHTML = '<option value="all">All Municipalities</option>';
    document.getElementById('filter-barangay').innerHTML = '<option value="all">All Barangays</option>';
    document.getElementById('filter-status').value = 'all';
    document.getElementById('filter-date-from').value = '';
    document.getElementById('filter-date-to').value = '';
    
    document.getElementById('filter-district').disabled = true;
    document.getElementById('filter-municipality').disabled = true;
    document.getElementById('filter-barangay').disabled = true;
}

// Export functions
function exportPDF() {
    const filters = getCurrentFilters();
    const params = new URLSearchParams({...filters, type: 'pdf'});
    window.open(`/projects/newPTS/api/export.php?${params}`, '_blank');
}

function exportExcel() {
    const filters = getCurrentFilters();
    const params = new URLSearchParams({...filters, type: 'csv'});
    window.location.href = `/projects/newPTS/api/export.php?${params}`;
}

// Get current filter values
function getCurrentFilters() {
    return {
        province: document.getElementById('filter-province').value,
        district: document.getElementById('filter-district').value,
        municipality: document.getElementById('filter-municipality').value,
        barangay: document.getElementById('filter-barangay').value,
        status: document.getElementById('filter-status').value,
        date_from: document.getElementById('filter-date-from').value,
        date_to: document.getElementById('filter-date-to').value
    };
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
