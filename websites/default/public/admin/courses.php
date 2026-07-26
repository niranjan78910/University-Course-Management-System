<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

$subjectStmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
$subjectStmt->execute();
$subjectAreas = $subjectStmt->fetchAll();

if (isset($_POST['submit']) && $_POST['submit'] == 'Add Course') {
    $title = trim($_POST['title']);
    $subject_area_id = $_POST['subject_area_id'];
    $type = $_POST['type'];
    $duration_years = $_POST['duration_years'];
    $description = trim($_POST['description']);
    $part_time = $_POST['part_time'] ?? 0;
    
    $stmt = $pdo->prepare('INSERT INTO courses (title, subject_area_id, type, duration_years, description, part_time) 
                          VALUES (:title, :subject_area_id, :type, :duration_years, :description, :part_time)');
    $stmt->execute([
        'title' => $title,
        'subject_area_id' => $subject_area_id,
        'type' => $type,
        'duration_years' => $duration_years,
        'description' => $description,
        'part_time' => $part_time
    ]);
    
    $course_id = $pdo->lastInsertId();
    $message = 'Course added successfully!';
}

if (isset($_POST['delete_course'])) {
    $course_id = $_POST['delete_course'];
    
    $deleteModulesStmt = $pdo->prepare('DELETE FROM course_modules WHERE course_id = :course_id');
    $deleteModulesStmt->execute(['course_id' => $course_id]);
    
    $stmt = $pdo->prepare('DELETE FROM courses WHERE id = :id');
    $stmt->execute(['id' => $course_id]);
    $message = 'Course deleted successfully!';
}

$courseStmt = $pdo->prepare('
    SELECT c.*, s.name as subject_name 
    FROM courses c 
    LEFT JOIN subject_areas s ON c.subject_area_id = s.id 
    ORDER BY s.name, c.title
');
$courseStmt->execute();
$courses = $courseStmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Manage Courses</title>
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
        <h1>Manage Courses</h1>
        
        <?php if ($message): ?>
            <p class="admin-message success"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="admin-message error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <h2>Add New Course</h2>
        <form action="" method="POST">
            <label>Course Title:</label>
            <input type="text" name="title" required placeholder="e.g., Software Engineering" />
            
            <label>Subject Area:</label>
            <select name="subject_area_id" required>
                <option value="">Select a subject area</option>
                <?php foreach ($subjectAreas as $area): ?>
                <option value="<?php echo $area['id']; ?>">
                    <?php echo htmlspecialchars($area['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <label>Course Type:</label>
            <select name="type" required>
                <option value="">Select type</option>
                <option value="BSc">BSc (Bachelor of Science)</option>
                <option value="BA">BA (Bachelor of Arts)</option>
                <option value="MSc">MSc (Master of Science)</option>
                <option value="MA">MA (Master of Arts)</option>
                <option value="PhD">PhD</option>
            </select>
            
            <label>Duration (years):</label>
            <input type="number" name="duration_years" min="1" max="5" value="3" required />
            
            <label>Part Time Option:</label>
            <select name="part_time">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
            
            <label>Description:</label>
            <textarea name="description" rows="5" required placeholder="Describe the course..."></textarea>
            
            <input type="submit" name="submit" value="Add Course" />
        </form>
        
        <h2>Existing Courses</h2>
        
        <?php if (count($courses) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Subject Area</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Part Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td data-label="ID"><?php echo $course['id']; ?></td>
                        <td data-label="Title">
                            <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                            <br>
                            <small><?php echo substr($course['description'], 0, 100); ?>...</small>
                        </td>
                        <td data-label="Subject">
                            <?php echo htmlspecialchars($course['subject_name'] ?? 'Not assigned'); ?>
                        </td>
                        <td data-label="Type"><?php echo $course['type']; ?></td>
                        <td data-label="Duration"><?php echo $course['duration_years']; ?> years</td>
                        <td data-label="Part Time"><?php echo ($course['part_time'] == 1) ? 'Yes' : 'No'; ?></td>
                        <td>
                            <a href="editcourse.php?id=<?php echo $course['id']; ?>">Edit</a>
                            
                            <form action="" method="POST" style="display: inline;">
                                <input type="hidden" name="delete_course" value="<?php echo $course['id']; ?>" />
                                <input type="submit" value="Delete" 
                                       onclick="return confirm('Delete course: <?php echo htmlspecialchars($course['title']); ?>?');" />
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No courses found. Add one above.</p>
        <?php endif; ?>
        
        <p><a href="index.php">← Back to Dashboard</a></p>
    </main>
    
    <aside>
        <p>Add, edit or delete courses.</p>
        <p>Courses will appear on the public subject area pages.</p>
        <p>Total courses: <?php echo count($courses); ?></p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>