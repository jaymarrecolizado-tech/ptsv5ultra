import React from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { useAppStore, useAuthStore } from '@/store/store';
import {
  LayoutDashboard,
  FolderKanban,
  FileBarChart,
  Settings,
  Menu,
  LogOut,
} from 'lucide-react';

export default function Sidebar() {
  const location = useLocation();
  const sidebarOpen = useAppStore((state) => state.sidebarOpen);
  const toggleSidebar = useAppStore((state) => state.toggleSidebar);
  const user = useAuthStore((state) => state.user);
  const clearAuth = useAuthStore((state) => state.clearAuth);

  const handleLogout = () => {
    clearAuth();
    window.location.href = '/login';
  };

  const navItems = [
    { path: '/dashboard', icon: LayoutDashboard, label: 'Dashboard' },
    { path: '/projects', icon: FolderKanban, label: 'Projects' },
    { path: '/reports', icon: FileBarChart, label: 'Reports' },
    { path: '/settings', icon: Settings, label: 'Settings' },
  ];

  return (
    <>
      {/* Mobile overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
          onClick={toggleSidebar}
        />
      )}

      {/* Sidebar */}
      <aside
        className={`fixed top-0 left-0 h-full bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 z-50 ${
          sidebarOpen ? 'w-64' : 'w-16'
        }`}
      >
        {/* Header */}
        <div className="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-700">
          {sidebarOpen && (
            <h1 className="text-xl font-bold text-gray-900 dark:text-white truncate">
              PTS
            </h1>
          )}
          <button
            onClick={toggleSidebar}
            className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400"
          >
            <Menu size={20} />
          </button>
        </div>

        {/* Navigation */}
        <nav className="mt-6 px-2">
          <ul className="space-y-1">
            {navItems.map((item) => {
              const isActive = location.pathname === item.path;
              const Icon = item.icon;

              return (
                <li key={item.path}>
                  <NavLink
                    to={item.path}
                    onClick={() => {
                      if (window.innerWidth < 1024) toggleSidebar();
                    }}
                    className={`flex items-center px-3 py-3 rounded-lg transition-colors ${
                      isActive
                        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    }`}
                  >
                    <Icon size={20} className="flex-shrink-0" />
                    {sidebarOpen && (
                      <span className="ml-3 truncate">{item.label}</span>
                    )}
                  </NavLink>
                </li>
              );
            })}
          </ul>
        </nav>

        {/* User info */}
        <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700">
          <div className="flex items-center">
            <div className="flex-shrink-0 h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
              {user?.username?.charAt(0).toUpperCase() || 'U'}
            </div>
            {sidebarOpen && (
              <div className="ml-3 flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {user?.full_name || user?.username}
                </p>
                <p className="text-xs text-gray-500 dark:text-gray-400 capitalize">
                  {user?.role || 'User'}
                </p>
              </div>
            )}
            <button
              onClick={handleLogout}
              className="flex-shrink-0 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400"
              title="Logout"
            >
              <LogOut size={20} />
            </button>
          </div>
        </div>
      </aside>
    </>
  );
}
