CREATE DATABASE IF NOT EXISTS uon_website;
USE uon_website;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, role) VALUES 
('superadmin', '$2y$10$ABCDEFGHIJKLMNOPQRSTUV.abcd1234efgh5678ijkl9012', 'superadmin');

CREATE TABLE subject_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO subject_areas (name, slug) VALUES 
('Computing', 'computing'),
('History', 'history'),
('Science', 'science');

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_area_id INT,
    title VARCHAR(200) NOT NULL,
    type VARCHAR(10) NOT NULL,
    duration_years INT NOT NULL,
    description TEXT NOT NULL,
    part_time TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_area_id) REFERENCES subject_areas(id) ON DELETE SET NULL
);

CREATE TABLE modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(20) NOT NULL UNIQUE,
    module_title VARCHAR(200) NOT NULL,
    module_description TEXT NOT NULL
);

CREATE TABLE course_modules (
    course_id INT NOT NULL,
    module_id INT NOT NULL,
    PRIMARY KEY (course_id, module_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

CREATE TABLE enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    course_id INT,
    message TEXT NOT NULL,
    responded_by INT,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL
);