-- Project Tracking System Database Schema
-- Run this SQL to set up the database

CREATE DATABASE IF NOT EXISTS project_tracking DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE project_tracking;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_code VARCHAR(50) UNIQUE NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    municipality VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 6) NOT NULL,
    longitude DECIMAL(10, 6) NOT NULL,
    activation_date DATE NOT NULL,
    status ENUM('Done', 'Pending') NOT NULL,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_site_code (site_code),
    INDEX idx_province (province),
    INDEX idx_status (status),
    INDEX idx_activation_date (activation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Validation logs table for import tracking
CREATE TABLE IF NOT EXISTS validation_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    import_batch_id VARCHAR(50),
    row_number INT,
    field_name VARCHAR(50),
    error_message TEXT,
    original_value TEXT,
    corrected_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_batch_id (import_batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, role) 
VALUES ('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample data for Batanes projects
INSERT INTO projects (site_code, project_name, site_name, barangay, municipality, province, district, latitude, longitude, activation_date, status, notes) VALUES
('UNDP-GI-0009A', 'Free-WIFI for All', 'Raele Barangay Hall - AP 1', 'Raele', 'Itbayat', 'Batanes', 'District I', 20.728794, 121.804235, '2024-04-30', 'Done', 'Successfully activated'),
('UNDP-GI-0010A', 'Free-WIFI for All', 'Itbayat Barangay Hall - AP 1', 'Itbayat', 'Itbayat', 'Batanes', 'District I', 20.787133, 121.842835, '2024-04-30', 'Done', 'Successfully activated'),
('UNDP-GI-0011A', 'Free-WIFI for All', 'Savidug Barangay Hall - AP 1', 'Savidug', 'Sabtang', 'Batanes', 'District I', 20.348356, 121.873008, '2024-04-30', 'Done', 'Successfully activated'),
('UNDP-GI-0012A', 'Free-WIFI for All', 'San Vicente Ferrer Chapel - AP 1', 'Savidug', 'Sabtang', 'Batanes', 'District I', 20.355561, 121.873811, '2024-04-30', 'Done', 'Successfully activated');

-- Sample data for Cagayan projects
INSERT INTO projects (site_code, project_name, site_name, barangay, municipality, province, district, latitude, longitude, activation_date, status, notes) VALUES
('UNDP-GI-0013A', 'Free-WIFI for All', 'Awallan Barangay Hall - AP 1', 'Awallan', 'Baggao', 'Cagayan', 'District III', 17.932950, 121.958900, '2024-05-15', 'Pending', 'Awaiting equipment'),
('UNDP-GI-0014A', 'Free-WIFI for All', 'Poblacion Barangay Hall - AP 1', 'Poblacion', 'Tuguegarao', 'Cagayan', 'District I', 17.6132, 121.7269, '2024-05-20', 'Done', 'Installation complete');
