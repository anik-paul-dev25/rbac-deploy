<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/BlogController.php';
require_once __DIR__ . '/../config/db.php';

restrictAccess(['admin', 'editor', 'contributor', 'user']);
$blogController = new BlogController($pdo);
$success = false;
$error = '';

$postId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: dashboard.php");
    exit;
}

if (!$blogController->canEditPost($post, $_SESSION['user_id'], $_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = $_POST['content'] ?? '';
    $postImage = isset($_FILES['post_image']) ? $_FILES['post_image'] : null;
    
    if ($title && $content) {
        if ($blogController->editPost($postId, $title, $content, $postImage)) {
            $success = true;
            $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to edit post.";
        }
    } else {
        $error = "Title and content are required.";
    }
}
?>

<?php include __DIR__ . '/../views/layout/header.php'; ?>
<style>
html {
    height: 100%;
}

body {
    background-color: #f8f9fa;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #333;
    margin: 0;
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

.content {
    flex: 1;
    margin-top: 10px;
    padding: 0 15px;
}

.alert {
    margin-top: 10px;
    border-radius: 6px;
    font-size: 0.9rem;
}

/* Auth Form Styles */
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 150px);
    padding: 20px 0;
}

.auth-card {
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    padding: 30px;
    width: 100%;
    max-width: 450px;
}

.auth-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #343a40;
    text-align: center;
    margin-bottom: 20px;
}

.auth-form .form-label {
    font-size: 0.9rem;
    font-weight: 500;
    color: #495057;
}

.auth-form .form-control,
.auth-form .form-select {
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 10px;
    font-size: 0.9rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.auth-form .form-control:focus,
.auth-form .form-select:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 8px rgba(23, 162, 184, 0.2);
    outline: none;
}

.auth-form .btn-primary {
    background-color: #17a2b8;
    border: none;
    border-radius: 6px;
    padding: 12px;
    font-size: 0.95rem;
    font-weight: 500;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.auth-form .btn-primary:hover {
    background-color: #138496;
    transform: translateY(-2px);
}

.auth-footer {
    text-align: center;
    margin-top: 20px;
    font-size: 0.85rem;
    color: #6c757d;
}

.auth-footer a {
    color: #17a2b8;
    text-decoration: none;
    font-weight: 500;
}

.auth-footer a:hover {
    color: #138496;
    text-decoration: underline;
}

/* Textarea Specific Styles */
.auth-form textarea.form-control {
    resize: vertical;
    min-height: 120px;
    max-height: 400px;
    line-height: 1.5;
}

/* Ensure file input consistency */
.auth-form input[type="file"].form-control {
    padding: 8px;
}

/* Current Image Styles */
.auth-form .current-image {
    max-width: 100px;
    border-radius: 6px;
    margin-top: 10px;
    display: block;
}

/* Responsive Design */
@media (max-width: 992px) {
    .auth-card {
        padding: 20px;
        max-width: 400px;
    }
    .auth-title {
        font-size: 1.3rem;
    }
    .auth-form .form-control,
    .auth-form .form-select {
        padding: 8px;
        font-size: 0.85rem;
    }
    .auth-form .btn-primary {
        padding: 10px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .auth-card {
        padding: 15px;
        max-width: 100%;
        margin: 0 10px;
    }
    .auth-title {
        font-size: 1.2rem;
    }
    .auth-form .form-label {
        font-size: 0.85rem;
    }
    .auth-form .form-control,
    .auth-form .form-select {
        padding: 7px;
        font-size: 0.8rem;
    }
    .auth-form .btn-primary {
        padding: 8px;
        font-size: 0.85rem;
    }
    .auth-footer {
        font-size: 0.8rem;
    }
    .auth-form .current-image {
        max-width: 80px;
    }
}
</style>

<div class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Edit Blog Post</h2>
        <?php if ($success): ?>
            <div class="alert alert-success">Post updated successfully!</div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="auth-form">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="8" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="post_image" class="form-label">Post Image (Optional)</label>
                <input type="file" class="form-control" id="post_image" name="post_image" accept="image/*">
                <?php if ($post['post_image']): ?>
                    <p>Current Image: <img src="<?php echo htmlspecialchars($post['post_image']); ?>" alt="Post Image" class="current-image"></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Post</button>
        </form>
        <p class="auth-footer"><a href="posts.php">Back to Posts</a></p>
    </div>
</div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>