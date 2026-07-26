<?php
session_start();
require_once '../includes/database.php';

$error = '';

if (isset($_POST['submit'])) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute(['username' => $_POST['username']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['loggedin'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Admin Login</title>
    <link rel="stylesheet" href="../uon.css" />
</head>
<body>
    <header>
        <img src="../logo.jpg" alt="logo" />
        <ul>
            <li><a href="../index.php">Home</a></li>
            <li>Subject Areas
                <ul>
                    <li><a href="../subject-area.php?area=computing">Computing</a></li>
                    <li><a href="../subject-area.php?area=history">History</a></li>
                    <li><a href="../subject-area.php?area=science">Science</a></li>
                </ul>
            </li>
            <li><a href="../enquiry.php">Make an enquiry</a></li>
        </ul>
    </header>
    <section></section>
    <main class="admin-login">
        <h1>Admin Login</h1>
        
        <?php if ($error): ?>
            <p class="admin-message error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form action="" method="POST">
            <label>Username:</label>
            <input type="text" name="username" required />
            
            <label>Password:</label>
            <input type="password" name="password" required />
            
            <input type="submit" name="submit" value="Login" />
        </form>
        
        <p><a href="../index.php">← Back to main website</a></p>
    </main>
    
    <aside>
        <p>Admin area access only. Please log in with your credentials.</p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>