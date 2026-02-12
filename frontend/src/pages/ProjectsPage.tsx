import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Project, ProjectFilters } from '@/types';
import {
  Search,
  Filter,
  Plus,
  ArrowUpDown,
  Eye,
  Edit,
  Trash2,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';
import toast from 'react-hot-toast';

const statusColors: Record<string, string> = {
  planning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
  in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
  on_hold: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
  done: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
  pending: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

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
  {
    id: 3,
    site_code: 'UNDP-GI-0011A',
    project_name: 'Community Center',
    site_name: 'San Jose Barangay Hall',
    barangay: 'San Jose',
    municipality: 'Basco',
    province: 'Batanes',
    district: 'District I',
    latitude: 20.447,
    longitude: 121.964,
    activation_date: '2024-06-01',
    status: 'done',
    progress: 100,
    created_by: 1,
    created_at: '2024-05-01',
    updated_at: '2024-06-01',
  },
];

export default function ProjectsPage() {
  const navigate = useNavigate();
  const [filters, setFilters] = useState<ProjectFilters>({
    page: 1,
    page_size: 20,
  });
  const [showFilters, setShowFilters] = useState(false);

  const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    setFilters({
      ...filters,
      search: formData.get('search') as string,
      page: 1,
    });
  };

  const handleFilterChange = (key: keyof ProjectFilters, value: any) => {
    setFilters({
      ...filters,
      [key]: value,
      page: 1,
    });
  };

  const handleSort = (field: string) => {
    if (filters.sort_by === field) {
      setFilters({
        ...filters,
        sort_desc: !filters.sort_desc,
      });
    } else {
      setFilters({
        ...filters,
        sort_by: field,
        sort_desc: true,
      });
    }
  };

  const handleDelete = async (_id: number) => {
    if (!window.confirm('Are you sure you want to delete this project?')) return;
    toast.success('Project deleted successfully');
  };

  const handlePageChange = (newPage: number) => {
    setFilters({
      ...filters,
      page: newPage,
    });
  };

  const projects = mockProjects;
  const totalPages = 1;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Projects
          </h1>
          <p className="text-gray-600 dark:text-gray-400">
            {projects.length} projects found
          </p>
        </div>
        <button
          onClick={() => navigate('/projects/new')}
          className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
        >
          <Plus size={20} />
          Add Project
        </button>
      </div>

      {/* Search and Filters */}
      <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <form onSubmit={handleSearch} className="flex items-center gap-4">
          <div className="flex-1 relative">
            <Search
              size={20}
              className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
            />
            <input
              type="text"
              name="search"
              placeholder="Search projects by name, code, or location..."
              defaultValue={filters.search || ''}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
            />
          </div>
          <button
            type="button"
            onClick={() => setShowFilters(!showFilters)}
            className={`flex items-center gap-2 px-4 py-2 rounded-lg transition-colors ${
              showFilters
                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
                : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
            }`}
          >
            <Filter size={20} />
            Filters
          </button>
        </form>

        {/* Filter Options */}
        {showFilters && (
          <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Status
              </label>
              <select
                value={filters.status || ''}
                onChange={(e) => handleFilterChange('status', e.target.value || undefined)}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
              >
                <option value="">All Statuses</option>
                <option value="planning">Planning</option>
                <option value="in_progress">In Progress</option>
                <option value="on_hold">On Hold</option>
                <option value="done">Done</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Province
              </label>
              <select
                value={filters.province || ''}
                onChange={(e) => handleFilterChange('province', e.target.value || undefined)}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
              >
                <option value="">All Provinces</option>
                <option value="Bataan">Bataan</option>
                <option value="Bulacan">Bulacan</option>
                <option value="Cavite">Cavite</option>
                <option value="Laguna">Laguna</option>
                <option value="Pampanga">Pampanga</option>
                <option value="Rizal">Rizal</option>
                <option value="Zambales">Zambales</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Municipality
              </label>
              <input
                type="text"
                value={filters.municipality || ''}
                onChange={(e) => handleFilterChange('municipality', e.target.value || undefined)}
                placeholder="Filter by municipality"
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
              />
            </div>
            <div className="flex items-end">
              <button
                onClick={() => setFilters({ page: 1, page_size: 20 })}
                className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                Clear Filters
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Projects Table */}
      <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        {projects.length === 0 ? (
          <div className="p-8 text-center text-gray-500 dark:text-gray-400">
            No projects found
          </div>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 dark:bg-gray-700/50">
                  <tr>
                    <th
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                      onClick={() => handleSort('project_name')}
                    >
                      <div className="flex items-center gap-1">
                        Project Name
                        <ArrowUpDown size={14} />
                      </div>
                    </th>
                    <th
                      className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                      onClick={() => handleSort('site_code')}
                    >
                      <div className="flex items-center gap-1">
                        Code
                        <ArrowUpDown size={14} />
                      </div>
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Location
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Status
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Progress
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Activation Date
                    </th>
                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                  {projects.map((project) => (
                    <tr
                      key={project.id}
                      className="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
                      onClick={() => navigate(`/projects/${project.id}`)}
                    >
                      <td className="px-6 py-4">
                        <div>
                          <p className="font-medium text-gray-900 dark:text-white">
                            {project.project_name}
                          </p>
                          <p className="text-sm text-gray-500 dark:text-gray-400">
                            {project.site_name}
                          </p>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                        {project.site_code}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                        {project.barangay}, {project.municipality}, {project.province}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span
                          className={`px-3 py-1 rounded-full text-xs font-medium capitalize ${
                            statusColors[project.status] || statusColors.pending
                          }`}
                        >
                          {project.status.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center gap-2">
                          <div className="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div
                              className="h-full bg-blue-600 rounded-full"
                              style={{ width: `${project.progress}%` }}
                            />
                          </div>
                          <span className="text-sm text-gray-700 dark:text-gray-300 min-w-[3rem]">
                            {project.progress}%
                          </span>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                        {new Date(project.activation_date).toLocaleDateString()}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              navigate(`/projects/${project.id}`);
                            }}
                            className="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded transition-colors"
                            title="View"
                          >
                            <Eye size={16} />
                          </button>
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              navigate(`/projects/${project.id}/edit`);
                            }}
                            className="p-1.5 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded transition-colors"
                            title="Edit"
                          >
                            <Edit size={16} />
                          </button>
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              handleDelete(project.id);
                            }}
                            className="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-colors"
                            title="Delete"
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            <div className="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
              <p className="text-sm text-gray-700 dark:text-gray-300">
                Showing {((filters.page || 1) - 1) * (filters.page_size || 20) + 1} to{' '}
                {Math.min((filters.page || 1) * (filters.page_size || 20), projects.length)} of{' '}
                {projects.length} projects
              </p>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => handlePageChange((filters.page || 1) - 1)}
                  disabled={(filters.page || 1) <= 1}
                  className="flex items-center gap-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <ChevronLeft size={16} />
                  Previous
                </button>
                <span className="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                  Page {filters.page || 1} of {totalPages}
                </span>
                <button
                  onClick={() => handlePageChange((filters.page || 1) + 1)}
                  disabled={(filters.page || 1) >= totalPages}
                  className="flex items-center gap-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  Next
                  <ChevronRight size={16} />
                </button>
              </div>
            </div>
          </>
        )}
      </div>
    </div>
  );
}
