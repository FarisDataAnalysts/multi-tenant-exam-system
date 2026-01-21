# 📦 Installation Guide

Complete step-by-step installation guide for Multi-Tenant Exam System.

## 🔧 Prerequisites

Before installation, ensure you have:

- **PHP 7.4+** (Recommended: PHP 8.0+)
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Apache** or **Nginx** web server
- **XAMPP/WAMP** (for local development)

---

## 🚀 Installation Steps

### Step 1: Download/Clone Repository

```bash
# Clone from GitHub
git clone https://github.com/FarisDataAnalysts/multi-tenant-exam-system.git

# Or download ZIP and extract
```

### Step 2: Move Files to Web Server

#### For XAMPP (Windows):
```bash
# Copy files to:
C:\xampp\htdocs\exam-system\
```

#### For WAMP (Windows):
```bash
# Copy files to:
C:\wamp64\www\exam-system\
```

#### For Linux/Mac:
```bash
# Copy files to:
sudo cp -r multi-tenant-exam-system /var/www/html/exam-system/

# Set permissions:
sudo chmod -R 755 /var/www/html/exam-system/
sudo chown -R www-data:www-data /var/www/html/exam-system/
```

### Step 3: Create Database

#### Option A: Using phpMyAdmin
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `exam_system`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Import" tab
6. Choose file: `database.sql`
7. Click "Go"

#### Option B: Using MySQL Command Line
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE exam_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit MySQL
exit;

# Import database
mysql -u root -p exam_system < database.sql
```

### Step 4: Configure Database Connection

Edit `config.php` file:

```php
// Update these lines with your database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
define('DB_NAME', 'exam_system');
```

### Step 5: Test Installation

1. Start your web server (Apache/Nginx)
2. Open browser and navigate to:
   - Student Portal: `http://localhost/exam-system/student/`
   - Teacher Portal: `http://localhost/exam-system/teacher/`

---

## 🔐 Default Login Credentials

### Organization A - Teacher 1
- **URL:** `http://localhost/exam-system/teacher/`
- **Username:** `teacher1`
- **Password:** `teacher123`

### Organization A - Teacher 2
- **URL:** `http://localhost/exam-system/teacher/`
- **Username:** `teacher1b`
- **Password:** `teacher123`

### Organization B - Teacher
- **URL:** `http://localhost/exam-system/teacher/`
- **Username:** `teacher2`
- **Password:** `teacher123`

### Student Access
- **URL:** `http://localhost/exam-system/student/?org=ORG_A`
- No default credentials (students enter ID and name)

---

## ⚙️ Configuration Options

### Change Exam Duration

Edit `config.php`:
```php
define('EXAM_DURATION', 1800); // 30 minutes (in seconds)
// Change to 3600 for 60 minutes
```

### Change Timezone

Edit `config.php`:
```php
define('TIMEZONE', 'Asia/Karachi');
// Change to your timezone
// Examples: 'America/New_York', 'Europe/London', 'Asia/Dubai'
```

### Change Site URL

Edit `config.php`:
```php
define('SITE_URL', 'http://localhost:8000');
// Change to your domain
// Example: 'https://yourdomain.com'
```

---

## 🏢 Adding New Organization

### Method 1: Using SQL

```sql
-- Add new organization
INSERT INTO organizations (org_name, org_code) 
VALUES ('Your Organization Name', 'ORG_C');

-- Add teacher for this organization
INSERT INTO teachers (org_id, username, password, full_name, email) 
VALUES (3, 'teacher3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teacher Name', 'email@example.com');

-- Add courses
INSERT INTO courses (org_id, teacher_id, course_name, course_code) 
VALUES (3, 3, 'Course Name', 'COURSE101');

-- Add timings
INSERT INTO timings (org_id, timing_slot) 
VALUES (3, '9:00 AM - 11:00 AM');
```

### Method 2: Generate Password Hash

Create a PHP file `generate_password.php`:
```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

Run it and copy the hash to use in SQL INSERT.

---

## 🔒 Security Recommendations

### 1. Change Default Passwords
```sql
-- Update teacher password
UPDATE teachers 
SET password = '$2y$10$YOUR_NEW_HASH_HERE' 
WHERE username = 'teacher1';
```

### 2. Secure config.php
```bash
# Linux/Mac - Restrict access
chmod 600 config.php
```

### 3. Enable HTTPS
- Get SSL certificate (Let's Encrypt)
- Update `SITE_URL` in config.php to use `https://`

### 4. Database Security
- Use strong MySQL root password
- Create separate MySQL user for application
- Grant only necessary privileges

```sql
CREATE USER 'exam_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON exam_system.* TO 'exam_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"
**Solution:**
- Check MySQL service is running
- Verify credentials in `config.php`
- Ensure database `exam_system` exists

### Issue: "No questions available"
**Solution:**
- Check unlock/lock dates in questions table
- Verify questions exist for selected course & month
- Ensure organization isolation is correct

### Issue: "Cannot re-attempt exam"
**Solution:**
- This is by design (feature, not bug)
- To allow re-attempt, delete from `exams` table:
```sql
DELETE FROM exams WHERE student_id = 'STUDENT_ID' AND course_id = X AND month = Y;
```

### Issue: "Page not found (404)"
**Solution:**
- Check file paths are correct
- Ensure `.htaccess` is present (if using Apache)
- Verify web server configuration

### Issue: "Permission denied"
**Solution (Linux/Mac):**
```bash
sudo chmod -R 755 /var/www/html/exam-system/
sudo chown -R www-data:www-data /var/www/html/exam-system/
```

---

## 📊 Database Backup

### Backup Database
```bash
mysqldump -u root -p exam_system > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root -p exam_system < backup_20240121.sql
```

---

## 🔄 Updating System

### Pull Latest Changes
```bash
cd exam-system
git pull origin main
```

### Update Database (if schema changed)
```bash
mysql -u root -p exam_system < database_update.sql
```

---

## 📞 Support

For installation issues or questions:

- **Email:** thepersonalityschool43@gmail.com
- **GitHub Issues:** [Create Issue](https://github.com/FarisDataAnalysts/multi-tenant-exam-system/issues)

---

## ✅ Post-Installation Checklist

- [ ] Database created and imported successfully
- [ ] `config.php` configured with correct credentials
- [ ] Web server running (Apache/Nginx)
- [ ] Student portal accessible
- [ ] Teacher portal accessible
- [ ] Can login with default credentials
- [ ] Can add questions
- [ ] Can take exam
- [ ] Can view results
- [ ] Can export to Excel
- [ ] Default passwords changed
- [ ] Backup system configured

---

**🎉 Congratulations! Your Multi-Tenant Exam System is ready to use!**