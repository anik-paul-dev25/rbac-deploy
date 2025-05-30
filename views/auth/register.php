<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/db.php';

$auth = new AuthController($pdo);
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $profileImage = isset($_FILES['profile_image']) ? $_FILES['profile_image'] : null;
    
    if (in_array($role, ['editor', 'contributor', 'user'])) {
        if ($auth->register($name, $email, $password, $role, false, $profileImage)) {
            $success = true;
        } else {
            $error = "Registration failed. Email may already be in use.";
        }
    } else {
        $error = "Invalid role selected.";
    }
}
?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Register</h2>
        <?php if ($success): ?>
            <div class="alert alert-success">Registration successful! Awaiting admin approval.</div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="auth-form">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="editor">Editor</option>
                    <option value="contributor">Contributor</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="profile_image" class="form-label">Profile Image (Optional)</label>
                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="auth-footer">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>