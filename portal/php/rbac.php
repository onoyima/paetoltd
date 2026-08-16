<?php
// php/rbac.php — Role-Based Access Control helpers for the admin area.
// Roles: superadmin (full control), admin (standard management), staff (read-only).

if (!function_exists('pt_admin_roles')) {
    function pt_admin_roles() {
        return array('superadmin', 'admin', 'staff');
    }
}

if (!function_exists('pt_valid_admin_role')) {
    function pt_valid_admin_role($role) {
        return in_array($role, pt_admin_roles(), true);
    }
}

if (!function_exists('pt_permissions')) {
    function pt_permissions($role) {
        $map = array(
            'superadmin' => array('dashboard', 'manage_hostel', 'manage_session', 'assign_room', 'confirm_payment', 'list_student', 'view_hostel', 'reset_password', 'create_admin'),
            'admin'      => array('dashboard', 'manage_hostel', 'manage_session', 'assign_room', 'confirm_payment', 'list_student', 'view_hostel', 'reset_password'),
            'staff'      => array('dashboard', 'list_student', 'view_hostel'),
        );
        return isset($map[$role]) ? $map[$role] : array();
    }
}

if (!function_exists('pt_is_admin')) {
    function pt_is_admin() {
        return isset($_SESSION['user_id'])
            && isset($_SESSION['role'])
            && pt_valid_admin_role($_SESSION['role']);
    }
}

if (!function_exists('pt_can')) {
    function pt_can($permission) {
        if (!pt_is_admin()) {
            return false;
        }
        return in_array($permission, pt_permissions($_SESSION['role']), true);
    }
}

if (!function_exists('pt_role_label')) {
    function pt_role_label($role) {
        $labels = array(
            'superadmin' => 'Super Admin',
            'admin'      => 'Administrator',
            'staff'      => 'Staff',
        );
        return isset($labels[$role]) ? $labels[$role] : ucfirst($role);
    }
}

// Gate for HTML pages: requires a valid admin session.
// Optionally requires a specific permission; otherwise redirects to the dashboard.
if (!function_exists('pt_require_page')) {
    function pt_require_page($permission = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!pt_is_admin()) {
            header('Location: admin_login.html');
            exit;
        }
        if (isset($_SESSION['timeout']) && $_SESSION['timeout'] < time()) {
            session_unset();
            session_destroy();
            header('Location: admin_login.html');
            exit;
        }
        $_SESSION['timeout'] = time() + 1800;
        if ($permission !== null && !pt_can($permission)) {
            header('Location: admin-dashboard.php');
            exit;
        }
    }
}

// Gate for AJAX/JSON endpoints: requires a specific permission, exits with JSON on failure.
if (!function_exists('pt_require')) {
    function pt_require($permission) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!pt_can($permission)) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'message' => 'Access denied. You do not have permission for this action.'));
            exit;
        }
        if (isset($_SESSION['timeout']) && $_SESSION['timeout'] < time()) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'message' => 'Session expired. Please log in again.'));
            exit;
        }
        $_SESSION['timeout'] = time() + 1800;
    }
}
?>
