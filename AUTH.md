# Authentication & RBAC Documentation

## Overview

ATLAS implements comprehensive authentication and role-based access control (RBAC) to secure access to the platform.

## Features

### Authentication
- **JWT-based authentication** with access tokens (30 min expiry) and refresh tokens (7 days expiry)
- **Automatic token refresh** to maintain seamless user sessions
- **Secure password hashing** using bcrypt
- **Session management** with device tracking capability

### Role-Based Access Control (RBAC)
Three user roles with granular permissions:

| Role | Description | Permissions |
|------|-------------|--------------|
| **Admin** | Full system access | view_dashboard, view_projects, add_project, edit_project, delete_project, manage_users, view_reports, export_data |
| **Editor** | Project management | view_dashboard, view_projects, add_project, edit_project, view_reports, export_data |
| **Viewer** | Read-only access | view_dashboard, view_projects, view_reports, export_data |

## Setup

### 1. Backend Configuration

The backend is already configured with authentication. Default credentials are:

| Username | Password | Role |
|----------|----------|-------|
| admin | admin123 | Admin |
| editor | editor123 | Editor |
| viewer | viewer123 | Viewer |

### 2. Database Setup

The database schema includes:
- `users` table with roles, verification tokens, password reset tokens
- `sessions` table for refresh token management

Run the database setup:
```bash
mysql -u root -p < backend/database_setup.sql
```

### 3. Environment Variables

Update `backend/.env`:

```env
SECRET_KEY=your-super-secret-key-min-32-chars
ACCESS_TOKEN_EXPIRE_MINUTES=30
REFRESH_TOKEN_EXPIRE_DAYS=7
```

## Authentication Flow

### Login Process

1. User enters credentials in `login.html`
2. Frontend sends POST request to `/api/v1/auth/login`
3. Backend validates credentials and returns:
   - `access_token` (JWT, 30 min expiry)
   - `refresh_token` (JWT, 7 days expiry)
4. Tokens stored in `localStorage`
5. User redirected to dashboard

### Token Refresh

Automatic refresh happens every 25 minutes:

```javascript
// In js/auth.js
setInterval(async () => {
    const response = await fetch('/api/v1/auth/token/refresh', {
        method: 'POST',
        body: JSON.stringify({ refresh_token: localStorage.getItem('refresh_token') })
    });
    // Update tokens in localStorage
}, 25 * 60 * 1000);
```

### Logout Process

1. User clicks logout button
2. Frontend calls `/api/v1/auth/logout` with refresh token
3. Backend invalidates the session
4. Frontend clears localStorage
5. User redirected to login page

## RBAC Implementation

### Backend RBAC

Middleware in `backend/app/core/security.py`:

```python
# Require admin access only
@app.get("/admin-only")
async def admin_endpoint(user: UserModel = Depends(require_admin)):
    return {"message": "Admin only"}

# Require editor or admin
@app.get("/editor-or-above")
async def editor_endpoint(user: UserModel = Depends(require_editor)):
    return {"message": "Editor or admin"}

# Custom role checker
@app.get("/specific-roles")
async def custom_endpoint(
    user: UserModel = Depends(RoleChecker(["admin", "editor"]))
):
    return {"message": "Admin or editor"}
```

### Frontend RBAC

#### For React Frontend

Use the `useAuth` hook and `RoleGuard` component:

```typescript
// Using useAuth hook
const { hasPermission, user } = useAuth();

if (hasPermission('delete_project')) {
    // Show delete button
}

// Using RoleGuard component
<RoleGuard allowedRoles={['admin', 'editor']} fallback={<p>Access denied</p>}>
    <DeleteButton />
</RoleGuard>
```

#### For Static Dashboard

Use `AuthService`:

```javascript
// Check permission
if (AuthService.hasPermission('add_project')) {
    // Show add project button
}

// Check role
if (AuthService.hasRole(['admin', 'editor'])) {
    // Show admin/editor features
}
```

