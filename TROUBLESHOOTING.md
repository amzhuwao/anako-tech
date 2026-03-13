# 🆘 Troubleshooting & FAQ

## Common Issues & Solutions

### 1. "Cannot connect to database"

**Error Message**: `Connection failed: Can't connect to MySQL server on 'localhost'`

**Causes & Solutions**:
- ❌ MySQL not running
  - ✅ Start MySQL from XAMPP Control Panel
  
- ❌ Wrong database credentials
  - ✅ Check `includes/db.php` settings
  
- ❌ Wrong database name
  - ✅ Verify database `anako_technicians` exists in phpMyAdmin

**Debug Steps**:
```bash
# Test MySQL connection from terminal
mysql -u root -p
# If you see mysql> prompt, MySQL is running
SHOW DATABASES;
# You should see 'anako_technicians' in the list
```

---

### 2. "Schema creation failed"

**Error**: When opening `create_schema.php`

**Solutions**:
1. Ensure database exists:
   - Open phpMyAdmin
   - Create database: `anako_technicians`
   
2. Check file permissions:
   ```bash
   chmod 755 /opt/lampp/htdocs/anako-tech/
   chmod 755 /opt/lampp/htdocs/anako-tech/uploads/
   ```
   
3. Try creating schema again
   - Open: `http://localhost/anako-tech/includes/create_schema.php`

4. Check for errors in browser console (F12)

---

### 3. "File upload failed"

**Error**: Document or photo won't upload

**Check**:
1. **Folder permissions**:
   ```bash
   chmod 755 uploads/profile_photos/
   chmod 755 uploads/documents/
   ```

2. **File size**:
   - Maximum allowed: 5MB
   - Check your file size

3. **File type**:
   - Photos: JPG, PNG only
   - Documents: PDF, JPG, PNG only
   - Convert if needed

4. **Disk space**:
   - Check available disk space on server

---

### 4. "Page not found (404)"

**Error**: `The page you are looking for could not be found.`

**Solutions**:
1. **Check URL**:
   - Use: `http://localhost/anako-tech/`
   - NOT: `http://127.0.0.1` (usually works but use localhost)

2. **Verify file exists**:
   ```bash
   ls -la /opt/lampp/htdocs/anako-tech/
   # Should show all PHP files
   ```

3. **Restart Apache**:
   - XAMPP Control Panel
   - Stop Apache
   - Start Apache again

4. **Check `.htaccess`** if it exists and has rewrite rules

---

### 5. "Login not working"

**Problem**: Can't login with correct credentials

**Check**:
1. **Session enabled in PHP**:
   - Login page might have session issues

2. **Correct username/password**:
   - Admin: username=`admin`, password=`admin123`
   - Technician: Use registered email and password

3. **Check browser cookies**:
   - Allow cookies in browser settings

4. **Clear browser cache**:
   - Ctrl+Shift+Delete (Windows)
   - Cmd+Shift+Delete (Mac)

5. **Try different browser**:
   - Sometimes browser-specific issue

---

### 6. "Blank page or white screen"

**Error**: Page loads but shows nothing

**Check**:
1. **Enable error reporting** (in development):
   
   Add to top of PHP files:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. **Check PHP version**:
   - Need PHP 7.4+
   - XAMPP: Usually has latest PHP

3. **Check PHP error logs**:
   - XAMPP logs folder: `xampp/apache/logs/`

---

### 7. "Database table doesn't exist"

**Error**: `Table 'anako_technicians.xyz' doesn't exist`

**Solution**:
1. Open: `http://localhost/anako-tech/includes/create_schema.php`
2. Re-run schema creation
3. Verify all checkmarks (✓) show success

---

## FAQ - Frequently Asked Questions

### Q1: Where do I start after installation?

A: Follow these steps:
1. ✅ Run database schema: `includes/create_schema.php`
2. ✅ Login as admin with: username=`admin`, password=`admin123`
3. ✅ Create a test technician account
4. ✅ Test uploading documents
5. ✅ Test approving/rejecting applications

