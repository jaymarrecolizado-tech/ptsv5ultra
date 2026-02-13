/**
 * Map Service - Leaflet Integration with Clustering
 * Colors based on unique Project Names
 */

const MapService = {
    map: null,
    markers: [],
    markerClusterGroup: null,
    currentFilter: 'all',
    currentTileLayer: null,
    projectColors: {}, // Store unique project name -> color mapping
    colorPalette: [
        '#3B82F6', // Blue
        '#EF4444', // Red
        '#10B981', // Green
        '#F59E0B', // Orange
        '#8B5CF6', // Purple
        '#EC4899', // Pink
        '#06B6D4', // Cyan
        '#84CC16', // Lime
        '#F97316', // Orange-red
        '#6366F1', // Indigo
        '#14B8A6', // Teal
        '#D946EF', // Fuchsia
        '#22C55E', // Green-light
        '#EAB308', // Yellow
        '#A855F7', // Violet
        '#0EA5E9', // Sky blue
        '#F43F5E', // Rose
        '#65A30D', // Lime-dark
        '#0891B2', // Cyan-dark
        '#7C3AED', // Violet-dark
    ],
    
    /**
     * Get color for a project name
     */
    getProjectColor(projectName) {
        if (!this.projectColors[projectName]) {
            // Assign next color from palette
            const colorIndex = Object.keys(this.projectColors).length % this.colorPalette.length;
            this.projectColors[projectName] = this.colorPalette[colorIndex];
        }
        return this.projectColors[projectName];
    },
    
    /**
     * Generate legend HTML
     */
    generateLegend(projects) {
        // Get unique project names
        const uniqueProjects = [...new Set(projects.map(p => p.project_name))];
        
        // Build legend HTML
        let legendHTML = '';
        uniqueProjects.forEach(projectName => {
            const color = this.getProjectColor(projectName);
            legendHTML += `
                <div class="flex items-center gap-1 bg-gray-100 px-2 py-1 rounded text-xs">
                    <span class="w-3 h-3 rounded-full" style="background-color: ${color};"></span>
                    <span class="text-gray-700 truncate max-w-[150px]">${projectName}</span>
                </div>
            `;
        });
        
        return legendHTML;
    },
    
    /**
     * Initialize map
     */
    init(containerId = 'map') {
        if (this.map) {
            return this.map;
        }
        
        const container = document.getElementById(containerId);
        if (!container) return null;
        
        // Initialize map centered on Philippines
        this.map = L.map(containerId).setView([17.0, 121.0], 6);
        
        // Add default tile layer
        this.setTileLayer('standard');
        
        // Initialize marker cluster group
        this.markerClusterGroup = L.markerClusterGroup({
            chunkedLoading: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            maxClusterRadius: 80,
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div style="background-color: #3B82F6; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">' + cluster.getChildCount() + '</div>',
                    className: 'marker-cluster',
                    iconSize: L.point(40, 40)
                });
            }
        });
        
        this.map.addLayer(this.markerClusterGroup);
        
        return this.map;
    },
    
    /**
     * Set tile layer (standard or satellite)
     */
    setTileLayer(type) {
        if (this.currentTileLayer) {
            this.map.removeLayer(this.currentTileLayer);
        }
        
        if (type === 'satellite') {
            this.currentTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Esri',
                maxZoom: 19
            });
        } else {
            this.currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            });
        }
        
        this.currentTileLayer.addTo(this.map);
    },
    
    /**
     * Toggle between standard and satellite view
     */
    toggleView() {
        const currentType = this.currentTileLayer.options.attribution.includes('Esri') ? 'satellite' : 'standard';
        this.setTileLayer(currentType === 'standard' ? 'satellite' : 'standard');
    },
    
    /**
     * Clear all markers
     */
    clearMarkers() {
        this.markerClusterGroup.clearLayers();
        this.markers = [];
    },
    
    /**
     * Add project markers to map
     */
    addMarkers(projects) {
        this.clearMarkers();
        
        if (!projects || projects.length === 0) return;
        
        // Generate and display legend
        const legendHTML = this.generateLegend(projects);
        const legendContainer = document.getElementById('project-legend');
        if (legendContainer) {
            legendContainer.innerHTML = legendHTML;
        }
        
        const markers = [];
        
        projects.forEach(project => {
            // Filter by status if set
            if (this.currentFilter !== 'all' && project.status !== this.currentFilter) {
                return;
            }
            
            // Parse coordinates with high precision (6 decimal places)
            const lat = parseFloat(project.latitude);
            const lng = parseFloat(project.longitude);
            
            // Validate coordinates with strict checking
            if (isNaN(lat) || isNaN(lng)) {
                console.warn(`Invalid coordinates for project ${project.site_code}: lat=${project.latitude}, lng=${project.longitude}`);
                return;
            }
            
            if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                console.warn(`Coordinates out of range for project ${project.site_code}: lat=${lat}, lng=${lng}`);
                return;
            }
            
            // Format coordinates to exactly 6 decimal places for display
            const latFormatted = lat.toFixed(6);
            const lngFormatted = lng.toFixed(6);
            
            // Get color based on project name
            const projectColor = this.getProjectColor(project.project_name);
            
            // Status indicator color (border color)
            const statusColor = project.status === 'Done' ? '#10B981' : '#F97316';
            
            // Create custom icon with project color and status border
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    width: 28px;
                    height: 28px;
                    background-color: ${projectColor};
                    border-radius: 50%;
                    border: 4px solid ${statusColor};
                    box-shadow: 0 3px 6px rgba(0,0,0,0.4);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: transform 0.2s;
                " onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'"></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -14]
            });
            
            // Create marker with precise coordinates
            const marker = L.marker([lat, lng], { 
                icon,
                title: `${project.site_name} (${latFormatted}, ${lngFormatted})`
            });
            
            // Create popup content with precise coordinates
            const popupContent = `
                <div style="min-width: 300px; max-width: 350px;">
                    <div style="position: relative;">
                        ${project.photo_url ? `<img src="${project.photo_url}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;">` : ''}
                        <div style="position: absolute; top: 8px; right: 8px; display: flex; gap: 4px;">
                            <span style="background: ${projectColor}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                ${project.project_name.substring(0, 20)}${project.project_name.length > 20 ? '...' : ''}
                            </span>
                            <span style="background: ${statusColor}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                ${project.status}
                            </span>
                        </div>
                    </div>
                    <h3 style="font-weight: bold; margin-bottom: 6px; color: #1f2937; font-size: 15px;">${project.site_name}</h3>
                    <p style="margin: 3px 0; color: #4b5563; font-size: 13px;"><strong>Site Code:</strong> ${project.site_code}</p>
                    <p style="margin: 3px 0; color: #4b5563; font-size: 13px;"><strong>Location:</strong> ${project.barangay}, ${project.municipality}</p>
                    <p style="margin: 3px 0; color: #4b5563; font-size: 13px;"><strong>Province:</strong> ${project.province}</p>
                    <div style="margin: 6px 0; padding: 6px; background-color: #f3f4f6; border-radius: 4px; font-family: monospace; font-size: 11px;">
                        <strong>Coordinates:</strong><br>
                        <span style="color: #059669;">Lat: ${latFormatted}</span> | 
                        <span style="color: #dc2626;">Lng: ${lngFormatted}</span>
                    </div>
                    <p style="margin: 3px 0; color: #4b5563; font-size: 13px;"><strong>Date:</strong> ${project.activation_date_formatted || project.activation_date}</p>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e5e7eb; display: flex; gap: 8px;">
                        <a href="../pages/project-form.php?id=${project.id}" style="color: #3B82F6; font-size: 13px; font-weight: 600;">Edit Project →</a>
                        <span style="color: #9ca3af;">|</span>
                        <button onclick="navigator.clipboard.writeText('${latFormatted}, ${lngFormatted}').then(() => alert('Coordinates copied!'))" style="color: #10B981; font-size: 13px; font-weight: 600; background: none; border: none; cursor: pointer;">Copy Coords</button>
                    </div>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers.push(marker);
        });
        
        // Add markers to cluster group
        this.markerClusterGroup.addLayers(markers);
        this.markers = markers;
        
        // Fit bounds if we have markers with better precision
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            
            if (markers.length === 1) {
                // Single marker - zoom in closer for better precision view
                const marker = markers[0];
                const latLng = marker.getLatLng();
                this.map.setView(latLng, 18); // Maximum zoom for single point
            } else {
                // Multiple markers - fit bounds with smaller padding for tighter view
                this.map.fitBounds(group.getBounds(), {
                    padding: [30, 30],
                    maxZoom: 16, // Allow higher zoom for better precision
                    animate: true
                });
            }
        }
    },
    
    /**
     * Filter markers by status
     */
    filter(status) {
        this.currentFilter = status;
        this.loadProjects();
    },
    
    /**
     * Filter by province
     */
    filterByProvince(province) {
        this.loadProjects({ province });
    },
    
    /**
     * Load projects and display on map
     */
    async loadProjects(filters = {}) {
        try {
            if (this.currentFilter !== 'all') {
                filters.status = this.currentFilter;
            }
            
            const response = await API.projects.getAll(filters);
            if (response.success && response.data) {
                // Handle both paginated format (response.data.projects) and old format (response.data array)
                const projects = Array.isArray(response.data) ? response.data : (response.data.projects || []);
                this.addMarkers(projects);
            }
        } catch (error) {
            console.error('Failed to load projects:', error);
        }
    },
    
    /**
     * Draw zone/area on map
     */
    drawZone(coordinates, options = {}) {
        const defaultOptions = {
            color: '#3B82F6',
            fillColor: 'rgba(59, 130, 246, 0.2)',
            fillOpacity: 0.5,
            weight: 2
        };
        
        const polygon = L.polygon(coordinates, { ...defaultOptions, ...options }).addTo(this.map);
        return polygon;
    },
    
    /**
     * Refresh map
     */
    refresh() {
        this.loadProjects();
    }
};

// Global function for filter buttons
function filterMap(status) {
    MapService.filter(status);
}

// Global function for satellite toggle
function toggleMapView() {
    MapService.toggleView();
}

// Global function for province filter
function filterMapByProvince(province) {
    MapService.filterByProvince(province);
}
