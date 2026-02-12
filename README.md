# Project Tracking System

A web-based application for tracking and visualizing project implementations across the Philippines with geospatial data.

## Tech Stack

- **Backend**: Vanilla PHP 7.4+
- **Frontend**: Vanilla JavaScript (ES6+)
- **Styling**: Tailwind CSS (via CDN)
- **Database**: MySQL/MariaDB
- **Maps**: Leaflet.js
- **Charts**: Chart.js

## Features

### 1. Dashboard
- Interactive map showing all projects
- Status overview charts
- Recent projects table
- Quick stats cards

### 2. Project Management
- View all projects in searchable table
- Add new project via form
- Edit existing projects
- Delete projects
- Filter by status, province, date

### 3. CSV Import/Export
- Drag-and-drop CSV upload
- Real-time validation
- Auto-correction suggestions
- Error reporting
- Download template

### 4. Reports
- Summary report with statistics
- Province analysis with charts
- Timeline report with monthly data
- Status report with pending duration

### 5. Data Validation
- Required field validation
- Coordinate validation (range checks)
- Date format validation
- Status value normalization
- Duplicate site code detection
- Province/municipality standardization

## Installation

### Prerequisites
- XAMPP/WAMP/MAMP installed
- PHP 7.4 or higher
- MySQL/MariaDB

### Setup Steps

1. **Clone/Copy the project** to your web root:
   ```
   C:\xampp\htdocs\projects\newPTS
   ```

2. **Create the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database called `project_tracking`
   - Import the SQL file: `sql/schema.sql`

3. **Update database configuration** (if needed):
   - Edit `config/database.php`
   - Update DB_HOST, DB_USERNAME, DB_PASSWORD if different from defaults

4. **Access the application**:
   - Open your browser
   - Go to: `http://localhost/projects/newPTS/`
   - Default login: `admin` / `admin123`

## File Structure

```
newPTS/
├── api/                    # API endpoints
│   ├── auth.php           # Authentication API
│   ├── projects.php       # Projects CRUD API
│   ├── import.php         # CSV import/export API
│   └── reports.php        # Reports API
├── assets/
│   ├── css/
│   │   └── styles.css     # Custom styles
│   └── js/
│       ├── api.js         # API utilities
│       ├── app.js         # Main application logic
│       ├── charts.js      # Chart.js integration
│       ├── map.js         # Leaflet map integration
│       └── validation.js  # Form validation
├── config/
│   └── database.php       # Database configuration
├── includes/
│   ├── auth.php           # Authentication functions
│   ├── functions.php      # Utility functions
│   ├── header.php         # Common header template
│   └── footer.php         # Common footer template
├── pages/                  # Application pages
│   ├── dashboard.php      # Dashboard
│   ├── projects.php       # All projects list
│   ├── project-form.php   # Add/Edit project
│   ├── import.php         # CSV import page
│   └── reports.php        # Reports page
├── sql/
│   └── schema.sql         # Database schema
├── .htaccess              # URL rewriting
├── index.php              # Login page
├── logout.php             # Logout handler
└── SPEC.md                # Technical specification
```

## API Endpoints

### Authentication
- `POST /api/auth.php` - Login/Logout
- `GET /api/auth.php` - Check session

### Projects
- `GET /api/projects.php` - List all projects
- `GET /api/projects.php?id=X` - Get single project
- `POST /api/projects.php` - Create project
- `PUT /api/projects.php` - Update project
- `DELETE /api/projects.php?id=X` - Delete project
- `GET /api/projects.php?action=stats` - Get statistics

### Import/Export
- `POST /api/import.php` - Import CSV file
- `GET /api/import.php` - Export all projects as CSV
- `GET /api/import.php?action=template` - Download CSV template

### Reports
- `GET /api/reports.php?report=summary` - Summary report data
- `GET /api/reports.php?report=province` - Province analysis
- `GET /api/reports.php?report=timeline` - Timeline report
- `GET /api/reports.php?report=status` - Status report
- `GET /api/reports.php?report=provinces-list` - Get provinces list

## CSV Import Format

Required headers (case-insensitive):
```
Site Code, Project Name, Site Name, Barangay, Municipality, Province, District, Latitude, Longitude, Date of Activation, Status, Notes
```

**Validation Rules:**
- All fields except Notes are required
- Latitude: -90 to 90 (must be valid number)
- Longitude: -180 to 180 (must be valid number)
- Status: Must be "Done" or "Pending" (auto-normalized)
- Site Code: Must be unique
- Date: Various formats accepted (auto-parsed)

**Auto-Correction:**
- Province names: Case-insensitive matching and similarity search
- Status: Auto-capitalized and normalized
- Location names: Proper capitalization
- Date formats: Attempts to parse various formats

## Security Features

- Session-based authentication
- Password hashing (bcrypt)
- Input sanitization
- SQL injection prevention (prepared statements)
- CSRF protection ready
- XSS prevention

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Development

To modify or extend the application:

1. **Database Changes**: Update `sql/schema.sql` and run migrations
2. **API Changes**: Modify files in `api/` directory
3. **UI Changes**: Edit files in `pages/` and `assets/` directories
4. **New Features**: Follow the existing code structure and patterns

## Troubleshooting

### Database Connection Error
- Check `config/database.php` credentials
- Ensure MySQL service is running
- Verify database `project_tracking` exists

### Map Not Loading
- Check internet connection (Leaflet loads from CDN)
- Verify no ad-blocker is blocking the tiles

### Charts Not Displaying
- Check browser console for JavaScript errors
- Verify Chart.js loaded from CDN

### Import Errors
- Ensure CSV uses UTF-8 encoding
- Check for proper header names
- Verify file is not corrupted

## License

This project is open source and available for use and modification.

## Support

For issues or questions:
1. Check the browser console for error messages
2. Review the application logs
3. Verify database connectivity
4. Ensure all prerequisites are met
