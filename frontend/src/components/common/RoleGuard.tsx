import React from 'react';
import { useAuth } from '@/hooks/useAuth';

interface RoleGuardProps {
  children: React.ReactNode;
  allowedRoles: ('admin' | 'editor' | 'viewer')[];
  fallback?: React.ReactNode;
}

export const RoleGuard: React.FC<RoleGuardProps> = ({
  children,
  allowedRoles,
  fallback = null
}) => {
  const { user, hasRole } = useAuth();

  if (!user) {
    return <>{fallback}</>;
  }

  if (!hasRole(allowedRoles)) {
    return <>{fallback}</>;
  }

  return <>{children}</>;
};

interface PermissionGuardProps {
  children: React.ReactNode;
  permission: string;
  fallback?: React.ReactNode;
}

export const PermissionGuard: React.FC<PermissionGuardProps> = ({
  children,
  permission,
  fallback = null
}) => {
  const { user, hasPermission } = useAuth();

  if (!user) {
    return <>{fallback}</>;
  }

  if (!hasPermission(permission)) {
    return <>{fallback}</>;
  }

  return <>{children}</>;
};

interface AuthGuardProps {
  children: React.ReactNode;
  redirectTo?: string;
}

export const AuthGuard: React.FC<AuthGuardProps> = ({
  children,
  redirectTo = '/login'
}) => {
  const { isAuthenticated } = useAuth();
  const [loading, setLoading] = React.useState(true);

  React.useEffect(() => {
    const checkAuth = async () => {
      const token = localStorage.getItem('access_token');
      if (!token) {
        setLoading(false);
        return;
      }

      // Token exists, verify by calling me endpoint
      setLoading(false);
    };

    checkAuth();
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (!isAuthenticated) {
    window.location.href = redirectTo;
    return null;
  }

  return <>{children}</>;
};
