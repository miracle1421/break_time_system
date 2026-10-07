<?php
// Database Credentials
$host = 'localhost';
$dbname = 'onelivesched';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; text-align:center;'>
            <h2>Database Connection Failed!</h2>
            <p>" . $e->getMessage() . "</p>
         </div>");
}

$today = date('Y-m-d');
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 10; // Exactly 10 cards per page (5 top, 5 bottom)
$offset = ($page - 1) * $limit;

// Fetch Live Items (date_schedule >= Today)
$total_stmt = $db->prepare("SELECT COUNT(*) FROM queue WHERE date_schedule >= ?");
$total_stmt->execute([$today]);
$total_live = $total_stmt->fetchColumn();
$total_pages = ceil($total_live / $limit);

$stmt = $db->prepare("SELECT * FROM queue WHERE date_schedule >= ? ORDER BY date_schedule ASC, time_schedule ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $today, PDO::PARAM_STR);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        /* 5-Top / 5-Bottom Layout Grid */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            min-height: calc(100vh - 160px);
        }

        /* Enlarged Box Cards */
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

        /* Auto-resizing Square Image Wrapper */
        .avatar-container {
            width: 100%;
            max-width: 180px; /* Bigger default dimensions */
            aspect-ratio: 1 / 1; /* Forces a perfect square ratio */
            margin: 0 auto 10px auto;
        }

        .seller-avatar {
            width: 100%;
            height: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 12px; /* Clean squared corners */
            object-fit: cover; /* Prevents stretching and cropping issues */
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

        /* Fluid Auto-Resizing Breakpoints */
        @media (max-width: 1600px) {
            .avatar-container {
                max-width: 140px;
            }
            .card-name {
                font-size: 1.5rem;
            }
            .card-detail {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 1200px) {
            .grid-container {
                grid-template-columns: repeat(3, 1fr);
            }
            .avatar-container {
                max-width: 160px;
            }
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
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
    <span>PAGE <?= $page ?> OF <?= max(1, $total_pages) ?></span>
    <button type="button" class="btn-next" onclick="goToNextPage()">Next ➔</button>
</div>

<script>
    // Live Clock
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Broadcast channel to signal creation screen when display advances
    const queueChannel = new BroadcastChannel('queue_updates');

    // Pagination Logic
    const currentPage = <?= $page ?>;
    const totalPages = <?= max(1, $total_pages) ?>;
    let countdown = 30;

    function goToNextPage() {
        let nextPage = currentPage + 1;
        if (nextPage > totalPages) {
            nextPage = 1; // Reset to page 1
        }
        // Signal Management tabs to sync
        queueChannel.postMessage({ type: 'PAGE_ADVANCED', page: nextPage });
        window.location.href = `display.php?p=${nextPage}`;
    }

    // 30-Second Rotation
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