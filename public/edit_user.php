<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../config/db.php';

restrictAccess(['admin', 'editor']);
$userController = new UserController($pdo);
$success = false;
$error = '';

$userId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if ($userController->editUser($userId, $name, $email)) {
        $success = true;
    } else {
        $error = "Failed to edit user.";
    }
}
?>

<?php include __DIR__ . '/../views/layout/header.php'; ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <h2>Edit User</h2>
        <?php if ($success): ?>
            <div class="alert alert-success">User updated successfully!</div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>