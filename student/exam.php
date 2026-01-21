<?php
/**
 * Exam Interface
 * 30-minute timer, anti-cheating measures, fullscreen mode
 */

require_once '../config.php';
requireLogin('student', 'index.php');

$db = getDB();

// Check if exam time expired
$elapsed_time = time() - $_SESSION['exam_start_time'];
if ($elapsed_time > EXAM_DURATION) {
    // Auto-submit
    redirect('submit.php?timeout=1');
}

// Fetch questions
$stmt = $db->prepare("
    SELECT question_id, question_text, option_a, option_b, option_c, option_d
    FROM questions
    WHERE org_id = ? AND course_id = ? AND month = ?
    AND (unlock_date IS NULL OR unlock_date <= CURDATE())
    AND (lock_date IS NULL OR lock_date >= CURDATE())
    ORDER BY RAND()
");
$stmt->execute([
    $_SESSION['org_id'],
    $_SESSION['course_id'],
    $_SESSION['month']
]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    die("No questions available");
}

// Store questions in session
if (!isset($_SESSION['exam_questions'])) {
    $_SESSION['exam_questions'] = array_column($questions, 'question_id');
    $_SESSION['exam_answers'] = [];
}

$remaining_time = EXAM_DURATION - $elapsed_time;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam in Progress</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="exam-mode">
    <div class="exam-header">
        <div class="exam-info">
            <span>Student: <?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
            <span>Questions: <?php echo count($questions); ?></span>
        </div>
        <div class="timer" id="timer">
            <span id="time-remaining">30:00</span>
        </div>
    </div>
    
    <div class="exam-container">
        <form id="exam-form" method="POST" action="submit.php">
            <?php foreach ($questions as $index => $q): ?>
                <div class="question-card" data-question="<?php echo $index + 1; ?>">
                    <div class="question-header">
                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                        <button type="button" class="translate-btn" onclick="translateQuestion(<?php echo $index; ?>)">🌐 Translate</button>
                    </div>
                    
                    <div class="question-text" id="question-<?php echo $index; ?>">
                        <?php echo htmlspecialchars($q['question_text']); ?>
                    </div>
                    
                    <div class="options">
                        <label class="option">
                            <input type="radio" name="answer_<?php echo $q['question_id']; ?>" 
                                   value="A" required>
                            <span class="option-text">A. <?php echo htmlspecialchars($q['option_a']); ?></span>
                        </label>
                        
                        <label class="option">
                            <input type="radio" name="answer_<?php echo $q['question_id']; ?>" 
                                   value="B" required>
                            <span class="option-text">B. <?php echo htmlspecialchars($q['option_b']); ?></span>
                        </label>
                        
                        <label class="option">
                            <input type="radio" name="answer_<?php echo $q['question_id']; ?>" 
                                   value="C" required>
                            <span class="option-text">C. <?php echo htmlspecialchars($q['option_c']); ?></span>
                        </label>
                        
                        <label class="option">
                            <input type="radio" name="answer_<?php echo $q['question_id']; ?>" 
                                   value="D" required>
                            <span class="option-text">D. <?php echo htmlspecialchars($q['option_d']); ?></span>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="submit-section">
                <button type="submit" class="btn btn-success btn-lg" id="submit-btn" disabled>
                    Submit Exam
                </button>
                <p class="warning-text">⚠️ Please answer all questions before submitting</p>
            </div>
        </form>
    </div>
    
    <script>
        const EXAM_DURATION = <?php echo $remaining_time; ?>;
        let timeRemaining = EXAM_DURATION;
        let warningCount = 0;
        
        // Timer
        function updateTimer() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            document.getElementById('time-remaining').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemaining <= 0) {
                document.getElementById('exam-form').submit();
            }
            
            timeRemaining--;
        }
        
        setInterval(updateTimer, 1000);
        
        // Anti-cheat measures
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('cut', e => e.preventDefault());
        document.addEventListener('paste', e => e.preventDefault());
        
        // Fullscreen enforcement
        function enterFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        }
        
        enterFullscreen();
        
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                warningCount++;
                alert(`Warning ${warningCount}/3: Please stay in fullscreen mode!`);
                if (warningCount >= 3) {
                    alert('Too many violations. Exam will be auto-submitted.');
                    document.getElementById('exam-form').submit();
                }
                enterFullscreen();
            }
        });
        
        // Tab switching detection
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                warningCount++;
                alert(`Warning ${warningCount}/3: Do not switch tabs!`);
                if (warningCount >= 3) {
                    alert('Too many violations. Exam will be auto-submitted.');
                    document.getElementById('exam-form').submit();
                }
            }
        });
        
        // Prevent back button
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.go(1);
        };
        
        // Check if all questions answered
        const form = document.getElementById('exam-form');
        const submitBtn = document.getElementById('submit-btn');
        
        form.addEventListener('change', function() {
            const totalQuestions = <?php echo count($questions); ?>;
            let answeredCount = 0;
            
            <?php foreach ($questions as $q): ?>
                if (document.querySelector('input[name="answer_<?php echo $q['question_id']; ?>"]:checked')) {
                    answeredCount++;
                }
            <?php endforeach; ?>
            
            if (answeredCount === totalQuestions) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Exam ✓';
            }
        });
        
        // Translation function (placeholder for Google Translate API)
        function translateQuestion(index) {
            alert('Translation feature - Integrate with Google Translate API for Urdu/English translation');
        }
        
        // Confirm before leaving
        window.addEventListener('beforeunload', function(e) {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>
</html>