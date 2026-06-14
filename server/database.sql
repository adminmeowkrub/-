-- Lab Management System Database
-- Import this file into MySQL/phpMyAdmin

CREATE DATABASE IF NOT EXISTS lab_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lab_system;

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('superadmin','admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Computers table
CREATE TABLE computers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    ip_address VARCHAR(15) UNIQUE NOT NULL,
    mac_address VARCHAR(17),
    status ENUM('offline','locked','unlocked','idle') DEFAULT 'offline',
    last_seen TIMESTAMP NULL,
    current_user VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sessions table (การใช้งานแต่ละครั้ง)
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    computer_id INT NOT NULL,
    unlocked_by INT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    duration_minutes INT,
    FOREIGN KEY (computer_id) REFERENCES computers(id),
    FOREIGN KEY (unlocked_by) REFERENCES admins(id)
);

-- Web logs
CREATE TABLE web_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    computer_id INT NOT NULL,
    session_id INT,
    url VARCHAR(500),
    title VARCHAR(200),
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (computer_id) REFERENCES computers(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- App logs
CREATE TABLE app_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    computer_id INT NOT NULL,
    session_id INT,
    app_name VARCHAR(200),
    process_name VARCHAR(100),
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    FOREIGN KEY (computer_id) REFERENCES computers(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- Unlock requests (Client ส่งมาขอปลดล็อค)
CREATE TABLE unlock_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    computer_id INT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT,
    FOREIGN KEY (computer_id) REFERENCES computers(id),
    FOREIGN KEY (processed_by) REFERENCES admins(id)
);

-- Default superadmin (password: admin1234)
INSERT INTO admins (username, password, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'superadmin');

-- Sample computers
INSERT INTO computers (name, ip_address, status) VALUES
('PC-01', '192.168.0.101', 'offline'),
('PC-02', '192.168.0.102', 'offline'),
('PC-03', '192.168.0.103', 'offline'),
('PC-04', '192.168.0.104', 'offline'),
('PC-05', '192.168.0.105', 'offline');
