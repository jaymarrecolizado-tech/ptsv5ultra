/**
 * Main Application Logic
 */

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize page-specific functionality
    initPage();
    
    // Setup export button
    const exportBtn = document.getElementById('export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            API.import.export();
        });
    }
    
    // Setup refresh button
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            location.reload();
        });
    }
    
    // Update sidebar stats
    updateSidebarStats();
    
    // Setup province autocomplete if on project form
    if (document.getElementById('province')) {
        Validator.setupProvinceAutocomplete('province', 'province-suggestions');
    }
});

/**
 * Initialize page-specific functionality
 */
function initPage() {
    const path = window.location.pathname;
    
    if (path.includes('dashboard.php')) {
        initDashboard();
    } else if (path.includes('projects.php')) {
        initProjectsList();
    } else if (path.includes('project-form.php')) {
        initProjectForm();
    } else if (path.includes('import.php')) {
        initImportPage();
    } else if (path.includes('reports.php')) {
        initReportsPage();
    }
}

/**
 * Initialize Dashboard
 */
function initDashboard() {
    // Initialize map
    MapService.init('map');
    MapService.loadProjects();
    
    // Load stats
    loadDashboardStats();
    
    // Load recent projects
    loadRecentProjects();
}

/**
 * Load dashboard stats
 */
async function loadDashboardStats() {
    try {
        const response = await API.projects.getStats();
        if (response.success && response.data) {
            const stats = response.data;
            
            // Update stat cards
            const totalEl = document.getElementById('stat-total');
            const completedEl = document.getElementById('stat-completed');
            const pendingEl = document.getElementById('stat-pending');
            const provincesEl = document.getElementById('stat-provinces');
            
            if (totalEl) totalEl.textContent = stats.total;
            if (completedEl) completedEl.textContent = stats.completed;
            if (pendingEl) pendingEl.textContent = stats.pending;
            if (provincesEl) provincesEl.textContent = stats.provinces;
            
            // Load charts
            loadDashboardCharts();
        }
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
    }
}

/**
 * Load dashboard charts
 */
async function loadDashboardCharts() {
    try {
        // Load status chart
        const statusResponse = await API.reports.summary();
        if (statusResponse.success && statusResponse.data) {
            const byStatus = statusResponse.data.by_status;
            if (byStatus && byStatus.length > 0) {
                ChartService.createStatusChart('statusChart', byStatus);
            }
            
            // Load timeline chart
            const timelineResponse = await API.reports.timeline();
            if (timelineResponse.success && timelineResponse.data) {
                const timeline = timelineResponse.data.timeline;
                if (timeline && timeline.length > 0) {
                    ChartService.createTimelineChart('timelineChart', timeline);
                }
            }
        }
    } catch (error) {
        console.error('Failed to load dashboard charts:', error);
    }
}

/**
 * Load recent projects
 */
async function loadRecentProjects() {
    try {
        const response = await API.projects.getAll({ limit: 5 });
        if (response.success && response.data) {
            const tbody = document.getElementById('recent-projects-body');
            if (!tbody) return;
            
            tbody.innerHTML = response.data.slice(0, 5).map(project => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">${project.site_code}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${project.project_name}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${project.site_name}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">${project.municipality}, ${project.province}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">${project.activation_date_formatted}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${project.status === 'Done' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">
                            ${project.status}
                        </span>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load recent projects:', error);
    }
}

/**
 * Initialize Projects List Page
 */
function initProjectsList() {
    loadProjectsTable();
    loadProvinceOptions();
    
    // Setup filters
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const provinceFilter = document.getElementById('province-filter');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            loadProjectsTable();
        }, 300));
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', () => loadProjectsTable());
    }
    
    if (provinceFilter) {
        provinceFilter.addEventListener('change', () => loadProjectsTable());
    }
}

/**
 * Load projects table
 */
