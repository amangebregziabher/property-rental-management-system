-- Property Rental Management System
-- Database Schema Definition (Extended with Tenant Profiles)

CREATE DATABASE IF NOT EXISTS prms_db;
USE prms_db;

-- Disable foreign key checks to allow dropping tables with dependencies
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS property_amenities;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS property_documents;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS maintenance_requests;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS tenant_profiles;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS amenities;
DROP TABLE IF EXISTS users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Table: users
-- Manages all system users (Admin, Property Owners, Tenants)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner', 'tenant') NOT NULL DEFAULT 'tenant',
    phone VARCHAR(20),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (email),
    INDEX (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tenant_profiles
-- Extended profile information for tenants
CREATE TABLE IF NOT EXISTS tenant_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    emergency_contact_name VARCHAR(255),
    emergency_contact_phone VARCHAR(20),
    employer_name VARCHAR(255),
    job_title VARCHAR(100),
    monthly_income DECIMAL(10, 2),
    id_document_path VARCHAR(255), -- Path to uploaded ID/Passport
    background_check_status ENUM('Pending', 'Cleared', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tp_user
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: categories
-- Property type categories (replaces hardcoded ENUM)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: amenities
-- Available property amenities/features
CREATE TABLE IF NOT EXISTS amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50), -- Icon class or identifier
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROPERTY TABLES
-- ============================================================================

-- Table: properties
-- Stores core details for each rental listing
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) UNSIGNED NOT NULL DEFAULT 0.00,
    location VARCHAR(255) NOT NULL,
    address TEXT,
    bedrooms INT UNSIGNED DEFAULT 0,
    bathrooms INT UNSIGNED DEFAULT 0,
    type ENUM('Apartment', 'House', 'Condo', 'Studio', 'Villa', 'Townhouse') DEFAULT 'Apartment',
    status ENUM('Available', 'Rented', 'Maintenance') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_owner
        FOREIGN KEY (owner_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_category
        FOREIGN KEY (category_id) 
        REFERENCES categories(id) 
        ON DELETE RESTRICT,
    INDEX (status),
    INDEX (category_id),
    INDEX (owner_id),
    INDEX (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: property_amenities
-- Many-to-many relationship between properties and amenities
CREATE TABLE IF NOT EXISTS property_amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    amenity_id INT NOT NULL,
    CONSTRAINT fk_pa_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_pa_amenity
        FOREIGN KEY (amenity_id) 
        REFERENCES amenities(id) 
        ON DELETE CASCADE,
    UNIQUE KEY unique_property_amenity (property_id, amenity_id),
    INDEX (property_id),
    INDEX (amenity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: property_images
-- Supports multiple images per property
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pi_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    INDEX (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: property_documents
-- Stores documents associated with a property (Leases, ID copies, etc.)
CREATE TABLE IF NOT EXISTS property_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(255) NOT NULL,
    document_type VARCHAR(50), -- e.g., 'Lease Agreement', 'Title Deed'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pd_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    INDEX (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BOOKING & PAYMENT TABLES
-- ============================================================================

-- Table: bookings
-- Tracks property rental bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_amount DECIMAL(10, 2) UNSIGNED NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Active', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_booking_tenant
        FOREIGN KEY (tenant_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    INDEX (property_id),
    INDEX (tenant_id),
    INDEX (status),
    INDEX (start_date),
    INDEX (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payments
-- Records all payment transactions
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) UNSIGNED NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method ENUM('Cash', 'Bank Transfer', 'Credit Card', 'Debit Card', 'Mobile Money', 'Other') NOT NULL,
    transaction_reference VARCHAR(255),
    status ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
    notes TEXT,
    CONSTRAINT fk_payment_booking
        FOREIGN KEY (booking_id) 
        REFERENCES bookings(id) 
        ON DELETE CASCADE,
    INDEX (booking_id),
    INDEX (status),
    INDEX (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- MAINTENANCE & REVIEW TABLES
-- ============================================================================

-- Table: maintenance_requests
-- Tracks maintenance issues reported by tenants
CREATE TABLE IF NOT EXISTS maintenance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
    status ENUM('Open', 'In Progress', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    CONSTRAINT fk_mr_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_mr_tenant
        FOREIGN KEY (tenant_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    INDEX (property_id),
    INDEX (tenant_id),
    INDEX (status),
    INDEX (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reviews
-- Property reviews and ratings from tenants
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    booking_id INT, -- Optional: link to specific booking
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_review_tenant
        FOREIGN KEY (tenant_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_review_booking
        FOREIGN KEY (booking_id) 
        REFERENCES bookings(id) 
        ON DELETE SET NULL,
    INDEX (property_id),
    INDEX (tenant_id),
    INDEX (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_applications
-- Tracks property rental applications
CREATE TABLE IF NOT EXISTS rental_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT NULL,
    applicant_name VARCHAR(255) NOT NULL,
    applicant_email VARCHAR(255) NOT NULL,
    applicant_phone VARCHAR(50),
    message TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    occupants INT DEFAULT 1,
    move_in_date DATE,
    employer VARCHAR(255),
    job_title VARCHAR(255),
    monthly_income DECIMAL(10, 2),
    employment_status VARCHAR(50),
    id_document_path VARCHAR(255),
    income_document_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_app_property
        FOREIGN KEY (property_id) 
        REFERENCES properties(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_app_user
        FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE SET NULL,
    INDEX (property_id),
    INDEX (user_id),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: application_notes
-- Internal notes for property owners to track application review
CREATE TABLE IF NOT EXISTS application_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    owner_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_note_application
        FOREIGN KEY (application_id) 
        REFERENCES rental_applications(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_note_owner
        FOREIGN KEY (owner_id) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    INDEX (application_id),
    INDEX (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: application_status_history
-- Tracks status changes for applications
CREATE TABLE IF NOT EXISTS application_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    old_status ENUM('Pending', 'Approved', 'Rejected'),
    new_status ENUM('Pending', 'Approved', 'Rejected') NOT NULL,
    changed_by INT NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_application
        FOREIGN KEY (application_id) 
        REFERENCES rental_applications(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_history_user
        FOREIGN KEY (changed_by) 
        REFERENCES users(id) 
        ON DELETE CASCADE,
    INDEX (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Apartment', 'Multi-unit residential building'),
('House', 'Single-family detached home'),
('Condo', 'Individually owned unit in a larger building'),
('Studio', 'Single-room living space'),
('Villa', 'Luxury standalone residence'),
('Townhouse', 'Multi-floor home sharing walls with neighbors');

-- Insert sample amenities
INSERT INTO amenities (name, icon) VALUES 
('WiFi', 'wifi'),
('Parking', 'car'),
('Air Conditioning', 'wind'),
('Heating', 'flame'),
('Swimming Pool', 'waves'),
('Gym', 'dumbbell'),
('Laundry', 'washing-machine'),
('Balcony', 'home'),
('Pet Friendly', 'dog'),
('Security', 'shield');

-- Insert sample users
INSERT INTO users (name, email, password, role, phone) VALUES 
('System Admin', 'admin@prms.com', '$2y$10$abcdefghijklmnopqrstuv', 'admin', '+1234567890'),
('John Smith', 'john.smith@prms.com', '$2y$10$abcdefghijklmnopqrstuv', 'owner', '+1234567891'),
('Jane Doe', 'jane.doe@prms.com', '$2y$10$abcdefghijklmnopqrstuv', 'tenant', '+1234567892');

-- Insert sample tenant profile
INSERT INTO tenant_profiles (user_id, employer_name, job_title, monthly_income) VALUES
(3, 'Tech Corp', 'Software Developer', 5000.00);

-- Insert sample properties
INSERT INTO properties (owner_id, category_id, title, description, price, location, address, bedrooms, bathrooms, type, status) VALUES
(2, 1, 'Modern Downtown Apartment', 'A beautiful 2-bedroom apartment in the heart of the city with stunning views.', 1200.00, 'Downtown, City Center', '123 Main Street, Apt 5B', 2, 2, 'Apartment', 'Available'),
(2, 2, 'Cozy Suburban House', 'Perfect family home with a spacious backyard and quiet neighborhood.', 1800.00, 'Maple Street, Suburbs', '456 Maple Street', 3, 2, 'House', 'Available'),
(2, 4, 'City Center Studio', 'Compact and efficient studio perfect for young professionals.', 800.00, 'Downtown', '789 Urban Ave, Unit 12', 1, 1, 'Studio', 'Available');

-- Insert sample property amenities
INSERT INTO property_amenities (property_id, amenity_id) VALUES
(1, 1), (1, 3), (1, 8), (1, 10), -- Apartment: WiFi, AC, Balcony, Security
(2, 1), (2, 2), (2, 4), (2, 7), (2, 9), -- House: WiFi, Parking, Heating, Laundry, Pet Friendly
(3, 1), (3, 3), (3, 6); -- Studio: WiFi, AC, Gym

