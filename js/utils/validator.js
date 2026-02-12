/**
 * Validation Utility
 * Comprehensive validation for all project data
 */

const Validator = {
    patterns: {
        siteCode: /^[A-Z0-9\-]+$/,
        latitude: /^-?([1-8]?[0-9]|90)(\.[0-9]+)?$/,
        longitude: /^-?((1[0-7][0-9])|([0-9]?[0-9]))(\.[0-9]+)?$|^-?(180)(\.[0-9]+)?$/
    },

    validateProject(project, existingProjects = []) {
        const errors = [];
        const warnings = [];

        if (!project.siteCode || project.siteCode.trim() === '') {
            errors.push('Site Code is required');
        } else if (!this.patterns.siteCode.test(project.siteCode)) {
            errors.push('Site Code must contain only letters, numbers, and hyphens');
        } else {
            const duplicate = existingProjects.find(p => p.siteCode === project.siteCode);
            if (duplicate) {
                errors.push(`Site Code "${project.siteCode}" already exists`);
            }
        }

        if (!project.projectName || project.projectName.trim() === '') {
            errors.push('Project Name is required');
        }

        if (!project.siteName || project.siteName.trim() === '') {
            errors.push('Site Name is required');
        }

        if (!project.barangay || project.barangay.trim() === '') {
            errors.push('Barangay is required');
        }

        if (!project.municipality || project.municipality.trim() === '') {
            errors.push('Municipality is required');
        }

        if (!project.province || project.province.trim() === '') {
            errors.push('Province is required');
        }

        if (!project.district || project.district.trim() === '') {
            errors.push('District is required');
        }

        if (project.latitude === '' || project.latitude === null || isNaN(project.latitude)) {
            errors.push('Valid Latitude is required (-90 to 90)');
        } else if (project.latitude < -90 || project.latitude > 90) {
            errors.push('Latitude must be between -90 and 90');
        }

        if (project.longitude === '' || project.longitude === null || isNaN(project.longitude)) {
            errors.push('Valid Longitude is required (-180 to 180)');
        } else if (project.longitude < -180 || project.longitude > 180) {
            errors.push('Longitude must be between -180 and 180');
        }

        if (!project.activationDate || project.activationDate.trim() === '') {
            errors.push('Date of Activation is required');
        }

        if (!project.status || (project.status !== 'Done' && project.status !== 'Pending')) {
            errors.push('Status must be either "Done" or "Pending"');
        }

        if (project.notes && project.notes.length > 1000) {
            warnings.push('Notes are quite long (>1000 characters)');
        }

        return { valid: errors.length === 0, errors, warnings };
    },

    validateCSVRow(row, index, existingProjects = []) {
        const errors = [];
        const warnings = [];
        const project = {};

        const fieldMappings = {
            'Site Code': 'siteCode',
            'Project Name': 'projectName',
            'Site Name': 'siteName',
            'Barangay': 'barangay',
            'Municipality': 'municipality',
            'Province': 'province',
            'District': 'district',
            'Latitude': 'latitude',
            'Longitude': 'longitude',
            'Date of Activation': 'activationDate',
            'Status': 'status',
            'Notes': 'notes'
        };

        for (const [csvHeader, fieldName] of Object.entries(fieldMappings)) {
            const value = row[csvHeader];
            project[fieldName] = value;
        }

        if (!project.siteCode) {
            errors.push(`Row ${index + 1}: Site Code is missing`);
        }

        if (!project.projectName) {
            errors.push(`Row ${index + 1}: Project Name is missing`);
        }

        if (!project.province) {
            errors.push(`Row ${index + 1}: Province is missing`);
        }

        if (project.latitude !== undefined && project.latitude !== '') {
            const lat = parseFloat(project.latitude);
            if (isNaN(lat) || lat < -90 || lat > 90) {
                errors.push(`Row ${index + 1}: Invalid latitude value`);
            }
        }

        if (project.longitude !== undefined && project.longitude !== '') {
            const lng = parseFloat(project.longitude);
            if (isNaN(lng) || lng < -180 || lng > 180) {
                errors.push(`Row ${index + 1}: Invalid longitude value`);
            }
        }

        if (project.status && !['Done', 'Pending'].includes(project.status)) {
            project.status = this.autoCorrectStatus(project.status);
            warnings.push(`Row ${index + 1}: Status auto-corrected to "${project.status}"`);
        }

        return { valid: errors.length === 0, project, errors, warnings };
    },

    autoCorrectStatus(status) {
        const normalized = status.toLowerCase().trim();
        if (normalized === 'done' || normalized === 'complete' || normalized === 'completed') {
            return 'Done';
        }
        return 'Pending';
    },

    autoCorrectProvince(province) {
        const provinces = [
            'Batanes', 'Cagayan', 'Ilocos Norte', 'Ilocos Sur', 'La Union',
            'Pangasinan', 'Apayao', 'Kalinga', 'Mountain Province', 'Abra',
            'Benguet', 'Isabela', 'Nueva Vizcaya', 'Quirino', 'Aurora',
            'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales',
            'Bataan', 'Rizal', 'Cavite', 'Laguna', 'Batangas',
            'Quezon', 'Marinduque', 'Romblon', 'Palawan', 'Masbate',
            'Sorsogon', 'Albay', 'Camarines Norte', 'Camarines Sur', 'Catanduanes'
        ];

        const normalized = province.toLowerCase().trim();
        const match = provinces.find(p => p.toLowerCase() === normalized);
        return match || province;
    },

    normalizeDate(dateStr) {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) {
            return dateStr;
        }
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }
};
