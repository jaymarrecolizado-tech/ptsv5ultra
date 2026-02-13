# DICT Project Tracking System - Implementation Specification
## Version 2.0 - Multi-Project Type Support

**Date Created:** February 13, 2026
**Status:** Ready for Implementation
**Priority Projects:** 3 (EgovPH, ELGU, Free-WiFi for All)

---

## Executive Summary

This specification details the implementation of a flexible project tracking system for DICT Region II that supports **10 different project types**, each with unique data structures, validation rules, and reporting requirements.

### Key Requirements:
- Geographic tracking (Latitude, Longitude, Province, District, Municipality, Barangay)
- Type-specific import templates
- Flexible data storage using JSON for custom fields
- Daily metrics tracking for infrastructure projects
- Activity/event tracking for training programs

---

## 1. Project Types Overview

### Priority 1 - Implement First:

#### 1.1 EgovPH (eGovernment Philippines)
**Purpose:** Track agency onboarding to eGovPH super app
**Data Structure:** Activity/Event-based
**Key Metrics:** Monthly transactions, app downloads, services enabled

**Fields:**
- Site Code (EGV-XXXX)
- Agency/Office Name (required)
- Services Enabled (multiselect): Business Permits, Tax Payment, Civil Registry, Document Request, etc.
- Monthly Transactions (number)
- Citizen Downloads (number)
- Go-Live Date (date)
- Status (Done/Pending)
- Location: Province, District, Municipality, Barangay, Lat, Long

**Report Metrics:**
- Total agencies onboarded
- Monthly transaction volume
- App download count
- Services utilization rate

---

#### 1.2 ELGU (eLocal Government Unit)
**Purpose:** Track LGU digitalization and EBOSS compliance
**Data Structure:** Site/Agency-based
**Key Metrics:** Services digitalized, EBOSS compliance

**Fields:**
- Site Code (ELG-XXXX)
- LGU Name (required)
- LGU Type (select): Municipal Hall, City Hall, Barangay Hall
- Services Digitalized (multiselect): Business Permits, Barangay Clearance, Building Permits, Cedula, Civil Registry, etc.
- EBOSS Compliance (Yes/No)
- Date Digitalized (date)
- Status (Done/Pending)
- Location: Province, District, Municipality, Barangay, Lat, Long

**Report Metrics:**
- Total LGUs digitalized
- EBOSS compliance rate
- Average services per LGU
- Provincial coverage

---

#### 1.3 Free-WiFi for All
**Purpose:** Track public WiFi infrastructure and utilization
**Data Structure:** Infrastructure site with daily metrics
**Key Metrics:** Bandwidth utilization, unique users, uptime

**Fields - Main:**
- Site Code (WIFI-XXXX)
- Site Name (required)
- Location Type (select): Hospital, School, LGU Hall, Public Market, Transport Terminal, Park, etc.
- AP Count (number, 1-10)
- Bandwidth Mbps (select: 10, 20, 50, 100)
- Provider (select): PLDT, Globe, Smart, DITO, Converge, Others
- Technology (select): Fiber, LTE, LEO, VSAT, MEO
- Activation Date (date)
- Status (Done/Pending)
- Location: Province, District, Municipality, Barangay, Lat, Long

**Fields - Daily Metrics (Separate Table):**
- Site Code (foreign key)
- Date (YYYY-MM-DD)
- Status (UP/DOWN/NO NMS)
- Bandwidth Utilization (decimal, e.g., 3.06)
- Unique Users (integer)
- Remarks (text)

**Report Metrics:**
- Total sites activated
- Average uptime percentage
- Bandwidth utilization trends
- Provider performance comparison
- Unique users per site/region

---

### Priority 2 - Implement Later:

#### 1.4 Cybersecurity
**Purpose:** Track cybersecurity awareness activities
**Data Structure:** Activity/Event-based

**Fields:**
- Activity ID (CYB-XXXX)
- Activity Title
- Activity Type: Training, Seminar, Workshop, Orientation
- Target Audience
- Number of Participants
- Topics Covered (multiselect)
- Date Conducted
- Facilitator
- Status (Done/Pending)
- Location: Province, District, Municipality, Barangay

---

#### 1.5 ILCDB (ICT Literacy & Competency Development)
**Purpose:** Track ICT training programs
**Data Structure:** Activity/Course-based

