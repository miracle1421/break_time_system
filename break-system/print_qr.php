<?php
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid Profile ID.");
}

// Fetch profile details
$stmt = $pdo->prepare("SELECT * FROM profiles WHERE id = ?");
$stmt->execute([$id]);
$profile = $stmt->fetch();

if (!$profile) {
    die("Profile not found.");
}

// Fallback value for barcode if empty
$barcodeValue = !empty($profile['barcode']) 
    ? $profile['barcode'] 
    : ('BC' . str_pad($profile['id'], 6, '0', STR_PAD_LEFT));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print ID Badge - <?= htmlspecialchars($profile['name']) ?></title>
    
    <!-- Code Generators -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Control Panel (Hidden when printing) */
        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 0.95rem;
            font-weight: bold;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print { background-color: #2563eb; }
        .btn-back { background-color: #4b5563; }

        /* ID Badge Canvas */
        .id-badge {
            width: 86mm;
            height: 125mm;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 8mm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 10mm 6mm 6mm 6mm;
            position: relative;
            overflow: hidden;
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 10px 10px;
        }

        /* Top Header Color Bar */
        .badge-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10mm;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6);
        }

        /* Profile Photo Container */
        .photo-container {
            width: 30mm;
            height: 30mm;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            margin-top: 2mm;
            z-index: 1;
            background: #f9fafb;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Employee Meta Details */
        .details {
            text-align: center;
            margin: 2mm 0;
        }

        .details .name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .details .department {
            font-size: 0.85rem;
            font-weight: 600;
            color: #2563eb;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* Dual Code Output Layout */
        .codes-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2mm;
            background: #ffffff;
            padding: 3mm;
            border-radius: 6mm;
            border: 1px solid #e5e7eb;
        }

        .qr-box {
            width: 26mm;
            height: 26mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box img {
            width: 100% !important;
            height: 100% !important;
        }

        .barcode-box {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: visible;
        }

        .barcode-svg {
            max-width: 100%;
            height: auto;
        }

        /* Footer Stamp */
        .badge-footer {
            font-size: 0.6rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        /* Dedicated Print Stylesheet Overrides */
        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
                display: block;
            }

            .no-print {
                display: none !important;
            }

            .id-badge {
                box-shadow: none;
                border: 1px solid #000;
                margin: 0;
                page-break-inside: avoid;
            }

            @page {
                size: A5 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- Non-printable Header Navigation -->
    <div class="no-print">
        <a href="profiles.php" class="btn btn-back">← Back to Profiles</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Badge</button>
    </div>

    <!-- Printable ID Card Badge Container -->
    <div class="id-badge">
        <div class="badge-header"></div>

        <!-- Profile Photo -->
        <div class="photo-container">
            <img src="view_photo.php?id=<?= $profile['id'] ?>" alt="Profile Photo">
        </div>

        <!-- Name & Department -->
        <div class="details">
            <div class="name"><?= htmlspecialchars($profile['name']) ?></div>
            <div class="department"><?= htmlspecialchars($profile['department']) ?></div>
        </div>

        <!-- QR Code and 1D Barcode Section -->
        <div class="codes-container">
            <!-- QR Code -->
            <div class="qr-box" id="qrcode"></div>

            <!-- 1D Barcode -->
            <div class="barcode-box">
                <svg id="barcode" class="barcode-svg"></svg>
            </div>
        </div>

        <div class="badge-footer">
            Official Access Identification
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Generate QR Code
            <?php if (!empty($profile['qr_code'])): ?>
                new QRCode(document.getElementById("qrcode"), {
                    text: <?= json_encode($profile['qr_code']) ?>,
                    width: 100,
                    height: 100,
                    correctLevel : QRCode.CorrectLevel.H
                });
            <?php endif; ?>

            // 2. Generate 1D Code128 Barcode
            JsBarcode("#barcode", <?= json_encode($barcodeValue) ?>, {
                format: "CODE128",
                width: 1.4,
                height: 35,
                fontSize: 12,
                margin: 0,
                displayValue: true
            });
        });
    </script>
</body>
</html>