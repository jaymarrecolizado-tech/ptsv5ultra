/**
 * Map Service
 * Handles Leaflet map initialization, marker management, and clustering
 */

const MapService = {
    map: null,
    markers: [],
    markerCluster: null,
    defaultCenter: [17.0, 121.0],
    defaultZoom: 7,

    init(containerId = 'map') {
        if (this.map) {
            this.map.remove();
        }

        this.map = L.map(containerId).setView(this.defaultCenter, this.defaultZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(this.map);

        this.addReferenceMarker();

        return this.map;
    },

    addReferenceMarker() {
        L.marker([14.5995, 120.9842])
            .addTo(this.map)
            .bindPopup('National Capital Region (Reference Point)')
            .openPopup();
    },

    clearMarkers() {
        if (this.markerCluster) {
            this.markerCluster.clearLayers();
        }
        this.markers.forEach(marker => {
            if (this.map.hasLayer(marker)) {
                this.map.removeLayer(marker);
            }
        });
        this.markers = [];
    },

    renderProjects(projects, options = {}) {
        this.clearMarkers();

        const { statusFilter = 'all', showPopup = true } = options;

        const filteredProjects = statusFilter === 'all'
            ? projects
            : projects.filter(p => p.status === statusFilter);

        if (typeof L !== 'undefined' && L.markerClusterGroup) {
            this.markerCluster = L.markerClusterGroup({
                chunkedLoading: true,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                maxClusterRadius: 50
            });

            filteredProjects.forEach(project => {
                const marker = this.createMarker(project, showPopup);
                this.markerCluster.addLayer(marker);
            });

            this.map.addLayer(this.markerCluster);
        } else {
            filteredProjects.forEach(project => {
                const marker = this.createMarker(project, showPopup);
                this.markers.push(marker);
                marker.addTo(this.map);
            });
        }

        if (filteredProjects.length > 0 && !this.markerCluster) {
            const group = new L.featureGroup(this.markers);
            this.map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }

        return this.markers.length;
    },

    createMarker(project, showPopup = true) {
        const icon = this.getStatusIcon(project.status);

        const marker = L.marker([project.latitude, project.longitude], { icon });

        if (showPopup) {
            const popupContent = this.createPopupContent(project);
            marker.bindPopup(popupContent, { maxWidth: 300 });
        }

        marker.projectData = project;
        this.markers.push(marker);

        return marker;
    },

    getStatusIcon(status) {
        const colors = {
            'Done': { color: '#27ae60', icon: '✓' },
            'Pending': { color: '#f39c12', icon: '⏳' }
        };

        const config = colors[status] || colors['Pending'];

        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                background-color: ${config.color};
                width: 30px;
                height: 30px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
                font-size: 14px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                border: 2px solid white;
            ">${config.icon}</div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -15]
        });
    },

    createPopupContent(project) {
        return `
            <div style="min-width: 200px; padding: 5px;">
                <h4 style="margin: 0 0 8px 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    ${Sanitizer.sanitizeHTML(project.projectName)}
                </h4>
                <p style="margin: 4px 0; font-size: 13px;">
                    <strong>Site:</strong> ${Sanitizer.sanitizeHTML(project.siteName)}
                </p>
                <p style="margin: 4px 0; font-size: 13px;">
                    <strong>Location:</strong> ${Sanitizer.sanitizeHTML(project.barangay)}, ${Sanitizer.sanitizeHTML(project.municipality)}
                </p>
                <p style="margin: 4px 0; font-size: 13px;">
                    <strong>Province:</strong> ${Sanitizer.sanitizeHTML(project.province)}
                </p>
                <p style="margin: 4px 0; font-size: 13px;">
                    <strong>Activated:</strong> ${Sanitizer.sanitizeHTML(project.activationDate)}
                </p>
                <p style="margin: 4px 0; font-size: 13px;">
                    <strong>Status:</strong>
                    <span style="
                        color: ${project.status === 'Done' ? '#27ae60' : '#f39c12'};
                        font-weight: bold;
                    ">${Sanitizer.sanitizeHTML(project.status)}</span>
                </p>
                <p style="margin: 4px 0; font-size: 12px; color: #999;">
                    <strong>Code:</strong> ${Sanitizer.sanitizeHTML(project.siteCode)}
                </p>
            </div>
        `;
    },

    filterByStatus(status) {
        return this.renderProjects(DataService.getAllProjects(), { statusFilter: status });
    },

    fitBoundsToProjects(projects) {
        if (projects.length === 0) return;

        const bounds = L.latLngBounds(
            projects.map(p => [p.latitude, p.longitude])
        );

        this.map.fitBounds(bounds, { padding: [50, 50] });
    },

    highlightProject(siteCode) {
        const project = DataService.getProject(siteCode);
        if (!project) return;

        const marker = this.markers.find(m => m.projectData?.siteCode === siteCode);
        if (marker) {
            this.map.setView(marker.getLatLng(), 12);
            marker.openPopup();
        }
    },

    destroy() {
        this.clearMarkers();
        if (this.markerCluster) {
            this.map.removeLayer(this.markerCluster);
            this.markerCluster = null;
        }
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
    }
};
