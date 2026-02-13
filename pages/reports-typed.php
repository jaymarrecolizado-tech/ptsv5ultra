<?php
/**
 * Type-Specific Reports Page
 */

$pageTitle = 'Project Reports';
$activeTab = 'reports';
require_once __DIR__ . '/../includes/header.php';

$config = require __DIR__ . '/../config/project_types.php';
$projectTypes = $config['project_types'];

function getIconSVG($iconType) {
    $icons = [
        'wifi' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>',
        'government' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'city' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'computer' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'broadcast' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>',
        'library' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'briefcase' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'education' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>',
        'shield' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        'globe' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    ];
    return $icons[$iconType] ?? $icons['wifi'];
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Project Reports by Type</h1>
                <p class="text-gray-600 mt-1">View detailed reports for each project type</p>
            </div>
        </div>
    </div>

    <!-- Project Type Selection -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Select Project Type</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4" id="project-type-grid">
            <?php foreach ($projectTypes as $typeName => $typeConfig): ?>
            <div class="project-type-card border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all"
                 data-type="<?php echo htmlspecialchars($typeName); ?>"
                 onclick="selectProjectType('<?php echo htmlspecialchars($typeName); ?>')">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg"
                         style="background-color: <?php echo $typeConfig['color']; ?>">
                        <?php echo getIconSVG($typeConfig['icon']); ?>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-sm"><?php echo htmlspecialchars($typeName); ?></h3>
                        <p class="text-xs text-gray-500"><?php echo substr($typeConfig['description'], 0, 40); ?>...</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Report Content -->
    <div id="report-content" class="hidden space-y-6">
        <!-- Summary Cards -->
        <div id="summary-cards" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Populated by JS -->
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4" id="chart1-title">Distribution</h3>
                <canvas id="chart1" height="300"></canvas>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4" id="chart2-title">Breakdown</h3>
                <canvas id="chart2" height="300"></canvas>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="data-table-title">Records</h3>
                <button onclick="exportReport()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="data-table">
                    <thead class="bg-gray-50">
                        <tr id="table-header">
                            <!-- Populated by JS -->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="table-body">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let selectedProjectType = null;
let chart1Instance = null;
let chart2Instance = null;

function getIconSVG(iconType) {
    const icons = {
        'wifi': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>',
        'government': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'city': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'computer': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'broadcast': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>',
        'library': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'briefcase': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'education': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>',
        'shield': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        'globe': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
    };
    return icons[iconType] || icons['wifi'];
}

async function selectProjectType(type) {
    selectedProjectType = type;
    
    document.querySelectorAll('.project-type-card').forEach(card => {
        card.classList.remove('border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-200');
    });
    document.querySelector(`[data-type="${type}"]`).classList.remove('border-gray-200');
    document.querySelector(`[data-type="${type}"]`).classList.add('border-blue-500', 'bg-blue-50');
    
    document.getElementById('report-content').classList.remove('hidden');
    document.getElementById('report-content').scrollIntoView({ behavior: 'smooth' });
    
    // Load report data
    const response = await fetch(`../api/reports-typed.php?action=get-report&project_type=${encodeURIComponent(type)}`);
    const result = await response.json();
    
    if (result.success) {
        renderReport(result.data);
    } else {
        alert('Error loading report: ' + result.error);
    }
}

function renderReport(data) {
    const summary = data.summary;
    const charts = data.charts;
    const items = data.activities || data.sites || data.projects || [];
    
    // Render summary cards
    const summaryCards = document.getElementById('summary-cards');
    let cardsHtml = '';
    
    const cardConfig = {
        'EgovPH': [
            { label: 'Total Activities', key: 'total_activities', color: 'blue' },
            { label: 'Total Participants', key: 'total_participants', color: 'green' },
            { label: 'Total Downloads', key: 'total_downloads', color: 'purple' },
            { label: 'Avg Participants', key: 'avg_participants', color: 'orange' }
        ],
        'Free-WIFI for All': [
            { label: 'Total Sites', key: 'total_sites', color: 'blue' },
            { label: 'Avg Bandwidth (Mbps)', key: 'avg_bandwidth', color: 'green' },
            { label: 'Total Users', key: 'total_users', color: 'purple' },
            { label: 'Uptime Rate', key: 'uptime_rate', suffix: '%', color: 'orange' }
        ]
    };
    
    const cards = cardConfig[data.project_type] || [
        { label: 'Total', key: 'total_projects', color: 'blue' },
        { label: 'Completed', key: 'completed', color: 'green' },
        { label: 'Pending', key: 'pending', color: 'orange' }
    ];
    
    cards.forEach(card => {
        const value = summary[card.key] ?? 0;
        cardsHtml += `
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <p class="text-sm text-gray-600">${card.label}</p>
                <p class="text-2xl font-bold text-${card.color}-600 mt-2">${value}${card.suffix || ''}</p>
            </div>
        `;
    });
    summaryCards.innerHTML = cardsHtml;
    
    // Render charts
    if (chart1Instance) chart1Instance.destroy();
    if (chart2Instance) chart2Instance.destroy();
    
    const chartKeys = Object.keys(charts);
    
    if (chartKeys.length > 0) {
        const chart1Data = charts[chartKeys[0]];
        document.getElementById('chart1-title').textContent = chartKeys[0].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        chart1Instance = new Chart(document.getElementById('chart1'), {
            type: 'pie',
            data: {
                labels: chart1Data.labels,
                datasets: [{
                    data: chart1Data.data,
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    if (chartKeys.length > 1) {
        const chart2Data = charts[chartKeys[1]];
        document.getElementById('chart2-title').textContent = chartKeys[1].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        chart2Instance = new Chart(document.getElementById('chart2'), {
            type: 'bar',
            data: {
                labels: chart2Data.labels,
                datasets: [{
                    label: 'Count',
                    data: chart2Data.data,
                    backgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    
    // Render table
    const tableHeader = document.getElementById('table-header');
    const tableBody = document.getElementById('table-body');
    
    if (items.length > 0) {
        const columns = data.project_type === 'EgovPH' 
            ? ['activity_id', 'activity_title', 'activity_type', 'activity_date', 'participants', 'downloads', 'province', 'municipality', 'status']
            : ['site_code', 'site_name', 'municipality', 'province', 'latitude', 'longitude', 'status'];
        
        tableHeader.innerHTML = columns.map(col => 
            `<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${col.replace(/_/g, ' ')}</th>`
        ).join('');
        
        tableBody.innerHTML = items.slice(0, 50).map(item => `
            <tr class="hover:bg-gray-50">
                ${columns.map(col => {
                    let value = item[col] ?? item.custom_data?.[col] ?? '';
                    if (col === 'activity_date' || col === 'activation_date') {
                        value = value ? new Date(value).toLocaleDateString() : '';
                    }
                    return `<td class="px-4 py-3 text-sm text-gray-900">${value}</td>`;
                }).join('')}
            </tr>
        `).join('');
    } else {
        tableHeader.innerHTML = '<th class="px-4 py-3">No data available</th>';
        tableBody.innerHTML = '<tr><td class="px-4 py-3 text-sm text-gray-500">No records found</td></tr>';
    }
}

function exportReport() {
    if (!selectedProjectType) return;
    
    window.location.href = `../api/reports-typed.php?action=export&project_type=${encodeURIComponent(selectedProjectType)}`;
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
