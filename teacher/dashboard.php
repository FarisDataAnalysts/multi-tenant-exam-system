<?php
/**
 * Teacher Dashboard
 * Shows statistics and quick actions
 */

require_once '../config.php';
requireLogin('teacher', 'index.php');

$db = getDB();

// Get statistics
$stmt = $db->prepare("
    SELECT COUNT(*) as total FROM questions 
    WHERE org_id = ? AND teacher_id = ?
");
$stmt->execute([$_SESSION['org_id'], $_SESSION['teacher_id']]);
$total_questions = $stmt->fetch()['total'];

$stmt = $db->prepare("
    SELECT COUNT(*) as total FROM exams 
    WHERE org_id = ? AND course_id IN (
        SELECT course_id FROM courses WHERE teacher_id = ?
    )
");
$stmt->execute([$_SESSION['org_id'], $_SESSION['teacher_id']]);
$total_exams = $stmt->fetch()['total'];

$stmt = $db->prepare("
    SELECT COUNT(*) as total FROM courses 
    WHERE org_id = ? AND teacher_id = ?
");
$stmt->execute([$_SESSION['org_id'], $_SESSION['teacher_id']]);
$total_courses = $stmt->fetch()['total'];

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
    <title>Teacher Dashboard</title>
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
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="questions.php">Manage Questions</a></li>
                <li><a href="results.php">View Results</a></li>
                <li><a href="?logout=1" onclick="return confirm('Are you sure?')">Logout</a></li>
            </ul>
            
            <div class="sidebar-footer">
                <p>Logged in as:<br><strong><?php echo htmlspecialchars($_SESSION['teacher_name']); ?></strong></p>
            </div>
        </nav>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3><?php echo $total_questions; ?></h3>
                        <p>Total Questions</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-details">
                        <h3><?php echo $total_courses; ?></h3>
                        <p>Courses</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $total_exams; ?></h3>
                        <p>Exam Attempts</p>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="questions.php?action=add" class="btn btn-primary">➕ Add New Question</a>
                    <a href="results.php" class="btn btn-success">📊 View Results</a>
                    <a href="results.php?export=1" class="btn btn-info">📥 Export to Excel</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>