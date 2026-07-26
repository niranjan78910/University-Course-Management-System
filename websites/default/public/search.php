<?php
require_once 'includes/database.php';

$subjectStmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
$subjectStmt->execute();
$subjectAreas = $subjectStmt->fetchAll();

$typeStmt = $pdo->prepare('SELECT DISTINCT type FROM courses ORDER BY type');
$typeStmt->execute();
$courseTypes = $typeStmt->fetchAll();

$search_results = [];
$search_performed = false;
$total_courses = 0;
$total_pages = 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$courses_per_page = 10;

if (isset($_GET['search'])) {
    $search_performed = true;
    
    $subject_id = $_GET['subject_id'] ?? '';
    $type = $_GET['type'] ?? '';
    $min_years = $_GET['min_years'] ?? '';
    $max_years = $_GET['max_years'] ?? '';
    $part_time = $_GET['part_time'] ?? '';
    
    $sql = 'SELECT c.*, s.name as subject_name 
            FROM courses c 
            LEFT JOIN subject_areas s ON c.subject_area_id = s.id 
            WHERE 1=1';
    
    $params = [];
    
    if (!empty($subject_id)) {
        $sql .= ' AND c.subject_area_id = :subject_id';
        $params['subject_id'] = $subject_id;
    }
    
    if (!empty($type)) {
        $sql .= ' AND c.type = :type';
        $params['type'] = $type;
    }
    
    if (!empty($min_years)) {
        $sql .= ' AND c.duration_years >= :min_years';
        $params['min_years'] = $min_years;
    }
    
    if (!empty($max_years)) {
        $sql .= ' AND c.duration_years <= :max_years';
        $params['max_years'] = $max_years;
    }
    
    if (!empty($part_time)) {
        $sql .= ' AND c.part_time = :part_time';
        $params['part_time'] = $part_time;
    }
    
    $countSql = 'SELECT COUNT(*) FROM courses c 
                 LEFT JOIN subject_areas s ON c.subject_area_id = s.id 
                 WHERE 1=1';
    
    $countParams = $params;
    
    if (!empty($subject_id)) {
        $countSql .= ' AND c.subject_area_id = :subject_id';
    }
    
    if (!empty($type)) {
        $countSql .= ' AND c.type = :type';
    }
    
    if (!empty($min_years)) {
        $countSql .= ' AND c.duration_years >= :min_years';
    }
    
    if (!empty($max_years)) {
        $countSql .= ' AND c.duration_years <= :max_years';
    }
    
    if (!empty($part_time)) {
        $countSql .= ' AND c.part_time = :part_time';
    }
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($countParams);
    $total_courses = $countStmt->fetchColumn();
    $total_pages = ceil($total_courses / $courses_per_page);
    
    $offset = ($page - 1) * $courses_per_page;
    $sql .= ' ORDER BY s.name, c.title LIMIT :limit OFFSET :offset';
    
    $params['limit'] = $courses_per_page;
    $params['offset'] = $offset;
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        if ($key == 'limit' || $key == 'offset') {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':' . $key, $value);
        }
    }
    
    $stmt->execute();
    $search_results = $stmt->fetchAll();
}
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Course Search</title>
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
                    $navStmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
                    $navStmt->execute();
                    $nav_subjects = $navStmt->fetchAll();
                    foreach ($nav_subjects as $subject):
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
        <h1>Course Search</h1>
        
        <form action="" method="GET">
            <label>Subject Area</label>
            <select name="subject_id">
                <option value="">All Subject Areas</option>
                <?php foreach ($subjectAreas as $area): ?>
                <option value="<?php echo $area['id']; ?>"
                    <?php echo (isset($_GET['subject_id']) && $_GET['subject_id'] == $area['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($area['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <label>Course Type</label>
            <select name="type">
                <option value="">All Types</option>
                <?php foreach ($courseTypes as $typeItem): ?>
                <option value="<?php echo $typeItem['type']; ?>"
                    <?php echo (isset($_GET['type']) && $_GET['type'] == $typeItem['type']) ? 'selected' : ''; ?>>
                    <?php echo $typeItem['type']; ?>
                </option>
                <?php endforeach; ?>
                <option value="BSc" <?php echo (isset($_GET['type']) && $_GET['type'] == 'BSc') ? 'selected' : ''; ?>>BSc</option>
                <option value="BA" <?php echo (isset($_GET['type']) && $_GET['type'] == 'BA') ? 'selected' : ''; ?>>BA</option>
                <option value="MSc" <?php echo (isset($_GET['type']) && $_GET['type'] == 'MSc') ? 'selected' : ''; ?>>MSc</option>
                <option value="MA" <?php echo (isset($_GET['type']) && $_GET['type'] == 'MA') ? 'selected' : ''; ?>>MA</option>
            </select>
            
            <label>Minimum Duration (years)</label>
            <input type="number" name="min_years" min="1" max="5" 
                   value="<?php echo htmlspecialchars($_GET['min_years'] ?? ''); ?>" />
            
            <label>Maximum Duration (years)</label>
            <input type="number" name="max_years" min="1" max="5" 
                   value="<?php echo htmlspecialchars($_GET['max_years'] ?? ''); ?>" />
            
            <label>Part Time Option</label>
            <select name="part_time">
                <option value="">Any</option>
                <option value="1" <?php echo (isset($_GET['part_time']) && $_GET['part_time'] == '1') ? 'selected' : ''; ?>>Yes only</option>
                <option value="0" <?php echo (isset($_GET['part_time']) && $_GET['part_time'] == '0') ? 'selected' : ''; ?>>No only</option>
            </select>
            
            <input type="hidden" name="page" value="1">
            <input type="submit" name="search" value="Search Courses" />
        </form>
        
        <?php if ($search_performed): ?>
            <p>Found <?php echo $total_courses; ?> course(s).</p>
            
            <?php if (count($search_results) > 0): ?>
                <p>Page <?php echo $page; ?> of <?php echo $total_pages; ?></p>
                
                <?php foreach ($search_results as $course): ?>
                <div>
                    <h3><?php echo htmlspecialchars($course['title']); ?> (<?php echo $course['type']; ?>)</h3>
                    <p>Subject: <?php echo htmlspecialchars($course['subject_name'] ?? 'Not assigned'); ?></p>
                    <p>Duration: <?php echo $course['duration_years']; ?> years</p>
                    <p>Part time option: <?php echo ($course['part_time'] == 1) ? 'Yes' : 'No'; ?></p>
                    <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                </div>
                <?php endforeach; ?>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php 
                    $pagination_url = "?search=1";
                    if (!empty($subject_id)) $pagination_url .= "&subject_id=" . urlencode($subject_id);
                    if (!empty($type)) $pagination_url .= "&type=" . urlencode($type);
                    if (!empty($min_years)) $pagination_url .= "&min_years=" . urlencode($min_years);
                    if (!empty($max_years)) $pagination_url .= "&max_years=" . urlencode($max_years);
                    if (!empty($part_time)) $pagination_url .= "&part_time=" . urlencode($part_time);
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $pagination_url; ?>&page=<?php echo $page - 1; ?>">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current-page"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $pagination_url; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="<?php echo $pagination_url; ?>&page=<?php echo $page + 1; ?>">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No courses found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    
    <aside>
        <p>Search for courses by subject, type, or duration.</p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>