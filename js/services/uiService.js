/**
 * UI Service
 * Handles DOM manipulation, rendering, and UI state management with document fragments
 */

const UIService = {
    currentTab: 'dashboard',
    searchQuery: '',
    isLoading: false,

    init() {
        this.bindTabNavigation();
        this.bindSearchDebounced = debounce(this.handleSearch.bind(this), 300);
    },

    bindTabNavigation() {
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchTab(link.getAttribute('data-tab'));
            });
        });

        document.querySelectorAll('.tab[data-report]').forEach(tab => {
            tab.addEventListener('click', () => {
                this.switchReportTab(tab.getAttribute('data-report'));
            });
        });
    },

    switchTab(tabId) {
        document.querySelectorAll('.nav-links a').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));

        document.querySelector(`.nav-links a[data-tab="${tabId}"]`)?.classList.add('active');
        const tabContent = document.getElementById(tabId);
        if (tabContent) {
            tabContent.classList.add('active');
            this.currentTab = tabId;
        }

        if (tabId === 'reports') {
            // Activate the default summary report sub-tab
            this.switchReportTab('summary');
        } else if (tabId === 'projects') {
            this.renderAllProjectsTable();
        } else if (tabId === 'dashboard') {
            MapService.renderProjects(DataService.getAllProjects());
        }
    },

    switchReportTab(reportId) {
        document.querySelectorAll('[data-report]').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content[id$="-report"]').forEach(c => c.classList.remove('active'));

        document.querySelector(`.tab[data-report="${reportId}"]`)?.classList.add('active');
        const reportContent = document.getElementById(`${reportId}-report`);
        if (reportContent) {
            reportContent.classList.add('active');
        }

        ChartService.updateAllCharts();
        this.renderReportsTables();
    },

    handleSearch(event) {
        const query = event.target.value.trim();
        this.searchQuery = query;

        if (this.currentTab === 'projects') {
            this.renderAllProjectsTable(query);
        }
    },

    updateStats() {
        const stats = DataService.getStats();

        const elements = {
            'total-projects': stats.total,
            'completed-projects': stats.completed,
            'pending-projects': stats.pending
        };

        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }
    },

    renderRecentProjects() {
        const tableBody = document.querySelector('#recent-projects-table tbody');
        if (!tableBody) return;

        const projects = DataService.getAllProjects()
            .sort((a, b) => new Date(b.activationDate) - new Date(a.activationDate))
            .slice(0, 5);

        const fragment = document.createDocumentFragment();

        if (projects.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="6" style="text-align: center; padding: 20px; color: #999;">No projects found</td>`;
            fragment.appendChild(tr);
        } else {
            projects.forEach(project => {
                const tr = document.createElement('tr');
                tr.className = project.status === 'Done' ? 'status-done' : 'status-pending';
                tr.innerHTML = this.createProjectRowHTML(project, false);
                fragment.appendChild(tr);
            });
        }

        tableBody.innerHTML = '';
        tableBody.appendChild(fragment);
    },

    renderAllProjectsTable(query = '') {
        const tableBody = document.querySelector('#projects-table tbody');
        if (!tableBody) return;

        let projects = query
            ? DataService.searchProjects(query)
            : DataService.getAllProjects();

        projects = projects.sort((a, b) =>
            String(a.siteCode).localeCompare(String(b.siteCode))
        );

        const fragment = document.createDocumentFragment();

        if (projects.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="11" style="text-align: center; padding: 30px; color: #999;">
                ${query ? 'No projects match your search' : 'No projects found. Add your first project!'}
            </td>`;
            fragment.appendChild(tr);
        } else {
            projects.forEach(project => {
                const tr = document.createElement('tr');
                tr.className = `status-${project.status.toLowerCase()}`;
                tr.innerHTML = this.createProjectRowHTML(project, true);
                fragment.appendChild(tr);
            });
        }

        tableBody.innerHTML = '';
        tableBody.appendChild(fragment);
    },

    createProjectRowHTML(project, includeActions = false) {
        return `
            <td><strong>${Sanitizer.sanitizeHTML(project.siteCode)}</strong></td>
            <td>${Sanitizer.sanitizeHTML(project.projectName)}</td>
            <td>${Sanitizer.sanitizeHTML(project.siteName)}</td>
            <td>${Sanitizer.sanitizeHTML(project.barangay)}</td>
            <td>${Sanitizer.sanitizeHTML(project.municipality)}</td>
            <td>${Sanitizer.sanitizeHTML(project.province)}</td>
            <td>${Sanitizer.sanitizeHTML(project.district)}</td>
            <td>${project.latitude.toFixed(6)}, ${project.longitude.toFixed(6)}</td>
            <td>${Sanitizer.sanitizeHTML(project.activationDate)}</td>
            <td><span class="status-badge ${project.status.toLowerCase()}">${Sanitizer.sanitizeHTML(project.status)}</span></td>
            ${includeActions ? `
                <td>
                    <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick="viewProject('${Sanitizer.sanitizeHTML(project.siteCode)}')">View</button>
                    <button class="btn btn-accent" style="padding: 5px 10px; font-size: 12px;" onclick="deleteProject('${Sanitizer.sanitizeHTML(project.siteCode)}')">Delete</button>
                </td>
            ` : ''}
        `;
    },

    renderReportsTables() {
        this.renderProvinceDetailsTable();
        this.renderTimelineTable();
        this.renderPendingProjectsTable();
    },

    renderProvinceDetailsTable() {
        const tbody = document.querySelector('#province-details-table tbody');
        if (!tbody) return;

        const projects = DataService.getAllProjects();
        const provinceMap = {};

        projects.forEach(project => {
            if (!provinceMap[project.province]) {
                provinceMap[project.province] = {
                    total: 0,
                    completed: 0,
                    pending: 0,
                    firstDate: project.activationDate,
                    lastDate: project.activationDate
                };
            }

            provinceMap[project.province].total++;

            if (project.status === 'Done') {
                provinceMap[project.province].completed++;
            } else {
                provinceMap[project.province].pending++;
            }

            const projDate = new Date(project.activationDate);
            const firstDate = new Date(provinceMap[project.province].firstDate);
            const lastDate = new Date(provinceMap[project.province].lastDate);

            if (projDate < firstDate) provinceMap[project.province].firstDate = project.activationDate;
            if (projDate > lastDate) provinceMap[project.province].lastDate = project.activationDate;
        });

        const fragment = document.createDocumentFragment();

        Object.entries(provinceMap).forEach(([province, data]) => {
            const completionRate = data.total > 0 ? Math.round((data.completed / data.total) * 100) : 0;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${Sanitizer.sanitizeHTML(province)}</strong></td>
                <td>${data.total}</td>
                <td style="color: #27ae60;">${data.completed}</td>
                <td style="color: #f39c12;">${data.pending}</td>
                <td><span class="status-badge ${completionRate >= 70 ? 'done' : 'pending'}">${completionRate}%</span></td>
                <td>${Sanitizer.sanitizeHTML(data.firstDate)}</td>
                <td>${Sanitizer.sanitizeHTML(data.lastDate)}</td>
            `;
            fragment.appendChild(tr);
        });

        tbody.innerHTML = '';
        tbody.appendChild(fragment);
    },

    renderTimelineTable() {
        const tbody = document.querySelector('#timeline-table tbody');
        if (!tbody) return;

        const projects = DataService.getAllProjects();
        const timelineMap = {};

        projects.forEach(project => {
            const date = new Date(project.activationDate);
            const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;

            if (!timelineMap[key]) {
                timelineMap[key] = { newProjects: 0, completed: 0, pending: 0, cumulative: 0 };
            }

            timelineMap[key].newProjects++;
            if (project.status === 'Done') {
                timelineMap[key].completed++;
            } else {
                timelineMap[key].pending++;
            }
        });

        const sortedKeys = Object.keys(timelineMap).sort();
        let cumulative = 0;
        const fragment = document.createDocumentFragment();

        sortedKeys.forEach(key => {
            cumulative += timelineMap[key].newProjects;
            timelineMap[key].cumulative = cumulative;

            const [year, month] = key.split('-');
            const label = new Date(year, month - 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${label}</strong></td>
                <td>${timelineMap[key].newProjects}</td>
                <td style="color: #27ae60;">${timelineMap[key].completed}</td>
                <td style="color: #f39c12;">${timelineMap[key].pending}</td>
                <td><strong>${cumulative}</strong></td>
            `;
            fragment.appendChild(tr);
        });

        tbody.innerHTML = '';
        tbody.appendChild(fragment);
    },

    renderPendingProjectsTable() {
        const tbody = document.querySelector('#pending-projects-table tbody');
        if (!tbody) return;

        const pendingProjects = DataService.getProjectsByStatus('Pending')
            .sort((a, b) => new Date(b.activationDate) - new Date(a.activationDate));

        const fragment = document.createDocumentFragment();

        if (pendingProjects.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="7" style="text-align: center; padding: 30px; color: #27ae60;">
                All projects are completed! 🎉
            </td>`;
            fragment.appendChild(tr);
        } else {
            pendingProjects.forEach(project => {
                const daysPending = Math.floor(
                    (new Date() - new Date(project.activationDate)) / (1000 * 60 * 60 * 24)
                );

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${Sanitizer.sanitizeHTML(project.siteCode)}</strong></td>
                    <td>${Sanitizer.sanitizeHTML(project.projectName)}</td>
                    <td>${Sanitizer.sanitizeHTML(project.siteName)}</td>
                    <td>${Sanitizer.sanitizeHTML(project.barangay)}, ${Sanitizer.sanitizeHTML(project.municipality)}</td>
                    <td>${Sanitizer.sanitizeHTML(project.activationDate)}</td>
                    <td><span class="status-badge pending">${daysPending} days</span></td>
                    <td>
                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;"
                                onclick="viewProject('${Sanitizer.sanitizeHTML(project.siteCode)}')">View</button>
                    </td>
                `;
                fragment.appendChild(tr);
            });
        }

        tbody.innerHTML = '';
        tbody.appendChild(fragment);
    },

    showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '<div class="loading"></div> Loading...';
        }
    },

    hideLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element && element.querySelector('.loading')) {
            element.innerHTML = '';
        }
    },

    showToast(message, duration = 3000) {
        const toastId = 'toast-notification';

        let toast = document.getElementById(toastId);
        if (toast) {
            toast.remove();
        }

        toast = document.createElement('div');
        toast.id = toastId;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #2c3e50;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: fadeIn 0.3s ease;
            max-width: 350px;
            word-wrap: break-word;
        `;
        toast.textContent = message;

        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(20px); }
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    showError(message) {
        this.showToast(`Error: ${message}`, 5000);
    },

    showSuccess(message) {
        this.showToast(message, 3000);
    }
};
