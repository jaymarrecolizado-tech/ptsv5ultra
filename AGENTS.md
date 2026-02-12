# Project Implementation Tracking System - Agent Guide

This is a single-file web application for tracking and visualizing project implementations across the Philippines with geospatial data.

## Project Overview

A standalone HTML/JS/CSS application that provides:
- Interactive map visualization of project locations (Leaflet.js)
- Dashboard with status charts and analytics (Chart.js)
- CSV import/export functionality (PapaParse)
- Manual project entry with validation
- Comprehensive reporting (summary, province analysis, timeline, status)

## How to Run

Simply open `index.html` in any modern web browser. No build process, dependencies, or server required.

```bash
# Option 1: Direct file open
open index.html

# Option 2: Simple HTTP server (for better development experience)
python -m http.server 8000
# Then visit http://localhost:8000
```

## Project Structure

This is a single-file application with everything embedded:

```
index.html (2150 lines)
├── HTML Structure (lines 1-1050)
│   ├── Sidebar navigation
│   ├── Dashboard tab (map + charts + recent projects table)
│   ├── All Projects tab (searchable table)
│   ├── Manual Entry tab (form)
│   ├── Data Migration tab (CSV import)
│   └── Reports tab (4 sub-reports)
├── CSS Styles (lines 15-570)
│   └── Embedded in `<style>` tags
└── JavaScript (lines 1052-2147)
    └── Embedded in `<script>` tags
```

## Dependencies (CDN-Based)

All dependencies are loaded from CDNs - no npm/local installation needed:

- **Leaflet.js** (v1.9.4): Interactive maps
- **PapaParse** (v5.3.0): CSV parsing
- **Chart.js**: Data visualization charts

## Data Model

Projects are stored in the `projects` array with the following structure:

```javascript
{
  siteCode: string,        // e.g., "UNDP-GI-0009A" (unique identifier)
  projectName: string,     // e.g., "Free-WIFI for All"
  siteName: string,        // e.g., "Raele Barangay Hall - AP 1"
  barangay: string,        // e.g., "Raele"
  municipality: string,    // e.g., "Itbayat"
  province: string,        // e.g., "Batanes" or "Cagayan"
  district: string,        // e.g., "District I"
  latitude: number,         // e.g., 20.728794
  longitude: number,        // e.g., 121.804235
  activationDate: string,  // Format: "Month DD, YYYY" (e.g., "April 30, 2024")
  status: string,          // "Done" or "Pending" (case-sensitive)
  notes: string            // Optional field
}
```

## Key Functions

### Initialization
- `initMap()`: Initializes Leaflet map centered on Philippines (17.0, 121.0)
- `loadInitialData()`: Loads sample data from hardcoded CSV array
- `setupEventListeners()`: Binds all UI interactions
- `initCharts()`: Creates all Chart.js instances

### Data Management
- `addManualProject()`: Adds project from form, validates inputs, auto-formats date
- `exportData()`: Exports all projects as CSV file
- `handleFileUpload()`: Parses uploaded CSV/Excel using PapaParse
- `validateCSVData()`: Validates CSV data with auto-correction

### Rendering
- `renderProjectsOnMap()`: Adds markers for all projects to Leaflet map
- `filterMapProjects()`: Filters map markers by status
- `renderRecentProjects()`: Shows 5 most recent projects in dashboard
- `renderAllProjectsTable()`: Populates main projects table
- `updateCharts()`: Refreshes all Chart.js charts with current data

## CSV Import Format

Required CSV headers (case-sensitive):
```
Site Code, Project Name, Site Name, Barangay, Municipality, Province, District, Latitude, Longitude, Date of Activation, Status, Notes (optional)
```

**Validation Rules:**
- All fields except Notes are required
- Latitude: -90 to 90 (must be valid number)
- Longitude: -180 to 180 (must be valid number)
- Status: Must be exactly "Done" or "Pending"
- Site Code: Must be unique across all projects
- Date: Auto-converted to "Month DD, YYYY" format

**Auto-Correction:**
- Province names: Case-insensitive matching ("batanes" → "Batanes")
- Status: Auto-capitalized ("done" → "Done")
- Date formats: Attempts to parse various formats

## CSS Conventions

**Color Variables (CSS Custom Properties):**
```css
--primary-color: #3498db
--secondary-color: #2ecc71
--accent-color: #e74c3c
--dark-color: #2c3e50
--light-color: #ecf0f1
--warning-color: #f39c12
--success-color: #27ae60
--border-radius: 8px
--box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1)
--transition: all 0.3s ease
```

**Utility Classes:**
- `.hidden`: `display: none`
- `.fade-in`: Animation for new elements
- `.status-badge.done`: Green badge for completed projects
- `.status-badge.pending`: Orange badge for pending projects
- `.loading`: Spinner animation

## Code Patterns

