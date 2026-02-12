import { useState } from 'react';
import { Bell, BellDot, CheckCheck } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

export default function NotificationDropdown() {
  const [isOpen, setIsOpen] = useState(false);

  const handleNotificationClick = (_notification: any) => {
    setIsOpen(false);
  };

  const handleMarkAllAsRead = () => {};

  const unreadCount = 0;
  const notifications: any[] = [];

  return (
    <div className="relative">
      {/* Button */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors"
      >
        {unreadCount > 0 ? <BellDot size={20} /> : <Bell size={20} />}
        {unreadCount > 0 && (
          <span className="absolute top-1 right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
            {unreadCount > 9 ? '9+' : unreadCount}
          </span>
        )}
      </button>

      {/* Dropdown */}
      {isOpen && (
        <>
          {/* Overlay */}
          <div
            className="fixed inset-0 z-10"
            onClick={() => setIsOpen(false)}
          />

          {/* Menu */}
          <div className="absolute right-0 mt-2 w-96 max-h-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-20 overflow-hidden">
            {/* Header */}
            <div className="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <h3 className="font-semibold text-gray-900 dark:text-white">Notifications</h3>
              {unreadCount > 0 && (
                <button
                  onClick={handleMarkAllAsRead}
                  className="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1"
                >
                  <CheckCheck size={16} />
                  Mark all read
                </button>
              )}
            </div>

            {/* Notifications list */}
            <div className="overflow-y-auto max-h-80">
              {notifications.length === 0 ? (
                <div className="p-8 text-center text-gray-500 dark:text-gray-400">
                  No notifications
                </div>
              ) : (
                notifications.map((notification) => (
                  <div
                    key={notification.id}
                    className={`px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors ${
                      !notification.is_read ? 'bg-blue-50/50 dark:bg-blue-900/10' : ''
                    }`}
                  >
                    <div className="flex items-start gap-3">
                      <button
                        onClick={() => handleNotificationClick(notification)}
                        className="flex-1 text-left"
                      >
                        <div className="flex items-start justify-between gap-2">
                          <div className="flex-1">
                            <p className="text-sm font-medium text-gray-900 dark:text-white">
                              {notification.title}
                            </p>
                            <p className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                              {notification.message}
                            </p>
                            <p className="text-xs text-gray-400 dark:text-gray-500 mt-1">
                              {formatDistanceToNow(new Date(notification.created_at), {
                                addSuffix: true,
                              })}
                            </p>
                          </div>
                        </div>
                      </button>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </>
      )}
    </div>
  );
}
