<?php
require_once 'includes/database.php';

$area_slug = $_GET['area'] ?? 'computing';

$stmt = $pdo->prepare('SELECT * FROM subject_areas WHERE slug = :slug');
$stmt->execute(['slug' => $area_slug]);
$subject = $stmt->fetch();

if (!$subject) {
    $stmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name LIMIT 1');
    $stmt->execute();
    $subject = $stmt->fetch();
    if ($subject) {
        header('Location: subject-area.php?area=' . $subject['slug']);
        exit;
    }
}

$courses_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $courses_per_page;

$courseStmt = $pdo->prepare('
    SELECT * FROM courses 
    WHERE subject_area_id = :subject_id 
    ORDER BY title
    LIMIT :limit OFFSET :offset
');
$courseStmt->bindValue(':subject_id', $subject['id'], PDO::PARAM_INT);
$courseStmt->bindValue(':limit', $courses_per_page, PDO::PARAM_INT);
$courseStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$courseStmt->execute();
$courses = $courseStmt->fetchAll();

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE subject_area_id = :subject_id');
$countStmt->execute(['subject_id' => $subject['id']]);
$total_courses = $countStmt->fetchColumn();
$total_pages = ceil($total_courses / $courses_per_page);

$page_title = $subject['name'] ?? 'Subject Area';
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - <?php echo htmlspecialchars($page_title); ?></title>
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
                    $all_subjects = $stmt->fetchAll();
                    foreach ($all_subjects as $subj):
                    ?>
                    <li><a href="subject-area.php?area=<?php echo $subj['slug']; ?>">
                        <?php echo htmlspecialchars($subj['name']); ?>
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
        <h1><?php echo htmlspecialchars($subject['name'] ?? 'Subject Area'); ?></h1>
        
        <?php if (count($courses) > 0): ?>
            <h2>Courses in <?php echo htmlspecialchars($subject['name']); ?></h2>
            <p>Showing <?php echo count($courses); ?> of <?php echo $total_courses; ?> courses</p>
            
            <?php foreach ($courses as $course): ?>
            <div class="course" style="background: #f9f9f9; padding: 20px; margin: 20px 0; border-left: 4px solid #002147;">
                <h3><?php echo htmlspecialchars($course['title']); ?> (<?php echo $course['type']; ?>)</h3>
                <p><strong>Duration:</strong> <?php echo $course['duration_years']; ?> years</p>
                <p><strong>Can be completed part time?</strong> <?php echo ($course['part_time'] == 1) ? 'Yes' : 'No'; ?></p>
                <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            </div>
            <?php endforeach; ?>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?area=<?php echo $area_slug; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current-page"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?area=<?php echo $area_slug; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?area=<?php echo $area_slug; ?>&page=<?php echo $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="background: #f5f5f5; padding: 20px; margin: 20px 0; border-left: 4px solid #002147;">
                <p><em>No courses available for this subject area yet.</em></p>
                <p>Please check back later or contact us for more information.</p>
            </div>
        <?php endif; ?>
    </main>
    
    <aside>
        <p>We'll support you every step of the way whichever course you choose – providing you with first–class teaching, modern facilities, impressive accommodation and great learning.</p>
        Applications for 2026 are now open. <a href="">Find your course now.</a>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>