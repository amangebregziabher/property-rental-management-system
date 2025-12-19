# Property Rental Management System - Setup Guide

## Overview

This is a **blueprint-level prototype** of a Property Rental Management System built with PHP and MySQL for XAMPP. The system demonstrates UI, page flow, and database structure without implementing full backend logic or authentication.

## Requirements

- **XAMPP** (Apache + MySQL + PHP 7.4+)
- Web browser (Chrome, Firefox, Edge, etc.)
- Text editor (optional, for viewing code)

## Installation Steps

### 1. Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP to your preferred location (e.g., `C:\xampp`)
3. Start XAMPP Control Panel

### 2. Setup Project Files

1. Copy the `property-rental-management-system` folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\property-rental-management-system\
   ```
   
   Or if you're using a different drive:
   ```
   D:\PM\property-rental-management-system\
   ```

2. Ensure the folder structure looks like this:
   ```
   property-rental-management-system/
   ├── app/
   │   ├── controllers/
   │   ├── models/
   │   └── views/
   ├── config/
   ├── public/
   ├── storage/
   └── README.md
   ```

### 3. Start XAMPP Services

1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Click **Start** next to MySQL
4. Wait for both services to show green "Running" status

### 4. Create Database

1. Open your web browser
2. Navigate to: `http://localhost/phpmyadmin`
3. Click on **"New"** in the left sidebar
4. Create a new database:
   - Database name: `prms_db`
   - Collation: `utf8mb4_unicode_ci`
5. Click **Create**

### 5. Import Database Schema

1. In phpMyAdmin, select the `prms_db` database
2. Click on the **Import** tab
3. Click **Choose File**
4. Navigate to: `property-rental-management-system/storage/schema.sql`
5. Click **Go** to import
6. You should see a success message and three tables created:
   - `users`
   - `properties`
   - `property_images`

### 6. Verify Database Setup

1. Click on the `prms_db` database in phpMyAdmin
2. You should see 3 tables with sample data:
   - **users**: 5 sample users
   - **properties**: 6 sample properties
   - **property_images**: 11 sample image records

## Accessing the Application

### Main Entry Point
```
http://localhost/property-rental-management-system/public/
```

### Individual Pages

**Login Page (UI Only - No Authentication):**
```
http://localhost/property-rental-management-system/app/views/login.php
```

**Property List:**
```
http://localhost/property-rental-management-system/app/views/property_list.php
```

**Add Property:**
```
http://localhost/property-rental-management-system/app/views/add_property.php
```

**Edit Property:**
```
http://localhost/property-rental-management-system/app/views/edit_property.php
```

## Page Flow

1. **Start** → `index.php` (Landing page with navigation)
2. **Login** → `login.php` (UI only, no authentication)
3. **Property List** → `property_list.php` (View all properties)
4. **Add Property** → `add_property.php` (Form to add new property)
5. **Edit Property** → `edit_property.php` (Form to edit existing property)

## Important Notes

### ⚠️ Prototype Limitations

- **No Authentication**: Login page is UI only, no actual login logic
- **No Backend Logic**: Backend controllers contain comments only
- **Static Data**: Property list shows hardcoded data
- **No Database Operations**: Forms don't actually insert/update/delete data
- **No Image Upload**: Image upload is UI only, files aren't saved

### ✅ What Works

- All pages load and display correctly
- Forms have proper validation attributes
- Navigation between pages works
- Database schema is complete and ready
- UI is responsive and styled with Bootstrap

## Testing the System

### 1. Test Navigation
- Visit `http://localhost/property-rental-management-system/public/`
- Click on different feature cards
- Verify all pages load

### 2. Test Login Page
- Navigate to login page
- Try entering email and password
- Click login button
- Note: Nothing happens (expected behavior for prototype)

### 3. Test Property List
- Navigate to property list
- Verify table displays 5 properties
- Click Edit/Delete buttons
- Note: Shows placeholder messages

### 4. Test Add Property Form
- Navigate to add property page
- Fill in all form fields
- Try submitting
- Verify placeholder response page appears

### 5. Test Edit Property Form
- Navigate to edit property page
- Verify form is pre-filled with dummy data
- Try submitting
- Verify placeholder response page appears

## Troubleshooting

### Apache Won't Start
- Check if port 80 is being used by another application
- Try changing Apache port in XAMPP config

### MySQL Won't Start
- Check if port 3306 is being used
- Verify MySQL service isn't already running

### Page Not Found (404)
- Verify XAMPP Apache is running
- Check the URL path is correct
- Ensure files are in `htdocs` directory

### Database Connection Error
- Verify MySQL is running in XAMPP
- Check database name is `prms_db`
- Verify credentials in `config/database.php`

## Sample Login Credentials

**Note**: Login is not functional in this prototype, but here are the sample users in the database:

| Email | Role | Password (hashed) |
|-------|------|-------------------|
| john@example.com | owner | password |
| jane@example.com | owner | password |
| admin@example.com | admin | password |
| bob@example.com | tenant | password |
| alice@example.com | tenant | password |

## Next Steps for Full Implementation

To convert this prototype into a functional system:

1. Implement authentication logic in backend
2. Add session management
3. Implement CRUD operations in controllers
4. Add file upload functionality
5. Implement user role-based access control
6. Add validation and error handling
7. Implement search and filter features
8. Add pagination for property listings

## Support

For issues or questions about this prototype, refer to the code comments in each file which explain the intended functionality.

## License

Proprietary - For Educational Purposes Only
