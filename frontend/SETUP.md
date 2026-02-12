# Frontend Setup Guide

## Prerequisites

1. **Node.js 18+** - Download from https://nodejs.org/
2. **Backend running** - See backend/SETUP.md

## Installation

```bash
# Navigate to frontend directory
cd frontend

# Install dependencies
npm install

# Start development server
npm run dev
```

## Available Scripts

| Command | Description |
|---------|-------------|
| `npm run dev` | Start dev server at http://localhost:5173 |
| `npm run build` | Build for production |
| `npm run preview` | Preview production build |
| `npm run lint` | Run ESLint |
| `npm run format` | Format code with Prettier |

## Environment Variables

Create `.env` file:

```env
VITE_API_URL=http://localhost:8000/api/v1
```

## Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | admin123 |
| Editor | editor | editor123 |
| Viewer | viewer | viewer123 |

## Project Structure

```
src/
├── components/
│   ├── auth/          # Login, Register components
│   ├── common/        # Header, Sidebar, Layout
│   ├── dashboard/     # Stats, Charts, Map
│   ├── projects/      # Project forms, tables
│   └── reports/       # Report components
├── pages/
│   ├── auth/         # Login, Register pages
│   ├── DashboardPage.tsx
│   ├── ProjectsPage.tsx
│   ├── ProjectDetailPage.tsx
│   ├── ReportsPage.tsx
│   └── SettingsPage.tsx
├── services/
│   └── api.ts         # Axios API client
├── store/
│   └── store.ts       # Zustand state management
├── types/
│   └── index.ts       # TypeScript types
├── App.tsx           # Main app component
└── main.tsx          # Entry point
```

## Running with Backend

### 1. Start MySQL (WAMP)
```bash
# Make sure WAMP MySQL is running
# Create database
mysql -u root -p < ../backend/database_setup.sql
```

### 2. Start Backend
```bash
cd ../backend
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

### 3. Start Frontend
```bash
cd frontend
npm run dev
```

### 4. Open Browser
```
http://localhost:5173
```

## Production Build

```bash
npm run build
```

The build output will be in `dist/` folder. Serve with any static server:

```bash
npx serve dist
```

## Features

- **Authentication**: JWT-based with refresh tokens
- **Role-Based Access Control**: Admin, Editor, Viewer roles
- **Real-time Map**: Leaflet.js integration
- **Charts**: Chart.js for analytics
- **Dark Mode**: Full dark/light theme support
- **Responsive**: Mobile-friendly design
- **State Management**: Zustand with persistence
- **API Integration**: Axios with interceptors