**Fields:**
- Training ID (ILC-XXXX)
- Course Name
- Course Category
- Training Mode: Online, Face-to-Face, Blended
- Participants Count
- Training Hours
- Certification Offered (Yes/No)
- Certified Count
- Target Sector
- Date Range
- Status (Done/Pending)
- Location: Province, District, Municipality, Barangay

---

#### 1.6 GovNet (Government Network)
**Purpose:** Track fiber connectivity for government agencies
**Data Structure:** Infrastructure site

**Fields:**
- Site Code (GVN-XXXX)
- Agency Name
- Agency Type
- Bandwidth Mbps
- Connection Type
- Point of Presence (POP)
- Activation Date
- Status (Connected/Pending/Down)
- Location: Province, District, Municipality, Barangay, Lat, Long

---

#### 1.7 IIDB (ICT Industry Development)
**Purpose:** Track digital jobs creation
**Data Structure:** Program/Activity-based

**Fields:**
- Program ID (IID-XXXX)
- Program Name
- Activity Type: Job Fair, Training, Partnership
- Company Name
- Jobs Created
- Participants
- Hired on Spot
- Industry Sector
- Date
- Status
- Location: Province, District, Municipality

---

#### 1.8 DTC (Digital Transformation Center)
**Purpose:** Track MSME digitalization support
**Data Structure:** Center-based with assisted enterprises

**Fields:**
- Center ID (DTC-XXXX)
- Center Name
- MSMEs Assisted
- Consultancy Sessions
- Digital Solutions (multiselect)
- Jobs Supported
- Launch Date
- Status
- Location: Province, District, Municipality, Barangay, Lat, Long

---

#### 1.9 GECS (Government Emergency Communications)
**Purpose:** Track emergency communication deployments
**Data Structure:** Deployment-based

**Fields:**
- Deployment ID (GEC-XXXX)
- Deployment Type: GECS-MOVE, CommsBox, Satellite Phone
- Equipment Name
- Disaster/Event Name
- Deployment Date
- Area Covered
- Beneficiaries
- Status
- Location: Province, District, Municipality, Barangay, Lat, Long

---

#### 1.10 PNPKI (Simplified - Organizational Only)
**Purpose:** Track bulk certificate issuance
**Data Structure:** Organization-based (not individual)

**Fields:**
- Batch ID (PNK-XXXX)
- Organization Name
- Organization Type
- Certificates Count
- Certificate Type
- Issued Date
- Expiry Date
- Status
- Location: Province, District, Municipality

---

## 2. Database Schema Design

### 2.1 Core Projects Table
```sql
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_code VARCHAR(50) UNIQUE NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    project_type ENUM('EgovPH', 'ELGU', 'Free-WiFi', 'Cybersecurity', 'ILCDB', 
                      'GovNet', 'IIDB', 'DTC', 'GECS', 'PNPKI') NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    barangay VARCHAR(100),
    municipality VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 6) NOT NULL,
    longitude DECIMAL(10, 6) NOT NULL,
    activation_date DATE,
    status ENUM('Done', 'Pending', 'Active', 'Down') NOT NULL,
    notes TEXT,
    custom_data JSON,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_project_type (project_type),
    INDEX idx_province (province),
    INDEX idx_status (status),
    INDEX idx_site_code (site_code)
);
```

### 2.2 Daily Metrics Table (for Free-WiFi)
```sql
CREATE TABLE daily_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_code VARCHAR(50) NOT NULL,
    metric_date DATE NOT NULL,
    status VARCHAR(20), -- UP, DOWN, NO NMS
    bandwidth_utilization DECIMAL(10, 2),
    unique_users INT,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_code) REFERENCES projects(site_code) ON DELETE CASCADE,
    UNIQUE KEY unique_date_site (site_code, metric_date),
    INDEX idx_metric_date (metric_date)
);
```

### 2.3 Activities Table (for EgovPH, ELGU Events)
```sql
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_id VARCHAR(50) UNIQUE NOT NULL,
    project_type ENUM('EgovPH', 'ELGU', 'Cybersecurity', 'ILCDB', 'IIDB') NOT NULL,
    activity_title VARCHAR(255) NOT NULL,
    activity_type VARCHAR(100),
    target_audience VARCHAR(255),
    participants_count INT,
    topics_covered TEXT,
    activity_date DATE,
    facilitator VARCHAR(100),
    venue VARCHAR(255),
    status ENUM('Done', 'Pending') NOT NULL,
    province VARCHAR(100),
    municipality VARCHAR(100),
    barangay VARCHAR(100),
    custom_data JSON,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_project_type (project_type),
    INDEX idx_activity_date (activity_date)
);
```

