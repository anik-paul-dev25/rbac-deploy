<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

class BlogController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addPost($title, $content, $userId, $postImage = null) {
        $postImagePath = null;

        if ($postImage && $postImage['error'] === UPLOAD_ERR_OK) {
            
            $uploadDir = __DIR__ . '/../Uploads/';
            
            $fileExtension = strtolower(pathinfo($postImage['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                if (move_uploaded_file($postImage['tmp_name'], $filePath)) {
                    $postImagePath = '/Uploads/' . $fileName;
                }
            }
        }

        $stmt = $this->pdo->prepare("INSERT INTO blog_posts (title, content, user_id, post_image) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$title, $content, $userId, $postImagePath]);
    }

    public function editPost($postId, $title, $content, $postImage = null) {
        $params = [$title, $content];
        $query = "UPDATE blog_posts SET title = ?, content = ?";

        if ($postImage && $postImage['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../Uploads/';
            $fileExtension = strtolower(pathinfo($postImage['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                if (move_uploaded_file($postImage['tmp_name'], $filePath)) {
                    $postImagePath = '/Uploads/' . $fileName;
                    $query .= ", post_image = ?";
                    $params[] = $postImagePath;
                }
            }
        }

        $query .= " WHERE id = ?";
        $params[] = $postId;

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    public function deletePost($postId) {
        $stmt = $this->pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    public function getPosts() {
        $stmt = $this->pdo->prepare("SELECT bp.*, u.name, u.role, u.profile_image FROM blog_posts bp JOIN users u ON bp.user_id = u.id ORDER BY bp.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchPosts($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $stmt = $this->pdo->prepare("SELECT bp.*, u.name, u.role, u.profile_image FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE bp.title LIKE ? OR u.name LIKE ? ORDER BY bp.created_at DESC");
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterPostsByRole($role) {
        $stmt = $this->pdo->prepare("SELECT bp.*, u.name, u.role, u.profile_image FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE u.role = ? ORDER BY bp.created_at DESC");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterPostsByDate($date) {
        $stmt = $this->pdo->prepare("SELECT bp.*, u.name, u.role, u.profile_image FROM blog_posts bp JOIN users u ON bp.user_id = u.id WHERE DATE(bp.created_at) = ? ORDER BY bp.created_at DESC");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function canEditPost($post, $userId, $userRole) {
        if ($userRole === 'admin' || $userRole === 'editor') {
            return true;
        }
        if ($userRole === 'contributor') {
            return true; // Contributors can edit all posts
        }
        if ($userRole === 'user' && $post['user_id'] == $userId) {
            return true; // Users can edit their own posts
        }
        return false;
    }

    public function canDeletePost($post, $userId, $userRole) {
        if ($userRole === 'admin' || $userRole === 'editor') {
            return true;
        }
        if (($userRole === 'contributor' || $userRole === 'user') && $post['user_id'] == $userId) {
            return true; // Contributors and users can delete their own posts
        }
        return false;
    }
}
?>