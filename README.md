# 🔧 Anako Technician Registration Platform

A professional web-based system for managing technician registrations, profiles, and approvals.

## 📋 Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Default Credentials](#default-credentials)
- [File Structure](#file-structure)
- [Database](#database)
- [Security](#security)

## ✨ Features

### For Technicians
- ✅ Easy registration and profile creation
- ✅ Upload professional documents and certificates
- ✅ Manage skills and experience
- ✅ Profile photo uploads
- ✅ Track application status

### For Administrators
- ✅ View all technician registrations
- ✅ Approve/Reject applications
- ✅ Search technicians by skill, location, category, experience
- ✅ Manage technician profiles
- ✅ View uploaded documents and certificates
- ✅ Dashboard with statistics

## 🔧 Requirements

- **Web Server**: Apache with PHP support (XAMPP, LAMP, etc.)
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

## 📦 Installation

### Step 1: Download Files
The application is located at: `/opt/lampp/htdocs/anako-tech/`

### Step 2: Create Database
1. Start Apache and MySQL from XAMPP Control Panel
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database named `anako_technicians`

### Step 3: Run Schema Setup
1. Open your browser and go to: `http://localhost/anako-tech/includes/create_schema.php`
2. This will create all necessary tables and add a default admin account

### Step 4: Verify Installation
- All tables should be created successfully
- Default admin account will be created with:
  - **Username**: admin
  - **Password**: admin123

## 🚀 Usage

### For Technicians

#### Registration
1. Visit: `http://localhost/anako-tech/register.php`
2. Fill in all required information
3. Click "Create Account"
4. You will be redirected to login

#### Login
1. Go to: `http://localhost/anako-tech/login.php`
2. Select "Technician" as the role
3. Enter your email and password
4. Click "Login"

#### Complete Your Profile
1. From your dashboard, click "My Profile"
2. Click "Edit Profile" to:
   - Upload profile photo
   - Add biography
   - Update years of experience
3. Click "Manage Skills" to add your professional skills
4. Click "Upload Documents" to upload:
   - National ID
   - Certificates
   - Training proof
   - Portfolio images

### For Administrators

#### Admin Login
1. Go to: `http://localhost/anako-tech/login.php`
2. Select "Admin" as the role
3. Enter username: `admin`
4. Enter password: `admin123`
5. Click "Login"

#### Dashboard
The admin dashboard shows:
- Total technicians count
- Pending applications count
- Approved technicians count
- Rejected applications count

#### Review Applications
1. Click "Pending Review" in the sidebar
2. Click "View" button to see full details
3. Click "Approve" or "Reject" to make a decision
4. Document reviews are provided before decision-making

#### Search Technicians
1. Click "Search" in the sidebar
2. Filter by:
   - Skill (e.g., "Solar Installation")
   - Location
   - Category
   - Minimum experience years
3. Click "Search" to see results
4. Click "View" to see detailed profile

#### Manage Technicians
- **View All**: See all technicians in the system
- **Approved**: View only approved technicians
- **Rejected**: View rejected applications
- **View Details**: Click any technician to see complete profile and documents

## 🔐 Default Credentials

### Admin Account
- **URL**: `http://localhost/anako-tech/login.php`
- **Role**: Admin
- **Username**: admin
- **Password**: admin123
- **⚠️ IMPORTANT**: Change this password immediately after first login!

### Technician Registration
- Anyone can register at: `http://localhost/anako-tech/register.php`
- Registration forms require:
  - Full name
  - Email address
  - Phone number
  - Password (min 6 characters)
  - Location
  - Technician category

## 📁 File Structure

```
anako-tech/
├── includes/
│   ├── db.php                 # Database connection
│   ├── auth.php               # Authentication functions
│   ├── functions.php          # Helper functions
│   └── create_schema.php      # Database schema creation
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── technicians.php        # View all/filter technicians
│   ├── search.php             # Advanced search
│   ├── view_technician.php    # View technician details
│   ├── pending.php            # Redirect to pending filter
│   ├── approved.php           # Redirect to approved filter
│   └── rejected.php           # Redirect to rejected filter
├── technician/
│   ├── profile.php            # Technician profile view
│   ├── edit_profile.php       # Edit profile information
│   ├── upload_documents.php   # Upload documents
│   └── manage_skills.php      # Manage skills
├── uploads/
│   ├── profile_photos/        # Uploaded profile photos
│   └── documents/             # Uploaded documents
├── assets/
│   ├── css/                   # CSS files
│   ├── js/                    # JavaScript files
│   └── images/                # Image files
├── index.php                  # Homepage
├── login.php                  # Login page
├── register.php               # Registration page
└── logout.php                 # Logout

```

## 🗄️ Database

### Tables Created

1. **technicians** - Stores technician information
   - Basic profile data
   - Contact information
   - Approval status
   - Experience level

2. **skills** - Stores technician skills
   - References technician ID
   - Multiple skills per technician

3. **documents** - Stores uploaded files
   - Document type (ID, Certificate, etc.)
   - File path
   - Upload timestamp

4. **admins** - Stores admin accounts
   - Username and email
   - Hashed password
   - Creation date

5. **admin_logs** - Tracks admin activities (for future use)

## 🔒 Security Features

The system includes important security measures:

✅ **Password Hashing**: Uses bcrypt for secure password storage
✅ **Input Validation**: All user inputs are sanitized
✅ **Session Management**: Secure session handling
✅ **Role-Based Access**: Separate logins for technicians and admins
✅ **File Upload Security**: Allowed file types and size limits
✅ **SQL Injection Prevention**: Uses prepared statements

### Security Best Practices

1. **Change Admin Password**
   - Login as admin and consider creating a new secure account

2. **HTTPS**: Use HTTPS in production environment

3. **Database**: Keep database credentials secure and separate from code

4. **Backups**: Regular database backups are recommended

5. **Updates**: Keep PHP and MySQL updated

## 🛠️ Troubleshooting

### Issue: "Connection failed"
**Solution**: Check if MySQL is running. Verify database name and credentials in `includes/db.php`

### Issue: "Schema creation failed"
**Solution**: 
1. Make sure the `anako_technicians` database exists
2. Check that uploads folder has write permissions
3. Try running schema again

### Issue: "File upload failed"
**Solution**:
1. Ensure `uploads/` folder exists and has write permissions (chmod 755)
2. Check file size (max 5MB)
3. Verify file format (PDF, JPG, PNG only)

### Issue: "Cannot find page"
**Solution**:
1. Verify XAMPP is running
2. Check that files are in `/opt/lampp/htdocs/anako-tech/`
3. Try accessing: `http://localhost/anako-tech/`

## 📞 Support

For issues or questions about installation and setup, ensure:
1. MySQL and Apache are running
2. All files are in correct directories
3. Database schema has been created
4. File permissions are correct (chmod 755 for folders)

## 📝 License

This is a custom project developed for Anako Smart Systems.

---

**Version**: 1.0
**Last Updated**: March 2026
**Developer Contact**: Based on Anako Smart Systems requirements