### 2.4 Project Type Fields Configuration Table
```sql
CREATE TABLE project_type_fields (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_type VARCHAR(100) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_label VARCHAR(255) NOT NULL,
    field_type ENUM('text', 'number', 'select', 'multiselect', 'date', 'checkbox', 'textarea') NOT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    validation_rules JSON,
    field_options JSON,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_field_per_type (project_type, field_name),
    INDEX idx_project_type (project_type)
);
```

### 2.5 Import Templates Table
```sql
CREATE TABLE import_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_type VARCHAR(100) NOT NULL,
    template_name VARCHAR(255) NOT NULL,
    template_description TEXT,
    csv_headers JSON NOT NULL,
    field_mapping JSON NOT NULL,
    sample_data JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_template_name (template_name),
    INDEX idx_project_type (project_type)
);
```

---

## 3. Configuration File Structure

### 3.1 config/project_types.php
Already created in previous session. Contains:
- 10 project type definitions
- Field configurations for each type
- Validation rules
- Report metrics
- Chart types

### 3.2 Key Configuration Elements:

#### Common Fields (All Projects):
- site_code, project_name, site_name
- province, municipality, district, barangay
- latitude, longitude
- activation_date, status, notes

#### Free-WiFi Specific:
- Daily bandwidth utilization tracking
- Unique users per day
- UP/DOWN status per day
- Provider performance metrics

#### Activity-Based Projects (EgovPH, ELGU, Cybersecurity, ILCDB):
- Event/activity tracking
- Participant counts
- Facilitator information
- Topics covered

---

## 4. Import System Design

### 4.1 CSV Import Modes:

#### Mode 1: Infrastructure Import
For: Free-WiFi, GovNet, DTC, GECS
- One row = One site/location
- Geographic coordinates required
- Status tracking per site

#### Mode 2: Activity Import  
For: EgovPH, ELGU, Cybersecurity, ILCDB, IIDB
- One row = One activity/event
- Date-based tracking
- Participant metrics

#### Mode 3: Daily Metrics Import
For: Free-WiFi bandwidth data only
- Many rows per site (one per day)
- Links to main project via site_code
- Historical trend data

### 4.2 Import Validation Rules:
- Site code uniqueness check
- Geographic coordinate validation
- Date format parsing
- Required field validation
- Dropdown option matching
- Duplicate detection

### 4.3 Error Handling:
- Row-by-row validation
- Detailed error messages
- Partial import support (skip errors, import valid rows)
- Error report generation

---

## 5. Report Generation

### 5.1 Type-Specific Reports:

#### Free-WiFi Reports:
- Site status summary
- Bandwidth utilization trends
- Provider performance comparison
- Geographic coverage map
- Uptime/downtime analysis

#### EgovPH Reports:
- Agency onboarding progress
- Transaction volume trends
- Service adoption rates
- Provincial coverage

#### ELGU Reports:
- LGU digitalization status
- EBOSS compliance tracking
- Services offered breakdown
- Completion timeline

### 5.2 Common Reports (All Types):
- Summary dashboard
- Province-wise distribution
- Status breakdown (Done/Pending)
- Timeline/progress tracking
- Export to CSV/PDF

---

## 6. Implementation Plan

### Phase 1: Foundation (Priority 1 Projects)
1. ✅ Create configuration file (project_types.php)
2. ✅ Create database migration script
3. ⬜ Build import API (import-typed.php)
4. ⬜ Create import UI (import-typed.php page)
5. ⬜ Build type-specific reports API
6. ⬜ Create CSV templates for 3 priority types
7. ⬜ Test with real data samples

### Phase 2: Enhancement (Priority 2 Projects)
1. ⬜ Add remaining 7 project types
2. ⬜ Create additional import templates
3. ⬜ Build advanced analytics dashboards
4. ⬜ Add data visualization charts
5. ⬜ Implement bulk operations

