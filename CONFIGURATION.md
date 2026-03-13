# ⚙️ Configuration Guide

## Database Configuration

### File: `includes/db.php`

The database connection settings are configured in this file:

```php
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database user
define('DB_PASS', '');               // Database password
define('DB_NAME', 'anako_technicians'); // Database name
```

### Modifying Configuration

If you need to change database settings:

1. Open: `includes/db.php`
2. Update the database constants as needed
3. Save the file

**Common scenarios:**

- **XAMPP on Windows**: Usually `localhost` with empty password
- **LAMP on Linux**: Usually `localhost` with empty password
- **Production Server**: Use your server's database credentials
- **Remote Database**: Use server IP/domain and update credentials

## XAMPP Configuration

### Default Settings

XAMPP comes with:
- **MySQL Root User**: root
- **Default Password**: (empty)
- **Default Host**: localhost
- **Default Port**: 3306

### Changing MySQL Root Password

1. Open XAMPP Control Panel
2. Click "Shell" button
3. Run command:
   ```
   mysql -u root
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'newpassword';
   FLUSH PRIVILEGES;
   EXIT;
   ```
4. Update `DB_PASS` in `includes/db.php`

## Session Configuration

### Session Settings

Session timeout is controlled in `includes/auth.php`:

```php
session_start();
// Session duration depends on PHP.ini
// Default: 1440 seconds (24 minutes)
```

### Change Session Timeout

Edit `includes/auth.php` and add after `session_start()`:

```php
ini_set('session.gc_maxlifetime', 3600); // 1 hour in seconds
session.cookie_lifetime = 3600;
```

## File Upload Configuration

### Upload Limits

Current settings in `includes/functions.php`:

```php
$max_size = 5 * 1024 * 1024; // 5MB max file size
$allowed_types = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];
```

### Change Upload Limits

Edit `includes/functions.php` in the `uploadFile()` function:

1. **Increase file size limit** (to 10MB):
   ```php
   $max_size = 10 * 1024 * 1024;
   ```

2. **Add more file types** (e.g., Word documents):
   ```php
   $allowed_types = [
       'application/pdf',
       'image/jpeg',
       'image/png',
       'application/msword'
   ];
   ```

### Change Upload Directories

Edit `includes/functions.php` and update paths:

```php
// For profile photos
$upload_dir = '../uploads/profile_photos/';

// For documents
$upload_dir = '../uploads/documents/';
```

## Password Security

### BCrypt Configuration

Passwords are hashed using bcrypt in `includes/auth.php`:

```php
password_hash($password, PASSWORD_BCRYPT, ['cost' => 10])
```

The 'cost' parameter (10 = default) controls hash strength:
- Lower number (8-10): Faster, lower security
- Higher number (12+): Slower, higher security

### Change Bcrypt Cost

Edit `includes/auth.php` in the `hashPassword()` function:

```php
return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

## Category Configuration

### Available Technician Categories

Edit registration page or database to add/modify categories.

Current categories in `register.php`:

```php
$categories = [
    'Solar Technician',
    'Electrician',
    'CCTV Installer',
    'Network Technician',
    'Smart Home Installer',
    'Plumber',
    'Appliance Repair Technician'
];
```

### Add New Category

1. Edit `register.php`
2. Add to `$categories` array:
   ```php
   'Your New Category',
   ```

## Email Configuration (Future Enhancement)

For sending welcome emails or notifications:

1. You can add PHPMailer integration
2. Create `includes/email.php`
3. Configure SMTP settings
4. Send emails from registration confirmation

## Backup Configuration

### Database Backup

Using phpMyAdmin:
1. Click on `anako_technicians` database
2. Click "Export"
3. Select "Quick" or "Custom"
4. Click "Go"
5. Save the SQL file

Using command line:
```bash
mysqldump -u root anako_technicians > backup.sql
```

### Restore Backup

Using command line:
```bash
mysql -u root anako_technicians < backup.sql
```

## Security Configuration

### Enable HTTPS (Production)

1. Obtain SSL certificate
2. Update web server configuration
3. Modify all URLs to use `https://`
4. Add to `includes/db.php`:
   ```php
   if (empty($_SERVER['HTTPS'])) {
       header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
       exit;
   }
   ```

### Disable Directory Listing

Add to `.htaccess`:
```
Options -Indexes
```

### Set File Permissions

For Linux/Unix servers:
```bash
chmod 755 uploads/
chmod 755 uploads/profile_photos/
chmod 755 uploads/documents/
chmod 644 includes/*.php
chmod 644 admin/*.php
chmod 644 technician/*.php
```

## Admin Configuration

### Add New Admin Account

Using MySQL command:
```sql
INSERT INTO admins (username, email, password) 
VALUES ('newadmin', 'admin@example.com', 'hashed_password');
```

Using PHP:
```php
$conn = new mysqli('localhost', 'root', '', 'anako_technicians');
$username = 'newadmin';
$email = 'admin@example.com';
$password = password_hash('password123', PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password);
$stmt->execute();
```

### Change Admin Password

1. Login as admin
2. Use database tools or command to update password:
   ```sql
   UPDATE admins 
   SET password = '<new_hashed_password>'
   WHERE username = 'admin';
   ```

## Performance Optimization

### Database Indexing

Add indexes to improve search performance:

```sql
ALTER TABLE technicians ADD INDEX (status);
ALTER TABLE technicians ADD INDEX (location);
ALTER TABLE technicians ADD INDEX (category);
ALTER TABLE skills ADD INDEX (technician_id);
ALTER TABLE documents ADD INDEX (technician_id);
```

### Pagination

Current pagination: 10 items per page.

Change in `admin/technicians.php`:
```php
$per_page = 10;  // Change this number
```

## Logging

### Enable Activity Logging

Uncomment code in relevant files to log activities:

```php
// Log admin action to admin_logs table
$log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, technician_id) VALUES (?, ?, ?)");
$log_stmt->bind_param("isi", $admin_id, $action, $technician_id);
$log_stmt->execute();
```

## Troubleshooting Configuration

### Connection Errors

Check:
1. MySQL is running
2. Database name is correct: `anako_technicians`
3. Credentials in `includes/db.php` are correct
4. Database exists

### Upload Errors

Check:
1. Folder permissions: `chmod 755 uploads/`
2. File size under limit
3. File type is allowed (PDF, JPG, PNG)
4. Server disk space available

### Session Errors

Check:
1. PHP sessions are enabled
2. `/tmp` folder exists and is writable
3. Session timeout settings in php.ini

---
For more help, refer to README.md or QUICKSTART.md
