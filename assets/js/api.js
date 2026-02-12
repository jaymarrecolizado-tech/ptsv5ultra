/**
 * API Utility Functions
 */

const API = {
    baseUrl: '/projects/newPTS/api',
    
    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
        
        const response = await fetch(url, { ...defaultOptions, ...options });
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Request failed');
        }
        
        return data;
    },
    
    /**
     * GET request
     */
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },
    
    /**
     * POST request
     */
    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * PUT request
     */
    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },
    
    /**
     * Upload file
     */
    async upload(endpoint, formData) {
        const url = `${this.baseUrl}${endpoint}`;
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Upload failed');
        }
        
        return data;
    },
    
    // Projects API
    projects: {
        getAll: (filters = {}) => {
            const params = new URLSearchParams(filters);
            return API.get(`/projects.php?${params}`);
        },
        get: (id) => API.get(`/projects.php?id=${id}`),
        getStats: () => API.get('/projects.php?action=stats'),
        create: (data) => API.post('/projects.php', data),
        update: (id, data) => API.put('/projects.php', { id, ...data }),
        delete: (id) => API.delete(`/projects.php?id=${id}`)
    },
    
    // Import/Export API
    import: {
        upload: (formData) => API.upload('/import.php', formData),
        export: () => window.location.href = '/projects/newPTS/api/import.php',
        template: () => window.location.href = '/projects/newPTS/api/import.php?action=template'
    },
    
    // Reports API
    reports: {
        summary: (filters = {}) => {
            const params = new URLSearchParams({ report: 'summary', ...filters });
            return API.get(`/reports.php?${params}`);
        },
        province: (filters = {}) => {
            const params = new URLSearchParams({ report: 'province', ...filters });
            return API.get(`/reports.php?${params}`);
        },
        timeline: (filters = {}) => {
            const params = new URLSearchParams({ report: 'timeline', ...filters });
            return API.get(`/reports.php?${params}`);
        },
        status: (filters = {}) => {
            const params = new URLSearchParams({ report: 'status', ...filters });
            return API.get(`/reports.php?${params}`);
        },
        provincesList: () => API.get('/reports.php?report=provinces-list')
    }
};
