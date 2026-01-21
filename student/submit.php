<?php
/**
 * Exam Submission Handler
 * Processes answers, calculates score, displays results
 */

require_once '../config.php';
requireLogin('student', 'index.php');

$db = getDB();

try {
    $db->beginTransaction();
    
    // Create exam record
    $stmt = $db->prepare("
        INSERT INTO exams (org_id, student_id, student_name, course_id, timing_id, month, status)
        VALUES (?, ?, ?, ?, ?, ?, 'completed')
    ");
    $stmt->execute([
        $_SESSION['org_id'],
        $_SESSION['student_id'],
        $_SESSION['student_name'],
        $_SESSION['course_id'],
        $_SESSION['timing_id'],
        $_SESSION['month']
    ]);
    
    $exam_id = $db->lastInsertId();
    
    // Process answers
    $correct_count = 0;
    $total_questions = 0;
    
    foreach ($_SESSION['exam_questions'] as $question_id) {
        $total_questions++;
        
        // Get correct answer
        $stmt = $db->prepare("SELECT correct_answer FROM questions WHERE question_id = ?");
        $stmt->execute([$question_id]);
        $question = $stmt->fetch();
        
        $selected_answer = $_POST['answer_' . $question_id] ?? null;
        $is_correct = ($selected_answer === $question['correct_answer']);
        
        if ($is_correct) {
            $correct_count++;
        }
        
        // Save answer
        $stmt = $db->prepare("
            INSERT INTO exam_answers (exam_id, question_id, selected_answer, is_correct)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$exam_id, $question_id, $selected_answer, $is_correct]);
    }
    
    // Calculate score
    $score = ($correct_count / $total_questions) * 100;
    
    // Update exam record
    $stmt = $db->prepare("
        UPDATE exams 
        SET total_questions = ?, correct_answers = ?, score = ?, end_time = NOW()
        WHERE exam_id = ?
    ");
    $stmt->execute([$total_questions, $correct_count, $score, $exam_id]);
    
    $db->commit();
    
    // Clear session
    $student_name = $_SESSION['student_name'];
    session_unset();
    session_destroy();
    
} catch (Exception $e) {
    $db->rollBack();
    die("Error submitting exam: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Submitted</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="result-box">
            <div class="success-icon">✓</div>
            <h1>Exam Submitted Successfully!</h1>
            
            <div class="result-details">
                <p><strong>Student:</strong> <?php echo htmlspecialchars($student_name); ?></p>
                <p><strong>Total Questions:</strong> <?php echo $total_questions; ?></p>
                <p><strong>Correct Answers:</strong> <?php echo $correct_count; ?></p>
                <p><strong>Score:</strong> <?php echo number_format($score, 2); ?>%</p>
            </div>
            
            <div class="grade">
                <?php if ($score >= 80): ?>
                    <h2 class="grade-a">Grade: A (Excellent!)</h2>
                <?php elseif ($score >= 60): ?>
                    <h2 class="grade-b">Grade: B (Good)</h2>
                <?php elseif ($score >= 40): ?>
                    <h2 class="grade-c">Grade: C (Average)</h2>
                <?php else: ?>
                    <h2 class="grade-f">Grade: F (Needs Improvement)</h2>
                <?php endif; ?>
            </div>
            
            <p class="info-text">Your results have been recorded. You can close this window now.</p>
            
            <a href="index.php" class="btn btn-primary">Back to Login</a>
        </div>
    </div>
</body>
</html>