<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../controllers/BlogController.php';
require_once __DIR__ . '/../../config/db.php';

restrictAccess(['editor']);
$userController = new UserController($pdo);
$blogController = new BlogController($pdo);
$allUsers = $userController->getAllUsers();
$posts = $blogController->getPosts();
?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<style>
    body {
        background-color: #f5f7fa;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    .dashboard-container {
        max-width: 1000px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    h2, h3 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .table {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
    }
    .table th {
        background-color: #3498db;
        color: #fff;
        padding: 12px;
    }
    .table td {
        padding: 12px;
        vertical-align: middle;
    }
    .btn-custom {
        border-radius: 5px;
        padding: 8px 16px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    .btn-custom:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    a.btn-success {
        background-color: #28a745;
        border: none;
    }
    a.btn-success:hover {
        background-color: #218838;
    }
    @media (max-width: 768px) {
        .dashboard-container {
            margin: 10px;
            padding: 15px;
        }
        .table {
            font-size: 14px;
        }
        .btn-custom {
            padding: 6px 12px;
            font-size: 12px;
        }
    }
    @media (max-width: 576px) {
        .table th, .table td {
            padding: 8px;
            font-size: 12px;
        }
        h2 {
            font-size: 24px;
        }
        h3 {
            font-size: 18px;
        }
    }
</style>

<div class="dashboard-container">
    <h2>Editor Dashboard</h2>
    <h3>Manage Users</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allUsers as $user): ?>
                <?php if ($user['role'] !== 'admin'): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-custom">Edit</a>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Blog Posts</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td>
                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary btn-custom">Edit</a>
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-custom" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="add_post.php" class="btn btn-success btn-custom">Add Post</a>
    <!-- <a href="posts" class="btn btn-primary btn-custom">Manage Posts</a> -->
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>