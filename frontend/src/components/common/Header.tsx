import { useAppStore } from '@/store/store';
import NotificationDropdown from './NotificationDropdown';
import { Search, Sun, Moon } from 'lucide-react';

const user = { username: 'admin', full_name: 'Admin User', avatar: '', role: 'admin' };

export default function Header() {
  const toggleDarkMode = useAppStore((state) => state.toggleDarkMode);
  const darkMode = useAppStore((state) => state.darkMode);

  return (
    <header className="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 flex items-center justify-between">
      {/* Left side - Search */}
      <div className="flex-1 max-w-xl">
        <div className="relative">
          <Search
            size={20}
            className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
          />
          <input
            type="text"
            placeholder="Search projects..."
            className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
          />
        </div>
      </div>

      {/* Right side - Actions */}
      <div className="flex items-center gap-4">
        {/* Dark mode toggle */}
        <button
          onClick={toggleDarkMode}
          className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors"
          title="Toggle dark mode"
        >
          {darkMode ? <Sun size={20} /> : <Moon size={20} />}
        </button>

        {/* Notifications */}
        <NotificationDropdown />

        {/* User menu */}
        <div className="flex items-center gap-3 pl-4 border-l border-gray-200 dark:border-gray-700">
          <div className="text-right">
            <p className="text-sm font-medium text-gray-900 dark:text-white">
              {user?.full_name || user?.username}
            </p>
            <p className="text-xs text-gray-500 dark:text-gray-400 capitalize">
              {user?.role || 'User'}
            </p>
          </div>
          <div className="h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
            {user?.username?.charAt(0).toUpperCase() || 'U'}
          </div>
        </div>
      </div>
    </header>
  );
}
