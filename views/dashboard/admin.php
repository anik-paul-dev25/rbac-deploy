<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../controllers/BlogController.php';
require_once __DIR__ . '/../../config/db.php';

restrictAccess(['admin']);
$userController = new UserController($pdo);
$blogController = new BlogController($pdo);
$pendingUsers = $userController->getPendingUsers();

// Handle search and filter
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterRole = isset($_GET['role']) ? $_GET['role'] : '';
$allUsers = $userController->getAllUsers();

if ($searchTerm) {
    $allUsers = $userController->searchUsers($searchTerm);
} elseif ($filterRole) {
    $allUsers = $userController->filterUsersByRole($filterRole);
} else {
    $allUsers = $userController->getAllUsers();
}

$posts = $blogController->getPosts();
?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<style>
    body {
        background-color: #f5f7fa;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    .dashboard-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 1200px;
    }
    h2, h3 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-weight: 600;
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
    .table {
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
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
    .search-filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    .form-control, .form-select {
        border-radius: 5px;
        border: 1px solid #ced4da;
        padding: 10px;
        font-size: 14px;
    }
    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    .modal-header {
        background-color: #3498db;
        color: #fff;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    .modal-body {
        padding: 20px;
    }
    .modal-footer {
        border-top: none;
        padding: 15px 20px;
    }
    .alert {
        border-radius: 5px;
        margin-bottom: 20px;
    }
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 15px;
            margin: 10px;
        }
        .search-filter-container {
            flex-direction: column;
        }
        .table {
            font-size: 14px;
        }
        .btn-custom {
            padding: 6px 12px;
            font-size: 12px;
        }
        .form-control, .form-select {
            font-size: 12px;
        }
    }
    @media (max-width: 576px) {
        .table th, .table td {
            padding: 8px;
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
    <h2>Admin Dashboard</h2>

    <!-- Add User Button and Modal -->
    <button class="btn btn-primary btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Add New User</button>
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="add_user.php" method="POST" enctype="multipart/form-data">
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
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="contributor">Contributor</option>
                                <option value="user" selected>User</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image (Optional)</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-custom" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary btn-custom">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter-container">
        <form action="" method="GET" class="d-flex flex-grow-1">
            <input type="text" name="search" class="form-control me-2" placeholder="Search by name or email" value="<?php echo htmlspecialchars($searchTerm); ?>">
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
    </div>

    <!-- Status Alerts -->
    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-<?php echo $_GET['status'] === 'added' || $_GET['status'] === 'approved' ? 'success' : 'danger'; ?>">
            <?php
            if ($_GET['status'] === 'added') {
                echo 'User added successfully!';
            } elseif ($_GET['status'] === 'approved') {
                echo 'User approved successfully!';
            } elseif ($_GET['status'] === 'rejected') {
                echo 'User rejected successfully!';
            } else {
                echo 'Action failed.';
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Pending Users -->
    <h3>Pending Users</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pendingUsers as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <form action="approve_user.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-success btn-custom">Approve</button>
                        </form>
                        <form action="reject_user.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-custom">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Manage Users -->
     <h3>Manage Users</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allUsers as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-custom">Edit</a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-custom">Delete</a>
                        <form action="update_role.php" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="role" onchange="this.form.submit()" class="form-select d-inline w-auto">
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                <option value="contributor" <?php echo $user['role'] === 'contributor' ? 'selected' : ''; ?>>Contributor</option>
                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Blog Posts -->
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
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-custom">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="add_post.php" class="btn btn-success btn-custom">Add Post</a>
</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>