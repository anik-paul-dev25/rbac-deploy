<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../config/db.php';

restrictAccess(['admin']);
$userController = new UserController($pdo);

$userId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$userController->deleteUser($userId);
header("Location: dashboard.php");
exit;
?>