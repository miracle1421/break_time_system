<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Time-In Terminal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .scanner-card { max-width: 500px; margin: 80px auto; border-radius: 12px; }
        #feedbackAlert { display: none; }
    </style>
</head>
<body>

<div class="container">
    <div class="card scanner-card shadow-sm p-4">
        <h3 class="text-center mb-4">Barcode Time-In</h3>

        <div id="feedbackAlert" class="alert mb-3" role="alert"></div>

        <form id="barcodeForm">
            <div class="mb-3">
                <label for="barcodeInput" class="form-label font-weight-bold">Scan Barcode / ID:</label>
                <input type="text" id="barcodeInput" name="barcode" class="form-control form-control-lg" placeholder="Waiting for scan..." autofocus autocomplete="off" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg">Submit Time-In</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('barcodeForm');
    const barcodeInput = document.getElementById('barcodeInput');
    const feedbackAlert = document.getElementById('feedbackAlert');

    // Auto-refocus input field on window click
    window.addEventListener('click', () => barcodeInput.focus());

    function showFeedback(type, message) {
        feedbackAlert.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
        feedbackAlert.textContent = message;
        feedbackAlert.style.display = 'block';
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const barcodeVal = barcodeInput.value.trim();

        if (!barcodeVal) return;

        barcodeInput.disabled = true;

        const formData = new FormData();
        formData.append('action', 'time_in_barcode');
        formData.append('barcode', barcodeVal);

        fetch('process.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(async (res) => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;

            if (!res.ok) {
                const errorMsg = (data && data.message) || `Server Error (${res.status})`;
                return Promise.reject(errorMsg);
            }

            if (!data) {
                throw new Error('Server returned an invalid non-JSON response.');
            }

            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                showFeedback('success', data.message);
            } else {
                showFeedback('error', data.message);
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            showFeedback('error', typeof err === 'string' ? err : 'Connection error. Check console for details.');
        })
        .finally(() => {
            barcodeInput.disabled = false;
            barcodeInput.value = '';
            barcodeInput.focus();
        });
    });
});
</script>

</body>
</html>