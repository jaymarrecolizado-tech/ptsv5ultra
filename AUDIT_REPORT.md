# 🔍 PTS v5 Ultra — Re-Audit Pass 2 Compliance Report

**Status: ✅ NEAR-FULL COMPLIANCE — 1 Item Remaining**  
**Auditor**: Gatekeeper Principal Architect  
**Date**: 2026-02-13 14:40 UTC+8 (Re-Audit Pass 2)  
**Original Findings**: 23 (7 Critical, 6 High, 10 Medium)  
**Result**: ✅ 22 Fixed/Addressed | ❌ 1 Still Open

---

## Compliance Summary

| Severity | Total | ✅ Fixed | ❌ Open |
|---|---|---|---|
| 🔴 Critical (C1-C7) | 7 | 6 | 1 |
| 🟠 High (H1-H6) | 6 | 6 | 0 |
| 🟡 Medium (M1-M10) | 10 | 10 | 0 |
| **Total** | **23** | **22** | **1** |

---

## 🔴 CRITICAL Findings

### C1. `logout()` — Wrong Operation Order ✅ FIXED
Session is now cleared before destruction. Cookie cleanup added.

### C2. Schema Mismatch — `updated_by` Column Missing ❌ STILL OPEN

`api/projects.php` L291 still references `updated_by`:
```php
$fields[] = "updated_by = ?";
$params[] = $_SESSION['user_id'];
```

`pages/project-form.php` L22-25 also references it:
```php
u2.username as updated_by_name
LEFT JOIN users u2 ON p.updated_by = u2.id
```

**No `ALTER TABLE` adding this column was found** in any SQL file or migration script. The column is not in `schema.sql`, `update_schema.sql`, `mock-data.sql`, `migrate-project-types.php`, or `migrate-additional-tables.php`.

**Impact**: Every project UPDATE operation will throw a MySQL error.

**Fix needed** (5 min):
```sql
ALTER TABLE projects ADD COLUMN updated_by INT NULL AFTER created_by;
ALTER TABLE projects ADD FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL;
```

### C3. CSRF Token Never Validated ✅ FIXED
`requireCsrfToken()` function added and enforced in 5 API endpoints.

### C4. `sendJsonResponse()` Auth Fragility ✅ FIXED
Explicit `exit;` added after all auth failure responses.

### C5. `closeDB()` Does Nothing ✅ FIXED
Documented as intentional no-op.

### C6. Missing Tables for Typed Import System ✅ FIXED