### Permission Matrix

| Feature | Admin | Editor | Viewer |
|---------|-------|--------|--------|
| View Dashboard | ✅ | ✅ | ✅ |
| View Projects | ✅ | ✅ | ✅ |
| Add Project | ✅ | ✅ | ❌ |
| Edit Project | ✅ | ✅ | ❌ |
| Delete Project | ✅ | ❌ | ❌ |
| Manage Users | ✅ | ❌ | ❌ |
| View Reports | ✅ | ✅ | ✅ |
| Export Data | ✅ | ✅ | ✅ |

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/auth/register` | Register new user | No |
| POST | `/api/v1/auth/login` | Login (returns tokens) | No |
| GET | `/api/v1/auth/me` | Get current user | Yes |
| POST | `/api/v1/auth/token/refresh` | Refresh access token | No |
| POST | `/api/v1/auth/logout` | Logout (invalidates session) | No |
| POST | `/api/v1/auth/password-reset/request` | Request password reset | No |
| POST | `/api/v1/auth/password-reset/confirm` | Confirm password reset | No |

### Users (Admin Only)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/users` | List all users |
| POST | `/api/v1/users` | Create new user |
| GET | `/api/v1/users/{id}` | Get user details |
| PUT | `/api/v1/users/{id}` | Update user |
| DELETE | `/api/v1/users/{id}` | Delete user |

## Security Features

### Password Security
- Minimum 8 characters
- Bcrypt hashing with salt rounds
- No plaintext password storage

### Token Security
- JWT with HS256 algorithm
- Separate access and refresh tokens
- Token expiration enforcement
- Automatic token rotation

### Session Security
- Device information tracking (optional)
- IP address logging (optional)
- Session invalidation on logout

### CSRF Protection
- Backend endpoints protected via API design
- JWT tokens in Authorization header

## Testing Authentication

### Test with cURL

```bash
# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username_or_email": "admin", "password": "admin123"}'

# Get current user
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

# Refresh token
curl -X POST http://localhost:8000/api/v1/auth/token/refresh \
  -H "Content-Type: application/json" \
  -d '{"refresh_token": "YOUR_REFRESH_TOKEN"}'
```

### Test with Browser DevTools

1. Open browser DevTools (F12)
2. Go to Application tab
3. Check Local Storage for:
   - `access_token`
   - `refresh_token`
   - `user`

## Troubleshooting

### "401 Unauthorized" Errors

**Cause**: Token expired or invalid

**Solution**:
1. Check if access token exists in localStorage
2. Verify backend SECRET_KEY matches
3. Login again to get fresh tokens

### Token Not Auto-Refreshing

**Cause**: JavaScript error or CORS issue

**Solution**:
1. Check browser console for errors
2. Verify backend CORS settings
3. Check if refresh token exists

### User Roles Not Applied

**Cause**: Frontend not checking permissions

**Solution**:
1. Ensure AuthService.init() is called
2. Check user object in localStorage
3. Verify role value matches expected format

### CORS Errors

**Cause**: Backend not allowing frontend origin

**Solution**:
Update `backend/.env`:
```env
BACKEND_CORS_ORIGINS=["http://localhost:5173","http://localhost:8000"]
```

## Best Practices

### For Developers
1. Always use `AuthService.hasPermission()` for UI controls
2. Never expose admin endpoints to viewers
3. Use environment variables for SECRET_KEY in production
4. Implement rate limiting for auth endpoints
5. Log authentication attempts for security auditing

### For Users
1. Use strong passwords (8+ characters, mixed case, numbers)
2. Logout when done using the system
3. Report suspicious activity to admin
4. Never share login credentials

## Future Enhancements

- [ ] Two-factor authentication (2FA)
- [ ] OAuth integration (Google, SSO)
- [ ] Email verification requirement
- [ ] Password complexity enforcement
- [ ] Account lockout after failed attempts
- [ ] Audit logging for all actions
- [ ] Session timeout with warning
