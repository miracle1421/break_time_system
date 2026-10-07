<?php
require_once 'db.php';

$createdProfile = null;

// Handle profile creation on submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $userBarcode = trim($_POST['barcode'] ?? '');
    
    // Generate a permanent unique QR token
    $qrCode = 'EMP-' . strtoupper(bin2hex(random_bytes(6)));

    $photoData = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);
    }

    if (!empty($name) && !empty($department)) {
        try {
            $pdo->beginTransaction();

            // Insert initial record into database
            $stmt = $pdo->prepare("INSERT INTO profiles (name, department, qr_code, barcode, status, photo) VALUES (?, ?, ?, ?, 'AVAILABLE', ?)");
            $stmt->execute([$name, $department, $qrCode, $userBarcode, $photoData]);
            
            $newId = $pdo->lastInsertId();

            // If barcode field was left empty, generate formatted BC0000XX barcode and persist to SQL
            if (empty($userBarcode)) {
                $finalBarcode = 'BC' . str_pad($newId, 6, '0', STR_PAD_LEFT);
                $updateStmt = $pdo->prepare("UPDATE profiles SET barcode = ? WHERE id = ?");
                $updateStmt->execute([$finalBarcode, $newId]);
            }

            $pdo->commit();

            // Fetch newly created profile to render details
            $fetchStmt = $pdo->prepare("SELECT * FROM profiles WHERE id = ?");
            $fetchStmt->execute([$newId]);
            $createdProfile = $fetchStmt->fetch();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Profile</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        :root {
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --primary: #2563eb;
            --text: #1f2937;
            --subtext: #6b7280;
            --border-color: #e5e7eb;
            --info-bg: #f9fafb;
            --shadow: rgba(0, 0, 0, 0.08);
            --success: #16a34a;
        }

        [data-theme="dark"] {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --primary: #3b82f6;
            --text: #f8fafc;
            --subtext: #94a3b8;
            --border-color: #334155;
            --info-bg: #0f172a;
            --shadow: rgba(0, 0, 0, 0.4);
            --success: #22c55e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text);
            margin: 0;
            padding: 20px 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid var(--border-color);
            width: 100%;
            max-width: 450px;
            box-sizing: border-box;
            text-align: left;
        }

        .card.success-card { text-align: center; }
        h2 { margin-top: 0; font-size: 1.35rem; margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 6px; }

        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            background-color: var(--info-bg);
            color: var(--text);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            min-height: 48px;
        }

        .avatar-preview-container {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .avatar-preview {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border-color);
            background-color: var(--info-bg);
        }

        #qrcode {
            display: flex;
            justify-content: center;
            margin: 20px 0 10px 0;
        }

        .barcode-container {
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
            border: 1px solid var(--border-color);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background-color: var(--primary);
            color: white;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            min-height: 48px;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            box-sizing: border-box;
        }

        .btn-success { background-color: var(--success); }

        .btn-cancel {
            display: block;
            text-align: center;
            color: var(--subtext);
            text-decoration: none;
            margin-top: 14px;
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php if ($createdProfile): ?>
    <!-- SUCCESS DISPLAY VIEW -->
    <div class="card success-card">
        <h2 style="color: var(--success);">Profile Created Successfully!</h2>

        <div class="avatar-preview-container">
            <img src="view_photo.php?id=<?= $createdProfile['id'] ?>" class="avatar-preview" alt="Photo">
        </div>

        <h3 style="margin: 5px 0;"><?= htmlspecialchars($createdProfile['name']) ?></h3>
        <p style="color: var(--subtext); margin-top: 0;"><?= htmlspecialchars($createdProfile['department']) ?></p>

        <!-- QR Render -->
        <div id="qrcode"></div>
        <small style="color: var(--subtext); font-weight: bold; display: block; margin-bottom: 10px;">
            QR: <?= htmlspecialchars($createdProfile['qr_code']) ?>
        </small>

        <!-- Barcode Render -->
        <div class="barcode-container">
            <svg id="barcode-canvas"></svg>
            <div style="font-size: 0.8rem; font-weight: bold; color: #000;">
                <?= htmlspecialchars($createdProfile['barcode']) ?>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <a href="print_qr.php?id=<?= $createdProfile['id'] ?>" target="_blank" class="btn-submit btn-success">🖨️ Print ID Card</a>
            <a href="create_profile.php" class="btn-submit" style="background-color: var(--subtext);">Add Another Profile</a>
            <a href="profiles.php" class="btn-cancel">Go to Profiles List →</a>
        </div>
    </div>

    <script>
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= $createdProfile['qr_code'] ?>",
        width: 140,
        height: 140
    });

    JsBarcode("#barcode-canvas", "<?= $createdProfile['barcode'] ?>", {
        format: "CODE128",
        displayValue: false,
        height: 40,
        margin: 0
    });
    </script>

<?php else: ?>
    <!-- FORM VIEW -->
    <div class="card">
        <h2>Create New Profile</h2>

        <?php if (!empty($error)): ?>
            <p style="color: #ef4444; font-size: 0.9rem;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="create_profile.php" method="POST" enctype="multipart/form-data">
            <div class="avatar-preview-container">
                <img id="preview" src="view_photo.php" alt="Preview" class="avatar-preview">
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. John Doe" required>
            </div>

            <div class="form-group">
                <label>Department</label>
                <input type="text" name="department" placeholder="e.g. IT Operations" required>
            </div>

            <div class="form-group">
                <label>Barcode / Employee ID</label>
                <input type="text" name="barcode" placeholder="e.g. BC000042 (Optional)">
                <small style="color: var(--subtext); font-size: 0.78rem;">Leave empty to auto-generate (BC0000XX).</small>
            </div>

            <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="photo" id="photoInput" accept="image/*" onchange="previewImage(event)">
            </div>

            <button type="submit" class="btn-submit">Save Profile & Generate Codes</button>
            <a href="profiles.php" class="btn-cancel">Cancel</a>
        </form>
    </div>
<?php endif; ?>

<script>
const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);

function previewImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>