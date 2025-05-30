<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/db.php';

// Restrict access to authenticated users with specific roles
restrictAccess(['admin', 'editor', 'contributor', 'user']);

// Route to role-specific post management page
$role = $_SESSION['user_role'] ?? 'pending';
$status = $_SESSION['user_status'] ?? 'pending';

if ($status === 'pending') {
    header("Location: /not_approved.php");
    exit;
} elseif ($status === 'rejected') {
    session_destroy();
    header("Location: /login.php?error=rejected");
    exit;
}

switch ($role) {
    case 'admin':
        include __DIR__ . '/../views/dashboard/posts.php';
        break;
    case 'editor':
        include __DIR__ . '/../views/dashboard/editor_posts.php';
        break;
    case 'contributor':
        include __DIR__ . '/../views/dashboard/contributor_posts.php';
        break;
    case 'user':
        include __DIR__ . '/../views/dashboard/user_posts.php';
        break;
    default:
        session_destroy();
        header("Location: /login.php?error=invalid_role");
        exit;
}
?>