### Event Binding Pattern
All event listeners are centralized in `setupEventListeners()`:
```javascript
document.getElementById('element-id').addEventListener('click', function() {
    // Handler logic
});
```

### DOM Manipulation Pattern
```javascript
// Clear container
container.innerHTML = '';

// Build elements
const row = document.createElement('tr');
row.innerHTML = `template string with ${variable}`;
container.appendChild(row);
```

### Data Flow
1. Data source: `projects` array (in-memory)
2. UI updates: Call render functions after data changes
3. Example flow:
   ```javascript
   projects.push(newProject);  // Modify data
   updateStats();              // Update stats
   renderProjectsOnMap();      // Update map
   renderAllProjectsTable();   // Update table
   updateCharts();             // Update charts
   ```

## Chart Instances

Global chart variables (initialized in `initCharts()`):
- `statusChart`: Doughnut chart (dashboard)
- `timelineChart`: Line chart (dashboard)
- `provinceChart`: Bar chart (reports)
- `statusDistributionChart`: Pie chart (reports)
- `provinceDetailedChart`: Stacked bar chart
- `timelineDetailedChart`: Multi-line chart
- `pendingDurationChart`: Bar chart
- `completionRateChart`: Bar chart

All charts are updated via `updateCharts()` when reports tab is accessed.

## Tab Navigation

**Main Navigation (Sidebar):**
- Dashboard (`#dashboard`)
- All Projects (`#projects`)
- Manual Entry (`#manual-entry`)
- Data Migration (`#data-migration`)
- Reports (`#reports`)

**Report Sub-Tabs:**
- Summary Report
- Province Analysis
- Timeline Report
- Status Report

## Important Gotchas

### Date Formatting
- Display format: `"Month DD, YYYY"` (e.g., "April 30, 2024")
- Input form uses HTML `<input type="date">` (YYYY-MM-DD)
- Conversion happens in `addManualProject()` and CSV validation
- Always format dates before storing/displaying

### Map Coordinates
- Default center: Philippines region (17.0, 121.0)
- All projects must have valid coordinates
- Markers are stored in global `markers` array for cleanup
- Always call `markers.forEach(marker => map.removeLayer(marker))` before re-rendering

### Status Values
- Must be exactly "Done" or "Pending" (case-sensitive)
- Stored as is, no enum constants
- UI classes: `.status-done`, `.status-pending`, `.status-badge.done`, `.status-badge.pending`

### Global State
- `projects`: Main data store (array of objects)
- `map`: Leaflet map instance
- `markers`: Array of active map markers
- All chart instances: Global variables for Chart.js charts
- These persist across tab switches and user sessions (no persistence to localStorage)

### No Data Persistence
- All data is in-memory only
- Page refresh resets to initial sample data
- Export/Import CSV is the only way to save/load data
- No backend, database, or localStorage implementation

### Alert Usage
- Simple `alert()` used for notifications
- No custom toast/modal system
- This is intentional for the single-file simplicity

## Testing the Application

**Manual Testing Checklist:**
1. Open `index.html` in browser
2. Verify map loads with initial markers
3. Check charts display correctly
4. Add new project via Manual Entry tab
5. Verify project appears in map and tables
6. Test CSV import with sample data
7. Test CSV export
8. Verify all reports generate correctly

**Browser Compatibility:**
- Modern browsers with ES6+ support
- Requires JavaScript enabled
- Tested on Chrome, Firefox, Safari (latest versions)

## Common Modifications

### Adding New Fields
1. Update `loadInitialData()` sample data structure
2. Add to HTML forms (Manual Entry tab)
3. Update `addManualProject()` to capture new field
4. Update `validateCSVData()` required fields array
5. Update table HTML in render functions
6. Add to `exportData()` CSV headers

### Changing Chart Types
1. Modify Chart.js `type` in `initCharts()`
2. Update data structure in `updateCharts()`
3. Adjust `options` for new chart type

### Adding New Provinces
1. Update `<select id="province">` in Manual Entry form
2. No changes needed to validation (auto-correct handles case variations)

### Customizing Map Tiles
```javascript
// In initMap(), replace:
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {...})

// With other tile providers (e.g., CartoDB, Mapbox, etc.)
```

## Performance Considerations

- All data loaded at once in `loadInitialData()`
- No lazy loading or pagination implemented
- For large datasets (>1000 projects), consider:
  - Pagination for tables
  - Marker clustering on map
  - Lazy loading for charts
- Charts redraw on every tab switch to reports section

## Security Notes

- No server-side validation (client-side only)
- No CSRF protection needed (no backend)
- CSV parsing uses PapaParse with default settings
- No sanitization of user input before rendering (XSS risk if data from untrusted sources)
- For production: Add input sanitization before using in `.innerHTML`

## Deployment

Simply serve the `index.html` file:
- Static hosting (GitHub Pages, Netlify, Vercel)
- Any web server (nginx, Apache)
- No build step required