async function loadProjectsTable() {
    try {
        const search = document.getElementById('search-input')?.value || '';
        const status = document.getElementById('status-filter')?.value || 'all';
        const province = document.getElementById('province-filter')?.value || 'all';
        
        const filters = {};
        if (search) filters.search = search;
        if (status !== 'all') filters.status = status;
        if (province !== 'all') filters.province = province;
        
        const response = await API.projects.getAll(filters);
        if (response.success && response.data) {
            const tbody = document.getElementById('projects-body');
            if (!tbody) return;
            
            if (response.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                            No projects found
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = response.data.map(project => `
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
                                <a href="/projects/newPTS/pages/project-form.php?id=${project.id}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                                <button onclick="deleteProject(${project.id})" 
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
            
            // Update count
            const countEl = document.getElementById('projects-count');
            if (countEl) {
                countEl.textContent = `Showing ${response.data.length} projects`;
            }
        }
    } catch (error) {
        console.error('Failed to load projects:', error);
        const tbody = document.getElementById('projects-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="px-4 py-8 text-center text-red-500">
                        Failed to load projects. Please try again.
                    </td>
                </tr>
            `;
        }
    }
}

/**
 * Load province options for filters
 */
async function loadProvinceOptions() {
    try {
        const response = await API.reports.provincesList();
        if (response.success && response.data) {
            const provinces = response.data.provinces;
            
            // Update all province filters on the page
            document.querySelectorAll('select[id$="-province-filter"]').forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="all">All Provinces</option>' +
                    provinces.map(p => `<option value="${p}">${p}</option>`).join('');
                select.value = currentValue;
            });
        }
    } catch (error) {
        console.error('Failed to load provinces:', error);
    }
}

/**
 * Delete project
 */
async function deleteProject(id) {
    if (!confirm('Are you sure you want to delete this project?')) {
        return;
    }
    
    try {
        await API.projects.delete(id);
        loadProjectsTable();
        updateSidebarStats();
    } catch (error) {
        alert('Failed to delete project: ' + error.message);
    }
}

/**
 * Initialize Project Form
 */
function initProjectForm() {
    const form = document.getElementById('project-form');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Get form data
        const data = {
            site_code: document.getElementById('site-code').value,
            project_name: document.getElementById('project-name').value,
            site_name: document.getElementById('site-name').value,
            barangay: document.getElementById('barangay').value,
            municipality: document.getElementById('municipality').value,
            province: document.getElementById('province').value,
            district: document.getElementById('district').value,
            latitude: document.getElementById('latitude').value,
            longitude: document.getElementById('longitude').value,
            activation_date: document.getElementById('activation-date').value,
            status: document.getElementById('status').value,
            notes: document.getElementById('notes').value
        };
        
        // Validate
        const errors = Validator.validateProjectForm(data);
        if (errors.length > 0) {
            Validator.showErrors(errors);
            return;
        }
        
        Validator.clearErrors();
        
        try {
            const projectId = document.getElementById('project-id').value;
            
            if (projectId) {
                // Update existing
                await API.projects.update(projectId, data);
                alert('Project updated successfully');
            } else {
                // Create new
                await API.projects.create(data);
                alert('Project created successfully');
                form.reset();
            }
            
            // Redirect to projects list
            window.location.href = '/projects/newPTS/pages/projects.php';
        } catch (error) {
            Validator.showErrors([error.message]);
        }
    });
}

/**
 * Initialize Import Page
 */
function initImportPage() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    
    if (!dropZone || !fileInput) return;
    
    // Click to select
    dropZone.addEventListener('click', () => fileInput.click());
    
    // File selection
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileUpload(e.target.files[0]);
        }
    });
    
    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        
        if (e.dataTransfer.files.length > 0) {
            handleFileUpload(e.dataTransfer.files[0]);
        }
    });
}

/**
 * Handle file upload
 */
