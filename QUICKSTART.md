# Property Rental Management System - Quick Start

## 🚀 Quick Access Guide

### System URLs (After XAMPP Setup)

```
Main Entry:     http://localhost/property-rental-management-system/public/
Login Page:     http://localhost/property-rental-management-system/app/views/login.php
Property List:  http://localhost/property-rental-management-system/app/views/property_list.php
Add Property:   http://localhost/property-rental-management-system/app/views/add_property.php
Edit Property:  http://localhost/property-rental-management-system/app/views/edit_property.php
```

## 📦 What's Included

### Frontend Pages (5)
- ✅ `login.php` - Login interface (UI only, no authentication)
- ✅ `property_list.php` - Property listing with table
- ✅ `add_property.php` - Add property form
- ✅ `edit_property.php` - Edit property form (pre-filled)
- ✅ `index.php` - Landing page

### Backend Controllers (4)
- ✅ `add_property.php` - Add handler (comments only)
- ✅ `update_property.php` - Update handler (comments only)
- ✅ `delete_property.php` - Delete handler (comments only)
- ✅ `upload_image.php` - Image upload handler (comments only)

### Database
- ✅ `schema.sql` - Complete database with 3 tables + sample data
  - users (5 records)
  - properties (6 records)
  - property_images (11 records)

### Configuration
- ✅ `database.php` - Database config
- ✅ `db_connect.php` - MySQLi connection helper

### Assets
- ✅ `style.css` - Custom responsive stylesheet
- ✅ `uploads/` - Image storage directory

### Documentation
- ✅ `SETUP.md` - Detailed setup instructions
- ✅ `README.md` - Project overview
- ✅ `walkthrough.md` - Complete feature walkthrough

## ⚡ 5-Minute Setup

1. **Start XAMPP**
   - Start Apache
   - Start MySQL

2. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `prms_db`
   - Import: `storage/schema.sql`

3. **Access System**
   - Visit: `http://localhost/property-rental-management-system/public/`

## 🎯 Key Features

### What Works (UI)
- ✅ All pages load and display
- ✅ Forms with validation
- ✅ Navigation between pages
- ✅ Responsive Bootstrap design
- ✅ Static data display

### What's NOT Implemented (By Design)
- ❌ Authentication logic
- ❌ Database CRUD operations
- ❌ File upload processing
- ❌ Session management

## 📊 Database Schema

```sql
users
├── id (PK)
├── name
├── email
├── password
├── role (owner/tenant/admin)
└── created_at

properties
├── id (PK)
├── title
├── description
├── price
├── location
├── type (Apartment/House/Condo/Studio/Villa/Townhouse)
├── status (Available/Rented/Maintenance)
├── owner_id (FK → users.id)
├── created_at
└── updated_at

property_images
├── id (PK)
├── property_id (FK → properties.id)
├── image_path
├── is_primary
└── created_at
```

## 🧪 Testing Checklist

- [ ] Landing page loads with all sections
- [ ] Login page displays (no functionality expected)
- [ ] Property list shows 5 sample properties
- [ ] Add property form has all fields
- [ ] Edit property form is pre-filled
- [ ] Forms submit to backend (show placeholder responses)
- [ ] Navigation works between all pages

## 📝 Important Notes

> **This is a PROTOTYPE/BLUEPRINT**
> - Focus is on UI, structure, and database design
> - Backend files contain detailed comments, not implementation
> - Perfect for demonstrating system design and planning
> - Ready for Sprint 1 presentation

## 🔗 Related Files

- See `SETUP.md` for detailed installation instructions
- See `walkthrough.md` for complete feature documentation
- See `README.md` for project overview

## 💡 Next Steps for Full Implementation

1. Implement authentication with sessions
2. Add database CRUD operations
3. Implement file upload functionality
4. Add role-based access control
5. Implement search/filter features
6. Add pagination to property list

---

**Created**: December 2024  
**Purpose**: Educational Prototype  
**Environment**: XAMPP (PHP + MySQL)  
**Framework**: Procedural PHP + Bootstrap 5
