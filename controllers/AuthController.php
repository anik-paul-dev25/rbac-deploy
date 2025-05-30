<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($name, $email, $password, $role, $autoApprove = false, $profileImage = null) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $status = $autoApprove ? 'approved' : 'pending';
        $profileImagePath = null;

        // Handle profile image upload
        if ($profileImage && $profileImage['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../Uploads/';
            
            $fileExtension = strtolower(pathinfo($profileImage['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                if (move_uploaded_file($profileImage['tmp_name'], $filePath)) {
                    $profileImagePath = '/Uploads/' . $fileName;
                }
            }
        }

        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, status, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashedPassword, $role, $status, $profileImagePath])) {
            if (!$autoApprove) {
                $this->sendAdminNotification($email);
            }
            return true;
        }
        return false;
    }

    public function login($email, $password) {
    try {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'approved') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_status'] = $user['status'];
                return 'approved';
            } elseif ($user['status'] === 'pending') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_status'] = $user['status'];
                return 'pending';
            } elseif ($user['status'] === 'rejected') {
                return 'rejected';
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

    public function logout() {
        session_destroy();
        header("Location: /login.php");
        exit;
    }

    private function sendAdminNotification($userEmail) {
        $adminEmail = 'greatgatsbyontheway2.o@gmail.com';
        $subject = "New User Registration";
        $message = "A new user with email $userEmail has registered and is awaiting approval.";

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
            $mail->addAddress($adminEmail);

            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Send email
            $mail->send();

            // Log notification in database
            $stmt = $this->pdo->prepare("INSERT INTO notifications (message, recipient_role) VALUES (?, 'admin')");
            $stmt->execute(["New user registration: $userEmail"]);
        } catch (Exception $e) {
            error_log("Failed to send email: {$mail->ErrorInfo}");
        }
    }
}
?>