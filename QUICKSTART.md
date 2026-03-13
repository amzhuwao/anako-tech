# 🚀 Quick Start Guide

## Installation (5 minutes)

### 1. Download System
Files are already in: `/opt/lampp/htdocs/anako-tech/`

### 2. Start XAMPP
- Open XAMPP Control Panel
- Click "Start" next to Apache
- Click "Start" next to MySQL

### 3. Create Database
- Open browser: `http://localhost/phpmyadmin`
- Click "New"
- Database name: `anako_technicians`
- Click "Create"

### 4. Initialize System
- Open: `http://localhost/anako-tech/includes/create_schema.php`
- Wait for confirmation ✓

### 5. Access System
- **Homepage**: `http://localhost/anako-tech/`
- **Technician Register**: `http://localhost/anako-tech/register.php`
- **Login**: `http://localhost/anako-tech/login.php`

## Login Credentials

### Admin Login
- **Role**: Admin
- **Username**: admin
- **Password**: admin123

### Test Technician
- **Role**: Technician
- Create your own account using the registration page

## First Steps

### 1. Admin: Change Password
- Login as admin
- Go to settings (implement password change feature)

### 2. Technician: Complete Registration
- Register as a technician
- Upload documents
- Add skills
- Wait for admin approval

### 3. Admin: Review Applications
- Go to Dashboard
- Click "Pending Review"
- View technician details
- Approve or Reject

## Common Tasks

### Register as Technician
1. Click "Register" on homepage
2. Fill all fields
3. Category: Choose your profession
4. Location: Enter your city/region
5. Click "Create Account"
6. Login with your credentials

### Upload Documents
1. Login as technician
2. Click "Upload Documents"
3. Select document type
4. Choose file (PDF, JPG, PNG - max 5MB)
5. Click "Upload Document"

### Add Skills
1. From technician dashboard
2. Click "Manage Skills"
3. Enter skill name or click suggested skills
4. Click "Add Skill"

### Search for Technicians (Admin)
1. Login as admin
2. Click "Search" in sidebar
3. Enter search criteria
4. Click "Search"
5. View detailed profiles

## File Permissions

If you get permission errors, run in terminal:

```bash
cd /opt/lampp/htdocs/anako-tech
chmod 755 uploads
chmod 755 uploads/profile_photos
chmod 755 uploads/documents
```

## Need Help?

### Check Database Connection
Open: `includes/db.php`
Verify these settings:
- DB_HOST: localhost
- DB_USER: root
- DB_PASS: (leave blank if no password)
- DB_NAME: anako_technicians

### Check File Structure
Should have these folders:
- admin/
- technician/
- includes/
- uploads/
- assets/

### Verify Apache is Running
- XAMPP Control Panel should show Apache status as "Running"
- Green indicators next to Apache and MySQL

## Next Steps

After setup is complete:

1. ✅ Test admin login
2. ✅ Create a test technician account
3. ✅ Upload some sample documents
4. ✅ Practice approving/rejecting technicians
5. ✅ Test the search functionality
6. ✅ Familiarize yourself with all features

---
**Setup Time**: ~5 minutes
**Status**: Ready to use!