async function handleFileUpload(file) {
    const progressDiv = document.getElementById('upload-progress');
    const resultsDiv = document.getElementById('import-results');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    progressDiv.classList.remove('hidden');
    resultsDiv.classList.add('hidden');
    
    const formData = new FormData();
    formData.append('csv_file', file);
    
    try {
        // Simulate progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 10;
            if (progress <= 90) {
                progressBar.style.width = progress + '%';
                progressText.textContent = progress + '%';
            }
        }, 100);
        
        const response = await API.import.upload(formData);
        
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        progressText.textContent = '100%';
        
        // Show results
        document.getElementById('result-imported').textContent = response.data.imported;
        document.getElementById('result-total').textContent = response.data.total_rows;
        document.getElementById('result-errors').textContent = response.data.errors.length;
        
        // Show error details if any
        const errorDetails = document.getElementById('error-details');
        const errorList = document.getElementById('error-list');
        
        if (response.data.errors.length > 0) {
            errorList.innerHTML = response.data.errors.slice(0, 10).map(e => 
                `<li>Row ${e.row}: ${e.message}</li>`
            ).join('');
            
            if (response.data.errors.length > 10) {
                errorList.innerHTML += `<li>... and ${response.data.errors.length - 10} more errors</li>`;
            }
            
            errorDetails.classList.remove('hidden');
        } else {
            errorDetails.classList.add('hidden');
        }
        
        resultsDiv.classList.remove('hidden');
        
        if (response.data.imported > 0) {
            updateSidebarStats();
        }
    } catch (error) {
        alert('Import failed: ' + error.message);
        progressDiv.classList.add('hidden');
    }
}

/**
 * Initialize Reports Page
 */
function initReportsPage() {
    loadProvinceOptions();
    loadSummaryReport();
}

/**
 * Switch report tab
 */
