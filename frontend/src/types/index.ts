// User types
export interface User {
  id: number;
  username: string;
  email: string;
  full_name: string;
  role: 'admin' | 'editor' | 'viewer';
  avatar?: string;
  is_active: boolean;
  is_verified: boolean;
  last_login?: string;
  created_at: string;
  updated_at: string;
}

// Project types
export interface Project {
  id: number;
  site_code: string;
  project_name: string;
  site_name: string;
  barangay: string;
  municipality: string;
  province: string;
  district?: string;
  latitude: number;
  longitude: number;
  activation_date: string;
  completion_date?: string;
  status: 'planning' | 'in_progress' | 'on_hold' | 'done' | 'pending' | 'cancelled';
  notes?: string;
  progress: number;
  assigned_to?: number;
  created_by: number;
  created_at: string;
  updated_at: string;
  creator?: User;
  assignee?: User;
  tags?: Tag[];
}

export interface ProjectHistory {
  id: number;
  project_id: number;
  old_status?: string;
  new_status?: string;
  old_assigned_to?: number;
  new_assigned_to?: number;
  changed_fields?: Record<string, any>;
  changed_by: number;
  change_reason?: string;
  created_at: string;
  changer?: User;
}

// Comment types
export interface Comment {
  id: number;
  project_id: number;
  user_id: number;
  content: string;
  parent_id?: number;
  mentions?: number[];
  is_edited: boolean;
  created_at: string;
  updated_at: string;
  user?: User;
  replies?: Comment[];
}

// Attachment types
export interface Attachment {
  id: number;
  project_id: number;
  filename: string;
  original_filename: string;
  file_path: string;
  file_size: number;
  file_type: string;
  description?: string;
  uploaded_by: number;
  created_at: string;
  uploader?: User;
}

// Tag types
export interface Tag {
  id: number;
  name: string;
  color: string;
  description?: string;
  created_by: number;
  created_at: string;
  creator?: User;
  project_count?: number;
}

// Notification types
export interface Notification {
  id: number;
  user_id: number;
  type:
    | 'project_assigned'
    | 'project_status_changed'
    | 'comment_added'
    | 'deadline_approaching'
    | 'project_completed'
    | 'new_project';
  title: string;
  message: string;
  data?: Record<string, any>;
  is_read: boolean;
  read_at?: string;
  created_at: string;
}

// Analytics types
export interface ProjectStats {
  total: number;
  by_status: Record<string, number>;
  by_province: Record<string, number>;
  completed: number;
  pending: number;
  in_progress: number;
  completion_rate: number;
}

export interface HeatMapData {
  lat: number;
  lng: number;
  count: number;
  status: string;
}

export interface TrendData {
  date: string;
  new_projects: number;
  completed_projects: number;
  total: number;
}

export interface ActivityFeedItem {
  id: number;
  user_id?: number;
  action: string;
  entity_type: string;
  entity_id: number;
  details?: Record<string, any>;
  created_at: string;
  user?: User;
}

// Filter types
export interface ProjectFilters {
  status?: string;
  province?: string;
  municipality?: string;
  district?: string;
  search?: string;
  assigned_to?: number;
  page?: number;
  page_size?: number;
  sort_by?: string;
  sort_desc?: boolean;
}

// Pagination types
export interface PaginatedResponse<T> {
  total: number;
  page: number;
  page_size: number;
  items: T[];
}
