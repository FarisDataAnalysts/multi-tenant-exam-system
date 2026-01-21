<?php
/**
 * Results Page
 * View student results and export to Excel (CSV)
 */

require_once '../config.php';
requireLogin('teacher', 'index.php');

$db = getDB();

// Handle Excel Export
if (isset($_GET['export'])) {
    $month = (int)($_GET['month'] ?? 0);
    
    $query = "
        SELECT e.student_id, e.student_name, c.course_name, t.timing_slot,
               e.month, e.total_questions, e.correct_answers, e.score,
               e.start_time, e.end_time
        FROM exams e
        JOIN courses c ON e.course_id = c.course_id
        JOIN timings t ON e.timing_id = t.timing_id
        WHERE e.org_id = ? AND c.teacher_id = ?
    ";
    
    $params = [$_SESSION['org_id'], $_SESSION['teacher_id']];
    
    if ($month > 0) {
        $query .= " AND e.month = ?";
        $params[] = $month;
    }
    
    $query .= " ORDER BY e.start_time DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=exam_results_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Student ID', 'Student Name', 'Course', 'Timing', 'Month', 
                      'Total Questions', 'Correct Answers', 'Score (%)', 'Start Time', 'End Time']);
    
    foreach ($results as $row) {
        fputcsv($output, [
            $row['student_id'],
            $row['student_name'],
            $row['course_name'],
            $row['timing_slot'],
            'Month ' . $row['month'],
            $row['total_questions'],
            $row['correct_answers'],
            number_format($row['score'], 2),
            $row['start_time'],
            $row['end_time']
        ]);
    }
    
    fclose($output);
    exit();
}

// Fetch results
$month_filter = (int)($_GET['month'] ?? 0);

$query = "
    SELECT e.*, c.course_name, t.timing_slot
    FROM exams e
    JOIN courses c ON e.course_id = c.course_id
    JOIN timings t ON e.timing_id = t.timing_id
    WHERE e.org_id = ? AND c.teacher_id = ?
";

$params = [$_SESSION['org_id'], $_SESSION['teacher_id']];

if ($month_filter > 0) {
    $query .= " AND e.month = ?";
    $params[] = $month_filter;
}

$query .= " ORDER BY e.start_time DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();

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
    <title>View Results</title>
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
                <li><a href="questions.php">Manage Questions</a></li>
                <li><a href="results.php" class="active">View Results</a></li>
                <li><a href="?logout=1" onclick="return confirm('Are you sure?')">Logout</a></li>
            </ul>
            
            <div class="sidebar-footer">
                <p>Logged in as:<br><strong><?php echo htmlspecialchars($_SESSION['teacher_name']); ?></strong></p>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Exam Results</h1>
                <div class="header-actions">
                    <select onchange="filterByMonth(this.value)" class="filter-select">
                        <option value="0">All Months</option>
                        <option value="1" <?php echo $month_filter == 1 ? 'selected' : ''; ?>>Month 1</option>
                        <option value="2" <?php echo $month_filter == 2 ? 'selected' : ''; ?>>Month 2</option>
                        <option value="3" <?php echo $month_filter == 3 ? 'selected' : ''; ?>>Month 3</option>
                        <option value="4" <?php echo $month_filter == 4 ? 'selected' : ''; ?>>Month 4</option>
                    </select>
                    <a href="?export=1&month=<?php echo $month_filter; ?>" class="btn btn-success">📥 Export to Excel</a>
                </div>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Timing</th>
                            <th>Month</th>
                            <th>Questions</th>
                            <th>Correct</th>
                            <th>Score</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: #a0a0a0;">
                                    No exam results found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($r['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($r['timing_slot']); ?></td>
                                    <td>Month <?php echo $r['month']; ?></td>
                                    <td><?php echo $r['total_questions']; ?></td>
                                    <td><?php echo $r['correct_answers']; ?></td>
                                    <td>
                                        <span class="score-badge score-<?php 
                                            echo $r['score'] >= 80 ? 'a' : ($r['score'] >= 60 ? 'b' : ($r['score'] >= 40 ? 'c' : 'f')); 
                                        ?>">
                                            <?php echo number_format($r['score'], 2); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y, h:i A', strtotime($r['start_time'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <script>
        function filterByMonth(month) {
            window.location.href = '?month=' + month;
        }
    </script>
</body>
</html>