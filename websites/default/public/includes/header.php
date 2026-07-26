function getNavigation($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM subject_areas ORDER BY name');
    $stmt->execute();
    $subjects = $stmt->fetchAll();
    
    $nav = '<header>
        <img src="/logo.jpg" alt="logo" />  <!-- Added slash -->
        <ul>
            <li><a href="index.php">Home</a></li>
            <li>Subject Areas
                <ul>';
    
    foreach ($subjects as $subject) {
        $nav .= '<li><a href="subject-area.php?area=' . $subject['slug'] . '">' . 
                htmlspecialchars($subject['name']) . '</a></li>';
    }
    
    $nav .= '</ul>
            </li>
            <li><a href="enquiry.php">Make an enquiry</a></li>
        </ul>
    </header>
    <section></section>
    <main>';
    
    return $nav;
}