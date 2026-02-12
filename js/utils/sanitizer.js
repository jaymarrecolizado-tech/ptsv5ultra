/**
 * Security Utility - XSS Prevention
 * Sanitizes all user input to prevent Cross-Site Scripting attacks
 */

const Sanitizer = {
    DOMPurify: null,

    init() {
        if (typeof DOMPurify === 'undefined') {
            console.warn('DOMPurify not loaded, using fallback sanitizer');
        }
    },

    sanitizeHTML(str) {
        if (typeof str !== 'string') return '';

        if (typeof DOMPurify !== 'undefined') {
            return DOMPurify.sanitize(str, {
                ALLOWED_TAGS: [],
                ALLOWED_ATTR: []
            });
        }

        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    sanitizeProject(project) {
        return {
            siteCode: this.sanitizeHTML(project.siteCode),
            projectName: this.sanitizeHTML(project.projectName),
            siteName: this.sanitizeHTML(project.siteName),
            barangay: this.sanitizeHTML(project.barangay),
            municipality: this.sanitizeHTML(project.municipality),
            province: this.sanitizeHTML(project.province),
            district: this.sanitizeHTML(project.district),
            latitude: parseFloat(project.latitude) || 0,
            longitude: parseFloat(project.longitude) || 0,
            activationDate: this.sanitizeHTML(project.activationDate),
            status: this.sanitizeStatus(project.status),
            notes: this.sanitizeHTML(project.notes || '')
        };
    },

    sanitizeStatus(status) {
        const cleanStatus = this.sanitizeHTML(status);
        return (cleanStatus === 'Done' || cleanStatus === 'Pending') ? cleanStatus : 'Pending';
    },

    sanitizeObject(obj) {
        const sanitized = {};
        for (const key in obj) {
            if (typeof obj[key] === 'string') {
                sanitized[key] = this.sanitizeHTML(obj[key]);
            } else {
                sanitized[key] = obj[key];
            }
        }
        return sanitized;
    }
};

Sanitizer.init();
