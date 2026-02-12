import axios, { AxiosInstance } from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

const api: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add auth token to requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('access_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Handle token refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      const refreshToken = localStorage.getItem('refresh_token');

      try {
        const response = await axios.post(`${API_BASE_URL}/auth/refresh`, {
          refresh_token: refreshToken,
        });

        const { access_token, refresh_token: newRefreshToken } = response.data;
        localStorage.setItem('access_token', access_token);
        localStorage.setItem('refresh_token', newRefreshToken);

        originalRequest.headers.Authorization = `Bearer ${access_token}`;
        return api(originalRequest);
      } catch (refreshError) {
        localStorage.clear();
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  login: (username_or_email: string, password: string) =>
    api.post('/auth/login', { username_or_email, password }),
  register: (data: {
    username: string;
    email: string;
    password: string;
    full_name?: string;
    role?: string;
  }) => api.post('/auth/register', data),
  refresh: (refreshToken: string) =>
    api.post('/auth/refresh', { refresh_token: refreshToken }),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
};

// Projects API
export const projectsAPI = {
  getAll: (params?: any) => api.get('/projects', { params }),
  getById: (id: number) => api.get(`/projects/${id}`),
  create: (data: any) => api.post('/projects', data),
  update: (id: number, data: any) => api.put(`/projects/${id}`, data),
  delete: (id: number) => api.delete(`/projects/${id}`),
  getForMap: () => api.get('/projects/map'),
};

// Users API
export const usersAPI = {
  getAll: (params?: any) => api.get('/users', { params }),
  getById: (id: number) => api.get(`/users/${id}`),
  create: (data: any) => api.post('/users', data),
  update: (id: number, data: any) => api.put(`/users/${id}`, data),
  delete: (id: number) => api.delete(`/users/${id}`),
};

// Reports API
export const reportsAPI = {
  getSummary: () => api.get('/reports/summary'),
  getProvince: (province: string) => api.get(`/reports/province/${province}`),
  getStatus: () => api.get('/reports/status'),
};

// Comments API
export const commentsAPI = {
  getAll: (projectId: number, params?: any) =>
    api.get(`/projects/${projectId}/comments`, { params }),
  getById: (id: number) => api.get(`/comments/${id}`),
  create: (projectId: number, data: any) =>
    api.post(`/projects/${projectId}/comments`, data),
  update: (id: number, data: any) => api.put(`/comments/${id}`, data),
  delete: (id: number) => api.delete(`/comments/${id}`),
};

// Attachments API
export const attachmentsAPI = {
  getAll: (projectId: number, params?: any) =>
    api.get(`/projects/${projectId}/attachments`, { params }),
  getById: (id: number) => api.get(`/attachments/${id}`),
  upload: (projectId: number, formData: FormData) =>
    api.post(`/projects/${projectId}/attachments`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    }),
  delete: (id: number) => api.delete(`/attachments/${id}`),
};

// Notifications API
export const notificationsAPI = {
  getAll: (params?: any) => api.get('/notifications', { params }),
  getById: (id: number) => api.get(`/notifications/${id}`),
  markAsRead: (id: number) => api.put(`/notifications/${id}/read`),
  markAllAsRead: () => api.put('/notifications/read-all'),
};

// Analytics API
export const analyticsAPI = {
  getStats: () => api.get('/analytics/stats'),
  getActivityFeed: (limit?: number) =>
    api.get('/analytics/activity-feed', { params: { limit } }),
  getHeatMap: () => api.get('/analytics/heatmap'),
  getTrends: (days?: number) => api.get('/analytics/trends', { params: { days } }),
  getDashboard: () => api.get('/analytics/dashboard'),
};

export default api;
