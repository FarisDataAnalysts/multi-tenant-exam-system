-- =====================================================
-- Multi-Tenant Online Examination System
-- Database Schema
-- =====================================================

CREATE DATABASE IF NOT EXISTS exam_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE exam_system;

-- =====================================================
-- Organizations Table
-- =====================================================
CREATE TABLE organizations (
    org_id INT PRIMARY KEY AUTO_INCREMENT,
    org_name VARCHAR(255) NOT NULL,
    org_code VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    INDEX idx_org_code (org_code),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- Teachers Table
-- =====================================================
CREATE TABLE teachers (
    teacher_id INT PRIMARY KEY AUTO_INCREMENT,
    org_id INT NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE,
    INDEX idx_org (org_id),
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- Courses Table
-- =====================================================
CREATE TABLE courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    org_id INT NOT NULL,
    teacher_id INT NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    course_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    INDEX idx_org_teacher (org_id, teacher_id),
    INDEX idx_course_code (course_code)
) ENGINE=InnoDB;

-- =====================================================
-- Timings Table
-- =====================================================
CREATE TABLE timings (
    timing_id INT PRIMARY KEY AUTO_INCREMENT,
    org_id INT NOT NULL,
    timing_slot VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE,
    INDEX idx_org (org_id)
) ENGINE=InnoDB;

-- =====================================================
-- Questions Table
-- =====================================================
CREATE TABLE questions (
    question_id INT PRIMARY KEY AUTO_INCREMENT,
    org_id INT NOT NULL,
    teacher_id INT NOT NULL,
    course_id INT NOT NULL,
    month INT NOT NULL COMMENT '1=Month1, 2=Month2, 3=Month3, 4=Month4',
    question_text TEXT NOT NULL,
    option_a VARCHAR(500) NOT NULL,
    option_b VARCHAR(500) NOT NULL,
    option_c VARCHAR(500) NOT NULL,
    option_d VARCHAR(500) NOT NULL,
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    unlock_date DATE COMMENT 'Test available from this date',
    lock_date DATE COMMENT 'Test locked after this date',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    INDEX idx_org_teacher (org_id, teacher_id),
    INDEX idx_course_month (course_id, month),
    INDEX idx_dates (unlock_date, lock_date)
) ENGINE=InnoDB;

-- =====================================================
-- Exams Table (Prevents Re-attempts)
-- =====================================================
CREATE TABLE exams (
    exam_id INT PRIMARY KEY AUTO_INCREMENT,
    org_id INT NOT NULL,
    student_id VARCHAR(100) NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    course_id INT NOT NULL,
    timing_id INT NOT NULL,
    month INT NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    status ENUM('in_progress', 'completed', 'abandoned') DEFAULT 'in_progress',
    total_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    score DECIMAL(5,2) DEFAULT 0,
    FOREIGN KEY (org_id) REFERENCES organizations(org_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (timing_id) REFERENCES timings(timing_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attempt (org_id, student_id, course_id, month),
    INDEX idx_student (student_id),
    INDEX idx_org_course (org_id, course_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- Exam Answers Table
-- =====================================================
CREATE TABLE exam_answers (
    answer_id INT PRIMARY KEY AUTO_INCREMENT,
    exam_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_answer ENUM('A', 'B', 'C', 'D'),
    is_correct BOOLEAN DEFAULT FALSE,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
    INDEX idx_exam (exam_id),
    INDEX idx_question (question_id)
) ENGINE=InnoDB;

-- =====================================================
-- Sample Data
-- =====================================================

-- Insert Sample Organizations
INSERT INTO organizations (org_name, org_code) VALUES 
('Organization A', 'ORG_A'),
('Organization B', 'ORG_B');

-- Insert Sample Teachers
-- Password for all: teacher123
-- Generated using: password_hash('teacher123', PASSWORD_DEFAULT)
INSERT INTO teachers (org_id, username, password, full_name, email) VALUES 
(1, 'teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teacher One', 'teacher1@orga.com'),
(1, 'teacher1b', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teacher One B', 'teacher1b@orga.com'),
(2, 'teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teacher Two', 'teacher2@orgb.com');

-- Insert Sample Courses
INSERT INTO courses (org_id, teacher_id, course_name, course_code) VALUES 
(1, 1, 'Mathematics', 'MATH101'),
(1, 1, 'Physics', 'PHY101'),
(1, 2, 'Chemistry', 'CHEM101'),
(2, 3, 'Biology', 'BIO101'),
(2, 3, 'Computer Science', 'CS101');

-- Insert Sample Timings
INSERT INTO timings (org_id, timing_slot) VALUES 
(1, '9:00 AM - 11:00 AM'),
(1, '11:00 AM - 1:00 PM'),
(1, '2:00 PM - 4:00 PM'),
(2, '10:00 AM - 12:00 PM'),
(2, '3:00 PM - 5:00 PM');

-- Insert Sample Questions for Organization A - Teacher 1 - Mathematics
INSERT INTO questions (org_id, teacher_id, course_id, month, question_text, option_a, option_b, option_c, option_d, correct_answer, unlock_date, lock_date) VALUES 
(1, 1, 1, 1, 'What is 2 + 2?', '3', '4', '5', '6', 'B', '2024-01-01', '2024-01-05'),
(1, 1, 1, 1, 'What is the square root of 16?', '2', '3', '4', '5', 'C', '2024-01-01', '2024-01-05'),
(1, 1, 1, 1, 'What is 5 × 6?', '25', '30', '35', '40', 'B', '2024-01-01', '2024-01-05'),
(1, 1, 1, 2, 'What is 10 × 10?', '10', '50', '100', '1000', 'C', '2024-02-01', '2024-02-05'),
(1, 1, 1, 2, 'What is 15 ÷ 3?', '3', '4', '5', '6', 'C', '2024-02-01', '2024-02-05');

-- Insert Sample Questions for Organization A - Teacher 1 - Physics
INSERT INTO questions (org_id, teacher_id, course_id, month, question_text, option_a, option_b, option_c, option_d, correct_answer, unlock_date, lock_date) VALUES 
(1, 1, 2, 1, 'What is the speed of light?', '3 × 10^8 m/s', '3 × 10^6 m/s', '3 × 10^10 m/s', '3 × 10^4 m/s', 'A', '2024-01-01', '2024-01-05'),
(1, 1, 2, 1, 'What is the unit of force?', 'Joule', 'Newton', 'Watt', 'Pascal', 'B', '2024-01-01', '2024-01-05');

-- Insert Sample Questions for Organization B - Teacher 2 - Biology
INSERT INTO questions (org_id, teacher_id, course_id, month, question_text, option_a, option_b, option_c, option_d, correct_answer, unlock_date, lock_date) VALUES 
(2, 3, 4, 1, 'What is the powerhouse of the cell?', 'Nucleus', 'Mitochondria', 'Ribosome', 'Golgi Body', 'B', '2024-01-01', '2024-01-05'),
(2, 3, 4, 1, 'What is DNA?', 'Protein', 'Carbohydrate', 'Nucleic Acid', 'Lipid', 'C', '2024-01-01', '2024-01-05');

COMMIT;

-- =====================================================
-- Verification Queries
-- =====================================================

-- Check Organizations
-- SELECT * FROM organizations;

-- Check Teachers
-- SELECT t.*, o.org_name FROM teachers t JOIN organizations o ON t.org_id = o.org_id;

-- Check Courses
-- SELECT c.*, o.org_name, t.full_name FROM courses c 
-- JOIN organizations o ON c.org_id = o.org_id 
-- JOIN teachers t ON c.teacher_id = t.teacher_id;

-- Check Questions
-- SELECT q.question_id, o.org_name, t.full_name, c.course_name, q.month, q.question_text 
-- FROM questions q
-- JOIN organizations o ON q.org_id = o.org_id
-- JOIN teachers t ON q.teacher_id = t.teacher_id
-- JOIN courses c ON q.course_id = c.course_id;

-- =====================================================
-- End of Database Schema
-- =====================================================