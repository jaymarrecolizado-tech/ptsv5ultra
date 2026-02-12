import React, { useEffect } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { useAppStore } from '@/store/store';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

// Layout components
import MainLayout from '@/components/common/MainLayout';
import AuthLayout from '@/components/common/AuthLayout';

// Page components
import LoginPage from '@/pages/auth/LoginPage';
import RegisterPage from '@/pages/auth/RegisterPage';
import DashboardPage from '@/pages/DashboardPage';
import ProjectsPage from '@/pages/ProjectsPage';
import ProjectDetailPage from '@/pages/ProjectDetailPage';
import ReportsPage from '@/pages/ReportsPage';
import SettingsPage from '@/pages/SettingsPage';

// Create React Query client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

// Protected route wrapper (disabled for now - no backend)
const ProtectedRoute = ({ children }: { children: React.ReactNode }) => {
  return <>{children}</>;
};

// Public route wrapper (redirect if authenticated) (disabled for now)
const PublicRoute = ({ children }: { children: React.ReactNode }) => {
  return <>{children}</>;
};

function App() {
  const darkMode = useAppStore((state) => state.darkMode);

  // Apply dark mode to document
  useEffect(() => {
    if (darkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }, [darkMode]);

  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <Routes>
          {/* Public routes */}
          <Route
            path="/login"
            element={
              <PublicRoute>
                <AuthLayout>
                  <LoginPage />
                </AuthLayout>
              </PublicRoute>
            }
          />
          <Route
            path="/register"
            element={
              <PublicRoute>
                <AuthLayout>
                  <RegisterPage />
                </AuthLayout>
              </PublicRoute>
            }
          />

          {/* Protected routes */}
          <Route
            path="/"
            element={
              <ProtectedRoute>
                <MainLayout>
                  <Navigate to="/dashboard" replace />
                </MainLayout>
              </ProtectedRoute>
            }
          />
          <Route
            path="/dashboard"
            element={
              <ProtectedRoute>
                <MainLayout>
                  <DashboardPage />
                </MainLayout>
              </ProtectedRoute>
            }
          />
          <Route
            path="/projects"
            element={
              <ProtectedRoute>
                <MainLayout>
                  <ProjectsPage />
                </MainLayout>
              </ProtectedRoute>
            }
          />
          <Route
            path="/projects/:id"
            element={
              <ProtectedRoute>
                <MainLayout>
                  <ProjectDetailPage />
                </MainLayout>
              </ProtectedRoute>
            }
          />
          <Route
            path="/reports"
            element={
              <ProtectedRoute>
                <MainLayout>
                  <ReportsPage />
                </MainLayout>
              </ProtectedRoute>
            }
          />
          <Route
            path="/settings"
            element={
              <ProtectedRoute>
                <MainLayout>
                  <SettingsPage />
                </MainLayout>
              </ProtectedRoute>
            }
          />

          {/* 404 */}
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>

        {/* Toast notifications */}
        <Toaster
          position="top-right"
          toastOptions={{
            duration: 4000,
            style: {
              background: darkMode ? '#1e293b' : '#ffffff',
              color: darkMode ? '#ffffff' : '#000000',
            },
          }}
        />
      </BrowserRouter>
    </QueryClientProvider>
  );
}

export default App;
