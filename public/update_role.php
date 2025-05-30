<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../config/db.php';

restrictAccess(['admin']);
$userController = new UserController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    
    if (in_array($role, ['admin', 'editor', 'contributor', 'user'])) {
        $userController->updateUserRole($userId, $role);
    }
}
header("Location: dashboard.php");
exit;
?>