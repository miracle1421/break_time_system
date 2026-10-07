<?php
require_once 'db.php';

try {
    // 1. Fetch profiles where barcode is missing or empty
    $stmt = $pdo->query("SELECT id FROM profiles WHERE barcode IS NULL OR barcode = '' OR barcode = 'N/A'");
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($profiles)) {
        echo "All profile barcodes are already populated in MySQL!";
        exit;
    }

    $pdo->beginTransaction();

    // 2. Prepare update statement
    $updateStmt = $pdo->prepare("UPDATE profiles SET barcode = ? WHERE id = ?");

    $count = 0;
    foreach ($profiles as $profile) {
        $generatedBarcode = 'BC' . str_pad($profile['id'], 6, '0', STR_PAD_LEFT);
        $updateStmt->execute([$generatedBarcode, $profile['id']]);
        $count++;
    }

    $pdo->commit();

    echo "Successfully updated and stored {$count} barcodes directly in your MySQL database!";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error updating database: " . $e->getMessage();
}