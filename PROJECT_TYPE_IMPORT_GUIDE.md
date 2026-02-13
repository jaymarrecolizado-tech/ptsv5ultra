# Project Type Import System Documentation

## Overview

The Project Tracking System now supports **10 different project types**, each with its own unique fields, validation rules, and report formats. This allows for flexible data import and type-specific reporting.

## Supported Project Types

### 1. Free-WIFI for All
**Purpose:** Public WiFi hotspots in government facilities

**Custom Fields:**
- Number of Access Points (required)
- Bandwidth (Mbps) - 10, 20, 50, or 100 (required)
- Internet Provider - PLDT, Globe, Smart, DITO, Converge, Others

**Site Code Pattern:** `WIFI-XXXX` (e.g., `WIFI-0001`)

**Report Metrics:**
- Total Access Points
- Average Bandwidth
- Provider Distribution

---

### 2. Tech4ED
**Purpose:** Technology for Education digital learning centers

**Custom Fields:**
- Number of Computers (1-50)
- Number of Printers (0-5)
- Trained Personnel
- Courses Offered (multi-select)
- Number of Beneficiaries

**Site Code Pattern:** `T4E-XXXX`

**Report Metrics:**
- Total Computers Deployed
- Total Beneficiaries
- Popular Courses

---

### 3. National Broadband Program
**Purpose:** Fiber optic and broadband infrastructure

**Custom Fields:**
- Infrastructure Type - Fiber Node, Tower Site, Point of Presence, Cable Landing, Distribution Point
- Fiber Length (km)
- Coverage Area
- Connected Institutions

**Site Code Pattern:** `NBP-XXXX`

**Report Metrics:**
- Total Fiber Length (km)
- Institutions Connected
- Infrastructure Breakdown

---

### 4. Digital Cities Program
**Purpose:** Smart city infrastructure and digital services

**Custom Fields:**
- Facility Type - Command Center, Free WiFi Zone, Payment Hub, Information Kiosk, E-Government Center, CCTV Network
- Number of CCTV Cameras
- IoT Sensors Deployed
- Digital Services Offered (multi-select)

**Site Code Pattern:** `DCP-XXXX`

**Report Metrics:**
- Total CCTV Cameras
- Total IoT Sensors
- Smart City Readiness Score

---

### 5. PIPOL (Philippine Public Library System)
**Purpose:** Library digitization program

**Custom Fields:**
- Library Type - Public Library, School Library, Barangay Reading Center, Mobile Library, Digital Library
- Computer Workstations
- E-Books Available
- Physical Books Count
- Monthly Patrons

**Site Code Pattern:** `PIP-XXXX`

**Report Metrics:**
- Total Workstations
- Total E-Books
- Libraries by Type

---

### 6. Rural Impact Sourcing
**Purpose:** BPO and online freelancing hubs in rural areas

**Custom Fields:**
- Hub Type - BPO Training Center, Freelancers Hub, Digital Workers Co-op, Remote Work Station, Community Work Hub
- Available Workstations
- Total Graduates/Trainees
- Active Digital Workers
- Training Programs (multi-select)

**Site Code Pattern:** `RIS-XXXX`

**Report Metrics:**
- Total Graduates
- Active Digital Workers
- Employment Rate

---

### 7. e-Government Services
**Purpose:** Online government services and digital transactions

**Custom Fields:**
- Service Type - Business Permits, Civil Registry, Tax Payment, Health Records, Public Assistance, Document Processing
- Services Offered (multi-select)
- Average Daily Transactions

**Site Code Pattern:** `EGS-XXXX`

**Report Metrics:**
- Total Services Offered
- Daily Transactions
- Digital Adoption Rate

---

### 8. ICT Literacy Programs
**Purpose:** Digital skills training and computer literacy

**Custom Fields:**
- Program Type - Basic Computer Training, Senior Digital Literacy, Youth Coding Camp, Women in Tech, Teachers Training
- Training Hours
- Participants Trained
- Certification Offered (Yes/No)

**Site Code Pattern:** `ICT-XXXX`

**Report Metrics:**
- Total Participants
- Total Training Hours
- Certified Programs

---

### 9. Cybersecurity Awareness
**Purpose:** Online safety and security education

**Custom Fields:**
- Center Type - Seminar Hall, Workshop Venue, Prevention Center, Privacy Hub, Youth Cyber Club
- Topics Covered (multi-select)
- Attendees Educated
- Sessions Conducted

**Site Code Pattern:** `CSA-XXXX`

**Report Metrics:**
- Total Attendees
- Total Sessions
- Awareness Coverage

---

### 10. Free Internet in Public Places
**Purpose:** Free internet access in public areas

**Custom Fields:**
- Location Type - Municipal Plaza, Public Market, Hospital, Transport Terminal, Public School, Park, Barangay Hall
- Coverage Radius (meters)
- Expected Daily Users

**Site Code Pattern:** `FIP-XXXX`

**Report Metrics:**
- Total Coverage Radius
- Expected Users
- Public Access Score

---

## Common Fields (All Project Types)

Every project type includes these standard fields:
- Site Code (unique identifier)
- Project Name
- Site Name
- Barangay
- Municipality
- Province (Batanes, Cagayan, Isabela, Nueva Vizcaya, Quirino)
- District
- Latitude
- Longitude
- Activation Date
- Status (Done/Pending)
- Notes

---

## Import Process

### Step 1: Select Project Type
Go to **Import by Project Type** page and select the appropriate project type from the grid.

### Step 2: Download Template
Click "Download Template" to get the correct CSV format for your selected project type. Each template includes:
- Proper column headers matching the field labels
- A sample row with example data
- Required fields marked

