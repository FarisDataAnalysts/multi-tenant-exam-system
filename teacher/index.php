<?php
/**
 * Teacher Login Page
 */

require_once '../config.php';

if (isLoggedIn('teacher')) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT t.*, o.org_name 
            FROM teachers t
            JOIN organizations o ON t.org_id = o.org_id
            WHERE t.username = ? AND t.status = 'active' AND o.status = 'active'
        ");
        $stmt->execute([$username]);
        $teacher = $stmt->fetch();
        
        if ($teacher && password_verify($password, $teacher['password'])) {
            $_SESSION['teacher_id'] = $teacher['teacher_id'];
            $_SESSION['teacher_name'] = $teacher['full_name'];
            $_SESSION['org_id'] = $teacher['org_id'];
            $_SESSION['org_name'] = $teacher['org_name'];
            
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <h1>👨‍🏫 Teacher Portal</h1>
                <p>Exam Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Enter your username" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="instructions">
                <p style="text-align: center; color: #a0a0a0; margin-top: 20px;">
                    Default credentials: teacher1 / teacher123
                </p>
            </div>
        </div>
    </div>
</body>
</html>