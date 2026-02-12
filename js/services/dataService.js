/**
 * Data Service
 * Handles data persistence, CRUD operations, and localStorage sync
 */

const DataService = {
    STORAGE_KEY: 'projectTracker_data',
    BACKUP_KEY: 'projectTracker_backup',

    init(initialData = []) {
        this.loadFromStorage();
        if (this.projects.length === 0 && initialData.length > 0) {
            this.projects = initialData;
            this.saveToStorage();
        }
    },

    loadFromStorage() {
        try {
            const stored = localStorage.getItem(this.STORAGE_KEY);
            if (stored) {
                this.projects = JSON.parse(stored);
                this.updateLastUpdated();
                return true;
            }
        } catch (error) {
            console.error('Error loading from storage:', error);
            this.loadBackup();
        }
        this.projects = [];
        return false;
    },

    saveToStorage() {
        try {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(this.projects));
            this.createBackup();
            this.updateLastUpdated();
            return true;
        } catch (error) {
            console.error('Error saving to storage:', error);
            return false;
        }
    },

    createBackup() {
        const backup = {
            timestamp: new Date().toISOString(),
            data: this.projects
        };
        localStorage.setItem(this.BACKUP_KEY, JSON.stringify(backup));
    },

    loadBackup() {
        try {
            const backup = localStorage.getItem(this.BACKUP_KEY);
            if (backup) {
                const parsed = JSON.parse(backup);
                const backupDate = new Date(parsed.timestamp);
                const daysSinceBackup = (new Date() - backupDate) / (1000 * 60 * 60 * 24);

                if (daysSinceBackup < 7) {
                    console.warn('Restoring from backup (7 days old or less)');
                    this.projects = parsed.data;
                    this.saveToStorage();
                    return true;
                }
            }
        } catch (error) {
            console.error('Error loading backup:', error);
        }
        return false;
    },

    updateLastUpdated() {
        const lastUpdated = document.getElementById('last-updated');
        if (lastUpdated) {
            lastUpdated.textContent = new Date().toLocaleString();
        }
    },

    addProject(project) {
        const sanitizedProject = Sanitizer.sanitizeProject(project);
        const validation = Validator.validateProject(sanitizedProject, this.projects);

        if (!validation.valid) {
            return { success: false, errors: validation.errors };
        }

        this.projects.push(sanitizedProject);
        const saved = this.saveToStorage();

        return {
            success: saved,
            project: sanitizedProject,
            errors: saved ? [] : ['Failed to save to storage']
        };
    },

    updateProject(siteCode, updates) {
        const index = this.projects.findIndex(p => p.siteCode === siteCode);
        if (index === -1) {
            return { success: false, errors: ['Project not found'] };
        }

        const sanitizedUpdates = Sanitizer.sanitizeProject({
            ...this.projects[index],
            ...updates
        });

        const validation = Validator.validateProject(sanitizedUpdates, this.projects);
        if (!validation.valid) {
            return { success: false, errors: validation.errors };
        }

        this.projects[index] = sanitizedUpdates;
        const saved = this.saveToStorage();

        return {
            success: saved,
            project: sanitizedUpdates,
            errors: saved ? [] : ['Failed to save to storage']
        };
    },

    deleteProject(siteCode) {
        const index = this.projects.findIndex(p => p.siteCode === siteCode);
        if (index === -1) {
            return { success: false, errors: ['Project not found'] };
        }

        this.projects.splice(index, 1);
        const saved = this.saveToStorage();

        return {
            success: saved,
            errors: saved ? [] : ['Failed to save to storage']
        };
    },

    getProject(siteCode) {
        return this.projects.find(p => p.siteCode === siteCode);
    },

    getAllProjects() {
        return [...this.projects];
    },

    getProjectsByStatus(status) {
        return this.projects.filter(p => p.status === status);
    },

    getProjectsByProvince(province) {
        return this.projects.filter(p => p.province === province);
    },

    searchProjects(query) {
        const lowerQuery = query.toLowerCase().trim();
        if (!lowerQuery) return this.projects;

        return this.projects.filter(project =>
            project.siteCode.toLowerCase().includes(lowerQuery) ||
            project.projectName.toLowerCase().includes(lowerQuery) ||
            project.siteName.toLowerCase().includes(lowerQuery) ||
            project.barangay.toLowerCase().includes(lowerQuery) ||
            project.municipality.toLowerCase().includes(lowerQuery) ||
            project.province.toLowerCase().includes(lowerQuery)
        );
    },

    getStats() {
        return {
            total: this.projects.length,
            completed: this.projects.filter(p => p.status === 'Done').length,
            pending: this.projects.filter(p => p.status === 'Pending').length,
            provinces: [...new Set(this.projects.map(p => p.province))].length
        };
    },

    exportToCSV() {
        const headers = [
            'Site Code', 'Project Name', 'Site Name', 'Barangay',
            'Municipality', 'Province', 'District', 'Latitude',
            'Longitude', 'Date of Activation', 'Status', 'Notes'
        ];

        const rows = this.projects.map(project => [
            project.siteCode,
            project.projectName,
            project.siteName,
            project.barangay,
            project.municipality,
            project.province,
            project.district,
            project.latitude,
            project.longitude,
            project.activationDate,
            project.status,
            project.notes || ''
        ]);

        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
        ].join('\n');

        return csvContent;
    },

    downloadCSV() {
        const csv = this.exportToCSV();
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', `projects_export_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },

    importFromCSV(csvContent) {
        const results = Papa.parse(csvContent, {
            header: true,
            skipEmptyLines: true
        });

        if (results.errors.length > 0) {
            return {
                success: false,
                errors: results.errors.map(e => `Parse error: ${e.message}`),
                imported: 0
            };
        }

        const validProjects = [];
        const allErrors = [];
        const allWarnings = [];

        results.data.forEach((row, index) => {
            const validation = Validator.validateCSVRow(row, index, this.projects);

            if (validation.valid) {
                validProjects.push(validation.project);
            } else {
                allErrors.push(...validation.errors);
            }

            if (validation.warnings.length > 0) {
                allWarnings.push(...validation.warnings);
            }
        });

        if (validProjects.length > 0) {
            this.projects = [...this.projects, ...validProjects];
            this.saveToStorage();
        }

        return {
            success: allErrors.length === 0,
            imported: validProjects.length,
            errors: allErrors,
            warnings: allWarnings
        };
    },

    clearAllData() {
        this.projects = [];
        localStorage.removeItem(this.STORAGE_KEY);
        localStorage.removeItem(this.BACKUP_KEY);
        this.updateLastUpdated();
    }
};