---

### Q2: Can I change the admin password?

A: Yes, but it requires database access:

```sql
UPDATE admins 
SET password = SHA2('newpassword', 256) 
WHERE username = 'admin';
```

Better method - add change password feature (future enhancement)

---

### Q3: How do I backup my data?

A: Using phpMyAdmin:
1. Open: `http://localhost/phpmyadmin`
2. Select `anako_technicians` database
3. Click "Export"
4. Click "Go"
5. Save the SQL file

---

### Q4: Can I add more admins?

A: Yes, using MySQL:

```sql
INSERT INTO admins (username, email, password) 
VALUES ('username', 'email@example.com', SHA2('password', 256));
```

Or create an admin panel feature.

---

### Q5: How do I delete a technician account?

A: As admin:
1. Go to admin dashboard
2. Click "Technicians"
3. Find technician
4. Click "View"
5. Click "Delete Technician"

---

### Q6: How do I export technician list?

A: Using phpMyAdmin:
1. Go to `anako_technicians` database
2. Select `technicians` table
3. Click "Export"
4. Choose format (CSV, Excel, etc.)
5. Download

---

### Q7: Can I customize the categories?

A: Yes, edit `register.php`:

```php
$categories = [
    'Solar Technician',
    'New Category Here',
    // Add more...
];
```

---

### Q8: What file types can be uploaded?

A:**Allowed types**:
- Photos: JPG, PNG
- Documents: PDF, JPG, PNG
- Max size: 5MB per file

To change, edit `includes/functions.php`

---

### Q9: How do I reset a technician's password?

A: Using MySQL:

```sql
UPDATE technicians 
SET password = SHA2('newpassword', 256)
WHERE email = 'technician@email.com';
```

(Better to add password reset feature)

---

### Q10: Can I add a messaging feature?

A: Yes! You would need to:
1. Create `messages` table
2. Create messaging pages
3. Add notification system
4. See CONFIGURATION.md for database changes

---

## Performance Issues

### Slow page loading

**Check**:
1. Database connections are closed properly
2. Queries are not too complex
3. Add database indexes (see CONFIGURATION.md)
4. Check server resources (CPU, RAM, disk)

### Slow search results

**Solutions**:
1. Add database indexes for frequently searched columns
2. Limit search results per page
3. Use pagination
4. Cache frequently accessed data

---

## Security Checks

### Before going to production:

- [ ] Change default admin password
- [ ] Use HTTPS/SSL
- [ ] Enable output buffering
- [ ] Disable directory listing
- [ ] Set proper file permissions
- [ ] Move sensitive files outside web root
- [ ] Regular database backups
- [ ] Monitor error logs
- [ ] Keep PHP and MySQL updated

---

## PHP Version Issues

### Functions not available

If you see errors like "undefined function", check PHP version:

1. Create test file: `phpinfo.php`:
   ```php
   <?php phpinfo(); ?>
   ```

2. Open: `http://localhost/anako-tech/phpinfo.php`

3. Check PHP Version (need 7.4+)

---

## Still Having Issues?

### Debug Steps:

1. **Check error logs**:
   - Browser Console (Press F12)
   - XAMPP Apache logs

2. **Enable debugging** (add to files):
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Test database**:
   - Open phpMyAdmin
   - Check tables exist
   - Check data is there

4. **Test permissions**:
   ```bash
   ls -la /opt/lampp/htdocs/anako-tech/
   # Should show rwxr-xr-x
   ```

5. **Restart everything**:
   - Stop XAMPP
   - Wait 10 seconds
   - Start XAMPP
   - Try again

---

## Getting More Help

If issues persist:

1. Check all documentation files:
   - README.md
   - QUICKSTART.md
   - CONFIGURATION.md

2. Check PHP error logs in XAMPP

3. Use browser developer tools
   - F12 → Console tab for JavaScript errors
   - Network tab to check requests

4. Search Google for specific error messages

---

**Last Updated**: March 2026
**Version**: 1.0
