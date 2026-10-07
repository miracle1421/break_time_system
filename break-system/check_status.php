<?php
require_once 'db.php';
header('Content-Type: application/json');

$barcode = trim($_GET['barcode'] ?? '');

if (empty($barcode)) {
    echo json_encode(['status' => 'error', 'message' => 'Empty barcode.']);
    exit;
}

$stmt = $pdo->prepare("SELECT name, status FROM profiles WHERE barcode = ? OR qr_code = ? OR id = ? LIMIT 1");
$stmt->execute([$barcode, $barcode, $barcode]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($profile) {
    echo json_encode([
        'status' => 'success',
        'name' => $profile['name'],
        'profile_status' => strtoupper(trim($profile['status']))
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Profile not found.']);
}