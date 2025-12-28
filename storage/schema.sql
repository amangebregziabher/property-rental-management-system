-- ============================================
-- Property Rental Management System
-- Database Schema for MySQL (XAMPP)
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS prms_db;
USE prms_db;

-- ============================================
-- Table: users
-- Stores user information (owners, tenants, admins)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('owner', 'tenant', 'admin') DEFAULT 'tenant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: properties
-- Stores property listings
-- ============================================
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    location VARCHAR(200) NOT NULL,
    type ENUM('Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse') DEFAULT 'Apartment',
    status ENUM('Available', 'Rented', 'Maintenance') DEFAULT 'Available',
    owner_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: property_images
-- Stores multiple images for each property
-- ============================================
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample Data: Users
-- ============================================
INSERT INTO users (name, email, password, role) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner'),
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Bob Johnson', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant'),
('Alice Williams', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant');

-- ============================================
-- Sample Data: Properties
-- ============================================
INSERT INTO properties (title, description, price, location, type, status, owner_id) VALUES
('Modern Downtown Apartment', 'Beautiful 2-bedroom apartment in the heart of downtown with stunning city views.', 1200.00, 'Downtown, City Center', 'Apartment', 'Available', 1),
('Cozy Suburban House', 'Spacious 3-bedroom house with a large backyard, perfect for families.', 1800.00, 'Maple Street, Suburbs', 'House', 'Available', 1),
('Luxury Beachfront Condo', 'Premium condo with ocean views and private beach access.', 2500.00, 'Ocean Drive, Beach Area', 'Condo', 'Rented', 2),
('Student-Friendly Studio', 'Compact studio apartment near university campus with all amenities.', 650.00, 'University District', 'Studio', 'Available', 2),
('Executive Villa', 'Elegant 5-bedroom villa with pool and garden in exclusive neighborhood.', 4500.00, 'Hillside Estates', 'Villa', 'Maintenance', 1),
('Urban Townhouse', 'Modern 3-story townhouse with garage and rooftop terrace.', 2200.00, 'West End', 'Townhouse', 'Available', 2);

-- ============================================
-- Sample Data: Property Images
-- ============================================
INSERT INTO property_images (property_id, image_path, is_primary) VALUES
(1, 'uploads/property_1_main.jpg', TRUE),
(1, 'uploads/property_1_bedroom.jpg', FALSE),
(1, 'uploads/property_1_kitchen.jpg', FALSE),
(2, 'uploads/property_2_main.jpg', TRUE),
(2, 'uploads/property_2_backyard.jpg', FALSE),
(3, 'uploads/property_3_main.jpg', TRUE),
(3, 'uploads/property_3_ocean.jpg', FALSE),
(4, 'uploads/property_4_main.jpg', TRUE),
(5, 'uploads/property_5_main.jpg', TRUE),
(5, 'uploads/property_5_pool.jpg', FALSE),
(6, 'uploads/property_6_main.jpg', TRUE);

-- ============================================
-- End of Schema
-- ============================================
