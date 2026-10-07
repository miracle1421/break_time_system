<?php
require_once 'db.php';

// Fetch all profiles from database
$stmt = $pdo->query("SELECT id, name, department, qr_code, barcode, status FROM profiles ORDER BY id DESC");
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiles List</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text: #f8fafc;
            --subtext: #94a3b8;
            --border-color: #334155;
            --primary: #3b82f6;
            --warning: #f59e0b;
            --danger: #ef4444;
            --success: #22c55e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text);
            margin: 0;
            padding: 24px;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .btn-add {
            background-color: var(--primary);
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .profile-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 200px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-available { background-color: #dcfce7; color: #15803d; }
        .status-break { background-color: #fef3c7; color: #b45309; }

        .code-box {
            background: #0f172a;
            border: 1px solid var(--border-color);
            padding: 8px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .barcode-card {
            background: #ffffff;
            padding: 6px 10px;
            border-radius: 6px;
            text-align: center;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 8px 14px;
            border-radius: 6px;
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .btn-print { background-color: var(--primary); }
        .btn-edit { background-color: var(--warning); }
        .btn-delete { background-color: var(--danger); }
    </style>
</head>
<body>

<div class="header-bar">
    <h2>Registered Profiles</h2>
    <a href="create_profile.php" class="btn-add">+ Create Profile</a>
</div>

<?php foreach ($profiles as $profile): ?>
    <?php 
        // Fallback calculation for legacy rows where barcode wasn't set in DB
        $displayBarcode = !empty($profile['barcode']) 
            ? $profile['barcode'] 
            : 'BC' . str_pad($profile['id'], 6, '0', STR_PAD_LEFT);
    ?>
    <div class="profile-card">
        <!-- Info Block -->
        <div class="profile-info">
            <img src="view_photo.php?id=<?= $profile['id'] ?>" class="avatar" alt="Avatar">
            <div>
                <h3 style="margin: 0; font-size: 1.1rem;"><?= htmlspecialchars($profile['name']) ?></h3>
                <small style="color: var(--subtext);"><?= htmlspecialchars($profile['department']) ?></small>
            </div>
        </div>

        <!-- Status -->
        <div>
            <span class="status-badge <?= strtoupper($profile['status']) === 'ON_BREAK' ? 'status-break' : 'status-available' ?>">
                <?= strtoupper($profile['status']) === 'ON_BREAK' ? 'ON BREAK' : 'AVAILABLE' ?>
            </span>
        </div>

        <!-- QR Code Block -->
        <div class="code-box">
            <div class="qr-target" data-qr="<?= htmlspecialchars($profile['qr_code']) ?>"></div>
            <small style="font-size: 0.75rem; color: var(--subtext);"><?= htmlspecialchars($profile['qr_code']) ?></small>
        </div>

        <!-- Barcode Block -->
        <div class="barcode-card">
            <svg class="barcode-target" data-barcode="<?= htmlspecialchars($displayBarcode) ?>"></svg>
            <div style="font-size: 0.75rem; font-weight: bold; color: #000; margin-top: -2px;">
                <?= htmlspecialchars($displayBarcode) ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="print_qr.php?id=<?= $profile['id'] ?>" target="_blank" class="btn-action btn-print">🖨️ Print ID Card</a>
            <a href="edit_profile.php?id=<?= $profile['id'] ?>" class="btn-action btn-edit">Edit</a>
            
            <form action="process.php" method="POST" style="margin:0;" onsubmit="return confirm('Delete profile?');">
                <input type="hidden" name="action" value="delete_profile">
                <input type="hidden" name="profile_id" value="<?= $profile['id'] ?>">
                <button type="submit" class="btn-action btn-delete">Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<script>
// Render QR Codes
document.querySelectorAll('.qr-target').forEach(el => {
    const qrText = el.getAttribute('data-qr');
    if (qrText) {
        new QRCode(el, {
            text: qrText,
            width: 40,
            height: 40
        });
    }
});

// Render Barcodes
document.querySelectorAll('.barcode-target').forEach(el => {
    const code = el.getAttribute('data-barcode');
    if (code) {
        JsBarcode(el, code, {
            format: "CODE128",
            displayValue: false,
            height: 32,
            width: 1.5,
            margin: 0
        });
    }
});
</script>

</body>
</html>