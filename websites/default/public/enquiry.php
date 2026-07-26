<?php
require_once 'includes/database.php';

$message = '';
$error = '';

$courseStmt = $pdo->prepare('SELECT id, title FROM courses ORDER BY title');
$courseStmt->execute();
$courses = $courseStmt->fetchAll();

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']) ?: null;
    $course_id = $_POST['course_id'] ?: null;
    $message_text = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message_text)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO enquiries (name, email, phone, course_id, message) 
                              VALUES (:name, :email, :phone, :course_id, :message)');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'course_id' => $course_id,
            'message' => $message_text
        ]);
        
        $message = 'Thank you! Your enquiry has been submitted successfully.';
        
        $_POST = array();
    }
}
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Make an Enquiry</title>
    <link rel="stylesheet" href="uon.css" />
</head>
<body>
    <header>
        <img src="/logo.jpg" alt="logo" />
        <ul>
            <li><a href="index.php">Home</a></li>
            <li>Subject Areas
                <ul>
                    <?php
                    $stmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
                    $stmt->execute();
                    $subjects = $stmt->fetchAll();
                    foreach ($subjects as $subject):
                    ?>
                    <li><a href="subject-area.php?area=<?php echo $subject['slug']; ?>">
                        <?php echo htmlspecialchars($subject['name']); ?>
                    </a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <li><a href="enquiry.php">Make an enquiry</a></li>
            <li><a href="search.php">Course Search</a></li>
        </ul>
    </header>
    <section></section>
    <main>
        <h1>Make an enquiry</h1>

        <?php if ($message): ?>
            <p class="success-message"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <label>Your name *</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />

            <label>Email address *</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required />

            <label>Phone number (optional)</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" />

            <label>Which course are you enquiring about?</label>
            <select name="course_id">
                <option value="">Select a course (optional)</option>
                <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['id']; ?>"
                    <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($course['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>What do you want to ask? *</label>
            <textarea name="message" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>

            <input type="submit" name="submit" value="Submit Enquiry" />
        </form>
    </main>

    <aside>
        <p>We'll support you every step of the way whichever course you choose – providing you with first–class teaching, modern facilities, impressive accommodation and great learning. We have increased focus on seminars or tutorials that allow closer interaction between students and a member of staff in the form of discussion in small groups or one-to-one that mimic practice in the professional world, allowing for experimentation, ideas, and teamwork.</p>
        Applications for 2026 are now open. <a href="">Find your course now.</a>
    </aside>

    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>