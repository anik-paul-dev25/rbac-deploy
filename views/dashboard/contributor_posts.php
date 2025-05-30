<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../controllers/BlogController.php';
require_once __DIR__ . '/../../config/db.php';

restrictAccess(['contributor']);
$blogController = new BlogController($pdo);

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterRole = isset($_GET['role']) ? $_GET['role'] : '';
$filterDate = isset($_GET['date']) ? $_GET['date'] : '';

$posts = $blogController->getPosts();
if ($searchTerm) {
    $posts = $blogController->searchPosts($searchTerm);
} elseif ($filterRole) {
    $posts = $blogController->filterPostsByRole($filterRole);
} elseif ($filterDate) {
    $posts = $blogController->filterPostsByDate($filterDate);
}
?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<style>
    body {
        background-color: #f5f7fa;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    .posts-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
    }
    h2 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 20px;
        text-align: center;
    }
    .search-filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        background-color: #fff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 48px; /* Adjust based on navbar height */
        z-index: 1020; /* Below navbar (z-index: 1030) */
    }
    .form-control, .form-select {
        border-radius: 5px;
        border: 1px solid #ced4da;
        padding: 10px;
        font-size: 14px;
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
    .post-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        max-width: 100%;
        box-sizing: border-box;
    }
    .post-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    .user-info {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        max-width: 30%;
    }
    .user-image {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 8px;
        object-fit: cover;
    }
    .user-name {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .user-role {
        font-size: 10px;
        color: #7f8c8d;
        margin-left: 6px;
        white-space: nowrap;
    }
    .post-title {
        font-size: 16px;
        font-weight: 600;
        color: #3498db;
        text-align: center;
        flex-grow: 1;
        margin: 0 10px;
        word-wrap: break-word;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .post-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .action-icon {
        font-size: 16px;
        color: #6c757d;
        transition: color 0.2s ease;
    }
    .action-icon:hover {
        color: #3498db;
    }
    .delete-icon:hover {
        color: #dc3545;
    }
    .post-content {
        font-size: 14px;
        color: #34495e;
        margin-bottom: 10px;
        word-wrap: break-word;
    }
    .post-content.short {
        max-height: 1.5em;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .toggle-content {
        cursor: pointer;
        color: #3498db;
        font-size: 12px;
        display: inline-block;
        margin-bottom: 10px;
    }
    .post-image {
        width: 80%;
        height: 200px;
        border-radius: 8px;
        margin-bottom: 10px;
        display: block;
        object-fit: cover;
    }
    .post-meta {
        font-size: 12px;
        color: #7f8c8d;
        text-align: right;
    }
    @media (max-width: 768px) {
        .posts-container {
            padding: 15px;
            margin: 10px;
        }
        .search-filter-container {
            flex-direction: column;
        }
        .post-card {
            padding: 15px;
        }
        .post-title {
            font-size: 14px;
        }
        .post-content {
            font-size: 13px;
        }
        .user-info {
            max-width: 40%;
        }
        .post-image {
            width: 250px;
            height: 150px;
        }
    }
    @media (max-width: 576px) {
        .user-name {
            font-size: 12px;
        }
        .user-role {
            font-size: 9px;
        }
        .post-title {
            font-size: 13px;
        }
        .user-image {
            width: 25px;
            height: 25px;
        }
        .action-icon {
            font-size: 14px;
        }
        .user-info {
            max-width: 50%;
        }
        .post-image {
            width: 200px;
            height: 100px;
        }
    }
</style>
<script>
    function toggleContent(postId) {
        const content = document.getElementById(`content-${postId}`);
        const toggleBtn = document.getElementById(`toggle-${postId}`);
        if (content.classList.contains('short')) {
            content.classList.remove('short');
            toggleBtn.innerHTML = '▲ Show Less';
        } else {
            content.classList.add('short');
            toggleBtn.innerHTML = '▼ Show More';
        }
    }
</script>

<div class="posts-container">
    <h2>Manage Posts</h2>

    <div class="search-filter-container sticky-top">
        <form action="" method="GET" class="d-flex flex-grow-1">
            <input type="text" name="search" class="form-control me-2" placeholder="Search by title or name" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" class="btn btn-outline-primary btn-custom">Search</button>
        </form>
        <form action="" method="GET">
            <select name="role" class="form-select" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="admin" <?php echo $filterRole === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="editor" <?php echo $filterRole === 'editor' ? 'selected' : ''; ?>>Editor</option>
                <option value="contributor" <?php echo $filterRole === 'contributor' ? 'selected' : ''; ?>>Contributor</option>
                <option value="user" <?php echo $filterRole === 'user' ? 'selected' : ''; ?>>User</option>
            </select>
        </form>
        <form action="" method="GET">
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($filterDate); ?>" onchange="this.form.submit()">
        </form>
    </div>

    <?php if (empty($posts)): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <div class="post-header">
                    <div class="user-info">
                        <?php if (!empty($post['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($post['profile_image']); ?>" alt="User Image" class="user-image">
                        <?php else: ?>
                            <img src="/assets/images/default-profile.jpg" alt="Default Profile" class="user-image">
                        <?php endif; ?>
                        <span class="user-name"><?php echo htmlspecialchars($post['name']); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($post['role']); ?></span>
                    </div>
                    <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                    <div class="post-actions">
                        <?php if ($blogController->canEditPost($post, $_SESSION['user_id'], $_SESSION['user_role'])): ?>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="action-icon" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <?php endif; ?>
                        <?php if ($blogController->canDeletePost($post, $_SESSION['user_id'], $_SESSION['user_role'])): ?>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="action-icon delete-icon" title="Delete" onclick="return confirm('Are you sure you want to delete this post?');"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="post-content <?php echo strlen($post['content']) > 50 ? 'short' : ''; ?>" id="content-<?php echo $post['id']; ?>">
                    <?php echo htmlspecialchars($post['content']); ?>
                </div>
                <?php if (strlen($post['content']) > 50): ?>
                    <span class="toggle-content" id="toggle-<?php echo $post['id']; ?>" onclick="toggleContent(<?php echo $post['id']; ?>)">▼ Show More</span>
                <?php endif; ?>
                <?php if (!empty($post['post_image'])): ?>
                    <img src="<?php echo htmlspecialchars($post['post_image']); ?>" alt="Post Image" class="post-image">
                <?php endif; ?>
                <div class="post-meta">
                    <?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>