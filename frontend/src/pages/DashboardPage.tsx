import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import ProjectMap from '@/components/dashboard/ProjectMap';
import StatusChart from '@/components/dashboard/StatusChart';
import TrendChart from '@/components/dashboard/TrendChart';
import StatCard from '@/components/dashboard/StatCard';
import { Project } from '@/types';
import {
  FolderKanban,
  CheckCircle,
  Clock,
  TrendingUp,
  MapPin,
} from 'lucide-react';

export default function DashboardPage() {
  const navigate = useNavigate();
  const [projects, setProjects] = useState<Project[]>([]);

  // Mock dashboard stats
  const dashboardStats = {
    total_projects: 12,
    total_projects_trend: 5.2,
    in_progress: 5,
    in_progress_trend: 3.1,
    completed: 7,
    completed_trend: 8.4,
    completion_rate: 58.3,
    completion_rate_trend: 2.7,
    by_status: {
      done: 7,
      pending: 5,
    },
    recent_activity: [
      {
        id: 1,
        action: 'updated project status to Done',
        user: { username: 'admin', full_name: 'Admin User' },
        created_at: new Date().toISOString(),
      },
      {
        id: 2,
        action: 'created new project',
        user: { username: 'admin', full_name: 'Admin User' },
        created_at: new Date(Date.now() - 3600000).toISOString(),
      },
    ],
  };

  // Mock trend data
  const trendData = [
    { date: '2024-01-01', new_projects: 2, completed_projects: 1, total: 10 },
    { date: '2024-02-01', new_projects: 1, completed_projects: 2, total: 9 },
    { date: '2024-03-01', new_projects: 3, completed_projects: 1, total: 10 },
    { date: '2024-04-01', new_projects: 2, completed_projects: 2, total: 10 },
    { date: '2024-05-01', new_projects: 1, completed_projects: 3, total: 8 },
    { date: '2024-06-01', new_projects: 3, completed_projects: 0, total: 11 },
  ];

  // Mock projects for map
  const mockProjects: Project[] = [
    {
      id: 1,
      site_code: 'UNDP-GI-0009A',
      project_name: 'Free-WIFI for All',
      site_name: 'Raele Barangay Hall - AP 1',
      barangay: 'Raele',
      municipality: 'Itbayat',
      province: 'Batanes',
      district: 'District I',
      latitude: 20.728794,
      longitude: 121.804235,
      activation_date: '2024-04-30',
      status: 'done',
      progress: 100,
      created_by: 1,
      created_at: '2024-04-01',
      updated_at: '2024-04-30',
    },
    {
      id: 2,
      site_code: 'UNDP-GI-0010A',
      project_name: 'Free-WIFI for All',
      site_name: 'Racuayaman Barangay Hall - AP 2',
      barangay: 'Racuayaman',
      municipality: 'Itbayat',
      province: 'Batanes',
      district: 'District I',
      latitude: 20.735,
      longitude: 121.81,
      activation_date: '2024-05-15',
      status: 'pending',
      progress: 0,
      created_by: 1,
      created_at: '2024-05-01',
      updated_at: '2024-05-15',
    },
  ];

  // Use mock data
  useEffect(() => {
    setProjects(mockProjects);
  }, []);

  const handleMarkerClick = (project: Project) => {
    navigate(`/projects/${project.id}`);
  };

  const stats = dashboardStats;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          Dashboard
        </h1>
        <p className="text-gray-600 dark:text-gray-400">
          Overview of all infrastructure projects
        </p>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total Projects"
          value={stats.total_projects || 0}
          icon={FolderKanban}
          color="blue"
          trend={stats.total_projects_trend ? {
            value: stats.total_projects_trend,
            isPositive: stats.total_projects_trend > 0,
          } : undefined}
        />
        <StatCard
          title="In Progress"
          value={stats.in_progress || 0}
          icon={Clock}
          color="yellow"
          trend={stats.in_progress_trend ? {
            value: stats.in_progress_trend,
            isPositive: stats.in_progress_trend > 0,
          } : undefined}
        />
        <StatCard
          title="Completed"
          value={stats.completed || 0}
          icon={CheckCircle}
          color="green"
          trend={stats.completed_trend ? {
            value: stats.completed_trend,
            isPositive: stats.completed_trend > 0,
          } : undefined}
        />
        <StatCard
          title="Completion Rate"
          value={`${stats.completion_rate || 0}%`}
          icon={TrendingUp}
          color="purple"
          trend={stats.completion_rate_trend ? {
            value: stats.completion_rate_trend,
            isPositive: stats.completion_rate_trend > 0,
          } : undefined}
        />
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <StatusChart data={stats.by_status || {}} />
        </div>
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <TrendChart data={trendData || []} />
        </div>
      </div>

      {/* Map */}
      <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div className="flex items-center justify-between mb-4">
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <MapPin size={20} />
            Project Map
          </h3>
          <button
            onClick={() => navigate('/projects')}
            className="text-blue-600 dark:text-blue-400 hover:underline text-sm"
          >
            View All Projects →
          </button>
        </div>
        <ProjectMap
          projects={projects}
          height="500px"
          onMarkerClick={handleMarkerClick}
        />
      </div>

      {/* Recent Activity */}
      {stats.recent_activity && stats.recent_activity.length > 0 && (
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Recent Activity
          </h3>
          <div className="space-y-4">
            {stats.recent_activity.slice(0, 5).map((activity: any) => (
              <div
                key={activity.id}
                className="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
              >
                <div className="flex-shrink-0 h-8 w-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400">
                  {activity.user?.username?.charAt(0).toUpperCase() || 'U'}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm text-gray-900 dark:text-white">
                    <span className="font-medium">{activity.user?.full_name || activity.user?.username}</span>{' '}
                    {activity.action}
                  </p>
                  <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {new Date(activity.created_at).toLocaleString()}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