### Step 3: Prepare Your Data
Fill in your CSV file following the template format:
- Use the exact column headers from the template
- Ensure required fields are filled
- Follow the format specified for each field type
- Dates should be in YYYY-MM-DD format

### Step 4: Upload CSV
Drag and drop your CSV file or click to browse. The system will:
1. Validate the file format
2. Check required fields
3. Validate data types (numbers, dates, etc.)
4. Check for duplicate site codes
5. Import valid rows

### Step 5: Review Results
The system will display:
- Number of successfully imported projects
- Any validation errors with row numbers
- Error details for fixing and re-importing

---

## CSV Template Format Example

### Free-WIFI for All Template:
```csv
Site Code,Project Name,Site Name,Number of Access Points,Bandwidth (Mbps),Barangay,Municipality,Province,District,Latitude,Longitude,Date of Activation,Status,Internet Provider,Notes
WIFI-0001,Free-WIFI for All,Barangay Hall - AP 1,2,50,Centro,Tuguegarao,Cagayan,District I,17.6132,121.7269,2024-01-15,Done,PLDT,Installation complete
```

### Tech4ED Template:
```csv
Site Code,Project Name,Center Name,Number of Computers,Number of Printers,Trained Personnel,Courses Offered,Barangay,Municipality,Province,District,Latitude,Longitude,Date Established,Status,Number of Beneficiaries,Notes
T4E-0001,Tech4ED Program,Tech4ED Center - Main,15,2,3,"Computer Basics,Microsoft Office",Centro,Ilagan,Isabela,District I,17.1489,121.8893,2024-01-20,Done,200,Computer lab operational
```

---

## Validation Rules

### Text Fields
- Must not be empty if required
- Site codes must match the pattern for each project type
- Maximum length varies by field

### Number Fields
- Must be valid numbers
- Within specified min/max range
- Examples: Access Points (1-10), Computers (1-50)

### Select Fields
- Must be one of the allowed options
- Case-sensitive matching

### Date Fields
- Must be valid dates
- YYYY-MM-DD format preferred
- Various formats accepted (auto-parsed)

### Multi-select Fields
- Multiple values separated by commas
- Each value must be in the allowed list

---

## Type-Specific Reports

Each project type has custom reports showing relevant metrics:

### Report Types:
1. **Summary Report** - Overview with custom metrics
2. **Charts** - Visual representations of data
3. **Details by Location** - Breakdown by municipality
4. **Timeline** - Project activation over time

### Accessing Reports:
1. Go to the **Reports** section
2. Select the project type
3. Choose report filters (province, status, date range)
4. View or export the report

---

## Database Schema

### projects table (updated)
```sql
- id (primary key)
- site_code (unique)
- project_name
- project_type
- custom_data (JSON) -- stores type-specific fields
- site_name
- barangay
- municipality
- province
- district
- latitude
- longitude
- activation_date
- status
- notes
- created_by
- created_at
- updated_at
```

### project_type_fields table
```sql
- Stores field definitions for each project type
- field_name, field_label, field_type
- is_required, validation_rules (JSON)
- field_options (JSON for select/multiselect)
```

### import_templates table
```sql
- Stores CSV templates for each project type
- csv_headers, field_mapping, sample_data
```

---

## API Endpoints

### Get Project Types
```
GET /api/import-typed.php?action=get-project-types
```
Returns list of available project types with descriptions and icons.

### Get Template
```
GET /api/import-typed.php?action=get-template&project_type=Tech4ED
```
Returns template details including headers and sample data.

### Download Template
```
GET /api/import-typed.php?action=download-template&project_type=Tech4ED
```
Downloads CSV file with proper headers and sample row.

### Import CSV
```
POST /api/import-typed.php?action=import
Content-Type: multipart/form-data
Parameters:
  - project_type: "Tech4ED"
  - csv_file: [file upload]
```
Returns import results with success count and any errors.

### Get Type Report
```
GET /api/reports-typed.php?action=get-report&project_type=Tech4ED&province=Cagayan&status=Done
```
Returns comprehensive report for the specified project type.

### Get Summary
```
GET /api/reports-typed.php?action=get-summary
```
Returns summary statistics for all project types.

---

## Troubleshooting

### "Unknown project type" Error
Ensure the project_type parameter matches exactly with one of the configured types.

### Validation Errors
Check that:
- Required fields are not empty
- Numbers are within specified ranges
- Select field values match allowed options exactly
- Dates are in valid format

### Duplicate Site Codes
Each site_code must be unique across all projects. Check existing projects before importing.

### CSV Format Issues
- Use UTF-8 encoding
- Include all required columns
- Don't add extra columns not in the template
- Use commas as separators
- Quote text fields containing commas

---

## Best Practices

1. **Always download the template** for your project type before preparing data
2. **Use the correct site code pattern** for each project type
3. **Validate your CSV** before importing by checking a few rows manually
4. **Import in batches** if you have more than 500 rows
5. **Review error messages** carefully and fix issues before re-importing
6. **Keep backups** of your CSV files
7. **Test with a small batch** (5-10 rows) before importing large datasets

---

## Migration Notes

If you have existing data:
1. Existing projects without project_type will default to "Free-WIFI for All"
2. Custom fields can be added to the custom_data JSON column
3. Run `migrate-project-types.php` to set up the new database structure
4. Existing projects will continue to work with standard reports

---

## Support

For issues or questions:
1. Check this documentation
2. Review validation error messages carefully
3. Compare your CSV with the downloaded template
4. Check the browser console for JavaScript errors
5. Verify database connectivity

---

**Last Updated:** 2024
**Version:** 1.0
