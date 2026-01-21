<?php
/**
 * Student Login Page
 * Students enter ID, Name, Course, Timing, and Month to start exam
 */

require_once '../config.php';

// If already logged in, redirect to exam
if (isLoggedIn('student')) {
    redirect('exam.php');
}

$error = '';
$db = getDB();

// Get organization from URL or default to first org
$org_code = $_GET['org'] ?? 'ORG_A';

// Fetch organization
$stmt = $db->prepare("SELECT org_id, org_name FROM organizations WHERE org_code = ? AND status = 'active'");
$stmt->execute([$org_code]);
$organization = $stmt->fetch();

if (!$organization) {
    die("Invalid organization code");
}

$org_id = $organization['org_id'];

// Fetch courses for this organization
$stmt = $db->prepare("SELECT DISTINCT course_id, course_name FROM courses WHERE org_id = ? ORDER BY course_name");
$stmt->execute([$org_id]);
$courses = $stmt->fetchAll();

// Fetch timings for this organization
$stmt = $db->prepare("SELECT timing_id, timing_slot FROM timings WHERE org_id = ? ORDER BY timing_id");
$stmt->execute([$org_id]);
$timings = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = sanitize($_POST['student_id'] ?? '');
    $student_name = sanitize($_POST['student_name'] ?? '');
    $course_id = (int)($_POST['course_id'] ?? 0);
    $timing_id = (int)($_POST['timing_id'] ?? 0);
    $month = (int)($_POST['month'] ?? 0);
    
    // Validation
    if (empty($student_id) || empty($student_name) || !$course_id || !$timing_id || !$month) {
        $error = 'All fields are required';
    } else {
        // Check if student already attempted this exam
        $stmt = $db->prepare("
            SELECT exam_id FROM exams 
            WHERE org_id = ? AND student_id = ? AND course_id = ? AND month = ?
        ");
        $stmt->execute([$org_id, $student_id, $course_id, $month]);
        
        if ($stmt->fetch()) {
            $error = 'You have already attempted this exam. Re-attempt is not allowed.';
        } else {
            // Check if questions are available and unlocked
            $stmt = $db->prepare("
                SELECT COUNT(*) as count FROM questions 
                WHERE org_id = ? AND course_id = ? AND month = ?
                AND (unlock_date IS NULL OR unlock_date <= CURDATE())
                AND (lock_date IS NULL OR lock_date >= CURDATE())
            ");
            $stmt->execute([$org_id, $course_id, $month]);
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                $error = 'No questions available for this exam or exam is locked.';
            } else {
                // Create exam session
                $_SESSION['student_id'] = $student_id;
                $_SESSION['student_name'] = $student_name;
                $_SESSION['org_id'] = $org_id;
                $_SESSION['course_id'] = $course_id;
                $_SESSION['timing_id'] = $timing_id;
                $_SESSION['month'] = $month;
                $_SESSION['exam_start_time'] = time();
                
                redirect('exam.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - <?php echo htmlspecialchars($organization['org_name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <h1>📚 <?php echo htmlspecialchars($organization['org_name']); ?></h1>
                <p>Online Examination System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="student_id">Student ID Number</label>
                    <input type="text" id="student_id" name="student_id" required 
                           placeholder="Enter your ID number" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="student_name">Student Name</label>
                    <input type="text" id="student_name" name="student_name" required 
                           placeholder="Enter your full name" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="course_id">Select Course</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="timing_id">Select Timing</label>
                    <select id="timing_id" name="timing_id" required>
                        <option value="">-- Select Timing --</option>
                        <?php foreach ($timings as $timing): ?>
                            <option value="<?php echo $timing['timing_id']; ?>">
                                <?php echo htmlspecialchars($timing['timing_slot']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="month">Select Month</label>
                    <select id="month" name="month" required>
                        <option value="">-- Select Month --</option>
                        <option value="1">Month 1</option>
                        <option value="2">Month 2</option>
                        <option value="3">Month 3</option>
                        <option value="4">Month 4</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Start Test</button>
            </form>
            
            <div class="instructions">
                <h3>⚠️ Important Instructions:</h3>
                <ul>
                    <li>Exam duration: 30 minutes</li>
                    <li>You must answer all questions</li>
                    <li>No copy-paste allowed</li>
                    <li>Do not switch tabs or exit fullscreen</li>
                    <li>Once submitted, you cannot re-attempt</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>