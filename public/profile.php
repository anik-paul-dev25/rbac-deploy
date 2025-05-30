<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../config/db.php';

restrictAccess(['admin', 'editor', 'contributor', 'user']);
$userController = new UserController($pdo);
$user = $userController->getUserById($_SESSION['user_id']);
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    $profileImage = isset($_FILES['profile_image']) ? $_FILES['profile_image'] : null;

    if ($name && $email) {
        if ($userController->updateProfile($_SESSION['user_id'], $name, $email, $password, $profileImage)) {
            $success = true;
            $user = $userController->getUserById($_SESSION['user_id']); // Refresh user data
        } else {
            $error = "Profile update failed. Email may already be in use.";
        }
    } else {
        $error = "Name and email are required.";
    }
}
?>

<?php include __DIR__ . '/../views/layout/header.php'; ?>
<style>
    body {
        background-color: #f5f7fa;
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 0;
        padding: 0;
    }
    .profile-container {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        margin: 30px auto;
        max-width: 700px;
        width: 90%;
    }
    h2 {
        color: #2c3e50;
        font-size: 28px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 25px;
    }
    .form-label {
        font-weight: 500;
        color: #34495e;
        font-size: 16px;
    }
    .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 12px;
        font-size: 15px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 8px rgba(52, 152, 219, 0.2);
        outline: none;
    }
    .btn-primary {
        background-color: #3498db;
        border: none;
        border-radius: 6px;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 500;
        transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        width: 100%;
        max-width: 200px;
    }
    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }
    .alert {
        border-radius: 6px;
        padding: 15px;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .profile-img-container {
        text-align: center;
        margin-bottom: 30px;
    }
    .profile-img {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #3498db;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    @media (max-width: 768px) {
        .profile-container {
            padding: 20px;
            margin: 20px auto;
            width: 95%;
        }
        h2 {
            font-size: 24px;
        }
        .form-label {
            font-size: 14px;
        }
        .form-control {
            font-size: 14px;
            padding: 10px;
        }
        .btn-primary {
            font-size: 14px;
            padding: 10px 20px;
        }
        .profile-img {
            width: 140px;
            height: 140px;
        }
    }
    @media (max-width: 576px) {
        .profile-container {
            padding: 15px;
            margin: 15px auto;
        }
        h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .form-label {
            font-size: 13px;
        }
        .form-control {
            font-size: 13px;
            padding: 8px;
        }
        .btn-primary {
            font-size: 13px;
            padding: 8px 16px;
        }
        .profile-img {
            width: 100px;
            height: 100px;
        }
    }
</style>

<div class="profile-container">
    <h2>Your Profile</h2>
    <?php if ($success): ?>
        <div class="alert alert-success">Profile updated successfully!</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <div class="profile-img-container">
        <?php if ($user['profile_image']): ?>
            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="profile-img">
        <?php else: ?>
            <img src="/assets/images/default-profile.jpg" alt="Default Profile">
        <?php endif; ?>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">New Password (Optional)</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
        </div>
        <div class="form-group">
            <label for="profile_image" class="form-label">Profile Image (Optional)</label>
            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../views/layout/footer.php'; ?>