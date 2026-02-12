<?php
/**
 * Projects List Page
 */

$pageTitle = 'All Projects';
$activeTab = 'projects';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" id="search-input" placeholder="Search projects..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="all">All Status</option>
                    <option value="Done">Completed</option>
                    <option value="Pending">Pending</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <select id="province-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="all">All Provinces</option>
                </select>
            </div>
            <a href="/projects/newPTS/pages/project-form.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Project
            </a>
        </div>
    </div>
    
    <!-- Projects Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Site Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Site Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barangay</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Municipality</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Province</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">District</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coordinates</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activation Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="projects-body" class="divide-y divide-gray-200">
                    <!-- Filled by JavaScript -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 flex justify-between items-center">
            <span class="text-sm text-gray-500" id="projects-count">Loading...</span>
            <div class="flex gap-2" id="pagination">
                <!-- Filled by JavaScript -->
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
