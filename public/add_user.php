<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

restrictAccess(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    $profileImage = isset($_FILES['profile_image']) ? $_FILES['profile_image'] : null;

    if ($name && $email && $password && $role) {
        $authController = new AuthController($pdo);
        if ($authController->register($name, $email, $password, $role, true, $profileImage)) {
            // Send approval notification
            $env = parse_ini_file(__DIR__ . '/../.env');
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $env['SMTP_HOST'];
                $mail->SMTPAuth = true;
                $mail->Username = $env['SMTP_USERNAME'];
                $mail->Password = $env['SMTP_PASSWORD'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $env['SMTP_PORT'];
                $mail->setFrom($env['SMTP_USERNAME'], 'RBAC System');
                $mail->addAddress($email);
                $mail->isHTML(false);
                $mail->Subject = "Account Approved";
                $mail->Body = "Your account has been approved by the Admin.";
                $mail->send();

                // Log notification
                $stmt = $pdo->prepare("INSERT INTO notifications (message, recipient_role) VALUES (?, 'user')");
                $stmt->execute(["Account approved for $email"]);
                
                header("Location: dashboard.php?status=added");
            } catch (Exception $e) {
                error_log("Failed to send email: {$mail->ErrorInfo}");
                header("Location: dashboard.php?status=error");
            }
        } else {
            header("Location: dashboard.php?status=error");
        }
    } else {
        header("Location: dashboard.php?status=error");
    }
}
exit;
?>