function switchReport(reportType) {
    // Update tabs
    document.querySelectorAll('.report-tab').forEach(tab => {
        tab.classList.remove('bg-blue-600', 'text-white');
        tab.classList.add('text-gray-600', 'hover:bg-gray-100');
    });
    
    const activeTab = document.getElementById('tab-' + reportType);
    if (activeTab) {
        activeTab.classList.remove('text-gray-600', 'hover:bg-gray-100');
        activeTab.classList.add('bg-blue-600', 'text-white');
    }
    
    // Show content
    document.querySelectorAll('.report-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    const activeContent = document.getElementById('report-' + reportType);
    if (activeContent) {
        activeContent.classList.remove('hidden');
    }
    
    // Load report data
    switch(reportType) {
        case 'summary':
            loadSummaryReport();
            break;
        case 'province':
            loadProvinceReport();
            break;
        case 'timeline':
            loadTimelineReport();
            break;
        case 'status':
            loadStatusReport();
            break;
    }
}

/**
 * Load summary report
 */
async function loadSummaryReport() {
    try {
        const status = document.getElementById('summary-status-filter')?.value || 'all';
        const province = document.getElementById('summary-province-filter')?.value || 'all';
        
        const filters = {};
        if (status !== 'all') filters.status = status;
        if (province !== 'all') filters.province = province;
        
        const response = await API.reports.summary(filters);
        if (response.success && response.data) {
            const stats = response.data.stats;
            
            document.getElementById('report-total').textContent = stats.total;
            document.getElementById('report-completed').textContent = stats.completed;
            document.getElementById('report-pending').textContent = stats.pending;
            document.getElementById('report-provinces').textContent = stats.provinces;
            
            // Update charts
            if (response.data.by_province && response.data.by_province.length > 0) {
                ChartService.createProvinceChart('provinceChart', response.data.by_province);
            }
            
            if (response.data.by_status && response.data.by_status.length > 0) {
                ChartService.createStatusDistributionChart('statusDistributionChart', response.data.by_status);
            }
        }
    } catch (error) {
        console.error('Failed to load summary report:', error);
    }
}

/**
 * Load province report
 */
async function loadProvinceReport() {
    try {
        const status = document.getElementById('province-status-filter')?.value || 'all';
        const province = document.getElementById('province-province-filter')?.value || 'all';
        
        const filters = {};
        if (status !== 'all') filters.status = status;
        if (province !== 'all') filters.province = province;
        
        const response = await API.reports.province(filters);
        if (response.success && response.data) {
            const tbody = document.getElementById('province-table-body');
            if (tbody) {
                tbody.innerHTML = response.data.provinces.map(p => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${p.province}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${p.total}</td>
                        <td class="px-4 py-3 text-sm text-green-600">${p.completed}</td>
                        <td class="px-4 py-3 text-sm text-orange-600">${p.pending}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${p.completion_rate}%</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${p.first_implementation}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${p.last_implementation}</td>
                    </tr>
                `).join('');
            }
            
            // Update chart
            if (response.data.provinces && response.data.provinces.length > 0) {
                ChartService.createStackedProvinceChart('provinceDetailedChart', response.data.provinces);
            }
        }
    } catch (error) {
        console.error('Failed to load province report:', error);
    }
}

/**
 * Load timeline report
 */
async function loadTimelineReport() {
    try {
        const status = document.getElementById('timeline-status-filter')?.value || 'all';
        const dateFrom = document.getElementById('timeline-date-from')?.value || '';
        const dateTo = document.getElementById('timeline-date-to')?.value || '';
        
        const filters = {};
        if (status !== 'all') filters.status = status;
        if (dateFrom) filters.date_from = dateFrom;
        if (dateTo) filters.date_to = dateTo;
        
        const response = await API.reports.timeline(filters);
        if (response.success && response.data) {
            const tbody = document.getElementById('timeline-table-body');
            if (tbody) {
                tbody.innerHTML = response.data.timeline.map(t => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${t.month_formatted}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${t.new_projects}</td>
                        <td class="px-4 py-3 text-sm text-green-600">${t.completed}</td>
                        <td class="px-4 py-3 text-sm text-orange-600">${t.pending}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">${t.cumulative}</td>
                    </tr>
                `).join('');
            }
            
            // Update chart
            if (response.data.timeline && response.data.timeline.length > 0) {
                ChartService.createMultiLineChart('timelineDetailedChart', response.data.timeline);
            }
        }
    } catch (error) {
        console.error('Failed to load timeline report:', error);
    }
}

/**
 * Load status report
 */
async function loadStatusReport() {
    try {
        const province = document.getElementById('status-province-filter')?.value || 'all';
        
        const filters = {};
        if (province !== 'all') filters.province = province;
        
        const response = await API.reports.status(filters);
        if (response.success && response.data) {
            // Update charts
            if (response.data.pending_by_duration && response.data.pending_by_duration.length > 0) {
                ChartService.createPendingDurationChart('pendingDurationChart', response.data.pending_by_duration);
            }
            
            if (response.data.completion_rates && response.data.completion_rates.length > 0) {
                ChartService.createCompletionRateChart('completionRateChart', response.data.completion_rates);
            }
            
            // Update pending projects table
            const tbody = document.getElementById('pending-table-body');
            if (tbody) {
                tbody.innerHTML = response.data.pending_projects.map(p => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${p.site_code}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${p.project_name}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${p.site_name}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${p.barangay}, ${p.municipality}, ${p.province}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">${p.activation_date_formatted}</td>
                        <td class="px-4 py-3 text-sm text-orange-600 font-medium">${p.pending_days} days</td>
                        <td class="px-4 py-3">
                            <a href="/projects/newPTS/pages/project-form.php?id=${p.id}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</a>
                        </td>
                    </tr>
                `).join('');
            }
        }
    } catch (error) {
        console.error('Failed to load status report:', error);
    }
}

/**
 * Update sidebar stats
 */
async function updateSidebarStats() {
    try {
        const response = await API.projects.getStats();
        if (response.success && response.data) {
            const stats = response.data;
            
            const totalEl = document.getElementById('sidebar-total');
            const completedEl = document.getElementById('sidebar-completed');
            const pendingEl = document.getElementById('sidebar-pending');
            
            if (totalEl) totalEl.textContent = stats.total;
            if (completedEl) completedEl.textContent = stats.completed;
            if (pendingEl) pendingEl.textContent = stats.pending;
        }
    } catch (error) {
        console.error('Failed to update sidebar stats:', error);
    }
}

/**
 * Debounce function
 */
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
