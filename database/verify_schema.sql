-- Verification Script for PRMS Database Schema
-- This script drops and recreates the database to test the schema

-- Drop existing database if it exists
DROP DATABASE IF EXISTS prms_db;

-- Source the main schema file
SOURCE schema.sql;

-- Verify all tables were created
SELECT 'Checking tables...' as status;
SHOW TABLES;

-- Verify users table structure
SELECT 'Users table structure:' as status;
DESCRIBE users;

-- Verify categories table
SELECT 'Categories table structure:' as status;
DESCRIBE categories;

-- Verify amenities table
SELECT 'Amenities table structure:' as status;
DESCRIBE amenities;

-- Verify properties table
SELECT 'Properties table structure:' as status;
DESCRIBE properties;

-- Verify property_amenities table
SELECT 'Property Amenities table structure:' as status;
DESCRIBE property_amenities;

-- Verify bookings table
SELECT 'Bookings table structure:' as status;
DESCRIBE bookings;

-- Verify payments table
SELECT 'Payments table structure:' as status;
DESCRIBE payments;

-- Verify maintenance_requests table
SELECT 'Maintenance Requests table structure:' as status;
DESCRIBE maintenance_requests;

-- Verify reviews table
SELECT 'Reviews table structure:' as status;
DESCRIBE reviews;

-- Check sample data
SELECT 'Sample data verification:' as status;
SELECT COUNT(*) as user_count FROM users;
SELECT COUNT(*) as category_count FROM categories;
SELECT COUNT(*) as amenity_count FROM amenities;
SELECT COUNT(*) as property_count FROM properties;
SELECT COUNT(*) as property_amenity_count FROM property_amenities;
