<?php
/**
 * Reports Page
 */

$pageTitle = 'Reports';
$activeTab = 'reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Report Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2">
        <div class="flex flex-wrap gap-2">
            <button onclick="switchReport('summary')" id="tab-summary" class="report-tab px-4 py-2 rounded-lg font-medium transition-colors bg-blue-600 text-white">
                Summary Report
            </button>
            <button onclick="switchReport('province')" id="tab-province" class="report-tab px-4 py-2 rounded-lg font-medium transition-colors text-gray-600 hover:bg-gray-100">
                Province Analysis
            </button>
            <button onclick="switchReport('timeline')" id="tab-timeline" class="report-tab px-4 py-2 rounded-lg font-medium transition-colors text-gray-600 hover:bg-gray-100">
                Timeline Report
            </button>
            <button onclick="switchReport('status')" id="tab-status" class="report-tab px-4 py-2 rounded-lg font-medium transition-colors text-gray-600 hover:bg-gray-100">
                Status Report
            </button>
        </div>
    </div>
    
    <!-- Summary Report -->
    <div id="report-summary" class="report-content">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="summary-status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All</option>
                        <option value="Done">Completed</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                    <select id="summary-province-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All Provinces</option>
                    </select>
                </div>
                <button onclick="loadSummaryReport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 text-center">
                <p class="text-3xl font-bold text-blue-600" id="report-total">0</p>
                <p class="text-gray-500 mt-1">Total Projects</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 text-center">
                <p class="text-3xl font-bold text-green-600" id="report-completed">0</p>
                <p class="text-gray-500 mt-1">Completed Projects</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 text-center">
                <p class="text-3xl font-bold text-orange-600" id="report-pending">0</p>
                <p class="text-gray-500 mt-1">Pending Projects</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 text-center">
                <p class="text-3xl font-bold text-purple-600" id="report-provinces">0</p>
                <p class="text-gray-500 mt-1">Provinces Covered</p>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Projects by Province</h3>
                <div class="h-80">
                    <canvas id="provinceChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Projects by Status</h3>
                <div class="h-80">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Province Analysis Report -->
    <div id="report-province" class="report-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="province-status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All</option>
                        <option value="Done">Completed</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                    <select id="province-province-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All Provinces</option>
                    </select>
                </div>
                <button onclick="loadProvinceReport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Province Implementation Details</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Province</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Projects</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completion Rate</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">First Implementation</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Implementation</th>
                        </tr>
                    </thead>
                    <tbody id="province-table-body" class="divide-y divide-gray-200">
                        <!-- Filled by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Completion Rate by Province</h3>
            <div class="h-80">
                <canvas id="provinceDetailedChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Timeline Report -->
    <div id="report-timeline" class="report-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="timeline-status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All</option>
                        <option value="Done">Completed</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <div class="flex items-center gap-2">
                        <input type="date" id="timeline-date-from" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <span>to</span>
                        <input type="date" id="timeline-date-to" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                <button onclick="loadTimelineReport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Implementation Progress</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month/Year</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Projects</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cumulative Total</th>
                        </tr>
                    </thead>
                    <tbody id="timeline-table-body" class="divide-y divide-gray-200">
                        <!-- Filled by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Timeline Visualization</h3>
            <div class="h-80">
                <canvas id="timelineDetailedChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Status Report -->
    <div id="report-status" class="report-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                    <select id="status-province-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="all">All Provinces</option>
                    </select>
                </div>
                <button onclick="loadStatusReport()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pending Projects by Duration</h3>
                <div class="h-80">
                    <canvas id="pendingDurationChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Completion Rate by Province</h3>
                <div class="h-80">
                    <canvas id="completionRateChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pending Projects Requiring Attention</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Site Code</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Site Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date of Activation</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending Duration</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pending-table-body" class="divide-y divide-gray-200">
                        <!-- Filled by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
