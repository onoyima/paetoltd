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

if (!function_exists('pt_derive_bed_space')) {
    // Derives a human-readable bed space from a room_bunk string.
    // The last 2 characters encode the bunk + position, e.g. "ROOM 323-2U"
    // -> "Bunk 2 Up", "ROOM 323-1D" -> "Bunk 1 Down" (U/D map to Up/Down).
    // Any other trailing pair (e.g. "B1") is kept verbatim as the bunk label.
    function pt_derive_bed_space($room_bunk) {
        $bunk = trim((string)$room_bunk);
        if ($bunk === '') {
            return '';
        }
        $tail = substr($bunk, -2);
        if (strlen($tail) !== 2) {
            return '';
        }
        $bunkNo = $tail[0];
        $position = strtoupper($tail[1]);
        if ($position === 'U') {
            return "Bunk $bunkNo Up";
        }
        if ($position === 'D') {
            return "Bunk $bunkNo Down";
        }
        // "B2"/"B4" style labels: the B stands for Bunk.
        if ($bunkNo === 'B' && ctype_digit($position)) {
            return "Bunk $position";
        }
        return $tail;
    }
}
