import React, { useEffect, useState } from 'react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import MarkerClusterGroup from 'react-leaflet-markercluster';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
// import 'react-leaflet-markercluster/dist/styles.min.css';
import { Project } from '@/types';

// Fix for default marker icon
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

// Custom marker icons based on status
const statusColors: Record<string, string> = {
  planning: '#f59e0b',
  in_progress: '#3b82f6',
  on_hold: '#ef4444',
  done: '#10b981',
  pending: '#6b7280',
  cancelled: '#dc2626',
};

function createCustomIcon(status: string): L.DivIcon {
  const color = statusColors[status] || '#6b7280';
  return L.divIcon({
    className: 'custom-marker',
    html: `
      <div style="
        background-color: ${color};
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
      ">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
          <path d="M12 2L8 6H4v4L0 14l4 4v4h4l4 4 4-4h4v-4l4-4-4-4V6h-4l-4-4zm0 3l3 3h3v3l3 3-3 3v3h-3l-3 3-3-3H6v-3l-3-3 3-3V8h3l3-3z"/>
        </svg>
      </div>
    `,
    iconSize: [36, 36],
    iconAnchor: [18, 18],
    popupAnchor: [0, -18],
  });
}

interface MapProps {
  projects: Project[];
  height?: string;
  onMarkerClick?: (project: Project) => void;
}

function MapBounds({ projects }: { projects: Project[] }) {
  const map = useMap();

  useEffect(() => {
    if (projects.length > 0) {
      const bounds = L.latLngBounds(
        projects.map((p) => [p.latitude, p.longitude])
      );
      map.fitBounds(bounds, { padding: [50, 50] });
    }
  }, [projects, map]);

  return null;
}

export default function ProjectMap({ projects, height = '500px', onMarkerClick }: MapProps) {
  const [isDark, setIsDark] = useState(document.documentElement.classList.contains('dark'));

  useEffect(() => {
    const observer = new MutationObserver(() => {
      setIsDark(document.documentElement.classList.contains('dark'));
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    return () => observer.disconnect();
  }, []);

  const tileUrl = isDark
    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
    : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

  return (
    <div style={{ height }} className="rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
      <MapContainer
        center={[13.0, 122.0]}
        zoom={6}
        style={{ height: '100%', width: '100%' }}
        className="dark:leaflet-dark"
      >
        <TileLayer
          url={tileUrl}
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        />
        <MapBounds projects={projects} />
        <MarkerClusterGroup
          chunkedLoading
          showCoverageOnHover={false}
          spiderfyOnMaxZoom={true}
          removeOutsideVisibleBounds={true}
        >
          {projects.map((project) => (
            <Marker
              key={project.id}
              position={[project.latitude, project.longitude]}
              icon={createCustomIcon(project.status)}
              eventHandlers={{
                click: () => onMarkerClick?.(project),
              }}
            >
              <Popup>
                <div className="p-2 min-w-[200px]">
                  <h3 className="font-bold text-gray-900 dark:text-white mb-1">
                    {project.project_name}
                  </h3>
                  <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    {project.site_name}
                  </p>
                  <div className="space-y-1 text-xs">
                    <p className="text-gray-600 dark:text-gray-400">
                      <span className="font-medium">Location:</span>{' '}
                      {project.barangay}, {project.municipality}, {project.province}
                    </p>
                    <p className="text-gray-600 dark:text-gray-400">
                      <span className="font-medium">Status:</span>{' '}
                      <span className="capitalize">{project.status.replace('_', ' ')}</span>
                    </p>
                    <p className="text-gray-600 dark:text-gray-400">
                      <span className="font-medium">Progress:</span> {project.progress}%
                    </p>
                  </div>
                  <button
                    onClick={() => onMarkerClick?.(project)}
                    className="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded transition-colors"
                  >
                    View Details
                  </button>
                </div>
              </Popup>
            </Marker>
          ))}
        </MarkerClusterGroup>
      </MapContainer>
    </div>
  );
}
