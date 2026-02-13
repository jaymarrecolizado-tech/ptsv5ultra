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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Type</label>
                <select id="project-type-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="all">All Types</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" id="date-from-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" id="date-to-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
            <button onclick="resetFilters()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset
            </button>
            <a href="./project-form.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Encoded By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="projects-body" class="divide-y divide-gray-200">
                    <!-- Filled by JavaScript -->
                </tbody>
            </table>
        </div>
        
<!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200" id="pagination">
            <!-- Filled by JavaScript -->
        </div>
    </div>
</div>

<script>
// Pagination state
let currentPage = 1;
let perPage = 25; // Default 25 items per page
let totalPages = 1;

// Initialize
loadProjectsTable();
loadProvinceOptions();
loadProjectTypeOptions();

// Filter element references
const searchInput = document.getElementById('search-input');
const statusFilter = document.getElementById('status-filter');
const provinceFilter = document.getElementById('province-filter');
const projectTypeFilter = document.getElementById('project-type-filter');
const dateFromFilter = document.getElementById('date-from-filter');
const dateToFilter = document.getElementById('date-to-filter');

// Setup filter event listeners
if (searchInput) {
    searchInput.addEventListener('input', debounce(function() {
        currentPage = 1;
        loadProjectsTable();
    }, 300));
}

if (statusFilter) {
    statusFilter.addEventListener('change', function() {
        currentPage = 1;
        loadProjectsTable();
    });
}

if (provinceFilter) {
    provinceFilter.addEventListener('change', function() {
        currentPage = 1;
        loadProjectsTable();
    });
}

if (projectTypeFilter) {
    projectTypeFilter.addEventListener('change', function() {
        currentPage = 1;
        loadProjectsTable();
    });
}

if (dateFromFilter) {
    dateFromFilter.addEventListener('change', function() {
        currentPage = 1;
        loadProjectsTable();
    });
}

if (dateToFilter) {
    dateToFilter.addEventListener('change', function() {
        currentPage = 1;
        loadProjectsTable();
    });
}

async function loadProjectsTable() {
    try {
        const search = document.getElementById('search-input')?.value || '';
        const status = document.getElementById('status-filter')?.value || 'all';
        const province = document.getElementById('province-filter')?.value || 'all';
        const projectType = document.getElementById('project-type-filter')?.value || 'all';
        const dateFrom = document.getElementById('date-from-filter')?.value || '';
        const dateTo = document.getElementById('date-to-filter')?.value || '';
        
        const filters = { page: currentPage, per_page: perPage };
        if (search) filters.search = search;
        if (status !== 'all') filters.status = status;
        if (province !== 'all') filters.province = province;
        if (projectType !== 'all') filters.project_type = projectType;
        if (dateFrom) filters.date_from = dateFrom;
        if (dateTo) filters.date_to = dateTo;
        
        const response = await API.projects.getAll(filters);
        
        if (response.success && response.data) {
            const { projects, pagination } = response.data;
            currentPage = pagination.current_page;
            totalPages = pagination.total_pages;
            perPage = pagination.per_page;
            
            renderProjectsTable(projects);
            renderPagination(pagination);
        }
    } catch (error) {
        console.error('Failed to load projects:', error);
        document.getElementById('projects-body').innerHTML = `
            <tr><td colspan="8" class="px-4 py-8 text-center text-red-500">Failed to load projects: ${error.message}</td></tr>
        `;
    }
}

