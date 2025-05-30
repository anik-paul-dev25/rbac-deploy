<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">RBAC Blog</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="posts.php">Manage Posts</a>
                        </li>
                    <?php elseif ($_SESSION['user_role'] === 'editor'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="posts.php">Manage Posts</a>
                        </li>
                    <?php elseif ($_SESSION['user_role'] === 'contributor'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="posts.php">Manage Posts</a>
                        </li>
                    <?php elseif ($_SESSION['user_role'] === 'user'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user_posts.php">My Posts</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>