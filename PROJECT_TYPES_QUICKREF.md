# DICT Project Types - Quick Reference

## 10 Project Types with Tracking Fields

### Priority 1 (Implement First):

| Type | Tracking Unit | Key Metrics | Data Structure |
|------|---------------|-------------|----------------|
| **EgovPH** | Agency/Office | Monthly transactions, App downloads | Activity-based |
| **ELGU** | LGU | Services digitalized, EBOSS compliance | Site + Activity |
| **Free-WiFi** | Infrastructure Site | Daily bandwidth, Unique users, Uptime | Site + Daily Metrics |

### Priority 2 (Implement Later):

| Type | Tracking Unit | Key Metrics | Data Structure |
|------|---------------|-------------|----------------|
| **Cybersecurity** | Training Activity | Participants trained, Topics covered | Activity-based |
| **ILCDB** | Training Course | Certified participants, Training hours | Activity-based |
| **GovNet** | Connected Agency | Bandwidth, Agencies connected | Infrastructure |
| **IIDB** | Job Program | Jobs created, People hired | Activity-based |
| **DTC** | Support Center | MSMEs assisted, Consultancies | Center-based |
| **GECS** | Emergency Deployment | Response events, Areas covered | Deployment-based |
| **PNPKI** | Organization | Certificates issued (bulk) | Organization-based |

---

## Geographic Fields (ALL Types)

Every project MUST include:
- ✅ Latitude (decimal)
- ✅ Longitude (decimal)  
- ✅ Province
- ✅ Municipality
- ✅ District
- ✅ Barangay (where applicable)

---

## Import Template Types

### Type A: Infrastructure Site
**For:** Free-WiFi, GovNet, DTC, GECS
```
Site Code, Site Name, Location Type, [Type-Specific Fields], 
Province, Municipality, District, Barangay, Latitude, Longitude, 
Activation Date, Status, Notes
```

### Type B: Activity/Event
**For:** EgovPH, ELGU, Cybersecurity, ILCDB, IIDB
```
Activity ID, Activity Title, Activity Type, Date, 
Participants, [Type-Specific Fields],
Province, Municipality, District, Barangay, 
Status, Facilitator, Notes
```

### Type C: Daily Metrics (Free-WiFi Only)
```
Site Code, Date, Status, Bandwidth Utilization, 
Unique Users, Remarks
```

---

## Site Code Patterns

| Project Type | Pattern | Example |
|--------------|---------|---------|
| EgovPH | EGV-XXXX | EGV-0001 |
| ELGU | ELG-XXXX | ELG-0001 |
| Free-WiFi | WIFI-XXXX | WIFI-0001 |
| Cybersecurity | CYB-XXXX | CYB-0001 |
| ILCDB | ILC-XXXX | ILC-0001 |
| GovNet | GVN-XXXX | GVN-0001 |
| IIDB | IID-XXXX | IID-0001 |
| DTC | DTC-XXXX | DTC-0001 |
| GECS | GEC-XXXX | GEC-0001 |
| PNPKI | PNK-XXXX | PNK-0001 |

---

## Database Tables

1. **projects** - Main project records
2. **daily_metrics** - Free-WiFi daily data
3. **activities** - Training events and activities
4. **project_type_fields** - Field configurations
5. **import_templates** - CSV templates

---

## Report Types

### Free-WiFi Reports:
- Bandwidth utilization trends
- Site uptime/downtime
- Provider performance
- User access statistics
- Geographic heat map

### EgovPH Reports:
- Agency onboarding status
- Transaction volumes
- Service adoption rates
- Provincial progress

### ELGU Reports:
- LGU digitalization status
- EBOSS compliance rate
- Services per LGU
- Digitalization timeline

---

## Key Decisions Made

✅ **Free-WiFi**: Import daily bandwidth data (all date columns)  
✅ **EgovPH/ELGU**: Track activities/events (like eGovPH sample)  
❌ **PNPKI**: No individual certificates (organizational only)  
✅ **Geographic Data**: Required for ALL projects (Lat/Long + location)  

---

## Files Ready

✅ `config/project_types.php` - Configuration  
✅ `api/import-typed.php` - Import API  
✅ `api/reports-typed.php` - Reports API  
✅ `pages/import-typed.php` - Import UI  
✅ `migrate-project-types.php` - Migration script  
✅ `IMPLEMENTATION_SPEC.md` - Full specification  

---

## Next Actions

1. Run `php migrate-project-types.php`
2. Create CSV templates for 3 priority types
3. Test import with sample data
4. Build report pages
5. Verify map plotting

---

**Date:** February 13, 2026  
**Status:** Ready for Implementation  
**Priority:** EgovPH, ELGU, Free-WiFi
