<?php
session_start();
require_once '../includes/database.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$current_user_id = $_SESSION['loggedin'];

$message = '';
$error = '';

$section = $_GET['section'] ?? 'pending';

if (isset($_POST['mark_responded'])) {
    $enquiry_id = $_POST['mark_responded'];
    
    $stmt = $pdo->prepare('UPDATE enquiries SET responded_by = :user_id, responded_at = NOW() 
                          WHERE id = :id AND responded_at IS NULL');
    $stmt->execute([
        'user_id' => $current_user_id,
        'id' => $enquiry_id
    ]);
    
    if ($stmt->rowCount() > 0) {
        $message = 'Enquiry marked as responded.';
    } else {
        $error = 'Enquiry not found or already responded.';
    }
}

$pendingStmt = $pdo->prepare('
    SELECT e.*, c.title as course_title, u.username as responder_name 
    FROM enquiries e 
    LEFT JOIN courses c ON e.course_id = c.id 
    LEFT JOIN users u ON e.responded_by = u.id 
    WHERE e.responded_at IS NULL 
    ORDER BY e.created_at DESC
');
$pendingStmt->execute();
$pending_enquiries = $pendingStmt->fetchAll();

$respondedStmt = $pdo->prepare('
    SELECT e.*, c.title as course_title, u.username as responder_name 
    FROM enquiries e 
    LEFT JOIN courses c ON e.course_id = c.id 
    LEFT JOIN users u ON e.responded_by = u.id 
    WHERE e.responded_at IS NOT NULL 
    ORDER BY e.responded_at DESC
');
$respondedStmt->execute();
$responded_enquiries = $respondedStmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <title>University of Northampton - Manage Enquiries</title>
    <link rel="stylesheet" href="../uon.css" />
    <style>
    </style>
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
        </ul>
    </header>
    <section></section>
    <main>
        <h1>Manage Enquiries</h1>
        
        <?php if ($message): ?>
            <p class="admin-message success"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <p class="admin-message error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <div class="enquiry-tabs">
            <a href="?section=pending" class="enquiry-tab <?php echo ($section == 'pending') ? 'active' : ''; ?>">
                Pending Enquiries 
                <span class="status-badge status-pending"><?php echo count($pending_enquiries); ?></span>
            </a>
            <a href="?section=responded" class="enquiry-tab <?php echo ($section == 'responded') ? 'active' : ''; ?>">
                Responded Enquiries 
                <span class="status-badge status-responded"><?php echo count($responded_enquiries); ?></span>
            </a>
        </div>
        
        <?php if ($section == 'pending'): ?>
            <h2>Pending Enquiries (Not Responded Yet)</h2>
            
            <?php if (count($pending_enquiries) > 0): ?>
                <?php foreach ($pending_enquiries as $enquiry): ?>
                <div class="enquiry-item">
                    <div class="enquiry-meta">
                        <strong>From:</strong> <?php echo htmlspecialchars($enquiry['name']); ?> 
                        &lt;<?php echo htmlspecialchars($enquiry['email']); ?>&gt;
                        <?php if ($enquiry['phone']): ?>
                            | <strong>Phone:</strong> <?php echo htmlspecialchars($enquiry['phone']); ?>
                        <?php endif; ?>
                        | <strong>Submitted:</strong> <?php echo $enquiry['created_at']; ?>
                        <?php if ($enquiry['course_title']): ?>
                            | <strong>Course:</strong> <?php echo htmlspecialchars($enquiry['course_title']); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="enquiry-message">
                        <p><?php echo nl2br(htmlspecialchars($enquiry['message'])); ?></p>
                    </div>
                    
                    <div class="enquiry-actions">
                        <form action="" method="POST">
                            <input type="hidden" name="mark_responded" value="<?php echo $enquiry['id']; ?>" />
                            <input type="submit" value="Mark as Responded" />
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No pending enquiries. Great work!</p>
            <?php endif; ?>
        <?php else: ?>
            <h2>Responded Enquiries</h2>
            
            <?php if (count($responded_enquiries) > 0): ?>
                <?php foreach ($responded_enquiries as $enquiry): ?>
                <div class="enquiry-item responded">
                    <div class="enquiry-meta">
                        <strong>From:</strong> <?php echo htmlspecialchars($enquiry['name']); ?> 
                        &lt;<?php echo htmlspecialchars($enquiry['email']); ?>&gt;
                        <?php if ($enquiry['phone']): ?>
                            | <strong>Phone:</strong> <?php echo htmlspecialchars($enquiry['phone']); ?>
                        <?php endif; ?>
                        | <strong>Submitted:</strong> <?php echo $enquiry['created_at']; ?>
                        | <strong>Responded:</strong> <?php echo $enquiry['responded_at']; ?>
                        by <?php echo htmlspecialchars($enquiry['responder_name'] ?? 'Unknown'); ?>
                        <?php if ($enquiry['course_title']): ?>
                            | <strong>Course:</strong> <?php echo htmlspecialchars($enquiry['course_title']); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="enquiry-message">
                        <p><?php echo nl2br(htmlspecialchars($enquiry['message'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No responded enquiries yet.</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <p><a href="index.php">← Back to Dashboard</a></p>
    </main>
    
    <aside>
        <p>Manage student enquiries here.</p>
        <p>Mark enquiries as responded when you have contacted the student.</p>
        <p>Total enquiries: <?php echo count($pending_enquiries) + count($responded_enquiries); ?></p>
    </aside>
    
    <footer>
        &copy; <?php echo date('Y'); ?> University of Northampton
    </footer>
</body>
</html>