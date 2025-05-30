<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getPendingUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE status = 'pending'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchUsers($searchTerm) {
        $searchTerm = "%$searchTerm%";
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ?");
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterUsersByRole($role) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveUser($userId) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
            $success = $stmt->execute([$userId]);
            if ($success && $stmt->rowCount() > 0) {
                $stmt = $this->pdo->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $this->sendApprovalNotification($user['email']);
                    $this->pdo->commit();
                    return true;
                }
            }
            $this->pdo->rollBack();
            return false;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Approval failed: " . $e->getMessage());
            return false;
        }
    }

    public function rejectUser($userId) {
        $stmt = $this->pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function updateUserRole($userId, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $userId]);
    }

    public function editUser($userId, $name, $email) {
        $stmt = $this->pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $userId]);
    }

    public function deleteUser($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function getUserById($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($userId, $name, $email, $password = null, $profileImage = null) {
        $params = [$name, $email];
        $query = "UPDATE users SET name = ?, email = ?";

        if ($password) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query .= ", password = ?";
            $params[] = $hashedPassword;
        }

        if ($profileImage && $profileImage['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../Uploads/';
            $fileExtension = strtolower(pathinfo($profileImage['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                if (move_uploaded_file($profileImage['tmp_name'], $filePath)) {
                    $profileImagePath = '/Uploads/' . $fileName;
                    $query .= ", profile_image = ?";
                    $params[] = $profileImagePath;
                }
            }
        }

        $query .= " WHERE id = ?";
        $params[] = $userId;

        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    private function sendApprovalNotification($userEmail) {
    // Fetch user details
    $stmt = $this->pdo->prepare("SELECT name, email FROM users WHERE email = ?");
    $stmt->execute([$userEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        error_log("User not found for email: $userEmail");
        return;
    }

    $subject = "Account Approved - Welcome to RBAC System!";
    $loginLink = "http://localhost:8000/login.php"; // Adjust to your domain if not localhost
    $message = "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <h2>Congratulations, {$user['name']}!</h2>
            <p>Your account with email <strong>{$user['email']}</strong> has been approved by the Admin.</p>
            <p>We are thrilled to welcome you to the RBAC System!</p>
            <p>Please <a href='$loginLink' target='_blank' style='color: #17a2b8; text-decoration: none; font-weight: bold;'>click here to login</a> and start exploring.</p>
            <p>Best regards,<br>RBAC System Team</p>
        </body>
        </html>
    ";

    // Load .env variables
    $env = parse_ini_file('../.env');

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $env['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $env['SMTP_USERNAME'];
        $mail->Password = $env['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $env['SMTP_PORT'];

        // Recipients
        $mail->setFrom($env['SMTP_USERNAME'], 'RBAC System');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true); // Changed to HTML for link and formatting
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = "Congratulations, {$user['name']}!\nYour account with email {$user['email']} has been approved by the Admin.\nPlease visit $loginLink to login.\nBest regards,\nRBAC System Team";

        // Send email
        $mail->send();

        // Log notification in database
        $stmt = $this->pdo->prepare("INSERT INTO notifications (message, recipient_role) VALUES (?, 'user')");
        $stmt->execute(["Account approved for $userEmail"]);
    } catch (Exception $e) {
        error_log("Failed to send email: {$mail->ErrorInfo}");
    }
}
}
?>