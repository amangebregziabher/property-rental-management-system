-- Property Rental Management System
-- Database Schema Definition (PRMS-21)

CREATE DATABASE IF NOT EXISTS prms_db;
USE prms_db;

-- Table: users
-- Manages property owners and system users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: properties
-- Stores core details for each rental listing
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) UNSIGNED NOT NULL DEFAULT 0.00,
    location VARCHAR(255) NOT NULL,
    property_type ENUM('Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse') NOT NULL,
    status ENUM('Available', 'Rented', 'Maintenance') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_owner
        FOREIGN KEY (owner_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    INDEX (status),
    INDEX (property_type),
    INDEX (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: property_images
-- Supports multiple images per property
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    INDEX (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data (Standard Bootstrap User and Properties)
INSERT INTO users (name, email, password) VALUES 
('System Admin', 'admin@prms.com', '$2y$10$abcdefghijklmnopqrstuv'), -- Dummy hash
('Property Manager', 'manager@prms.com', '$2y$10$abcdefghijklmnopqrstuv');

INSERT INTO properties (owner_id, title, description, price, location, property_type, status) VALUES
(1, 'Modern Downtown Apartment', 'A beautiful 2-bedroom apartment in the heart of the city.', 1200.00, 'Downtown, City Center', 'Apartment', 'Available'),
(2, 'Cozy Suburban House', 'Perfect family home with a spacious backyard.', 1800.00, 'Maple Street, Suburbs', 'House', 'Available');

-- Table: property_documents
-- Stores documents associated with a property (Leases, ID copies, etc.)
CREATE TABLE IF NOT EXISTS property_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_type VARCHAR(50), -- e.g., 'Lease Agreement', 'Title Deed'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_document_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    INDEX (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_applications
-- Stores applications submitted by tenants for properties
CREATE TABLE IF NOT EXISTS rental_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT NOT NULL,
    applicant_name VARCHAR(255) NOT NULL,
    applicant_email VARCHAR(255) NOT NULL,
    applicant_phone VARCHAR(50) NOT NULL,
    message TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (property_id),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


