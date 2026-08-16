<?php
// php/academic_helper.php — academic session + hostel helpers.
// Requires config.php to have been included (provides $conn).

if (!function_exists('pt_active_session')) {
    // Returns the currently active academic session row, or null.
    // Cached per request.
    function pt_active_session() {
        static $cache = false;
        if ($cache !== false) {
            return $cache;
        }
        $conn = $GLOBALS['conn'];
        $row = null;
        if (isset($conn)) {
            $stmt = $conn->prepare("SELECT id, name, is_active, activated_at FROM academic_session WHERE is_active = 1 LIMIT 1");
            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                }
                $stmt->close();
            }
        }
        $cache = $row;
        return $row;
    }
}

if (!function_exists('pt_active_session_id')) {
    function pt_active_session_id() {
        $s = pt_active_session();
        return $s ? (int)$s['id'] : 0;
    }
}

if (!function_exists('pt_active_hostel')) {
    // Returns the "default" hostel (first active), used for admin management.
    function pt_active_hostel() {
        static $cache = false;
        if ($cache !== false) {
            return $cache;
        }
        $conn = $GLOBALS['conn'];
        $row = null;
        if (isset($conn)) {
            $stmt = $conn->prepare("SELECT id, name, address, status FROM hostel WHERE status = 'active' ORDER BY id ASC LIMIT 1");
            if ($stmt) {
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                }
                $stmt->close();
            }
        }
        $cache = $row;
        return $row;
    }
}

if (!function_exists('pt_all_hostels')) {
    function pt_all_hostels() {
        static $cache = false;
        if ($cache !== false) {
            return $cache;
        }
        $conn = $GLOBALS['conn'];
        $list = array();
        if (isset($conn)) {
            $res = $conn->query("SELECT id, name, address, status FROM hostel ORDER BY id ASC");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $list[] = $row;
                }
            }
        }
        $cache = $list;
        return $list;
    }
}

if (!function_exists('pt_all_sessions')) {
    function pt_all_sessions() {
        static $cache = false;
        if ($cache !== false) {
            return $cache;
        }
        $conn = $GLOBALS['conn'];
        $list = array();
        if (isset($conn)) {
            $res = $conn->query("SELECT id, name, is_active, activated_at, created_at FROM academic_session ORDER BY id ASC");
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $list[] = $row;
                }
            }
        }
        $cache = $list;
        return $list;
    }
}
