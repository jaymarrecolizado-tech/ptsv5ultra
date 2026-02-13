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
            <a href="./pages/project-form.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
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

<script>
// Pagination state
let currentPage = 1;
let perPage = 25;
let totalPages = 1;

// Initialize
loadProjectsTable();
loadProvinceOptions();

// Setup filters
document.getElementById('search-input').addEventListener('input', debounce(() => {
    currentPage = 1;
    loadProjectsTable();
}, 300));

document.getElementById('status-filter').addEventListener('change', () => {
    currentPage = 1;
    loadProjectsTable();
});

document.getElementById('province-filter').addEventListener('change', () => {
    currentPage = 1;
    loadProjectsTable();
});

async function loadProjectsTable() {
    try {
        const search = document.getElementById('search-input')?.value || '';
        const status = document.getElementById('status-filter')?.value || 'all';
        const province = document.getElementById('province-filter')?.value || 'all';
        
        const filters = { page: currentPage, per_page: perPage };
        if (search) filters.search = search;
        if (status !== 'all') filters.status = status;
        if (province !== 'all') filters.province = province;
        
        const response = await API.projects.getAll(filters);
        if (response.success && response.data) {
            const { projects, pagination } = response.data;
            currentPage = pagination.current_page;
            totalPages = pagination.total_pages;
            
            renderProjectsTable(projects);
            renderPagination(pagination);
        }
    } catch (error) {
        console.error('Failed to load projects:', error);
        document.getElementById('projects-body').innerHTML = `
            <tr><td colspan="11" class="px-4 py-8 text-center text-red-500">Failed to load projects</td></tr>
        `;
    }
}

function renderProjectsTable(projects) {
    const tbody = document.getElementById('projects-body');
    
    if (projects.length === 0) {
        tbody.innerHTML = `<tr><td colspan="11" class="px-4 py-8 text-center text-gray-500">No projects found</td></tr>`;
        return;
    }
    
    tbody.innerHTML = projects.map(project => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-900 font-medium">${project.site_code}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${project.project_name}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${project.site_name}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.barangay}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.municipality}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.province}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.district}</td>
            <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">${parseFloat(project.latitude).toFixed(4)}, ${parseFloat(project.longitude).toFixed(4)}</td>
            <td class="px-4 py-3 text-sm text-gray-500">${project.activation_date_formatted}</td>
            <td class="px-4 py-3">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${project.status === 'Done' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">
                    ${project.status}
                </span>
            </td>
            <td class="px-4 py-3">
                <div class="flex gap-2">
                    <a href="./pages/project-form.php?id=${project.id}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                    <button onclick="deleteProject(${project.id})" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function renderPagination(pagination) {
    const { current_page, total_pages, total_count, per_page } = pagination;
    
    // Update count text
    const start = (current_page - 1) * per_page + 1;
    const end = Math.min(current_page * per_page, total_count);
    document.getElementById('projects-count').textContent = 
        `Showing ${start}-${end} of ${total_count} projects (Page ${current_page} of ${total_pages})`;
    
    // Build pagination controls
    let html = '';
    
    // Previous button
    html += `<button onclick="goToPage(${current_page - 1})" ${current_page <= 1 ? 'disabled' : ''} 
             class="px-3 py-1 rounded border ${current_page <= 1 ? 'bg-gray-100 text-gray-400' : 'bg-white hover:bg-gray-50'} text-sm">Previous</button>`;
    
    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, current_page - Math.floor(maxVisible / 2));
    let endPage = Math.min(total_pages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
        html += `<button onclick="goToPage(1)" class="px-3 py-1 rounded border bg-white hover:bg-gray-50 text-sm">1</button>`;
        if (startPage > 2) html += `<span class="px-2">...</span>`;
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button onclick="goToPage(${i})" 
                 class="px-3 py-1 rounded border text-sm ${i === current_page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50'}">${i}</button>`;
    }
    
    if (endPage < total_pages) {
        if (endPage < total_pages - 1) html += `<span class="px-2">...</span>`;
        html += `<button onclick="goToPage(${total_pages})" class="px-3 py-1 rounded border bg-white hover:bg-gray-50 text-sm">${total_pages}</button>`;
    }
    
    // Next button
    html += `<button onclick="goToPage(${current_page + 1})" ${current_page >= total_pages ? 'disabled' : ''} 
             class="px-3 py-1 rounded border ${current_page >= total_pages ? 'bg-gray-100 text-gray-400' : 'bg-white hover:bg-gray-50'} text-sm">Next</button>`;
    
    // Per page selector
    html += `<select onchange="changePerPage(this.value)" class="ml-4 px-2 py-1 border rounded text-sm">
        <option value="10" ${per_page === 10 ? 'selected' : ''}>10 per page</option>
        <option value="25" ${per_page === 25 ? 'selected' : ''}>25 per page</option>
        <option value="50" ${per_page === 50 ? 'selected' : ''}>50 per page</option>
        <option value="100" ${per_page === 100 ? 'selected' : ''}>100 per page</option>
    </select>`;
    
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
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
