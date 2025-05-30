<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/BlogController.php';
require_once __DIR__ . '/../config/db.php';

restrictAccess(['admin', 'editor', 'contributor', 'user']);
$blogController = new BlogController($pdo);

$postId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post || !$blogController->canDeletePost($post, $_SESSION['user_id'], $_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit;
}

$blogController->deletePost($postId);
header("Location: dashboard.php");
exit;
?>