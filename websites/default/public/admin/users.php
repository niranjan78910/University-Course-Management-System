<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(['id' => $_SESSION['loggedin']]);
$current_user = $stmt->fetch();

if ($current_user['role'] != 'superadmin') {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

if (isset($_POST['submit'])) {
    if ($_POST['submit'] == 'Add User') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        $checkStmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
        $checkStmt->execute(['username' => $username]);
        
        if ($checkStmt->fetch()) {
            $error = 'Username already exists!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (:username, :password, :role)');
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'role' => $role
            ]);
            $message = 'User added successfully!';
        }
    }
}

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['delete_user'];
    if ($user_id != 1) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $user_id]);
        $message = 'User deleted successfully!';
    } else {
        $error = 'Cannot delete superadmin account!';
    }
}

$stmt = $pdo->prepare('SELECT * FROM users ORDER BY role, username');
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - User Management</title>
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
        <h1>User Management</h1>
        
        <?php if ($message): ?>
            <p class="admin-message success"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="admin-message error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <h2>Add New User</h2>
        <form action="" method="POST">
            <label>Username:</label>
            <input type="text" name="username" required />
            
            <label>Password:</label>
            <input type="password" name="password" required />
            
            <label>Role:</label>
            <select name="role">
                <option value="admin">Admin</option>
                <option value="superadmin">Super Admin</option>
            </select>
            
            <input type="submit" name="submit" value="Add User" />
        </form>
        
        <h2>Existing Users</h2>
        
        <?php if (count($users) > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td data-label="ID"><?php echo $user['id']; ?></td>
                        <td data-label="Username"><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td data-label="Role">
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td data-label="Created"><?php echo $user['created_at']; ?></td>
                        <td>
                            <?php if ($user['id'] != 1): ?>
                            <form action="" method="POST" class="admin-actions">
                                <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>" />
                                <input type="submit" value="Delete" 
                                       onclick="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars($user['username']); ?>?');" />
                            </form>
                            <?php else: ?>
                            <em>Cannot delete</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
        
        <p><a href="index.php">← Back to Dashboard</a></p>
    </main>
    
    <aside>
        <p>Only superadmin can manage user accounts.</p>
        <p>Total users: <?php echo count($users); ?></p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>