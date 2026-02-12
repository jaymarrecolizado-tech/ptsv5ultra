# Project Tracking System - Backend Setup Guide

## Prerequisites

### 1. Install WAMP Server
- Download WAMP from: https://www.wampserver.com/
- Install and start WAMP
- Ensure MySQL is running (green icon)

### 2. Create Database
Open phpMyAdmin (http://localhost/phpmyadmin) or MySQL command line:

```sql
CREATE DATABASE project_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or run the setup file:
```bash
mysql -u root -p < database_setup.sql
```

### 3. Install Python Dependencies
```bash
cd backend
pip install -r requirements.txt
```

### 4. Configure Environment
Edit `.env` file:
```env
# Database (WAMP MySQL)
DATABASE_URL=mysql+pymysql://root:@localhost:3306/project_tracker

# JWT Secret (change this in production!)
SECRET_KEY=your-super-secret-key-min-32-chars

# Other settings...
```

### 5. Run the Server
```bash
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

### 6. Access API Documentation
- Swagger UI: http://localhost:8000/api/docs
- ReDoc: http://localhost:8000/api/redoc

---

## Default Users (after running database_setup.sql)

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | admin |
| editor | editor123 | editor |
| viewer | viewer123 | viewer |

---

## API Endpoints

### Authentication
- `POST /api/v1/auth/register` - Register new user
- `POST /api/v1/auth/login` - Login (returns JWT tokens)
- `POST /api/v1/auth/token/refresh` - Refresh access token
- `GET /api/v1/auth/me` - Get current user
- `POST /api/v1/auth/logout` - Logout

### Projects (with RBAC)
- `GET /api/v1/projects` - List all projects (viewer+)
- `POST /api/v1/projects` - Create project (editor+)
- `GET /api/v1/projects/{id}` - Get project (viewer+)
- `PUT /api/v1/projects/{id}` - Update project (editor+)
- `DELETE /api/v1/projects/{id}` - Delete project (admin only)

### Users (Admin only)
- `GET /api/v1/users` - List users
- `PUT /api/v1/users/{id}` - Update user
- `DELETE /api/v1/users/{id}` - Delete user

---

## Role-Based Access Control (RBAC)

| Role | Permissions |
|------|-------------|
| **admin** | Full access: create, read, update, delete all resources |
| **editor** | Create projects, edit any project, view all |
| **viewer** | View projects only, cannot create/edit/delete |

---

## Testing with Frontend

1. Start backend: `uvicorn app.main:app --reload --host 0.0.0.0 --port 8000`
2. Start frontend: `python -m http.server 8000` (in frontend folder)
3. Open: http://localhost:8000
4. Login with admin credentials

---

## Migrating to VPS Later

1. Update `.env` on VPS:
   ```env
   DATABASE_URL=mysql+pymysql://user:password@your-vps-ip:3306/project_tracker
   SECRET_KEY=strong-new-secret-key
   ```

2. Export database from WAMP:
   ```bash
   mysqldump -u root -p project_tracker > backup.sql
   ```

3. Import on VPS:
   ```bash
   mysql -u user -p project_tracker < backup.sql
   ```

4. Deploy backend using Docker or PM2
