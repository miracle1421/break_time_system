<?php
require_once 'db.php';

// Set timezone to match local server/user operations
date_default_timezone_set('Asia/Manila');

// Suppress raw PHP HTML errors to prevent corrupting AJAX JSON responses
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Detect AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Standardized response handler (JSON for AJAX, HTTP Redirect for standard POST)
function respond($status, $message, $isAjax, $redirectPage = 'index.php') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message]);
        exit;
    } else {
        $param = ($status === 'success') ? 'msg' : 'error';
        header("Location: {$redirectPage}?{$param}=" . urlencode($message));
        exit;
    }
}

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond('error', 'Invalid request method.', $isAjax);
}

$action = $_POST['action'] ?? '';

switch ($action) {

    // =========================================================================
    // 1. ADD / CREATE PROFILE (Saves Barcode to MySQL)
    // =========================================================================
    case 'add_profile':
    case 'create_profile':
        $name = trim($_POST['name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $barcode = trim($_POST['barcode'] ?? '');
        $qr_code = trim($_POST['qr_code'] ?? $barcode); // Default to barcode if qr_code isn't explicitly provided

        if (empty($name)) {
            respond('error', 'Profile name is required.', $isAjax, 'create_profile.php');
        }

        try {
            // Check if barcode/qr_code is unique if provided
            if (!empty($barcode)) {
                $checkStmt = $pdo->prepare("SELECT id FROM profiles WHERE barcode = ? OR qr_code = ? LIMIT 1");
                $checkStmt->execute([$barcode, $barcode]);
                if ($checkStmt->fetch()) {
                    respond('error', 'Barcode or QR Code already exists in the database.', $isAjax, 'create_profile.php');
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO profiles (name, department, barcode, qr_code, status) 
                VALUES (?, ?, ?, ?, 'AVAILABLE')
            ");
            $stmt->execute([$name, $department, $barcode, $qr_code]);

            respond('success', 'Profile created successfully!', $isAjax, 'profiles.php');

        } catch (PDOException $e) {
            respond('error', 'Database error: ' . $e->getMessage(), $isAjax, 'create_profile.php');
        }
        break;

    // =========================================================================
    // 2. EDIT / UPDATE PROFILE (Updates Barcode in MySQL)
    // =========================================================================
    case 'edit_profile':
    case 'update_profile':
        $profile_id = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $barcode = trim($_POST['barcode'] ?? '');
        $qr_code = trim($_POST['qr_code'] ?? $barcode);

        if ($profile_id <= 0) {
            respond('error', 'Invalid profile selection.', $isAjax, 'profiles.php');
        }

        if (empty($name)) {
            respond('error', 'Profile name is required.', $isAjax, 'profiles.php');
        }

        try {
            // Check barcode uniqueness against other profiles
            if (!empty($barcode)) {
                $checkStmt = $pdo->prepare("SELECT id FROM profiles WHERE (barcode = ? OR qr_code = ?) AND id != ? LIMIT 1");
                $checkStmt->execute([$barcode, $barcode, $profile_id]);
                if ($checkStmt->fetch()) {
                    respond('error', 'Barcode or QR Code is already assigned to another profile.', $isAjax, 'profiles.php');
                }
            }

            $stmt = $pdo->prepare("
                UPDATE profiles 
                SET name = ?, department = ?, barcode = ?, qr_code = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $department, $barcode, $qr_code, $profile_id]);

            respond('success', 'Profile updated successfully!', $isAjax, 'profiles.php');

        } catch (PDOException $e) {
            respond('error', 'Database error: ' . $e->getMessage(), $isAjax, 'profiles.php');
        }
        break;

    // =========================================================================
    // 3. QUICK BARCODE SCANNER / SMART SCAN (Toggles Time-In / Time-Out)
    // =========================================================================
    case 'quick_scan':
    case 'quick_barcode_scan':
        $barcode = trim($_POST['barcode'] ?? '');

        if (empty($barcode)) {
            respond('error', 'Please scan or enter a valid barcode.', $isAjax, 'index.php');
        }

        try {
            // Flexible match across barcode, qr_code, or numerical ID
            $stmt = $pdo->prepare("
                SELECT id, name, status 
                FROM profiles 
                WHERE barcode = ? OR qr_code = ? OR id = ? 
                LIMIT 1
            ");
            $stmt->execute([$barcode, $barcode, $barcode]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                respond('error', "No profile found matching: {$barcode}", $isAjax, 'index.php');
            }

            $time_now = date('Y-m-d H:i:s');
            $status = strtoupper(trim($profile['status']));

            $pdo->beginTransaction();

            if ($status === 'ON_BREAK') {
                // --- TIME IN LOGIC ---
                $stmtLog = $pdo->prepare("
                    UPDATE records_log
                    SET time_in = ?
                    WHERE profile_id = ? AND time_in IS NULL
                    ORDER BY id DESC LIMIT 1
                ");
                $stmtLog->execute([$time_now, $profile['id']]);

                $stmtProfile = $pdo->prepare("
                    UPDATE profiles
                    SET status = 'AVAILABLE', time_out = NULL, expected_in = NULL
                    WHERE id = ?
                ");
                $stmtProfile->execute([$profile['id']]);

                $pdo->commit();
                respond('success', "Welcome back, {$profile['name']}! Time-In logged.", $isAjax, 'index.php');

            } else {
                // --- TIME OUT LOGIC ---
                $stmtUsed = $pdo->prepare("
                    SELECT SUM(TIMESTAMPDIFF(SECOND, time_out, time_in)) AS seconds_used 
                    FROM records_log 
                    WHERE profile_id = ? 
                      AND DATE(time_out) = CURDATE() 
                      AND time_in IS NOT NULL
                ");
                $stmtUsed->execute([$profile['id']]);
                $usedRow = $stmtUsed->fetch(PDO::FETCH_ASSOC);

                $seconds_used = (int)($usedRow['seconds_used'] ?? 0);
                $total_allowed_seconds = 60 * 60; // 60 minutes allowance per day
                $remaining_seconds = $total_allowed_seconds - $seconds_used;

                if ($remaining_seconds <= 0) {
                    $pdo->rollBack();
                    respond('error', "{$profile['name']} has used their full 60-minute daily break allowance.", $isAjax, 'index.php');
                }

                $break_duration_minutes = ceil($remaining_seconds / 60);
                $expected_in = date('Y-m-d H:i:s', strtotime("+{$remaining_seconds} seconds"));

                $stmtLog = $pdo->prepare("INSERT INTO records_log (profile_id, time_out, expected_in) VALUES (?, ?, ?)");
                $stmtLog->execute([$profile['id'], $time_now, $expected_in]);

                $stmtProfile = $pdo->prepare("UPDATE profiles SET status = 'ON_BREAK', time_out = ?, expected_in = ? WHERE id = ?");
                $stmtProfile->execute([$time_now, $expected_in, $profile['id']]);

                $pdo->commit();
                respond('success', "{$profile['name']} started break ({$break_duration_minutes} mins remaining today).", $isAjax, 'index.php');
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            respond('error', 'Database error: ' . $e->getMessage(), $isAjax, 'index.php');
        }
        break;

    // =========================================================================
    // 4. DELETE PROFILE
    // =========================================================================
    case 'delete_profile':
        $profile_id = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;

        if ($profile_id <= 0) {
            respond('error', 'Invalid profile ID.', $isAjax, 'profiles.php');
        }

        try {
            $stmt = $pdo->prepare("SELECT status FROM profiles WHERE id = ?");
            $stmt->execute([$profile_id]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                respond('error', 'Profile not found.', $isAjax, 'profiles.php');
            }

            if (strtoupper($profile['status']) === 'ON_BREAK') {
                respond('error', 'Cannot delete profile while on break. Time-in first.', $isAjax, 'profiles.php');
            }

            $pdo->beginTransaction();

            $stmtLog = $pdo->prepare("DELETE FROM records_log WHERE profile_id = ?");
            $stmtLog->execute([$profile_id]);

            $stmtProfile = $pdo->prepare("DELETE FROM profiles WHERE id = ?");
            $stmtProfile->execute([$profile_id]);

            $pdo->commit();

            respond('success', 'Profile deleted successfully.', $isAjax, 'profiles.php');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            respond('error', 'Database error: ' . $e->getMessage(), $isAjax, 'profiles.php');
        }
        break;

    // =========================================================================
    // 5. TIME IN (Manual Action from Dashboard)
    // =========================================================================
    case 'time_in':
        $profile_id = isset($_POST['profile_id']) ? (int)$_POST['profile_id'] : 0;

        if ($profile_id <= 0) {
            respond('error', 'Invalid profile selection.', $isAjax, 'index.php');
        }

        try {
            $time_in = date('Y-m-d H:i:s');

            $pdo->beginTransaction();

            $stmtLog = $pdo->prepare("
                UPDATE records_log
                SET time_in = ?
                WHERE profile_id = ? AND time_in IS NULL
                ORDER BY id DESC LIMIT 1
            ");
            $stmtLog->execute([$time_in, $profile_id]);

            $stmtProfile = $pdo->prepare("
                UPDATE profiles
                SET status = 'AVAILABLE', time_out = NULL, expected_in = NULL
                WHERE id = ?
            ");
            $stmtProfile->execute([$profile_id]);

            $pdo->commit();

            respond('success', 'Time-In recorded successfully!', $isAjax, 'index.php');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            respond('error', 'Error updating status: ' . $e->getMessage(), $isAjax, 'index.php');
        }
        break;

    // =========================================================================
    // 6. TIME IN VIA BARCODE SCANNER
    // =========================================================================
    case 'time_in_barcode':
        $barcode = trim($_POST['barcode'] ?? '');

        if (empty($barcode)) {
            respond('error', 'Please scan or enter a valid barcode.', $isAjax, 'time_in_barcode.php');
        }

        try {
            $stmt = $pdo->prepare("
                SELECT id, name, status 
                FROM profiles 
                WHERE barcode = ? OR qr_code = ? OR id = ? 
                LIMIT 1
            ");
            $stmt->execute([$barcode, $barcode, $barcode]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                respond('error', "No profile found matching: {$barcode}", $isAjax, 'time_in_barcode.php');
            }

            if (strtoupper($profile['status']) !== 'ON_BREAK') {
                respond('error', "{$profile['name']} is not currently on break.", $isAjax, 'time_in_barcode.php');
            }

            $time_in = date('Y-m-d H:i:s');

            $pdo->beginTransaction();

            $stmtLog = $pdo->prepare("
                UPDATE records_log
                SET time_in = ?
                WHERE profile_id = ? AND time_in IS NULL
                ORDER BY id DESC LIMIT 1
            ");
            $stmtLog->execute([$time_in, $profile['id']]);

            $stmtProfile = $pdo->prepare("
                UPDATE profiles
                SET status = 'AVAILABLE', time_out = NULL, expected_in = NULL
                WHERE id = ?
            ");
            $stmtProfile->execute([$profile['id']]);

            $pdo->commit();

            respond('success', "Welcome back, {$profile['name']}! Time-In logged.", $isAjax, 'time_in_barcode.php');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            respond('error', 'Database error: ' . $e->getMessage(), $isAjax, 'time_in_barcode.php');
        }
        break;

    // =========================================================================
    // 7. TIME OUT VIA QR SCANNER
    // =========================================================================
    case 'time_out_qr':
        $qr_code = trim($_POST['qr_code'] ?? '');

        if (empty($qr_code)) {
            respond('error', 'No QR code detected.', $isAjax, 'scanner.php');
        }

        try {
            $stmt = $pdo->prepare("SELECT id, name, status FROM profiles WHERE qr_code = ? OR barcode = ? OR id = ? LIMIT 1");
            $stmt->execute([$qr_code, $qr_code, $qr_code]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                respond('error', 'Invalid or unregistered QR code.', $isAjax, 'scanner.php');
            }

            if (strtoupper(trim($profile['status'])) === 'ON_BREAK') {
                respond('error', $profile['name'] . ' is already on break.', $isAjax, 'scanner.php');
            }

            $stmtUsed = $pdo->prepare("
                SELECT SUM(TIMESTAMPDIFF(SECOND, time_out, time_in)) AS seconds_used 
                FROM records_log 
                WHERE profile_id = ? 
                  AND DATE(time_out) = CURDATE() 
                  AND time_in IS NOT NULL
            ");
            $stmtUsed->execute([$profile['id']]);
            $usedRow = $stmtUsed->fetch(PDO::FETCH_ASSOC);

            $seconds_used = (int)($usedRow['seconds_used'] ?? 0);
            $total_allowed_seconds = 60 * 60; // 60 minutes
            $remaining_seconds = $total_allowed_seconds - $seconds_used;

            if ($remaining_seconds <= 0) {
                respond('error', $profile['name'] . ' has used their full 60-minute daily allowance.', $isAjax, 'scanner.php');
            }

            $break_duration_minutes = ceil($remaining_seconds / 60);

            $time_out = date('Y-m-d H:i:s');
            $expected_in = date('Y-m-d H:i:s', strtotime("+{$remaining_seconds} seconds"));

            $pdo->beginTransaction();

            $stmtLog = $pdo->prepare("INSERT INTO records_log (profile_id, time_out, expected_in) VALUES (?, ?, ?)");
            $stmtLog->execute([$profile['id'], $time_out, $expected_in]);

            $stmtProfile = $pdo->prepare("UPDATE profiles SET status = 'ON_BREAK', time_out = ?, expected_in = ? WHERE id = ?");
            $stmtProfile->execute([$time_out, $expected_in, $profile['id']]);

            $pdo->commit();

            respond('success', $profile['name'] . ' started break (' . $break_duration_minutes . ' mins remaining today).', $isAjax, 'scanner.php');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            respond('error', 'Database error: ' . $e->getMessage(), $isAjax, 'scanner.php');
        }
        break;

    default:
        respond('error', 'Invalid action execution.', $isAjax, 'index.php');
        break;
}