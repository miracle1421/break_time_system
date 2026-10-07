<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - Break Monitoring</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .scanner-container {
            width: 100%;
            max-width: 500px;
            background: #1e293b;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            text-align: center;
            box-sizing: border-box;
            position: relative;
        }

        #reader {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            background: #000;
        }

        /* Alert notifications */
        .alert-msg {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: bold;
            display: none;
            word-wrap: break-word;
        }
        .alert-danger { background-color: #fee2e2; color: #991b1b; }
        .alert-success { background-color: #dcfce7; color: #166534; }

        .back-link {
            color: #94a3b8;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 16px;
        }
        .back-link:hover {
            color: #f8fafc;
        }

        /* Modal Popup Styling */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #1e293b;
            border: 1px solid #334155;
            padding: 24px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }

        .modal-content h3 {
            margin-top: 0;
            color: #f8fafc;
        }

        .modal-content p {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn:hover {
            opacity: 0.9;
        }

        .btn-yes {
            background-color: #22c55e;
            color: #ffffff;
        }

        .btn-no {
            background-color: #ef4444;
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="scanner-container">
    <a href="index.php" class="back-link">← Back to Dashboard</a>
    <h2 style="margin-top:0;">Scan QR Code to Time Out / Time In</h2>

    <!-- Dynamic Alert Banner -->
    <div id="alertBox" class="alert-msg"></div>

    <div id="reader"></div>
</div>

<!-- Time-In Confirmation Modal -->
<div id="confirmModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Confirm Time In</h3>
        <p>User: <strong id="scannedProfileDisplay" style="color: #38bdf8;"></strong></p>
        <p>Are you sure you want to end break and <strong>Time In</strong>?</p>
        <div class="modal-buttons">
            <button class="btn btn-yes" onclick="confirmTimeIn(true)">Yes</button>
            <button class="btn btn-no" onclick="confirmTimeIn(false)">No</button>
        </div>
    </div>
</div>

<script>
let isProcessing = false;
let pendingTimeInCode = null;

function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    alertBox.className = 'alert-msg ' + (type === 'success' ? 'alert-success' : 'alert-danger');
    alertBox.textContent = message;
    alertBox.style.display = 'block';

    setTimeout(() => {
        alertBox.style.display = 'none';
    }, 4000);
}

function onScanSuccess(decodedText, decodedResult) {
    if (isProcessing) return;
    isProcessing = true;

    const barcode = decodedText.trim();

    // Check profile status first to determine action
    const checkFormData = new FormData();
    checkFormData.append('action', 'check_status');
    checkFormData.append('barcode', barcode);

    // Fetch user status from backend
    fetch('check_status.php?barcode=' + encodeURIComponent(barcode))
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.profile_status === 'ON_BREAK') {
            // IF ON BREAK -> Require Confirmation Prompt for Time In
            html5QrcodeScanner.pause(true);
            pendingTimeInCode = barcode;
            document.getElementById('scannedProfileDisplay').textContent = data.name + ' (' + barcode + ')';
            document.getElementById('confirmModal').style.display = 'flex';
        } else {
            // IF AVAILABLE -> Automatic Time Out (No Modal)
            executeScanRequest(barcode);
        }
    })
    .catch(() => {
        // Fallback to direct request if pre-check endpoint isn't set up
        executeScanRequest(barcode);
    });
}

function confirmTimeIn(shouldProceed) {
    document.getElementById('confirmModal').style.display = 'none';

    if (!shouldProceed) {
        pendingTimeInCode = null;
        isProcessing = false;
        html5QrcodeScanner.resume();
        return;
    }

    executeScanRequest(pendingTimeInCode, true);
}

function executeScanRequest(barcode, isFromModal = false) {
    const formData = new FormData();
    formData.append('action', 'quick_scan');
    formData.append('barcode', barcode);

    fetch('process.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(err => {
        showAlert('Network or server error occurred.', 'error');
    })
    .finally(() => {
        pendingTimeInCode = null;
        
        setTimeout(() => {
            isProcessing = false;
            if (isFromModal) {
                html5QrcodeScanner.resume();
            }
        }, 2500);
    });
}

// Initialize Camera Reader
let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader", 
    { 
        fps: 10, 
        qrbox: { width: 250, height: 250 },
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true
        }
    },
    /* verbose= */ false
);

html5QrcodeScanner.render(onScanSuccess);
</script>

</body>
</html>