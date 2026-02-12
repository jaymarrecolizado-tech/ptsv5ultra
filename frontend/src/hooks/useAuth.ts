import { useAuthStore } from '@/store/store';
import { authAPI } from '@/services/api';
import { User } from '@/types';

export const useAuth = () => {
  const { user, accessToken, refreshToken, isAuthenticated, setAuth, clearAuth, updateUser } = useAuthStore();

  const login = async (username_or_email: string, password: string) => {
    try {
      const response = await authAPI.login(username_or_email, password);
      const { access_token, refresh_token, token_type } = response.data;

      // Store tokens and fetch user info
      localStorage.setItem('access_token', access_token);
      localStorage.setItem('refresh_token', refresh_token);

      // Fetch current user
      const userResponse = await authAPI.me();
      setAuth(userResponse.data, access_token, refresh_token);

      return { success: true, user: userResponse.data };
    } catch (error: any) {
      console.error('Login error:', error);
      const message = error.response?.data?.detail || 'Login failed. Please try again.';
      return { success: false, error: message };
    }
  };

  const register = async (userData: {
    username: string;
    email: string;
    password: string;
    full_name?: string;
    role?: string;
  }) => {
    try {
      const response = await authAPI.register(userData);
      return { success: true, user: response.data };
    } catch (error: any) {
      console.error('Registration error:', error);
      const message = error.response?.data?.detail || 'Registration failed. Please try again.';
      return { success: false, error: message };
    }
  };

  const logout = async () => {
    try {
      const refresh_token = localStorage.getItem('refresh_token');
      if (refresh_token) {
        await authAPI.logout(refresh_token);
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // Clear tokens and state
      localStorage.removeItem('access_token');
      localStorage.removeItem('refresh_token');
      clearAuth();
      window.location.href = '/login';
    }
  };

  const refreshUser = async () => {
    try {
      const response = await authAPI.me();
      updateUser(response.data);
      return response.data;
    } catch (error) {
      console.error('Failed to refresh user:', error);
      await logout();
      return null;
    }
  };

  const hasRole = (roles: string[]) => {
    return user && roles.includes(user.role);
  };

  const hasPermission = (permission: string) => {
    if (!user) return false;

    const permissions = {
      admin: ['view_dashboard', 'view_projects', 'add_project', 'edit_project', 'delete_project', 'manage_users', 'view_reports', 'export_data'],
      editor: ['view_dashboard', 'view_projects', 'add_project', 'edit_project', 'view_reports', 'export_data'],
      viewer: ['view_dashboard', 'view_projects', 'view_reports', 'export_data']
    };

    return permissions[user.role]?.includes(permission) || false;
  };

  return {
    user,
    accessToken,
    refreshToken,
    isAuthenticated,
    login,
    register,
    logout,
    refreshUser,
    hasRole,
    hasPermission
  };
};
