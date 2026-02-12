-- Additional tables for advanced features
USE project_tracking;

-- User profiles table
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    department VARCHAR(100),
    avatar VARCHAR(255),
    timezone VARCHAR(50) DEFAULT 'Asia/Manila',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token)
);

-- Project photos
CREATE TABLE IF NOT EXISTS project_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    caption TEXT,
    is_primary BOOLEAN DEFAULT FALSE,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_project (project_id)
);

-- Project documents
CREATE TABLE IF NOT EXISTS project_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    document_type ENUM('contract', 'permit', 'report', 'other') DEFAULT 'other',
    description TEXT,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_project (project_id)
);

-- Project categories/tags
CREATE TABLE IF NOT EXISTS project_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#3B82F6',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_name (name)
);

-- Project-category relationship
CREATE TABLE IF NOT EXISTS project_category_relations (
    project_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (project_id, category_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES project_categories(id) ON DELETE CASCADE
);

-- Activity audit log
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);

-- Application settings
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Map zones/areas
CREATE TABLE IF NOT EXISTS map_zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    coordinates JSON NOT NULL,
    color VARCHAR(7) DEFAULT '#3B82F6',
    fill_color VARCHAR(7) DEFAULT 'rgba(59, 130, 246, 0.2)',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insert default categories
INSERT INTO project_categories (name, color, description) VALUES
('Infrastructure', '#3B82F6', 'Infrastructure projects'),
('Education', '#10B981', 'Educational projects'),
('Health', '#EF4444', 'Health-related projects'),
('Technology', '#8B5CF6', 'Technology projects'),
('Community', '#F59E0B', 'Community development');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('app_name', 'Project Tracking System', 'string', 'Application name'),
('items_per_page', '25', 'integer', 'Number of items per page'),
('enable_registration', 'true', 'boolean', 'Allow user registration'),
('default_province', '', 'string', 'Default province for new projects'),
('map_default_lat', '17.0', 'string', 'Default map latitude'),
('map_default_lng', '121.0', 'string', 'Default map longitude'),
('map_default_zoom', '6', 'integer', 'Default map zoom level'),
('date_format', 'F d, Y', 'string', 'Date display format'),
('session_timeout', '1440', 'integer', 'Session timeout in minutes'),
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status');

-- Add status_history tracking
CREATE TABLE IF NOT EXISTS project_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    changed_by INT,
    change_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id),
    INDEX idx_project (project_id)
);

-- Add completion forecasts table
CREATE TABLE IF NOT EXISTS project_forecasts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    province VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    predicted_completion INT DEFAULT 0,
    confidence_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_forecast (province, year, month)
);
