<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function restrictAccess($allowedRoles = []) {

    // Log redirect attempts
    error_log("restrictAccess called for URI: " . $_SERVER['REQUEST_URI'] . ", User Role: " . ($_SESSION['user_role'] ?? 'none') . ", User Status: " . ($_SESSION['user_status'] ?? 'none'));
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit;
    }

    // Check if user role is allowed
    $userRole = $_SESSION['user_role'] ?? 'pending';
    if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles)) {
        // Prevent redirect loop by checking current URI
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($currentUri !== '/dashboard.php') {
            header("Location: /dashboard.php");
            exit;
        }
    }

    // Check user status
    $userStatus = $_SESSION['user_status'] ?? 'pending';
    if ($userStatus === 'pending' && $currentUri !== '/not_approved.php') {
        header("Location: /not_approved.php");
        exit;
    } elseif ($userStatus === 'rejected') {
        session_destroy();
        header("Location: /login.php?error=rejected");
        exit;
    }
}