<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $_SESSION['loggedin']]);
$user = $stmt->fetch();
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Admin Dashboard</title>
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
    <main>
        <h1>Admin Dashboard</h1>
        
        <p>Welcome, <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</p>
        
        <ul class="dashboard-list">
            <?php if ($user['role'] == 'superadmin'): ?>
            <li>
                <h2>User Management</h2>
                <p>Add, edit, or delete admin accounts</p>
                <a href="users.php">Manage Users</a>
            </li>
            <?php endif; ?>
            
            <li>
                <h2>Subject Areas</h2>
                <p>Manage subject areas and courses</p>
                <a href="subjects.php">Manage Subjects</a>
            </li>
            
            <li>
                <h2>Enquiries</h2>
                <p>View and respond to student enquiries</p>
                <a href="enquiries.php">View Enquiries</a>
            </li>
            
            <li>
                <h2>Courses</h2>
                <p>Add or edit courses</p>
                <a href="courses.php">Manage Courses</a>
            </li>
        </ul>
        
        <p><a href="logout.php">Logout</a></p>
    </main>
    
    <aside>
        <p>Admin dashboard for managing website content.</p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>