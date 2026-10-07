<?php
require_once 'db.php';

$today = date('Y-m-d');
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$limit = 10; // 10 cards per page (5 top, 5 bottom)
$offset = ($page - 1) * $limit;

// Fetch Live Queue Items using $pdo_live
$total_stmt = $pdo_live->prepare("SELECT COUNT(*) FROM queue WHERE date_schedule >= ?");
$total_stmt->execute([$today]);
$total_live = $total_stmt->fetchColumn();
$total_pages = max(1, ceil($total_live / $limit));

$stmt = $pdo_live->prepare("SELECT * FROM queue WHERE date_schedule >= ? ORDER BY date_schedule ASC, time_schedule ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $today, PDO::PARAM_STR);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Queue Display - onelivesched</title>
    <style>
        :root {
            --bg-color: #121212;
            --text-color: #ffffff;
            --card-bg: #1e1e1e;
            --today-bg: #f39c12;
            --today-text: #000000;
            --upcoming-bg: #c0392b;
            --upcoming-text: #ffffff;
            --shadow: rgba(0, 0, 0, 0.7);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            padding: 20px;
            overflow-x: hidden;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #1a1a1a;
            padding: 15px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px var(--shadow);
            border-left: 6px solid #f39c12;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #00e676;
        }

        .header-info {
            display: flex;
            gap: 20px;
            align-items: center;
            font-weight: bold;
            font-size: 1.3rem;
        }

        .timer-badge {
            background: #2980b9;
            color: #fff;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            min-height: calc(100vh - 180px);
        }

        .card {
            border-radius: 16px;
            padding: 20px 15px;
            box-shadow: 0 8px 20px var(--shadow);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            min-height: 320px;
            transition: transform 0.3s ease;
        }

        .card.today {
            background-color: var(--today-bg);
            color: var(--today-text);
            border: 4px solid #ffffff;
        }

        .card.upcoming {
            background-color: var(--upcoming-bg);
            color: var(--upcoming-text);
            border: 4px solid #ffffff;
        }

        .avatar-container {
            width: 100%;
            max-width: 180px;
            aspect-ratio: 1 / 1;
            margin: 0 auto 10px auto;
        }

        .seller-avatar {
            width: 100%;
            height: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 12px;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 12px var(--shadow);
            display: block;
        }

        .card-name {
            font-size: 1.8rem;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 8px 0 6px 0;
            word-break: break-word;
        }

        .card-detail {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 3px 0;
            letter-spacing: 0.5px;
        }

        .card-staff {
            font-size: 1.1rem;
            font-weight: 800;
            text-transform: uppercase;
            opacity: 0.95;
            background: rgba(0, 0, 0, 0.25);
            padding: 6px 12px;
            border-radius: 10px;
            width: 100%;
            margin-top: 8px;
        }

        .pagination-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .btn-next {
            background-color: #27ae60;
            color: #ffffff;
            border: none;
            padding: 8px 20px;
            font-size: 1.1rem;
            font-weight: 900;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 8px var(--shadow);
            transition: transform 0.2s ease, background-color 0.2s ease;
            text-transform: uppercase;
        }

        .btn-next:hover {
            background-color: #2ecc71;
            transform: scale(1.05);
        }

        @media (max-width: 1600px) {
            .avatar-container { max-width: 140px; }
            .card-name { font-size: 1.5rem; }
            .card-detail { font-size: 1.1rem; }
        }

        @media (max-width: 1200px) {
            .grid-container { grid-template-columns: repeat(3, 1fr); }
            .avatar-container { max-width: 160px; }
        }

        @media (max-width: 768px) {
            .grid-container { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<header>
    <h1>LIVE QUEUE DISPLAY</h1>
    <div class="header-info">
        <span id="liveClock">Loading...</span>
        <span class="timer-badge">Next Page in: <span id="timer">30</span>s</span>
    </div>
</header>

<div class="grid-container">
    <?php if (empty($schedules)): ?>
        <div style="grid-column: span 5; text-align:center; padding: 100px 0;">
            <h2 style="font-size: 2.5rem; color: #888;">NO QUEUE SCHEDULED FOR TODAY</h2>
        </div>
    <?php else: ?>
        <?php foreach ($schedules as $item): 
            $isToday = ($item['date_schedule'] === $today);
            $cardClass = $isToday ? 'today' : 'upcoming';
            $photo = !empty($item['seller_photo']) ? htmlspecialchars($item['seller_photo']) : 'https://via.placeholder.com/200?text=Seller';
        ?>
            <div class="card <?= $cardClass ?>">
                <div class="avatar-container">
                    <img src="<?= $photo ?>" alt="Seller" class="seller-avatar">
                </div>
                <div>
                    <div class="card-name"><?= htmlspecialchars($item['client_name']) ?></div>
                    <div class="card-detail">DATE: <?= htmlspecialchars($item['date_schedule']) ?></div>
                    <div class="card-detail">TIME: <?= date("g:i A", strtotime($item['time_schedule'])) ?></div>
                </div>
                <div class="card-staff">CONFIRMED: <?= htmlspecialchars($item['staff_confirmed']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="pagination-footer">
    <span>PAGE <?= $page ?> OF <?= $total_pages ?></span>
    <button type="button" class="btn-next" onclick="goToNextPage()">Next ➔</button>
</div>

<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    }
    setInterval(updateClock, 1000);
    updateClock();

    const queueChannel = new BroadcastChannel('queue_updates');
    const currentPage = <?= $page ?>;
    const totalPages = <?= $total_pages ?>;
    let countdown = 30;

    function goToNextPage() {
        let nextPage = currentPage + 1;
        if (nextPage > totalPages) {
            nextPage = 1;
        }
        queueChannel.postMessage({ type: 'PAGE_ADVANCED', page: nextPage });
        window.location.href = `live_display.php?p=${nextPage}`;
    }

    setInterval(() => {
        countdown--;
        document.getElementById('timer').textContent = countdown;
        if (countdown <= 0) {
            goToNextPage();
        }
    }, 1000);
</script>

</body>
</html>