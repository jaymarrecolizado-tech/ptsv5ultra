import { Outlet } from 'react-router-dom';
import { useAppStore } from '@/store/store';
import Sidebar from './Sidebar';
import Header from './Header';

export default function MainLayout({ children }: { children?: React.ReactNode }) {
  const sidebarOpen = useAppStore((state) => state.sidebarOpen);

  return (
    <div className="min-h-screen bg-background">
      {/* Sidebar */}
      <Sidebar />

      {/* Main Content */}
      <div
        className={`transition-all duration-300 ${
          sidebarOpen ? 'ml-64' : 'ml-16'
        }`}
      >
        {/* Header */}
        <Header />

        {/* Page Content */}
        <main className="container mx-auto p-6">
          {children || <Outlet />}
        </main>
      </div>
    </div>
  );
}
