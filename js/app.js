/**
 * Project Implementation Tracking System - Main Application
 * Production-ready version with modular architecture
 */

(function() {
    'use strict';

    const App = {
        init() {
            this.bindCDNFallbacks();
            this.bindEvents();
            this.bindForms();
            this.bindButtons();

            DataService.init(this.getInitialData());
            UIService.init();
            MapService.init('map');
            ChartService.init();

            this.createCharts();
            this.refreshAll();

            // Let AuthService handle showing/hiding based on auth status
            AuthService.init();
        },

        bindCDNFallbacks() {
            window.addEventListener('error', (e) => {
                if (e.target.tagName === 'SCRIPT' || e.target.tagName === 'LINK') {
                    console.error('CDN resource failed to load:', e.target.src || e.target.href);
                    UIService.showToast('Some resources failed to load. Please check your internet connection.', 5000);
                }
            });
        },

        getInitialData() {
            return [
                { siteCode: "UNDP-GI-0009A", projectName: "Free-WIFI for All", siteName: "Raele Barangay Hall - AP 1", barangay: "Raele", municipality: "Itbayat", province: "Batanes", district: "District I", latitude: 20.728794, longitude: 121.804235, activationDate: "April 30, 2024", status: "Done", notes: "" },
                { siteCode: "UNDP-GI-0009B", projectName: "Free-WIFI for All", siteName: "Raele Barangay Hall - AP 2", barangay: "Raele", municipality: "Itbayat", province: "Batanes", district: "District I", latitude: 20.728794, longitude: 121.804235, activationDate: "April 30, 2024", status: "Done", notes: "" },
                { siteCode: "UNDP-GI-0010A", projectName: "Free-WIFI for All", siteName: "Salagao Barangay Hall - AP 1", barangay: "Salagao", municipality: "Ivana", province: "Batanes", district: "District I", latitude: 20.373518, longitude: 121.915566, activationDate: "May 08, 2024", status: "Done", notes: "" },
                { siteCode: "UNDP-GI-0010B", projectName: "Free-WIFI for All", siteName: "Salagao Barangay Hall - AP 2", barangay: "Salagao", municipality: "Ivana", province: "Batanes", district: "District I", latitude: 20.373518, longitude: 121.915566, activationDate: "May 08, 2024", status: "Done", notes: "" },
                { siteCode: "UNDP-IP-0031A", projectName: "Free-WIFI for All", siteName: "Santa Lucia Barangay Hall - AP 1", barangay: "Santa Lucia", municipality: "Itbayat", province: "Batanes", district: "District I", latitude: 20.784595, longitude: 121.840664, activationDate: "May 01, 2024", status: "Done", notes: "" },
                { siteCode: "UNDP-IP-0031B", projectName: "Free-WIFI for All", siteName: "Santa Lucia Barangay Hall - AP 2", barangay: "Santa Lucia", municipality: "Itbayat", province: "Batanes", district: "District I", latitude: 20.784595, longitude: 121.840664, activationDate: "May 01, 2024", status: "Done", notes: "" },
                { siteCode: "UNDP-IP-0032A", projectName: "Free-WIFI for All", siteName: "Santa Maria Barangay Hall - AP 1", barangay: "Santa Maria", municipality: "Itbayat", province: "Batanes", district: "District I", latitude: 20.785447, longitude: 121.842022, activationDate: "April 30, 2024", status: "Done", notes: "" },
                { siteCode: "CYBER-1231231", projectName: "PNPKI/CYBER", siteName: "Iguig National High School", barangay: "Ajat", municipality: "Iguig", province: "Cagayan", district: "District III", latitude: 17.7492984, longitude: 121.7350356, activationDate: "March 20, 2025", status: "Done", notes: "" },
                { siteCode: "IIDB-1231231", projectName: "IIDB", siteName: "Iguig National High School", barangay: "Ajat", municipality: "Iguig", province: "Cagayan", district: "District III", latitude: 17.7492984, longitude: 121.7350356, activationDate: "March 20, 2025", status: "Done", notes: "" },
                { siteCode: "eLGU-1231231", projectName: "DigiGov-eLGU", siteName: "Iguig National High School", barangay: "Ajat", municipality: "Iguig", province: "Cagayan", district: "District III", latitude: 17.7492984, longitude: 121.7350356, activationDate: "March 20, 2026", status: "Pending", notes: "" }
            ];
        },

        bindEvents() {
            document.getElementById('search-projects')?.addEventListener('input', UIService.bindSearchDebounced);

            document.querySelectorAll('#filter-all, #filter-done, #filter-pending').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('#filter-all, #filter-done, #filter-pending').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const filter = btn.id === 'filter-all' ? 'all' : btn.id === 'filter-done' ? 'Done' : 'Pending';
                    MapService.renderProjects(DataService.getAllProjects(), { statusFilter: filter });
                });
            });

            document.getElementById('view-all-projects')?.addEventListener('click', () => {
                UIService.switchTab('projects');
            });

            document.getElementById('add-project-btn')?.addEventListener('click', () => {
                UIService.switchTab('manual-entry');
            });

            this.setupFileUpload();
        },

        bindForms() {
            const form = document.getElementById('manual-entry-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handleManualEntry();
                });

                document.getElementById('clear-form-btn')?.addEventListener('click', () => {
                    form.reset();
                });
            }

            this.setupAutocomplete('province', 'province-suggestions', 'province');
            this.setupAutocomplete('district', 'district-suggestions', 'district');
        },

        bindButtons() {
            document.getElementById('export-btn')?.addEventListener('click', () => {
                DataService.downloadCSV();
                UIService.showSuccess('Data exported successfully!');
            });

            document.getElementById('refresh-btn')?.addEventListener('click', () => {
                this.refreshAll();
                UIService.showSuccess('Data refreshed!');
            });

            document.getElementById('download-template')?.addEventListener('click', () => {
                this.downloadTemplate();
            });

            document.getElementById('import-data-btn')?.addEventListener('click', () => {
                this.importValidatedData();
            });

            document.getElementById('download-errors-btn')?.addEventListener('click', () => {
                this.downloadErrorReport();
            });

            // PDF generation buttons
            document.getElementById('generate-summary-pdf')?.addEventListener('click', () => this.generatePDF('Summary Report'));
            document.getElementById('generate-province-pdf')?.addEventListener('click', () => this.generatePDF('Province Analysis Report'));
            document.getElementById('generate-timeline-pdf')?.addEventListener('click', () => this.generatePDF('Timeline Report'));
            document.getElementById('generate-status-pdf')?.addEventListener('click', () => this.generatePDF('Status Analysis Report'));

            // Report filter bindings
            this.bindReportFilters();
        },

        setupFileUpload() {
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('file-input');

            if (!dropZone || !fileInput) return;

            dropZone.addEventListener('click', () => fileInput.click());

            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    this.handleFileUpload(files[0]);
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.handleFileUpload(e.target.files[0]);
                }
            });
        },

        handleFileUpload(file) {
            if (!file) return;

            const validTypes = ['.csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (!validTypes.some(type => file.name.endsWith(type) || file.type === type)) {
                UIService.showError('Please upload a valid CSV file');
                return;
            }

            const reader = new FileReader();

            reader.onload = (e) => {
                try {
                    const csvContent = e.target.result;
                    const result = Papa.parse(csvContent, {
                        header: true,
                        skipEmptyLines: true
                    });

                    const validationResult = this.validateCSVData(result.data);

                    this.showValidationResults(validationResult);

                    window.pendingImportData = validationResult.validProjects;

                } catch (error) {
                    console.error('Error parsing CSV:', error);
                    UIService.showError('Failed to parse CSV file');
                }
            };

            reader.onerror = () => {
                UIService.showError('Failed to read file');
            };

            reader.readAsText(file);
        },

        validateCSVData(data) {
            const validProjects = [];
            const errors = [];
            const warnings = [];

            data.forEach((row, index) => {
                const validation = Validator.validateCSVRow(row, index, DataService.getAllProjects());

                if (validation.valid) {
                    const sanitized = Sanitizer.sanitizeProject(validation.project);
                    validProjects.push(sanitized);
                } else {
                    errors.push(...validation.errors);
                }

                if (validation.warnings.length > 0) {
                    warnings.push(...validation.warnings);
                }
            });

            return { validProjects, errors, warnings, hasErrors: errors.length > 0 };
        },

        showValidationResults(result) {
            const container = document.getElementById('validation-results');
            const messages = document.getElementById('validation-messages');

            if (!container || !messages) return;

            container.classList.remove('hidden');

            let html = '';

            if (result.errors.length > 0) {
                html += `
                    <div style="margin-bottom: 15px;">
                        <h4 style="color: #e74c3c; margin-bottom: 10px;">Errors (${result.errors.length})</h4>
                        <ul class="error-list">
                            ${result.errors.slice(0, 10).map(e => `<li>${Sanitizer.sanitizeHTML(e)}</li>`).join('')}
                            ${result.errors.length > 10 ? `<li>...and ${result.errors.length - 10} more errors</li>` : ''}
                        </ul>
                    </div>
                `;
            }

            if (result.warnings.length > 0) {
                html += `
                    <div style="margin-bottom: 15px; padding: 10px; background: rgba(243, 156, 18, 0.1); border-radius: 4px;">
                        <h4 style="color: #f39c12; margin-bottom: 5px;">Warnings (${result.warnings.length})</h4>
                        <p style="font-size: 13px; color: #666;">${Sanitizer.sanitizeHTML(result.warnings[0])}</p>
                    </div>
                `;
            }

            html += `
                <div class="success-message">
                    <strong>${result.validProjects.length}</strong> projects ready to import
                </div>
            `;

            messages.innerHTML = html;
        },

        importValidatedData() {
            if (!window.pendingImportData || window.pendingImportData.length === 0) {
                UIService.showError('No valid data to import');
                return;
            }

            const existingCount = DataService.getAllProjects().length;
            window.pendingImportData.forEach(project => DataService.projects.push(project));
            DataService.saveToStorage();

            const newCount = DataService.getAllProjects().length;
            const imported = newCount - existingCount;

            this.refreshAll();

            document.getElementById('validation-results')?.classList.add('hidden');
            window.pendingImportData = [];

            UIService.showSuccess(`${imported} projects imported successfully!`);
        },

        downloadErrorReport() {
            if (!window.pendingImportData || window.pendingImportData.length === 0) {
                UIService.showError('No error report to download');
                return;
            }

            const errorData = DataService.getAllProjects().map(p => ({
                'Site Code': p.siteCode,
                'Project Name': p.projectName,
                'Status': p.status,
                'Error': 'Duplicate or invalid'
            }));

            const csv = Papa.unparse(errorData);
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `error_report_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
        },

        downloadTemplate() {
            const template = [
                ['Site Code', 'Project Name', 'Site Name', 'Barangay', 'Municipality', 'Province', 'District', 'Latitude', 'Longitude', 'Date of Activation', 'Status', 'Notes'],
                ['EXAMPLE-001', 'Free-WIFI for All', 'Sample Barangay Hall', 'Sample Barangay', 'Sample Municipality', 'Batanes', 'District I', '20.728794', '121.804235', 'April 30, 2024', 'Done', '']
            ];

            const csv = Papa.unparse(template);
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'project_template.csv';
            link.click();
        },

        handleManualEntry() {
            const form = document.getElementById('manual-entry-form');
            if (!form) return;

            const project = {
                siteCode: document.getElementById('site-code').value.trim(),
                projectName: document.getElementById('project-name').value.trim(),
                siteName: document.getElementById('site-name').value.trim(),
                barangay: document.getElementById('barangay').value.trim(),
                municipality: document.getElementById('municipality').value.trim(),
                province: document.getElementById('province').value.trim(),
                district: document.getElementById('district').value.trim(),
                latitude: parseFloat(document.getElementById('latitude').value),
                longitude: parseFloat(document.getElementById('longitude').value),
                activationDate: Validator.normalizeDate(document.getElementById('activation-date').value),
                status: document.getElementById('status').value,
                notes: document.getElementById('notes').value.trim()
            };

            const result = DataService.addProject(project);

            if (result.success) {
                form.reset();
                this.refreshAll();
                UIService.showSuccess('Project added successfully!');
            } else {
                UIService.showError(result.errors.join(', '));
            }
        },

        createCharts() {
            ChartService.createStatusChart('status-chart');
            ChartService.createTimelineChart('timeline-chart');
            ChartService.createProvinceChart('province-chart');
            ChartService.createStatusDistributionChart('status-distribution-chart');
            ChartService.createProvinceDetailedChart('province-detailed-chart');
            ChartService.createTimelineDetailedChart('timeline-detailed-chart');
            ChartService.createPendingDurationChart('pending-duration-chart');
            ChartService.createCompletionRateChart('completion-rate-chart');
        },

        setupAutocomplete(inputId, suggestionsId, dataField) {
            const input = document.getElementById(inputId);
            const suggestions = document.getElementById(suggestionsId);
            if (!input || !suggestions) return;

            let selectedIndex = -1;
            let debounceTimer;

            input.addEventListener('input', (e) => {
                const value = e.target.value.trim();
                selectedIndex = -1;

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (value.length === 0) {
                        suggestions.classList.remove('active');
                        return;
                    }

                    const projects = DataService.getAllProjects();
                    const matches = this.findMatches(projects, dataField, value);

                    this.renderSuggestions(suggestions, matches, value, input, selectedIndex);
                }, 300);
            });

            input.addEventListener('keydown', (e) => {
                const items = suggestions.querySelectorAll('.suggestion-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % items.length;
                    this.updateSelection(items, selectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
                    this.updateSelection(items, selectedIndex);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedIndex >= 0 && items[selectedIndex]) {
                        items[selectedIndex].click();
                    }
                } else if (e.key === 'Escape') {
                    suggestions.classList.remove('active');
                    selectedIndex = -1;
                }
            });

            input.addEventListener('blur', () => {
                setTimeout(() => suggestions.classList.remove('active'), 200);
            });

            input.addEventListener('focus', () => {
                if (input.value.trim().length > 0) {
                    input.dispatchEvent(new Event('input'));
                }
            });
        },

        findMatches(projects, field, query) {
            const queryLower = query.toLowerCase();
            const uniqueValues = new Set();

            projects.forEach(project => {
                const value = project[field];
                if (value && value.toLowerCase().includes(queryLower)) {
                    uniqueValues.add(value);
                }
            });

            return Array.from(uniqueValues).sort((a, b) => {
                const aLower = a.toLowerCase();
                const bLower = b.toLowerCase();
                const aStarts = aLower.startsWith(queryLower);
                const bStarts = bLower.startsWith(queryLower);

                if (aStarts && !bStarts) return -1;
                if (!aStarts && bStarts) return 1;
                return a.localeCompare(b);
            });
        },

        renderSuggestions(container, matches, query, input, selectedIndex) {
            if (matches.length === 0) {
                container.innerHTML = '<div class="no-results">No matches found</div>';
                container.classList.add('active');
                return;
            }

            container.innerHTML = matches.map((match, index) => {
                const highlightedMatch = this.highlightMatch(match, query);
                const count = this.countOccurrences(DataService.getAllProjects(), query);
                const selectedClass = index === selectedIndex ? 'selected' : '';

                return `
                    <div class="suggestion-item ${selectedClass}" data-value="${Sanitizer.sanitizeHTML(match)}">
                        <span class="suggestion-match">${Sanitizer.sanitizeHTML(highlightedMatch)}</span>
                        <span class="suggestion-count">${count} project(s)</span>
                    </div>
                `;
            }).join('');

            container.querySelectorAll('.suggestion-item').forEach((item, index) => {
                item.addEventListener('click', () => {
                    input.value = item.dataset.value;
                    container.classList.remove('active');
                });
            });

            container.classList.add('active');
        },

        highlightMatch(text, query) {
            const queryLower = query.toLowerCase();
            const textLower = text.toLowerCase();
            let result = '';
            let lastIndex = 0;

            for (let i = 0; i < textLower.length; i++) {
                if (textLower.substring(i, i + queryLower.length) === queryLower) {
                    result += `<strong>${Sanitizer.sanitizeHTML(text.substring(i, i + queryLower.length))}</strong>`;
                    i += queryLower.length - 1;
                    lastIndex = i + 1;
                }
            }

            result += text.substring(lastIndex);
            return result;
        },

        countOccurrences(projects, query) {
            const queryLower = query.toLowerCase();
            let count = 0;

            projects.forEach(project => {
                const province = project.province?.toLowerCase() || '';
                const district = project.district?.toLowerCase() || '';

                if (province.includes(queryLower) || district.includes(queryLower)) {
                    count++;
                }
            });

            return count;
        },

        updateSelection(items, selectedIndex) {
            items.forEach((item, index) => {
                item.classList.toggle('selected', index === selectedIndex);
            });

            if (selectedIndex >= 0 && items[selectedIndex]) {
                items[selectedIndex].scrollIntoView({ block: 'nearest' });
            }
        },

        refreshAll() {
            const projects = DataService.getAllProjects();

            MapService.renderProjects(projects);
            UIService.renderRecentProjects();
            UIService.renderAllProjectsTable();
            UIService.updateStats();
            ChartService.updateAllCharts();
            this.populateFilterDropdowns();
        },

        bindReportFilters() {
            // Summary report filters
            document.getElementById('summary-status-filter')?.addEventListener('change', () => this.applyFilters('summary'));
            document.getElementById('summary-province-filter')?.addEventListener('change', () => this.applyFilters('summary'));
            document.getElementById('summary-date-from')?.addEventListener('change', () => this.applyFilters('summary'));
            document.getElementById('summary-date-to')?.addEventListener('change', () => this.applyFilters('summary'));

            // Province report filters
            document.getElementById('province-status-filter')?.addEventListener('change', () => this.applyFilters('province'));
            document.getElementById('province-province-filter')?.addEventListener('change', () => this.applyFilters('province'));

            // Timeline report filters
            document.getElementById('timeline-status-filter')?.addEventListener('change', () => this.applyFilters('timeline'));
            document.getElementById('timeline-date-from')?.addEventListener('change', () => this.applyFilters('timeline'));
            document.getElementById('timeline-date-to')?.addEventListener('change', () => this.applyFilters('timeline'));

            // Status report filters
            document.getElementById('status-province-filter')?.addEventListener('change', () => this.applyFilters('status'));
            document.getElementById('status-duration-filter')?.addEventListener('change', () => this.applyFilters('status'));
        },

        populateFilterDropdowns() {
            const projects = DataService.getAllProjects();
            const provinces = [...new Set(projects.map(p => p.province))].sort();

            // Populate province dropdowns in all reports
            const provinceFilters = ['summary-province-filter', 'province-province-filter', 'status-province-filter'];
            provinceFilters.forEach(filterId => {
                const select = document.getElementById(filterId);
                if (select) {
                    const currentValue = select.value;
                    select.innerHTML = '<option value="all">All Provinces</option>';
                    provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province;
                        option.textContent = province;
                        select.appendChild(option);
                    });
                    select.value = currentValue;
                }
            });
        },

        applyFilters(reportType) {
            let filteredProjects = DataService.getAllProjects();
            const filters = this.getFilters(reportType);

            // Apply filters
            if (filters.status && filters.status !== 'all') {
                filteredProjects = filteredProjects.filter(p => p.status === filters.status);
            }

            if (filters.province && filters.province !== 'all') {
                filteredProjects = filteredProjects.filter(p => p.province === filters.province);
            }

            if (filters.dateFrom) {
                filteredProjects = filteredProjects.filter(p => new Date(p.activationDate) >= new Date(filters.dateFrom));
            }

            if (filters.dateTo) {
                filteredProjects = filteredProjects.filter(p => new Date(p.activationDate) <= new Date(filters.dateTo));
            }

            if (filters.duration && filters.duration !== 'all') {
                const pendingProjects = filteredProjects.filter(p => p.status === 'Pending');
                if (filters.duration === '30') {
                    filteredProjects = pendingProjects.filter(p => this.getDaysPending(p) < 30);
                } else if (filters.duration === '60') {
                    filteredProjects = pendingProjects.filter(p => this.getDaysPending(p) < 60);
                } else if (filters.duration === '90') {
                    filteredProjects = pendingProjects.filter(p => this.getDaysPending(p) < 90);
                } else if (filters.duration === '90') {
                    filteredProjects = pendingProjects.filter(p => this.getDaysPending(p) >= 90);
                }
            }

            // Update UI with filtered data
            this.updateReportsWithFilteredData(filteredProjects, reportType);
        },

        getFilters(reportType) {
            const filters = {};

            if (reportType === 'summary') {
                filters.status = document.getElementById('summary-status-filter')?.value;
                filters.province = document.getElementById('summary-province-filter')?.value;
                filters.dateFrom = document.getElementById('summary-date-from')?.value;
                filters.dateTo = document.getElementById('summary-date-to')?.value;
            } else if (reportType === 'province') {
                filters.status = document.getElementById('province-status-filter')?.value;
                filters.province = document.getElementById('province-province-filter')?.value;
            } else if (reportType === 'timeline') {
                filters.status = document.getElementById('timeline-status-filter')?.value;
                filters.dateFrom = document.getElementById('timeline-date-from')?.value;
                filters.dateTo = document.getElementById('timeline-date-to')?.value;
            } else if (reportType === 'status') {
                filters.province = document.getElementById('status-province-filter')?.value;
                filters.duration = document.getElementById('status-duration-filter')?.value;
            }

            return filters;
        },

        getDaysPending(project) {
            return Math.floor((new Date() - new Date(project.activationDate)) / (1000 * 60 * 60 * 24));
        },

        updateReportsWithFilteredData(projects, reportType) {
            // Temporary update DataService with filtered projects
            const originalProjects = DataService.getAllProjects();
            DataService.projects = projects;

            if (reportType === 'summary' || reportType === 'all') {
                ChartService.updateAllCharts();
                UIService.renderReportsTables();
            }

            if (reportType === 'province' || reportType === 'all') {
                UIService.renderProvinceDetailsTable();
            }

            if (reportType === 'timeline' || reportType === 'all') {
                UIService.renderTimelineTable();
            }

            if (reportType === 'status' || reportType === 'all') {
                UIService.renderPendingProjectsTable();
            }

            // Restore original projects
            DataService.projects = originalProjects;
        },

        generatePDF(reportTitle) {
            const printContent = document.querySelector('.tab-content.active .card')?.parentElement;
            if (!printContent) {
                UIService.showError('Unable to generate PDF. Please try again.');
                return;
            }

            // Create a clean print-only version
            const printWindow = window.open('', '_blank', 'width=1024,height=768');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${reportTitle}</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 40px; line-height: 1.6; }
                        .print-header { text-align: center; margin-bottom: 40px; }
                        .print-header h1 { font-size: 28px; color: #1e3a8a; margin-bottom: 10px; }
                        .print-header p { color: #64748b; font-size: 14px; }
                        .print-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
                        .stat-card { background: #f8fafc; padding: 20px; border-radius: 12px; text-align: center; }
                        .stat-card h3 { font-size: 32px; color: #3b82f6; margin-bottom: 8px; }
                        .stat-card p { color: #64748b; font-weight: 500; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
                        th { background: #f1f5f9; font-weight: 600; color: #1e3a8a; }
                        tr:hover { background: #f8fafc; }
                        .status-done { color: #22c55e; font-weight: 600; }
                        .status-pending { color: #f39c12; font-weight: 600; }
                        .page-footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; color: #64748b; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>${reportTitle}</h1>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>
            `);

            // Add report-specific content
            const reportStats = DataService.getStats();
            const projects = DataService.getAllProjects();

            if (reportTitle.includes('Summary')) {
                printWindow.document.write(`
                    <div class="print-stats">
                        <div class="stat-card">
                            <h3>${reportStats.total}</h3>
                            <p>Total Projects</p>
                        </div>
                        <div class="stat-card">
                            <h3>${reportStats.completed}</h3>
                            <p>Completed</p>
                        </div>
                        <div class="stat-card">
                            <h3>${reportStats.pending}</h3>
                            <p>Pending</p>
                        </div>
                        <div class="stat-card">
                            <h3>${reportStats.provinces}</h3>
                            <p>Provinces</p>
                        </div>
                    </div>
                    <h2>Projects List</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Site Code</th>
                                <th>Project Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Activation Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${projects.map(p => `
                                <tr>
                                    <td>${Sanitizer.sanitizeHTML(p.siteCode)}</td>
                                    <td>${Sanitizer.sanitizeHTML(p.projectName)}</td>
                                    <td>${Sanitizer.sanitizeHTML(p.province)}, ${Sanitizer.sanitizeHTML(p.municipality)}</td>
                                    <td class="status-${p.status.toLowerCase()}">${Sanitizer.sanitizeHTML(p.status)}</td>
                                    <td>${Sanitizer.sanitizeHTML(p.activationDate)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `);
            } else if (reportTitle.includes('Province')) {
                const provinceMap = {};
                projects.forEach(p => {
                    if (!provinceMap[p.province]) {
                        provinceMap[p.province] = { total: 0, completed: 0, pending: 0 };
                    }
                    provinceMap[p.province].total++;
                    if (p.status === 'Done') provinceMap[p.province].completed++;
                    else provinceMap[p.province].pending++;
                });

                printWindow.document.write(`
                    <h2>Projects by Province</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Province</th>
                                <th>Total Projects</th>
                                <th>Completed</th>
                                <th>Pending</th>
                                <th>Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${Object.entries(provinceMap).map(([province, data]) => {
                                const rate = Math.round((data.completed / data.total) * 100);
                                return `
                                    <tr>
                                        <td>${Sanitizer.sanitizeHTML(province)}</td>
                                        <td>${data.total}</td>
                                        <td>${data.completed}</td>
                                        <td>${data.pending}</td>
                                        <td>${rate}%</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                `);
            } else if (reportTitle.includes('Timeline')) {
                const timelineMap = {};
                projects.forEach(p => {
                    const date = new Date(p.activationDate);
                    const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
                    if (!timelineMap[key]) {
                        timelineMap[key] = { new: 0, completed: 0, pending: 0 };
                    }
                    timelineMap[key].new++;
                    if (p.status === 'Done') timelineMap[key].completed++;
                    else timelineMap[key].pending++;
                });

                printWindow.document.write(`
                    <h2>Implementation Timeline</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>New Projects</th>
                                <th>Completed</th>
                                <th>Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${Object.entries(timelineMap)
                                .sort((a, b) => a[0].localeCompare(b[0]))
                                .map(([month, data]) => {
                                    const [year, m] = month.split('-');
                                    const label = new Date(year, m - 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                                    return `
                                        <tr>
                                            <td>${label}</td>
                                            <td>${data.new}</td>
                                            <td>${data.completed}</td>
                                            <td>${data.pending}</td>
                                        </tr>
                                    `;
                                }).join('')}
                        </tbody>
                    </table>
                `);
            } else if (reportTitle.includes('Status')) {
                const pendingProjects = projects.filter(p => p.status === 'Pending');

                printWindow.document.write(`
                    <h2>Pending Projects Analysis</h2>
                    <h3>Total Pending: ${pendingProjects.length}</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Site Code</th>
                                <th>Project Name</th>
                                <th>Location</th>
                                <th>Activation Date</th>
                                <th>Days Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${pendingProjects.map(p => {
                                const days = Math.floor((new Date() - new Date(p.activationDate)) / (1000 * 60 * 60 * 24));
                                return `
                                    <tr>
                                        <td>${Sanitizer.sanitizeHTML(p.siteCode)}</td>
                                        <td>${Sanitizer.sanitizeHTML(p.projectName)}</td>
                                        <td>${Sanitizer.sanitizeHTML(p.province)}, ${Sanitizer.sanitizeHTML(p.municipality)}</td>
                                        <td>${Sanitizer.sanitizeHTML(p.activationDate)}</td>
                                        <td class="status-pending">${days} days</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                `);
            }

            printWindow.document.write(`
                    <div class="page-footer">
                        <p>Project Implementation Tracking System • Report generated automatically</p>
                    </div>
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.print();
            UIService.showSuccess('PDF generated! Use your browser\'s print dialog to save as PDF.');
        }
    };

    window.viewProject = function(siteCode) {
        const project = DataService.getProject(siteCode);
        if (project) {
            MapService.highlightProject(siteCode);
            UIService.switchTab('dashboard');
        }
    };

    window.deleteProject = function(siteCode) {
        // Check delete permission
        if (!AuthService.hasPermission('delete_project')) {
            UIService.showError('You do not have permission to delete projects.');
            return;
        }

        if (confirm(`Are you sure you want to delete project "${siteCode}"?`)) {
            const result = DataService.deleteProject(siteCode);
            if (result.success) {
                App.refreshAll();
                UIService.showSuccess('Project deleted successfully!');
            } else {
                UIService.showError(result.errors.join(', '));
            }
        }
    };

    window.addEventListener('DOMContentLoaded', () => {
        App.init();
    });

})();