function renderProjectsTable(projects) {
    const tbody = document.getElementById('projects-body');
    
    if (!projects || projects.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No projects found</td></tr>`;
        return;
    }
    
    tbody.innerHTML = projects.map(project => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-900 font-medium">${project.site_code || ''}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${project.project_name || ''}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${project.site_name || ''}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.municipality || ''}, ${project.province || ''}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.activation_date_formatted || ''}</td>
            <td class="px-4 py-3">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${project.status === 'Done' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">
                    ${project.status || 'Pending'}
                </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">
                <span class="font-medium">${project.created_by_name || 'System'}</span>
                <span class="text-xs text-gray-400 block">${project.created_at ? new Date(project.created_at).toLocaleDateString() : ''}</span>
            </td>
            <td class="px-4 py-3">
                <div class="flex gap-2">
                    <a href="project-form.php?id=${project.id}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                    <button onclick="deleteProject(${project.id})" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(pagination) {
    const { current_page, total_pages, total_count, per_page } = pagination;
    
    const start = (current_page - 1) * perPage + 1;
    const end = Math.min(current_page * perPage, total_count);
    
    // Build clean pagination with Previous/Next and page info
    let html = `
        <div class="flex items-center justify-between w-full">
            <div class="text-sm text-gray-600">
                Showing <span class="font-medium">${start}-${end}</span> of <span class="font-medium">${total_count}</span> projects
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Items per page -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Show</span>
                    <select onchange="changePerPage(this.value)" class="px-2 py-1 border rounded text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="25" ${perPage == 25 ? 'selected' : ''}>25</option>
                        <option value="50" ${perPage == 50 ? 'selected' : ''}>50</option>
                        <option value="100" ${perPage == 100 ? 'selected' : ''}>100</option>
                    </select>
                    <span class="text-sm text-gray-500">per page</span>
                </div>
                
                <!-- Navigation -->
                <div class="flex items-center gap-1 border-l pl-3">
                    <button onclick="goToPage(1)" ${current_page <= 1 ? 'disabled' : ''}
                            class="px-3 py-1.5 rounded text-sm font-medium ${current_page <= 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-300 text-gray-700'}">
                        First
                    </button>
                    
                    <button onclick="goToPage(${current_page - 1})" ${current_page <= 1 ? 'disabled' : ''}
                            class="px-3 py-1.5 rounded text-sm font-medium ${current_page <= 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-300 text-gray-700'}">
                        ← Previous
                    </button>
                    
                    <span class="px-3 py-1.5 text-sm text-gray-700 font-medium">
                        Page <span class="text-blue-600">${current_page}</span> of <span class="text-blue-600">${total_pages}</span>
                    </span>
                    
                    <button onclick="goToPage(${current_page + 1})" ${current_page >= total_pages ? 'disabled' : ''}
                            class="px-3 py-1.5 rounded text-sm font-medium ${current_page >= total_pages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-300 text-gray-700'}">
                        Next →
                    </button>
                    
                    <button onclick="goToPage(${total_pages})" ${current_page >= total_pages ? 'disabled' : ''}
                            class="px-3 py-1.5 rounded text-sm font-medium ${current_page >= total_pages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-300 text-gray-700'}">
                        Last
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('pagination').innerHTML = html;
}

function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadProjectsTable();
}

function changePerPage(value) {
    perPage = parseInt(value);
    currentPage = 1;
    loadProjectsTable();
}

async function loadProvinceOptions() {
    try {
        const response = await API.reports.provincesList();
        if (response.success && response.data) {
            const provinces = response.data.provinces;
            const select = document.getElementById('province-filter');
            select.innerHTML = '<option value="all">All Provinces</option>' +
                provinces.map(p => `<option value="${p}">${p}</option>`).join('');
        }
    } catch (error) {
        console.error('Failed to load provinces:', error);
    }
}

async function loadProjectTypeOptions() {
    try {
        const response = await API.get('/projects.php?action=project_types');
        if (response.success && response.data) {
            const types = response.data;
            const select = document.getElementById('project-type-filter');
            select.innerHTML = '<option value="all">All Types</option>' +
                types.map(t => `<option value="${t}">${t}</option>`).join('');
        }
    } catch (error) {
        // If API doesn't exist, use hardcoded list
        const types = ['Free-WIFI for All', 'Tech4ED', 'Cybersecurity Awareness', 'Digital Cities Program', 'National Broadband Program', 'e-Government Services', 'ICT Literacy Programs', 'PIPOL', 'Rural Impact Sourcing', 'Free Internet in Public Places'];
        const select = document.getElementById('project-type-filter');
        select.innerHTML = '<option value="all">All Types</option>' +
            types.map(t => `<option value="${t}">${t}</option>`).join('');
    }
}

async function deleteProject(id) {
    if (!confirm('Are you sure you want to delete this project?')) return;
    
    try {
        await API.projects.delete(id);
        loadProjectsTable();
    } catch (error) {
        alert('Failed to delete project: ' + error.message);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = 'all';
    document.getElementById('province-filter').value = 'all';
    document.getElementById('project-type-filter').value = 'all';
    document.getElementById('date-from-filter').value = '';
    document.getElementById('date-to-filter').value = '';
    currentPage = 1;
    loadProjectsTable();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