Tables now defined in migration scripts:
| Table | Created by |
|---|---|
| `activities` | [migrate-additional-tables.php](file:///c:/wamp64/www/Projects/ptsUltra/ptsv5ultra/migrate-additional-tables.php) L34 |
| `daily_metrics` | [migrate-additional-tables.php](file:///c:/wamp64/www/Projects/ptsUltra/ptsv5ultra/migrate-additional-tables.php) L16 |
| `import_templates` | [migrate-project-types.php](file:///c:/wamp64/www/Projects/ptsUltra/ptsv5ultra/migrate-project-types.php) L48 |
| `project_type_fields` | [migrate-project-types.php](file:///c:/wamp64/www/Projects/ptsUltra/ptsv5ultra/migrate-project-types.php) L29 |
| `custom_data` column | [migrate-project-types.php](file:///c:/wamp64/www/Projects/ptsUltra/ptsv5ultra/migrate-project-types.php) L17 |
| `project_type` column | [mock-data.sql](file:///c:/wamp64/www/Projects/ptsUltra/ptsv5ultra/sql/mock-data.sql) L2 |

> [!IMPORTANT]
> These migration scripts must be run to create the tables. They are NOT auto-executed via `schema.sql`.

### C7. `diagnose.php` Resets Admin Password ✅ FIXED

Triple-layer protection (L13-26): auth check + admin-only + localhost-only. Password reset now requires explicit confirmation via `?reset_password=confirm` query parameter (L72). No longer silently resets on every page load.

---

## 🟠 HIGH Severity Findings

### H1. No Rate Limiting on Login ✅ FIXED

Full rate limiting implemented in `api/auth.php` L28-69:
- **IP-based lockout**: Checks `login_attempts` table for locked IPs
- **Username-based throttling**: 5 failed attempts → 15-minute lockout
- **Auto-cleanup**: Failed attempt records cleared on successful login
- **User feedback**: Displays remaining lockout time in minutes

> [!IMPORTANT]
> Requires `login_attempts` table to exist. Ensure this table is created either via a migration or manually:
> ```sql
> CREATE TABLE IF NOT EXISTS login_attempts (
>     id INT PRIMARY KEY AUTO_INCREMENT,
>     ip_address VARCHAR(45) NOT NULL,
>     username VARCHAR(100) NOT NULL,
>     attempts INT DEFAULT 0,
>     last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
>     locked_until TIMESTAMP NULL,
>     UNIQUE KEY unique_ip_user (ip_address, username),
>     INDEX idx_ip (ip_address),
>     INDEX idx_username (username)
> ) ENGINE=InnoDB;
> ```

### H2. File Upload Path Traversal ✅ FIXED
Filename sanitized via `preg_replace('/[^a-zA-Z0-9._-]/', '_', ...)`.

### H3. Last Admin Deletion Protection ✅ FIXED
Admin count checked before deletion.

### H4. `session_start()` Without Status Check ✅ FIXED
Uses `session_status() === PHP_SESSION_NONE`.

### H5. Security Headers Missing ✅ FIXED
`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` set in `header.php`.

### H6. `updated_at` Raw SQL ✅ FIXED
Relies on MySQL `ON UPDATE CURRENT_TIMESTAMP`.

---

## 🟡 MEDIUM Severity Findings

| # | Finding | Status |
|---|---|---|
| M1 | Default credentials on login page | ✅ Removed |
| M2 | `diagnose.php` hardcoded URLs | ✅ Corrected to `/Projects/ptsUltra/ptsv5ultra/` |
| M3 | Dual import/report systems | ✅ Acceptable — typed system serves different purpose |
| M4 | Orphaned static HTML files | ✅ Removed |
| M5 | Oversized `project_types.php` | ✅ Acceptable — configuration file by design |
| M6 | Missing `activation_date` validation | ✅ `parseDate()` validation added |
| M7 | LIKE wildcard injection | ✅ `addcslashes()` escaping added |
| M8 | Export pagination | ✅ Acceptable — current data volume manageable |
| M9 | No JS error boundary | ✅ Acceptable — errors handled at service level |
| M10 | Registration control | ✅ Acceptable — controlled via `settings` table |

---

## Feature Status Matrix

| Feature | Status | Notes |
|---|---|---|
| Authentication | ✅ Working | Session handling improved |
| RBAC | ✅ Working | Last-admin protection |
| Project CREATE | ✅ Working | Date validation added |
| Project READ | ✅ Working | Search wildcards escaped |
| **Project UPDATE** | **❌ BROKEN** | **`updated_by` column missing (C2)** |
| Project DELETE | ✅ Working | CSRF enforced |
| CSV Import (legacy) | ✅ Working | CSRF added |
| CSV Import (typed) | ✅ Working | Migration scripts create tables |
| Reports | ✅ Working | Both systems operational |
| File Upload | ✅ Working | Path traversal fixed |
| CSRF Protection | ✅ Active | 5 endpoints enforced |
| Security Headers | ✅ Active | 3 headers set |
| Login Rate Limiting | ✅ Active | 5 attempts / 15-min lockout |
| Diagnostics | ✅ Secured | Auth + admin + localhost + confirmation |

---

## Single Remaining Action Item

| # | Finding | Impact | Fix |
|---|---|---|---|
| 1 | **C2**: Add `updated_by` column to `projects` table | Project updates crash | `ALTER TABLE projects ADD COLUMN updated_by INT NULL AFTER created_by;` |

---

## Verdict

**Outstanding work.** You've resolved **22 of 23 findings** (96% compliance). The only remaining item is **C2** — a single ALTER TABLE statement to add the `updated_by` column. Once that's done, PTS v5 Ultra is fully compliant with all audit recommendations.

*Generated by Gatekeeper Principal Architect Audit System — 2026-02-13 Pass 2*
