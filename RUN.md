# Complete Setup Guide - ATLAS

## Quick Start

### Step 1: Start MySQL (WAMP)

1. Start WAMP Server
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create database: `project_tracker`

Or via command line:
```bash
mysql -u root -p < backend/database_setup.sql
```

### Step 2: Start Backend

```bash
cd backend
pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

Backend running at: **http://localhost:8000**
API Docs: **http://localhost:8000/api/docs**

### Step 3: Start Frontend

```bash
cd frontend
npm install
npm run dev
```

Frontend running at: **http://localhost:5173**

---

## Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Editor | editor | editor123 |
| Viewer | viewer | viewer123 |

---

## Project Structure

```
C:\Users\DICT\Desktop\mays\
├── backend/                    # FastAPI + MySQL
│   ├── app/
│   │   ├── api/endpoints/     # Auth, Projects, Users
│   │   ├── core/              # Security, Config
│   │   ├── models/            # SQLAlchemy models
│   │   └── schemas/           # Pydantic schemas
│   ├── database_setup.sql      # MySQL setup
│   └── requirements.txt
│
├── frontend/                   # React + TypeScript
│   ├── src/
│   │   ├── components/         # UI components
│   │   ├── pages/             # Page components
│   │   ├── services/          # API client
│   │   └── store/            # Zustand state
│   └── package.json
│
├── landing.html                 # Landing page
├── login.html                 # Login page
├── index.html                 # Main dashboard
├── js/                       # JavaScript modules
├── css/                      # Stylesheets
└── RUN.md                    # This file
```

---

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Register new user |
| POST | `/api/v1/auth/login` | Login (returns tokens) |
| POST | `/api/v1/auth/token/refresh` | Refresh access token |
| GET | `/api/v1/auth/me` | Get current user |
| POST | `/api/v1/auth/logout` | Logout |

### Projects
| Method | Endpoint | Required Role |
|--------|----------|---------------|
| GET | `/api/v1/projects` | viewer+ |
| POST | `/api/v1/projects` | editor+ |
| GET | `/api/v1/projects/{id}` | viewer+ |
| PUT | `/api/v1/projects/{id}` | editor+ |
| DELETE | `/api/v1/projects/{id}` | admin |

### Users (Admin Only)
| Method | Endpoint |
|--------|----------|
| GET | `/api/v1/users` |
| PUT | `/api/v1/users/{id}` |
| DELETE | `/api/v1/users/{id}` |

---

## Role Permissions

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

---

## Troubleshooting

### "Connection refused" on backend
- Check if MySQL is running in WAMP
- Verify DATABASE_URL in `.env`

### "401 Unauthorized" errors
- Token expired - login again
- Check if backend is running

### Frontend not loading
- Run `npm install` in frontend folder
- Check browser console for errors

### MySQL Access Denied
- Make sure WAMP MySQL is running
- Use root user or create dedicated user

---

## Next Steps (VPS Migration)

1. **Server Setup**:
   ```bash
   sudo apt update
   sudo apt install nginx mysql-server
   ```

2. **Configure MySQL**:
   ```bash
   sudo mysql
   CREATE DATABASE project_tracker;
   ```

3. **Deploy Backend**:
   ```bash
   # Upload backend folder
   pip install -r requirements.txt
   uvicorn app.main:app --host 0.0.0.0 --port 8000
   ```

4. **Configure Nginx**:
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;

       location /api {
           proxy_pass http://localhost:8000;
       }

       location / {
           root /path/to/frontend/dist;
           try_files $uri $uri/ /index.html;
       }
   }
   ```

5. **Update Frontend .env**:
   ```env
   VITE_API_URL=https://your-domain.com/api/v1
   ```
