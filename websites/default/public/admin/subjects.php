<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

if (isset($_POST['submit'])) {
    if ($_POST['submit'] == 'Add Subject') {
        $name = trim($_POST['name']);
        $slug = strtolower(str_replace(' ', '-', $name));
        
        // Check if subject already exists
        $checkStmt = $pdo->prepare('SELECT * FROM subject_areas WHERE name = :name OR slug = :slug');
        $checkStmt->execute(['name' => $name, 'slug' => $slug]);
        
        if ($checkStmt->fetch()) {
            $error = 'Subject area already exists!';
        } else {
            $stmt = $pdo->prepare('INSERT INTO subject_areas (name, slug) VALUES (:name, :slug)');
            $stmt->execute(['name' => $name, 'slug' => $slug]);
            $message = 'Subject area added successfully!';
        }
    }
    
    if ($_POST['submit'] == 'Update Subject') {
        $id = $_POST['id'];
        $name = trim($_POST['name']);
        $slug = strtolower(str_replace(' ', '-', $name));
        
        $stmt = $pdo->prepare('UPDATE subject_areas SET name = :name, slug = :slug WHERE id = :id');
        $stmt->execute(['name' => $name, 'slug' => $slug, 'id' => $id]);
        $message = 'Subject area updated successfully!';
        $edit_id = 0; 
    }
}

if (isset($_POST['delete_subject'])) {
    $subject_id = $_POST['delete_subject'];
    
    $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE subject_area_id = :id');
    $checkStmt->execute(['id' => $subject_id]);
    $courseCount = $checkStmt->fetchColumn();
    
    if ($courseCount > 0) {
        $error = 'Cannot delete subject area that has courses assigned to it!';
    } else {
        $stmt = $pdo->prepare('DELETE FROM subject_areas WHERE id = :id');
        $stmt->execute(['id' => $subject_id]);
        $message = 'Subject area deleted successfully!';
    }
}

$stmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
$stmt->execute();
$subjects = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Manage Subject Areas</title>
    <link rel="stylesheet" href="../uon.css" />
</head>
<body>
    <header>
        <img src="../logo.jpg" alt="logo" />
        <ul>
            <li><a href="../index.php">Home</a></li>
            <li>Subject Areas
                <ul>
                    <?php
                    $stmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
                    $stmt->execute();
                    $nav_subjects = $stmt->fetchAll();
                    foreach ($nav_subjects as $subject):
                    ?>
                    <li><a href="../subject-area.php?area=<?php echo $subject['slug']; ?>">
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <li><a href="../enquiry.php">Make an enquiry</a></li>
        </ul>
    </header>
    <section></section>
    <main>
        <h1>Manage Subject Areas</h1>
        
        <?php if ($message): ?>
            <p class="admin-message success"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="admin-message error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <h2>Add New Subject Area</h2>
        <form action="" method="POST">
            <label>Subject Area Name:</label>
            <input type="text" name="name" required placeholder="e.g., Business, Engineering, Medicine" />
            <input type="submit" name="submit" value="Add Subject" />
        </form>
        
        <h2>Existing Subject Areas</h2>
        
        <?php if (count($subjects) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td data-label="ID"><?php echo $subject['id']; ?></td>
                        <td data-label="Name">
                            <?php if ($edit_id == $subject['id']): ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $subject['id']; ?>" />
                                    <input type="text" name="name" 
                                           value="<?php echo htmlspecialchars($subject['name']); ?>" 
                                           required />
                                    <input type="submit" name="submit" value="Update Subject" />
                                    <a href="subjects.php">Cancel</a>
                                </form>
                            <?php else: ?>
                                <?php echo htmlspecialchars($subject['name']); ?>
                            <?php endif; ?>
                        </td>
                        <td data-label="Slug"><?php echo $subject['slug']; ?></td>
                        <td data-label="Created"><?php echo $subject['created_at']; ?></td>
                        <td>
                            <?php if ($edit_id != $subject['id']): ?>
                                <a href="subjects.php?edit=<?php echo $subject['id']; ?>">Edit</a>
                            <?php endif; ?>
                            
                            <form action="" method="POST" style="display: inline;">
                                <input type="hidden" name="delete_subject" value="<?php echo $subject['id']; ?>" />
                                <input type="submit" value="Delete" 
                                       onclick="return confirm('Delete <?php echo htmlspecialchars($subject['name']); ?>?');" />
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No subject areas found. Add one above.</p>
        <?php endif; ?>
        
        <p><a href="index.php">← Back to Dashboard</a></p>
    </main>
    
    <aside>
        <p>Add, edit or delete subject areas.</p>
        <p>Subject areas appear in the navigation menu automatically.</p>
        <p>Total subject areas: <?php echo count($subjects); ?></p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>