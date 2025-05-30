<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/db.php';

$auth = new AuthController($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $result = $auth->login($email, $password);
    
    if ($result === 'approved') {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        header("Location: /dashboard.php");
        exit;
    } elseif ($result === 'pending') {
        header("Location: /not_approved.php");
        exit;
    } elseif ($result === 'rejected') {
        $error = "Your account has been rejected.";
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<div class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'rejected'): ?>
            <div class="alert alert-danger">Your account has been rejected.</div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="auth-footer">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>