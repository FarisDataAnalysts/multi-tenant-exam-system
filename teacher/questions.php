<?php
/**
 * Question Management Page
 * Teachers can Add, Edit, Delete questions
 * Set unlock/lock dates for tests
 */

require_once '../config.php';
requireLogin('teacher', 'index.php');

$db = getDB();
$message = '';
$error = '';

// Get teacher's courses
$stmt = $db->prepare("SELECT course_id, course_name FROM courses WHERE org_id = ? AND teacher_id = ?");
$stmt->execute([$_SESSION['org_id'], $_SESSION['teacher_id']]);
$courses = $stmt->fetchAll();

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $course_id = (int)($_POST['course_id'] ?? 0);
        $month = (int)($_POST['month'] ?? 0);
        $question_text = sanitize($_POST['question_text'] ?? '');
        $option_a = sanitize($_POST['option_a'] ?? '');
        $option_b = sanitize($_POST['option_b'] ?? '');
        $option_c = sanitize($_POST['option_c'] ?? '');
        $option_d = sanitize($_POST['option_d'] ?? '');
        $correct_answer = $_POST['correct_answer'] ?? '';
        $unlock_date = $_POST['unlock_date'] ?? null;
        $lock_date = $_POST['lock_date'] ?? null;
        
        if ($action === 'add') {
            $stmt = $db->prepare("
                INSERT INTO questions (org_id, teacher_id, course_id, month, question_text, 
                                     option_a, option_b, option_c, option_d, correct_answer,
                                     unlock_date, lock_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['org_id'], $_SESSION['teacher_id'], $course_id, $month,
                $question_text, $option_a, $option_b, $option_c, $option_d,
                $correct_answer, $unlock_date, $lock_date
            ]);
            $message = 'Question added successfully!';
        } else {
            $question_id = (int)($_POST['question_id'] ?? 0);
            $stmt = $db->prepare("
                UPDATE questions SET course_id = ?, month = ?, question_text = ?,
                       option_a = ?, option_b = ?, option_c = ?, option_d = ?,
                       correct_answer = ?, unlock_date = ?, lock_date = ?
                WHERE question_id = ? AND org_id = ? AND teacher_id = ?
            ");
            $stmt->execute([
                $course_id, $month, $question_text, $option_a, $option_b,
                $option_c, $option_d, $correct_answer, $unlock_date, $lock_date,
                $question_id, $_SESSION['org_id'], $_SESSION['teacher_id']
            ]);
            $message = 'Question updated successfully!';
        }
    } elseif ($action === 'delete') {
        $question_id = (int)($_POST['question_id'] ?? 0);
        $stmt = $db->prepare("
            DELETE FROM questions 
            WHERE question_id = ? AND org_id = ? AND teacher_id = ?
        ");
        $stmt->execute([$question_id, $_SESSION['org_id'], $_SESSION['teacher_id']]);
        $message = 'Question deleted successfully!';
    }
}

// Fetch all questions
$stmt = $db->prepare("
    SELECT q.*, c.course_name 
    FROM questions q
    JOIN courses c ON q.course_id = c.course_id
    WHERE q.org_id = ? AND q.teacher_id = ?
    ORDER BY q.created_at DESC
");
$stmt->execute([$_SESSION['org_id'], $_SESSION['teacher_id']]);
$questions = $stmt->fetchAll();

// Handle logout
if (isset($_GET['logout'])) {
    logout('teacher');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>📚 Exam System</h2>
                <p><?php echo htmlspecialchars($_SESSION['org_name']); ?></p>
            </div>
            
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="questions.php" class="active">Manage Questions</a></li>
                <li><a href="results.php">View Results</a></li>
                <li><a href="?logout=1" onclick="return confirm('Are you sure?')">Logout</a></li>
            </ul>
            
            <div class="sidebar-footer">
                <p>Logged in as:<br><strong><?php echo htmlspecialchars($_SESSION['teacher_name']); ?></strong></p>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Manage Questions</h1>
                <button onclick="showAddForm()" class="btn btn-primary">➕ Add New Question</button>
            </div>
            
            <?php if ($message): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Add/Edit Form Modal -->
            <div id="questionModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2 id="formTitle">Add New Question</h2>
                    
                    <form method="POST" action="" id="questionForm">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="question_id" id="questionId">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Course *</label>
                                <select name="course_id" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['course_id']; ?>">
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Month *</label>
                                <select name="month" required>
                                    <option value="">Select Month</option>
                                    <option value="1">Month 1</option>
                                    <option value="2">Month 2</option>
                                    <option value="3">Month 3</option>
                                    <option value="4">Month 4</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Question Text *</label>
                            <textarea name="question_text" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Option A *</label>
                                <input type="text" name="option_a" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Option B *</label>
                                <input type="text" name="option_b" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Option C *</label>
                                <input type="text" name="option_c" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Option D *</label>
                                <input type="text" name="option_d" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Correct Answer *</label>
                            <select name="correct_answer" required>
                                <option value="">Select Correct Answer</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Unlock Date (Test available from)</label>
                                <input type="date" name="unlock_date">
                            </div>
                            
                            <div class="form-group">
                                <label>Lock Date (Test locked after)</label>
                                <input type="date" name="lock_date">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Save Question</button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Questions Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Course</th>
                            <th>Month</th>
                            <th>Question</th>
                            <th>Unlock Date</th>
                            <th>Lock Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $q): ?>
                            <tr>
                                <td><?php echo $q['question_id']; ?></td>
                                <td><?php echo htmlspecialchars($q['course_name']); ?></td>
                                <td>Month <?php echo $q['month']; ?></td>
                                <td><?php echo htmlspecialchars(substr($q['question_text'], 0, 50)) . '...'; ?></td>
                                <td><?php echo $q['unlock_date'] ?? 'N/A'; ?></td>
                                <td><?php echo $q['lock_date'] ?? 'N/A'; ?></td>
                                <td>
                                    <button onclick='editQuestion(<?php echo json_encode($q); ?>)' 
                                            class="btn btn-sm btn-info">Edit</button>
                                    <form method="POST" style="display:inline;" 
                                          onsubmit="return confirm('Delete this question?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
        function showAddForm() {
            document.getElementById('questionModal').style.display = 'block';
            document.getElementById('formTitle').textContent = 'Add New Question';
            document.getElementById('formAction').value = 'add';
            document.getElementById('questionForm').reset();
        }
        
        function editQuestion(question) {
            document.getElementById('questionModal').style.display = 'block';
            document.getElementById('formTitle').textContent = 'Edit Question';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('questionId').value = question.question_id;
            
            const form = document.getElementById('questionForm');
            form.elements['course_id'].value = question.course_id;
            form.elements['month'].value = question.month;
            form.elements['question_text'].value = question.question_text;
            form.elements['option_a'].value = question.option_a;
            form.elements['option_b'].value = question.option_b;
            form.elements['option_c'].value = question.option_c;
            form.elements['option_d'].value = question.option_d;
            form.elements['correct_answer'].value = question.correct_answer;
            form.elements['unlock_date'].value = question.unlock_date || '';
            form.elements['lock_date'].value = question.lock_date || '';
        }
        
        function closeModal() {
            document.getElementById('questionModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('questionModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>