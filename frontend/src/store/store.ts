import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { User, Project, Notification, Tag } from '@/types';

interface AuthState {
  user: User | null;
  accessToken: string | null;
  refreshToken: string | null;
  isAuthenticated: boolean;
  setAuth: (user: User, accessToken: string, refreshToken: string) => void;
  clearAuth: () => void;
  updateUser: (user: User) => void;
}

interface ProjectState {
  projects: Project[];
  selectedProject: Project | null;
  filters: any;
  setProjects: (projects: Project[]) => void;
  setSelectedProject: (project: Project | null) => void;
  setFilters: (filters: any) => void;
}

interface NotificationState {
  notifications: Notification[];
  unreadCount: number;
  setNotifications: (notifications: Notification[]) => void;
  addNotification: (notification: Notification) => void;
  markAsRead: (id: number) => void;
  markAllAsRead: () => void;
}

interface AppState {
  sidebarOpen: boolean;
  darkMode: boolean;
  toggleSidebar: () => void;
  toggleDarkMode: () => void;
}

// Auth store
export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      accessToken: null,
      refreshToken: null,
      isAuthenticated: false,
      setAuth: (user, accessToken, refreshToken) =>
        set({ user, accessToken, refreshToken, isAuthenticated: true }),
      clearAuth: () =>
        set({ user: null, accessToken: null, refreshToken: null, isAuthenticated: false }),
      updateUser: (user) => set({ user }),
    }),
    {
      name: 'auth-storage',
    }
  )
);

// Project store
export const useProjectStore = create<ProjectState>()((set) => ({
  projects: [],
  selectedProject: null,
  filters: {},
  setProjects: (projects) => set({ projects }),
  setSelectedProject: (project) => set({ selectedProject: project }),
  setFilters: (filters) => set({ filters }),
}));

// Notification store
export const useNotificationStore = create<NotificationState>()(
  persist(
    (set) => ({
      notifications: [],
      unreadCount: 0,
      setNotifications: (notifications) => {
        const unreadCount = notifications.filter((n) => !n.is_read).length;
        set({ notifications, unreadCount });
      },
      addNotification: (notification) =>
        set((state) => ({
          notifications: [notification, ...state.notifications],
          unreadCount: notification.is_read ? state.unreadCount : state.unreadCount + 1,
        })),
      markAsRead: (id) =>
        set((state) => ({
          notifications: state.notifications.map((n) =>
            n.id === id ? { ...n, is_read: true } : n
          ),
          unreadCount: Math.max(0, state.unreadCount - 1),
        })),
      markAllAsRead: () =>
        set((state) => ({
          notifications: state.notifications.map((n) => ({ ...n, is_read: true })),
          unreadCount: 0,
        })),
    }),
    {
      name: 'notification-storage',
    }
  )
);

// App store (UI state)
export const useAppStore = create<AppState>()(
  persist(
    (set) => ({
      sidebarOpen: true,
      darkMode: true,
      toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
      toggleDarkMode: () => set((state) => ({ darkMode: !state.darkMode })),
    }),
    {
      name: 'app-storage',
    }
  )
);
