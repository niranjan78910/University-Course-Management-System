<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$course_id = $_GET['id'] ?? 0;
$message = '';
$error = '';

$stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id');
$stmt->execute(['id' => $course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: courses.php');
    exit;
}

$subjectStmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
$subjectStmt->execute();
$subjectAreas = $subjectStmt->fetchAll();

if (isset($_POST['submit']) && $_POST['submit'] == 'Update Course') {
    $title = trim($_POST['title']);
    $subject_area_id = $_POST['subject_area_id'];
    $type = $_POST['type'];
    $duration_years = $_POST['duration_years'];
    $description = trim($_POST['description']);
    $part_time = $_POST['part_time'] ?? 0;
    
    $stmt = $pdo->prepare('UPDATE courses SET title = :title, subject_area_id = :subject_area_id, 
                          type = :type, duration_years = :duration_years, description = :description, 
                          part_time = :part_time WHERE id = :id');
    $stmt->execute([
        'title' => $title,
        'subject_area_id' => $subject_area_id,
        'type' => $type,
        'duration_years' => $duration_years,
        'description' => $description,
        'part_time' => $part_time,
        'id' => $course_id
    ]);
    
    $message = 'Course updated successfully!';
    
    $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = :id');
    $stmt->execute(['id' => $course_id]);
    $course = $stmt->fetch();
}
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Edit Course</title>
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
                    $navStmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
                    $navStmt->execute();
                    $nav_subjects = $navStmt->fetchAll();
                    foreach ($nav_subjects as $subject):
                    ?>
                    <li><a href="../subject-area.php?area=<?php echo $subject['slug']; ?>">
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <li><a href="../enquiry.php">Make an enquiry</a></li>
            <li><a href="../search.php">Course Search</a></li>
        </ul>
    </header>
    <section></section>
    <main>
        <h1>Edit Course</h1>
        
        <?php if ($message): ?>
            <p class="admin-message success"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="admin-message error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form action="" method="POST">
            <label>Course Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required />
            
            <label>Subject Area:</label>
            <select name="subject_area_id" required>
                <option value="">Select a subject area</option>
                <?php foreach ($subjectAreas as $area): ?>
                <option value="<?php echo $area['id']; ?>" 
                    <?php echo ($area['id'] == $course['subject_area_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($area['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <label>Course Type:</label>
            <select name="type" required>
                <option value="">Select type</option>
                <option value="BSc" <?php echo ($course['type'] == 'BSc') ? 'selected' : ''; ?>>BSc</option>
                <option value="BA" <?php echo ($course['type'] == 'BA') ? 'selected' : ''; ?>>BA</option>
                <option value="MSc" <?php echo ($course['type'] == 'MSc') ? 'selected' : ''; ?>>MSc</option>
                <option value="MA" <?php echo ($course['type'] == 'MA') ? 'selected' : ''; ?>>MA</option>
                <option value="PhD" <?php echo ($course['type'] == 'PhD') ? 'selected' : ''; ?>>PhD</option>
            </select>
            
            <label>Duration (years):</label>
            <input type="number" name="duration_years" min="1" max="5" 
                   value="<?php echo $course['duration_years']; ?>" required />
            
            <label>Part Time Option:</label>
            <select name="part_time">
                <option value="0" <?php echo ($course['part_time'] == 0) ? 'selected' : ''; ?>>No</option>
                <option value="1" <?php echo ($course['part_time'] == 1) ? 'selected' : ''; ?>>Yes</option>
            </select>
            
            <label>Description:</label>
            <textarea name="description" rows="5" required><?php echo htmlspecialchars($course['description']); ?></textarea>
            
            <input type="submit" name="submit" value="Update Course" />
            <a href="courses.php">Cancel</a>
        </form>
        
        <p><a href="courses.php">← Back to Courses</a></p>
    </main>
    
    <aside>
        <p>Edit course details.</p>
        <p>Changes will appear on the public website immediately.</p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>