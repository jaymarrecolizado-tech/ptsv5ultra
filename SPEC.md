# Project Tracking System - Technical Specification

## Overview
A web-based application for tracking and visualizing project implementations across the Philippines with geospatial data.

## Tech Stack
- **Backend**: Vanilla PHP 7.4+
- **Frontend**: Vanilla JavaScript (ES6+)
- **Styling**: Tailwind CSS (via CDN)
- **Database**: MySQL/MariaDB
- **Maps**: Leaflet.js
- **Charts**: Chart.js
- **CSV Processing**: Custom PHP parser + PapaParse (JS)

## Database Schema

### Tables

#### 1. users
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2. projects
```sql
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_code VARCHAR(50) UNIQUE NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    municipality VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 6) NOT NULL,
    longitude DECIMAL(10, 6) NOT NULL,
    activation_date DATE NOT NULL,
    status ENUM('Done', 'Pending') NOT NULL,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 3. validation_logs
```sql
CREATE TABLE validation_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    import_batch_id VARCHAR(50),
    row_number INT,
    field_name VARCHAR(50),
    error_message TEXT,
    original_value TEXT,
    corrected_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## File Structure

```
newPTS/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── header.php            # Common header
│   ├── footer.php            # Common footer
│   ├── auth.php              # Authentication functions
│   └── functions.php         # Utility functions
├── api/
│   ├── projects.php          # Project CRUD API
│   ├── import.php            # CSV import API
│   ├── export.php            # CSV export API
│   ├── reports.php           # Reports API
│   └── auth.php              # Authentication API
├── assets/
│   ├── css/
│   │   └── styles.css        # Custom styles
│   └── js/
│       ├── app.js            # Main application JS
│       ├── map.js            # Map functionality
│       ├── charts.js         # Chart functionality
│       ├── validation.js     # Form validation
│       └── api.js            # API utilities
├── pages/
│   ├── dashboard.php         # Dashboard view
│   ├── projects.php          # All projects list
│   ├── project-form.php      # Add/Edit project
│   ├── import.php            # CSV import page
│   └── reports.php           # Reports page
├── sql/
│   └── schema.sql            # Database schema
├── .htaccess                 # URL rewriting
└── index.php                 # Entry point (login/dashboard)
```

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/check` - Check session

### Projects
- `GET /api/projects` - List all projects (with filters)
- `GET /api/projects/{id}` - Get single project
- `POST /api/projects` - Create project
- `PUT /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Delete project
- `GET /api/projects/stats` - Get statistics

### Import/Export
- `POST /api/import/csv` - Import CSV file
- `GET /api/export/csv` - Export all projects as CSV
- `GET /api/export/template` - Download CSV template

### Reports
- `GET /api/reports/summary` - Summary report data
- `GET /api/reports/province` - Province analysis
- `GET /api/reports/timeline` - Timeline report
- `GET /api/reports/status` - Status report

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

## Security

- Session-based authentication
- CSRF token validation
- Input sanitization (XSS prevention)
- SQL injection prevention (prepared statements)
- Password hashing (bcrypt)
- Role-based access control

## UI Components

### Tailwind Classes Used
- Layout: `flex`, `grid`, `container`, `sidebar`, `main-content`
- Cards: `bg-white`, `rounded-lg`, `shadow-md`, `p-6`
- Buttons: `bg-blue-500`, `hover:bg-blue-600`, `rounded`, `px-4`, `py-2`
- Forms: `input`, `select`, `textarea` with border and focus states
- Tables: `w-full`, `divide-y`, `hover:bg-gray-50`
- Status badges: `bg-green-100`, `text-green-800`, `rounded-full`

## Response Format

All API responses follow this format:
```json
{
  "success": true|false,
  "data": {},
  "message": "",
  "errors": []
}
```

## CSV Import Format

Required headers:
- Site Code
- Project Name
- Site Name
- Barangay
- Municipality
- Province
- District
- Latitude
- Longitude
- Date of Activation
- Status
- Notes (optional)

## Development Phases

### Phase 1: Core Setup
- Database schema
- Authentication system
- Basic project CRUD

### Phase 2: UI Implementation
- Dashboard with map
- Project list and forms
- Navigation and layout

### Phase 3: Advanced Features
- CSV import/export
- Data validation
- Auto-correction

### Phase 4: Reporting
- Charts integration
- Report generation
- Data analytics
