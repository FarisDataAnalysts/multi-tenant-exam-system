<?php
/**
 * Multi-Tenant Exam System - Configuration File
 * Database connection and helper functions
 */

// =====================================================
// Database Configuration
// =====================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'exam_system');

// =====================================================
// System Configuration
// =====================================================
define('SITE_URL', 'http://localhost:8000');
define('EXAM_DURATION', 1800); // 30 minutes in seconds
define('TIMEZONE', 'Asia/Karachi');

// Set timezone
date_default_timezone_set(TIMEZONE);

// =====================================================
// Database Connection
// =====================================================
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// =====================================================
// Session Management
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// Helper Functions
// =====================================================

/**
 * Sanitize user input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn($type = 'student') {
    return isset($_SESSION[$type . '_id']);
}

/**
 * Require login or redirect
 */
function requireLogin($type = 'student', $redirect_url = null) {
    if (!isLoggedIn($type)) {
        if ($redirect_url) {
            redirect($redirect_url);
        } else {
            redirect(SITE_URL . '/' . $type . '/');
        }
    }
}

/**
 * Logout user
 */
function logout($type = 'student') {
    session_unset();
    session_destroy();
    redirect(SITE_URL . '/' . $type . '/');
}

/**
 * JSON Response
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd M Y, h:i A') {
    return date($format, strtotime($date));
}

/**
 * Calculate grade from score
 */
function getGrade($score) {
    if ($score >= 80) return 'A';
    if ($score >= 60) return 'B';
    if ($score >= 40) return 'C';
    return 'F';
}

/**
 * Get grade color class
 */
function getGradeClass($score) {
    if ($score >= 80) return 'grade-a';
    if ($score >= 60) return 'grade-b';
    if ($score >= 40) return 'grade-c';
    return 'grade-f';
}
?>