### Phase 3: Polish
1. ⬜ User feedback integration
2. ⬜ Performance optimization
3. ⬜ Documentation completion
4. ⬜ Training materials

---

## 7. Sample Data References

### Free-WiFi Sample:
File: `report REGION II SITE STATUS 2026 - JANUARY.csv`
Structure:
- Headers include daily dates (8-Jan-26, 9-Jan-26, etc.)
- Bandwidth utilization per day
- UP/DOWN status
- Unique user counts

### EgovPH Sample:
File: `report eGovPH Activities.csv`
Structure:
- Date, Title, Type of Activity
- Participants, Downloads
- Province, Municipality, District
- Status, Facilitator

---

## 8. Technical Requirements

### 8.1 Dependencies:
- PHP 7.4+
- MySQL 5.7+ (with JSON support)
- Tailwind CSS (already in project)
- Chart.js for visualizations
- Leaflet.js for maps

### 8.2 API Endpoints Needed:
```
GET  /api/import-typed.php?action=get-project-types
GET  /api/import-typed.php?action=get-template&project_type=XXX
GET  /api/import-typed.php?action=download-template&project_type=XXX
POST /api/import-typed.php?action=import
POST /api/import-typed.php?action=validate

GET  /api/reports-typed.php?action=get-report&project_type=XXX
GET  /api/reports-typed.php?action=get-summary
```

### 8.3 File Structure:
```
ptsv5ultra/
├── config/
│   └── project_types.php (✅ Created)
├── api/
│   ├── import-typed.php (✅ Created)
│   └── reports-typed.php (✅ Created)
├── pages/
│   └── import-typed.php (✅ Created)
├── sql/
│   └── mock-data.sql
├── migrate-project-types.php (✅ Created)
└── PROJECT_TYPE_IMPORT_GUIDE.md (✅ Created)
```

---

## 9. Critical Notes for Next Session

### 9.1 Geographic Data Importance:
- ALL projects must include Lat/Long for mapping
- Province/Municipality validation against standard list
- Barangay optional but preferred
- Coordinates must be accurate (not random)

### 9.2 Free-WiFi Daily Data:
- User confirmed: Import daily bandwidth utilization
- Store in separate daily_metrics table
- Link to main project via site_code
- Support historical trend analysis

### 9.3 Activity vs Infrastructure:
- EgovPH & ELGU: Can be treated as activities OR sites
- Recommendation: Track both
  - Main project record (site/office)
  - Activity records (events/orientations)

### 9.4 Validation Requirements:
- Site codes must be unique across all project types
- Dates must be valid
- Numeric fields must have proper ranges
- Dropdown values must match allowed options

---

## 10. Files Already Created (Do Not Recreate)

✅ `config/project_types.php` - Complete configuration for all 10 types
✅ `api/import-typed.php` - Import API with validation
✅ `api/reports-typed.php` - Report generation API
✅ `pages/import-typed.php` - Import UI page
✅ `migrate-project-types.php` - Database migration script
✅ `PROJECT_TYPE_IMPORT_GUIDE.md` - Documentation
✅ Updated `includes/header.php` - Added Import by Type menu

---

## 11. Next Steps (For Fresh Chat Session)

### Immediate Actions:
1. Run database migration to create new tables
2. Test import functionality with sample CSV files
3. Create proper CSV templates matching real data structure
4. Build type-specific report pages
5. Test geographic mapping accuracy

### Testing Checklist:
- [ ] Import EgovPH activities from sample CSV
- [ ] Import Free-WiFi sites with daily metrics
- [ ] Verify latitude/longitude plotting on map
- [ ] Check province/municipality grouping
- [ ] Validate site code uniqueness
- [ ] Test error handling and validation
- [ ] Generate type-specific reports
- [ ] Export data back to CSV

---

## 12. Contact & Context

**Previous Discussion Summary:**
- User provided 2 sample CSV files (Free-WiFi and eGovPH)
- Identified need for different data structures per project type
- Confirmed geographic tracking is critical
- Agreed to start with 3 priority projects
- User confirmed daily bandwidth data import for Free-WiFi

**Ready for Implementation:** ✅

**Next Session Start Point:** 
Run migration script and test with sample data files located at:
- `report REGION II SITE STATUS 2026 - JANUARY.csv`
- `report eGovPH Activities.csv`

---

END OF SPECIFICATION
