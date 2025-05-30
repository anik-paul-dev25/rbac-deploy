<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Log dashboard access
error_log("Dashboard accessed. User ID: " . ($_SESSION['user_id'] ?? 'none') . ", Role: " . ($_SESSION['user_role'] ?? 'none') . ", Status: " . ($_SESSION['user_status'] ?? 'none'));

// Redirect based on user role
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
        include __DIR__ . '/../views/dashboard/admin.php';
        break;
    case 'editor':
        include __DIR__ . '/../views/dashboard/editor.php';
        break;
    case 'contributor':
        include __DIR__ . '/../views/dashboard/contributor.php';
        break;
    case 'user':
        include __DIR__ . '/../views/dashboard/user.php';
        break;
    default:
        session_destroy();
        header("Location: /login.php?error=invalid_role");
        exit;
}