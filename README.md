# 🎓 Multi-Tenant Online Examination System

Complete examination management system with multi-tenant support, anti-cheating features, and comprehensive teacher dashboard.

## ✨ Features

### Student Portal
- ✅ Secure login with ID, Name, Course, Timing, Month selection
- ✅ 30-minute timer-based MCQ exams
- ✅ Must answer all questions before submission
- ✅ **Anti-Cheating Measures:**
  - Fullscreen mode enforced
  - Tab switching detection & warnings
  - Copy-paste disabled
  - Right-click disabled
  - Browser back button disabled
- ✅ **No Re-attempt:** Once submitted, cannot retake same exam
- ✅ Instant result display with grade

### Teacher Dashboard
- ✅ Secure login system
- ✅ **Question Management:**
  - Add new questions
  - Edit existing questions
  - Delete questions
  - Set unlock/lock dates for tests
- ✅ **Result Management:**
  - View all student results
  - Filter by month
  - Export to Excel (CSV)
- ✅ Month-wise test organization (Month 1-4)

### Multi-Tenant Architecture
- ✅ **Complete Data Isolation:**
  - Organization A data completely separate from Organization B
  - Teacher 1 can only see their own questions
  - Teacher 2 cannot access Teacher 1's data
- ✅ Scalable for multiple organizations
- ✅ Each organization has independent courses, timings, and questions

### Design & UX
- ✅ **Dark Theme** (Black background)
- ✅ Responsive design
- ✅ Translation support ready (Google Translate API integration point)
- ✅ Clean and intuitive interface

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (recommended for local development)

### Step 1: Clone Repository
```bash
git clone https://github.com/FarisDataAnalysts/multi-tenant-exam-system.git
cd multi-tenant-exam-system
```

### Step 2: Database Setup
```bash
# Create database and import schema
mysql -u root -p < database.sql

# Or using phpMyAdmin:
# 1. Create database: exam_system
# 2. Import database.sql file
```

### Step 3: Configuration
Edit `config.php` and update database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'exam_system');
```

### Step 4: Deploy
```bash
# For XAMPP
# Copy files to: C:\xampp\htdocs\exam-system\

# For Linux/Mac
# Copy files to: /var/www/html/exam-system/

# Set permissions (Linux/Mac)
chmod -R 755 /var/www/html/exam-system/
```

### Step 5: Access System
- **Student Portal:** http://localhost/exam-system/student/
- **Teacher Portal:** http://localhost/exam-system/teacher/

## 🔐 Default Credentials

### Organization A - Teacher
- **Username:** teacher1
- **Password:** teacher123

### Organization B - Teacher
- **Username:** teacher2
- **Password:** teacher123

**⚠️ Important:** Change default passwords after first login!

## 📁 Directory Structure

```
exam-system/
├── config.php              # Database configuration
├── database.sql            # Database schema & sample data
├── student/
│   ├── index.php          # Student login page
│   ├── exam.php           # Exam interface with timer
│   └── submit.php         # Exam submission handler
├── teacher/
│   ├── index.php          # Teacher login page
│   ├── dashboard.php      # Teacher dashboard
│   ├── questions.php      # Question management (Add/Edit/Delete)
│   └── results.php        # View results & export to Excel
└── assets/
    └── css/
        └── style.css      # Dark theme styles
```

## 📊 Database Schema

### Tables
1. **organizations** - Organization details
2. **teachers** - Teacher accounts
3. **courses** - Course list per organization
4. **timings** - Timing slots per organization
5. **questions** - MCQ questions with unlock/lock dates
6. **exams** - Exam attempts (prevents re-attempts)
7. **exam_answers** - Student answers

### Key Relationships
- Organizations → Teachers (1:N)
- Teachers → Courses (1:N)
- Teachers → Questions (1:N)
- Questions → Exams → Answers

## 🎯 Usage Guide

### For Teachers

#### Adding Questions
1. Login to teacher portal
2. Go to "Manage Questions"
3. Click "Add New Question"
4. Fill in:
   - Course
   - Month (1-4)
   - Question text
   - 4 options (A, B, C, D)
   - Correct answer
   - Unlock date (optional)
   - Lock date (optional)
5. Click "Save Question"

#### Setting Test Dates
- **Unlock Date:** Students can start taking test from this date
- **Lock Date:** Test becomes unavailable after this date
- Example: Set unlock=1st and lock=5th for tests available only from 1st to 5th

#### Viewing Results
1. Go to "View Results"
2. Filter by month (optional)
3. Click "Export to Excel" to download CSV

### For Students

#### Taking Exam
1. Enter Student ID
2. Enter Full Name
3. Select Course
4. Select Timing
5. Select Month
6. Click "Start Test"
7. Answer all questions (mandatory)
8. Submit before timer expires
9. View instant results

**⚠️ Important:** You cannot re-attempt the same exam!

## 🔒 Security Features

- ✅ Password hashing with bcrypt
- ✅ SQL injection protection (PDO prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Session management
- ✅ CSRF protection ready
- ✅ Multi-tenant data isolation

## 🌐 Multi-Language Support

Translation feature is ready for integration with Google Translate API:
- Questions can be translated to Urdu, English, or other languages
- Translation button available on exam interface
- Easy API integration point in `exam.php`

## 📈 Scalability

### Adding New Organization
```sql
INSERT INTO organizations (org_name, org_code) VALUES ('New Org', 'ORG_C');
```

### Adding New Teacher
```sql
INSERT INTO teachers (org_id, username, password, full_name, email) 
VALUES (1, 'teacher3', '$2y$10$...', 'Teacher Three', 'teacher3@org.com');
```

### Adding New Course
```sql
INSERT INTO courses (org_id, teacher_id, course_name, course_code) 
VALUES (1, 1, 'Chemistry', 'CHEM101');
```

## 🐛 Troubleshooting

### Database Connection Error
- Check `config.php` credentials
- Ensure MySQL service is running
- Verify database exists

### Questions Not Showing
- Check unlock/lock dates
- Verify questions exist for selected course & month
- Check organization isolation

### Cannot Submit Exam
- Ensure all questions are answered
- Check if timer has expired
- Verify no previous attempt exists

## 🤝 Contributing

This is a complete production-ready system. For customizations:
1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📝 License

MIT License - Free to use for educational institutions

## 📧 Support

For issues or customization requests:
- **Email:** thepersonalityschool43@gmail.com
- **GitHub Issues:** [Create Issue](https://github.com/FarisDataAnalysts/multi-tenant-exam-system/issues)

## 🎓 Credits

Developed for **Personality School**
Built with ❤️ using PHP & MySQL

---

**⭐ Star this repository if you find it useful!**