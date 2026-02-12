/**
 * Form Validation Utilities
 */

const Validator = {
    /**
     * Validate required field
     */
    required(value, fieldName) {
        if (!value || value.trim() === '') {
            return `${fieldName} is required`;
        }
        return null;
    },
    
    /**
     * Validate coordinate
     */
    coordinate(value, type) {
        const num = parseFloat(value);
        if (isNaN(num)) {
            return `${type} must be a valid number`;
        }
        
        if (type === 'latitude' && (num < -90 || num > 90)) {
            return 'Latitude must be between -90 and 90';
        }
        
        if (type === 'longitude' && (num < -180 || num > 180)) {
            return 'Longitude must be between -180 and 180';
        }
        
        return null;
    },
    
    /**
     * Validate date
     */
    date(value, fieldName) {
        if (!value) {
            return `${fieldName} is required`;
        }
        
        const date = new Date(value);
        if (isNaN(date.getTime())) {
            return `${fieldName} must be a valid date`;
        }
        
        return null;
    },
    
    /**
     * Validate status
     */
    status(value) {
        const valid = ['Done', 'Pending'];
        if (!valid.includes(value)) {
            return 'Status must be Done or Pending';
        }
        return null;
    },
    
    /**
     * Validate project form
     */
    validateProjectForm(formData) {
        const errors = [];
        
        // Required fields
        const required = ['site_code', 'project_name', 'site_name', 'barangay', 'municipality', 'province', 'district'];
        required.forEach(field => {
            const error = this.required(formData[field], field.replace('_', ' '));
            if (error) errors.push(error);
        });
        
        // Coordinates
        const latError = this.coordinate(formData.latitude, 'latitude');
        if (latError) errors.push(latError);
        
        const lngError = this.coordinate(formData.longitude, 'longitude');
        if (lngError) errors.push(lngError);
        
        // Date
        const dateError = this.date(formData.activation_date, 'activation date');
        if (dateError) errors.push(dateError);
        
        // Status
        const statusError = this.status(formData.status);
        if (statusError) errors.push(statusError);
        
        return errors;
    },
    
    /**
     * Show validation errors
     */
    showErrors(errors, containerId = 'form-errors') {
        let container = document.getElementById(containerId);
        
        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.className = 'bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4';
            
            const form = document.querySelector('form');
            if (form) {
                form.insertBefore(container, form.firstChild);
            }
        }
        
        if (errors.length === 0) {
            container.classList.add('hidden');
            return;
        }
        
        container.innerHTML = `
            <ul class="list-disc list-inside">
                ${errors.map(e => `<li>${e}</li>`).join('')}
            </ul>
        `;
        container.classList.remove('hidden');
    },
    
    /**
     * Clear validation errors
     */
    clearErrors(containerId = 'form-errors') {
        const container = document.getElementById(containerId);
        if (container) {
            container.classList.add('hidden');
        }
    },
    
    /**
     * Province list for autocomplete
     */
    provinces: [
        'Abra', 'Agusan del Norte', 'Agusan del Sur', 'Aklan', 'Albay', 'Antique',
        'Apayao', 'Aurora', 'Basilan', 'Bataan', 'Batanes', 'Batangas', 'Benguet',
        'Biliran', 'Bohol', 'Bukidnon', 'Bulacan', 'Cagayan', 'Camarines Norte',
        'Camarines Sur', 'Camiguin', 'Capiz', 'Catanduanes', 'Cavite', 'Cebu',
        'Compostela Valley', 'Cotabato', 'Davao del Norte', 'Davao del Sur',
        'Davao Occidental', 'Davao Oriental', 'Dinagat Islands', 'Eastern Samar',
        'Guimaras', 'Ifugao', 'Ilocos Norte', 'Ilocos Sur', 'Iloilo', 'Isabela',
        'Kalinga', 'La Union', 'Laguna', 'Lanao del Norte', 'Lanao del Sur',
        'Leyte', 'Maguindanao', 'Marinduque', 'Masbate', 'Metro Manila',
        'Misamis Occidental', 'Misamis Oriental', 'Mountain Province',
        'Negros Occidental', 'Negros Oriental', 'Northern Samar', 'Nueva Ecija',
        'Nueva Vizcaya', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan',
        'Pampanga', 'Pangasinan', 'Quezon', 'Quirino', 'Rizal', 'Romblon',
        'Samar', 'Sarangani', 'Siquijor', 'Sorsogon', 'South Cotabato',
        'Southern Leyte', 'Sultan Kudarat', 'Sulu', 'Surigao del Norte',
        'Surigao del Sur', 'Tarlac', 'Tawi-Tawi', 'Zambales', 'Zamboanga del Norte',
        'Zamboanga del Sur', 'Zamboanga Sibugay'
    ],
    
    /**
     * Find matching provinces
     */
    findProvinces(input) {
        if (!input || input.length < 2) return [];
        
        const query = input.toLowerCase();
        return this.provinces.filter(p => p.toLowerCase().includes(query)).slice(0, 10);
    },
    
    /**
     * Setup province autocomplete
     */
    setupProvinceAutocomplete(inputId, suggestionsId) {
        const input = document.getElementById(inputId);
        const suggestions = document.getElementById(suggestionsId);
        
        if (!input || !suggestions) return;
        
        input.addEventListener('input', (e) => {
            const matches = this.findProvinces(e.target.value);
            
            if (matches.length === 0) {
                suggestions.classList.add('hidden');
                return;
            }
            
            suggestions.innerHTML = matches.map(p => `
                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer" onclick="
                    document.getElementById('${inputId}').value = '${p}';
                    document.getElementById('${suggestionsId}').classList.add('hidden');
                ">${p}</div>
            `).join('');
            
            suggestions.classList.remove('hidden');
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.classList.add('hidden');
            }
        });
    }
};
