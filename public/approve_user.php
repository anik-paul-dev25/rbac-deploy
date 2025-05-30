<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../config/db.php';

restrictAccess(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $userController = new UserController($pdo);
    $userId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    if ($userId && $userController->approveUser($userId)) {
        header("Location: dashboard.php?status=approved");
    } else {
        header("Location: dashboard.php?status=error");
    }
} else {
    header("Location: dashboard.php?status=invalid");
}
exit;
?>