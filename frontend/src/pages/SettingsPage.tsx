import { useState } from 'react';
import { useAppStore } from '@/store/store';
import {
  User,
  Bell,
  Moon,
  Sun,
  Lock,
  Save,
} from 'lucide-react';
import toast from 'react-hot-toast';

export default function SettingsPage() {
  const { darkMode, toggleDarkMode } = useAppStore();
  const [isLoading, setIsLoading] = useState(false);

  const user = {
    username: 'admin',
    email: 'admin@example.com',
    full_name: 'Admin User',
    role: 'admin',
    is_active: true,
    is_verified: true,
    last_login: new Date().toISOString(),
    created_at: '2024-01-01',
  };

  const [formData, setFormData] = useState({
    full_name: user.full_name,
    email: user.email,
  });

  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: '',
  });

  const handleProfileUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      await new Promise(resolve => setTimeout(resolve, 1000));
      toast.success('Profile updated successfully');
    } catch (error: any) {
      toast.error('Failed to update profile');
    } finally {
      setIsLoading(false);
    }
  };

  const handlePasswordChange = async (e: React.FormEvent) => {
    e.preventDefault();

    if (passwordData.new_password !== passwordData.confirm_password) {
      toast.error('New passwords do not match');
      return;
    }

    if (passwordData.new_password.length < 6) {
      toast.error('Password must be at least 6 characters');
      return;
    }

    setIsLoading(true);

    try {
      await new Promise(resolve => setTimeout(resolve, 1000));
      toast.success('Password changed successfully');
      setPasswordData({
        current_password: '',
        new_password: '',
        confirm_password: '',
      });
    } catch (error: any) {
      toast.error('Failed to change password');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          Settings
        </h1>
        <p className="text-gray-600 dark:text-gray-400">
          Manage your account and preferences
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Profile Settings */}
        <div className="lg:col-span-2 space-y-6">
          {/* Profile Information */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <User size={20} />
              Profile Information
            </h2>
            <form onSubmit={handleProfileUpdate} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Username
                  </label>
                  <input
                    type="text"
                    value={user?.username || ''}
                    disabled
                    className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                  />
                  <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Username cannot be changed
                  </p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Role
                  </label>
                  <input
                    type="text"
                    value={user?.role || ''}
                    disabled
                    className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed capitalize"
                  />
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Full Name
                </label>
                <input
                  type="text"
                  value={formData.full_name}
                  onChange={(e) => setFormData({ ...formData, full_name: e.target.value })}
                  className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Email
                </label>
                <input
                  type="email"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                />
              </div>
              <button
                type="submit"
                disabled={isLoading}
                className="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Save size={20} />
                {isLoading ? 'Saving...' : 'Save Changes'}
              </button>
            </form>
          </div>

          {/* Change Password */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Lock size={20} />
              Change Password
            </h2>
            <form onSubmit={handlePasswordChange} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Current Password
                </label>
                <input
                  type="password"
                  value={passwordData.current_password}
                  onChange={(e) =>
                    setPasswordData({ ...passwordData, current_password: e.target.value })
                  }
                  required
                  className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  New Password
                </label>
                <input
                  type="password"
                  value={passwordData.new_password}
                  onChange={(e) =>
                    setPasswordData({ ...passwordData, new_password: e.target.value })
                  }
                  required
                  minLength={6}
                  className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                />
                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Minimum 6 characters
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Confirm New Password
                </label>
                <input
                  type="password"
                  value={passwordData.confirm_password}
                  onChange={(e) =>
                    setPasswordData({ ...passwordData, confirm_password: e.target.value })
                  }
                  required
                  minLength={6}
                  className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                />
              </div>
              <button
                type="submit"
                disabled={isLoading}
                className="flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Lock size={20} />
                Change Password
              </button>
            </form>
          </div>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* User Info Card */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div className="h-24 w-24 bg-blue-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
              {user?.username?.charAt(0).toUpperCase() || 'U'}
            </div>
            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-1">
              {user?.full_name || user?.username}
            </h3>
            <p className="text-gray-600 dark:text-gray-400 mb-3">{user?.email}</p>
            <div className="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-full text-sm font-medium capitalize">
              {user?.role}
            </div>
            <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 space-y-2">
              <div className="flex justify-between">
                <span>Member since:</span>
                <span className="font-medium">
                  {user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}
                </span>
              </div>
              {user?.last_login && (
                <div className="flex justify-between">
                  <span>Last login:</span>
                  <span className="font-medium">
                    {new Date(user.last_login).toLocaleString()}
                  </span>
                </div>
              )}
              <div className="flex justify-between">
                <span>Status:</span>
                <span
                  className={`font-medium ${
                    user?.is_active
                      ? 'text-green-600 dark:text-green-400'
                      : 'text-red-600 dark:text-red-400'
                  }`}
                >
                  {user?.is_active ? 'Active' : 'Inactive'}
                </span>
              </div>
              <div className="flex justify-between">
                <span>Verified:</span>
                <span
                  className={`font-medium ${
                    user?.is_verified
                      ? 'text-green-600 dark:text-green-400'
                      : 'text-yellow-600 dark:text-yellow-400'
                  }`}
                >
                  {user?.is_verified ? 'Yes' : 'No'}
                </span>
              </div>
            </div>
          </div>

          {/* Appearance Settings */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              {darkMode ? <Moon size={20} /> : <Sun size={20} />}
              Appearance
            </h3>
            <div className="space-y-3">
              <button
                onClick={toggleDarkMode}
                className="w-full flex items-center justify-between px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-left"
              >
                <div className="flex items-center gap-3">
                  {darkMode ? <Moon size={18} /> : <Sun size={18} />}
                  <span className="font-medium text-gray-900 dark:text-white">
                    {darkMode ? 'Dark Mode' : 'Light Mode'}
                  </span>
                </div>
                <div
                  className={`w-12 h-6 rounded-full transition-colors ${
                    darkMode ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
                  }`}
                >
                  <div
                    className={`w-5 h-5 bg-white rounded-full shadow transform transition-transform ${
                      darkMode ? 'translate-x-6' : 'translate-x-0.5'
                    }`}
                  />
                </div>
              </button>
            </div>
          </div>

          {/* Notification Settings */}
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Bell size={20} />
              Notifications
            </h3>
            <div className="space-y-3">
              <div className="flex items-center justify-between text-sm">
                <span className="text-gray-700 dark:text-gray-300">Email notifications</span>
                <div
                  className={`w-12 h-6 rounded-full transition-colors cursor-pointer bg-gray-300 dark:bg-gray-600`}
                >
                  <div className="w-5 h-5 bg-white rounded-full shadow transform translate-x-0.5" />
                </div>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-gray-700 dark:text-gray-300">Project updates</span>
                <div
                  className={`w-12 h-6 rounded-full transition-colors cursor-pointer bg-blue-600`}
                >
                  <div className="w-5 h-5 bg-white rounded-full shadow transform translate-x-6" />
                </div>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-gray-700 dark:text-gray-300">Task reminders</span>
                <div
                  className={`w-12 h-6 rounded-full transition-colors cursor-pointer bg-blue-600`}
                >
                  <div className="w-5 h-5 bg-white rounded-full shadow transform translate-x-6" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
