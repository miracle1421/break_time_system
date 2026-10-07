<?php
require_once 'db.php';

// Set PHP timezone to local time
date_default_timezone_set('Asia/Manila');

// 1. Fetch active break profiles (5 per page)
$stmt = $pdo->query("SELECT id, name, department, status, time_out, expected_in FROM profiles WHERE status = 'ON_BREAK' ORDER BY name ASC");
$activeBreaks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageSize = 5;
$pagedBreaks = array_chunk($activeBreaks, $pageSize);
$totalBreakPages = count($pagedBreaks);

// 2. Fetch Live Queue Schedules (5 per page)
$today = date('Y-m-d');
$live_stmt = $pdo_live->prepare("SELECT * FROM queue WHERE date_schedule >= ? ORDER BY date_schedule ASC, time_schedule ASC");
$live_stmt->execute([$today]);
$liveSellers = $live_stmt->fetchAll(PDO::FETCH_ASSOC);

$pagedSellers = array_chunk($liveSellers, $pageSize);
$totalLivePages = count($pagedSellers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Break & Live Queue Dashboard</title>
    <style>
        :root {
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --primary: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #f39c12;
            --text: #1f2937;
            --subtext: #6b7280;
            --border-color: #e5e7eb;
            --info-bg: #f9fafb;
            --timer-bg: #eff6ff;
            --shadow: rgba(0, 0, 0, 0.08);
        }

        [data-theme="dark"] {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --primary: #3b82f6;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text: #f8fafc;
            --subtext: #94a3b8;
            --border-color: #334155;
            --info-bg: #0f172a;
            --timer-bg: #1e3a8a;
            --shadow: rgba(0, 0, 0, 0.4);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text);
            margin: 0;
            padding: 16px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .header-actions {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 1400px;
            margin: 0 auto 20px auto;
        }

        .header-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
        }

        .refresh-badge, .theme-toggle-btn {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text);
            padding: 10px 14px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px var(--shadow);
            height: 44px;
            box-sizing: border-box;
        }
        
        .theme-toggle-btn { cursor: pointer; flex: 0 0 auto; width: auto; justify-content: center; }

        .btn-create, .btn-manage, .btn-scan, .btn-live {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 44px;
            padding: 0 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            box-sizing: border-box;
            color: white;
        }

        .btn-create { background-color: var(--primary); }
        .btn-manage { background-color: #64748b; }
        .btn-scan { background-color: #ef4444; }
        .btn-live { background-color: var(--success); }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 8px var(--shadow);
            border: 1px solid var(--border-color);
        }

        h1 { font-size: 1.5rem; margin: 0; }
        .display-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .display-header h2 { margin: 0; border: none; padding: 0; font-size: 1.25rem; }

        .pagination-controls { display: flex; align-items: center; gap: 8px; }
        .page-btn {
            background: var(--info-bg);
            border: 1px solid var(--border-color);
            color: var(--text);
            border-radius: 6px;
            padding: 6px 12px;
            cursor: pointer;
            font-weight: bold;
        }
        .page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .page-indicator { font-size: 0.85rem; color: var(--subtext); font-weight: 600; }

        .slider-container { overflow: hidden; width: 100%; position: relative; }
        .slider-track { display: flex; transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1); width: 100%; }
        .slide-page {
            min-width: 100%;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            align-content: start;
        }

        .profile-card, .seller-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 4px 10px var(--shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 2px solid var(--border-color);
            justify-content: space-between;
        }

        .card-avatar {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--border-color);
            margin-bottom: 10px;
            background-color: var(--info-bg);
        }

        .profile-card .card-avatar { border-radius: 50%; }

        .profile-card .name, .seller-name { font-size: 1.05rem; font-weight: 700; margin-bottom: 4px; line-height: 1.2; }
        .profile-card .dept, .seller-meta { font-size: 0.85rem; color: var(--subtext); margin-bottom: 4px; }

        .time-info {
            width: 100%; font-size: 0.8rem; background: var(--info-bg); padding: 8px;
            border-radius: 8px; margin-bottom: 10px; text-align: left;
            box-sizing: border-box; border: 1px solid var(--border-color);
        }

        .timer-box {
            font-size: 1.2rem; font-weight: bold; padding: 8px; border-radius: 8px;
            background: var(--timer-bg); color: var(--primary); width: 100%;
            box-sizing: border-box;
        }

        .seller-card.today { border-color: var(--warning); }
        .seller-card.upcoming { border-color: var(--danger); }
        .seller-staff {
            margin-top: 8px;
            width: 100%;
            font-size: 0.8rem;
            font-weight: bold;
            background: var(--info-bg);
            padding: 6px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            box-sizing: border-box;
        }

        .profile-card.overbreak { background-color: rgba(239, 68, 68, 0.08); border-color: var(--danger); }
        .profile-card.overbreak .timer-box { background-color: var(--danger); color: white; }

        .empty-msg { text-align: center; color: var(--subtext); padding: 30px 10px; font-size: 1rem; width: 100%; }

        @media (min-width: 1024px) {
            .slide-page {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        @media (min-width: 640px) and (max-width: 1023px) {
            .header-actions { flex-direction: row; justify-content: space-between; align-items: center; }
        }

        @media (min-width: 768px) { body { padding: 20px; } }
    </style>
</head>
<body>

<div class="header-actions">
    <h1>System Dashboard</h1>
    <div class="header-controls">
        <div class="refresh-badge">
            🔄 Refresh: <span id="refreshTimer">30s</span>
        </div>
        <button id="themeToggle" class="theme-toggle-btn" onclick="toggleTheme()">
            <span id="themeIcon">🌙</span> <span id="themeText">Dark Mode</span>
        </button>
        <a href="live_display.php" target="_blank" class="btn-live">📹 Live Display ↗</a>
        <a href="records.php" class="btn-manage" style="background-color: #0284c7;">Records Log</a>
        <a href="profiles.php" class="btn-manage">Profiles</a>
        <a href="create_profile.php" class="btn-create">+ New Profile</a>
        <a href="scanner.php" class="btn-scan">📷 Camera Scan</a>
    </div>
</div>

<div class="container">

    <!-- TOP SECTION: Break Monitoring -->
    <div class="display-area card">
        <div class="display-header">
            <h2 style="color: var(--warning);">☕ Currently On Break (<?= count($activeBreaks) ?>)</h2>
            <?php if ($totalBreakPages > 1): ?>
                <div class="pagination-controls">
                    <button class="page-btn" id="breakPrevBtn" onclick="moveBreakSlide(-1)">&larr;</button>
                    <span class="page-indicator" id="breakPageIndicator">Page 1 of <?= $totalBreakPages ?></span>
                    <button class="page-btn" id="breakNextBtn" onclick="moveBreakSlide(1)">&rarr;</button>
                </div>
            <?php endif; ?>
        </div>

        <div class="slider-container">
            <?php if (empty($activeBreaks)): ?>
                <div class="empty-msg">No employees are currently on break.</div>
            <?php else: ?>
                <div class="slider-track" id="breakSliderTrack">
                    <?php foreach ($pagedBreaks as $pageIndex => $pageItems): ?>
                        <div class="slide-page">
                            <?php foreach ($pageItems as $person): 
                                $expectedFormatted = $person['expected_in'] ? date('Y-m-d\TH:i:s', strtotime($person['expected_in'])) : '';
                            ?>
                                <div class="profile-card">
                                    <img class="card-avatar" 
                                         src="view_photo.php?id=<?= htmlspecialchars($person['id']) ?>" 
                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($person['name']) ?>&background=random';" 
                                         alt="Photo">
                                    <div class="name"><?= htmlspecialchars($person['name']) ?></div>
                                    <div class="dept"><?= htmlspecialchars($person['department']) ?></div>

                                    <div class="time-info">
                                        <div><strong>Time Out:</strong> <?= $person['time_out'] ? date('h:i A', strtotime($person['time_out'])) : '--:--' ?></div>
                                        <div><strong>Expected In:</strong> <?= $person['expected_in'] ? date('h:i A', strtotime($person['expected_in'])) : '--:--' ?></div>
                                    </div>

                                    <div class="timer-box" data-expected="<?= htmlspecialchars($expectedFormatted) ?>">
                                        --:--
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BOTTOM SECTION: Live Seller Queue -->
    <div class="display-area card">
        <div class="display-header">
            <h2 style="color: var(--success);">📹 Live Seller Schedules (<?= count($liveSellers) ?>)</h2>
            <div style="display: flex; align-items: center; gap: 12px;">
                <?php if ($totalLivePages > 1): ?>
                    <div class="pagination-controls">
                        <button class="page-btn" id="livePrevBtn" onclick="moveLiveSlide(-1)">&larr;</button>
                        <span class="page-indicator" id="livePageIndicator">Page 1 of <?= $totalLivePages ?></span>
                        <button class="page-btn" id="liveNextBtn" onclick="moveLiveSlide(1)">&rarr;</button>
                    </div>
                <?php endif; ?>
                <a href="live_display.php" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: bold; font-size: 0.9rem;">Full Monitor ↗</a>
            </div>
        </div>

        <div class="slider-container">
            <?php if (empty($liveSellers)): ?>
                <div class="empty-msg">No live queue schedules available.</div>
            <?php else: ?>
                <div class="slider-track" id="liveSliderTrack">
                    <?php foreach ($pagedSellers as $pageIndex => $pageItems): ?>
                        <div class="slide-page">
                            <?php foreach ($pageItems as $seller): 
                                $isToday = ($seller['date_schedule'] === $today);
                                $statusClass = $isToday ? 'today' : 'upcoming';
                                
                                // Map DB path (uploads/seller_xxx) directly to root /onelivesched/ folder
                                $dbPath = trim($seller['seller_photo'] ?? '');
                                $imgSrc = !empty($dbPath) ? '/onelivesched/' . ltrim(htmlspecialchars($dbPath), '/') : '';
                            ?>
                                <div class="seller-card <?= $statusClass ?>">
                                    <img class="card-avatar" 
                                         src="<?= $imgSrc ?>" 
                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($seller['client_name']) ?>&background=random';" 
                                         alt="Seller">
                                    <div>
                                        <div class="seller-name"><?= htmlspecialchars($seller['client_name']) ?></div>
                                        <div class="seller-meta">📅 <?= htmlspecialchars($seller['date_schedule']) ?></div>
                                        <div class="seller-meta">⏰ <?= date("g:i A", strtotime($seller['time_schedule'])) ?></div>
                                    </div>
                                    <div class="seller-staff">Confirmed: <?= htmlspecialchars($seller['staff_confirmed']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
// Auto-Refresh & Activity Detection
const REFRESH_INTERVAL = 30;
let refreshSeconds = REFRESH_INTERVAL;
let isUserActive = false;
let activityTimeout;
const refreshDisplay = document.getElementById('refreshTimer');

const slideIntervalTime = 10000; // 10 Seconds Auto Next

// Slider State
const totalBreakPages = <?= $totalBreakPages > 0 ? $totalBreakPages : 1 ?>;
const totalLivePages = <?= $totalLivePages > 0 ? $totalLivePages : 1 ?>;
let currentBreakPage = 0;
let currentLivePage = 0;
let breakInterval, liveInterval;

function handleUserActivity() {
    isUserActive = true;
    refreshSeconds = REFRESH_INTERVAL;
    if (refreshDisplay) refreshDisplay.innerText = 'Paused';

    clearInterval(breakInterval);
    clearInterval(liveInterval);
    clearTimeout(activityTimeout);
    
    activityTimeout = setTimeout(() => {
        isUserActive = false;
        startAutoSliders();
    }, 3000);
}

['mousemove', 'keydown', 'touchstart', 'scroll', 'click'].forEach(evt => {
    window.addEventListener(evt, handleUserActivity, { passive: true });
});

setInterval(() => {
    if (!isUserActive) {
        refreshSeconds--;
        if (refreshDisplay) refreshDisplay.innerText = `${refreshSeconds}s`;
        if (refreshSeconds <= 0) window.location.reload();
    }
}, 1000);

// Break Slider Navigation
function updateBreakPosition() {
    const track = document.getElementById('breakSliderTrack');
    if (track) track.style.transform = `translateX(-${currentBreakPage * 100}%)`;
    
    const indicator = document.getElementById('breakPageIndicator');
    if (indicator) indicator.innerText = `Page ${currentBreakPage + 1} of ${totalBreakPages}`;
    
    const prevBtn = document.getElementById('breakPrevBtn');
    const nextBtn = document.getElementById('breakNextBtn');
    if (prevBtn) prevBtn.disabled = currentBreakPage === 0;
    if (nextBtn) nextBtn.disabled = currentBreakPage === totalBreakPages - 1;
}

function moveBreakSlide(direction) {
    currentBreakPage = (currentBreakPage + direction + totalBreakPages) % totalBreakPages;
    updateBreakPosition();
    handleUserActivity();
}

// Live Seller Slider Navigation
function updateLivePosition() {
    const track = document.getElementById('liveSliderTrack');
    if (track) track.style.transform = `translateX(-${currentLivePage * 100}%)`;
    
    const indicator = document.getElementById('livePageIndicator');
    if (indicator) indicator.innerText = `Page ${currentLivePage + 1} of ${totalLivePages}`;
    
    const prevBtn = document.getElementById('livePrevBtn');
    const nextBtn = document.getElementById('liveNextBtn');
    if (prevBtn) prevBtn.disabled = currentLivePage === 0;
    if (nextBtn) nextBtn.disabled = currentLivePage === totalLivePages - 1;
}

function moveLiveSlide(direction) {
    currentLivePage = (currentLivePage + direction + totalLivePages) % totalLivePages;
    updateLivePosition();
    handleUserActivity();
}

// Start Auto Sliders (10-second timer)
function startAutoSliders() {
    clearInterval(breakInterval);
    clearInterval(liveInterval);

    if (!isUserActive) {
        if (totalBreakPages > 1) {
            breakInterval = setInterval(() => {
                currentBreakPage = (currentBreakPage + 1) % totalBreakPages;
                updateBreakPosition();
            }, slideIntervalTime);
        }

        if (totalLivePages > 1) {
            liveInterval = setInterval(() => {
                currentLivePage = (currentLivePage + 1) % totalLivePages;
                updateLivePosition();
            }, slideIntervalTime);
        }
    }
}

// Theme Toggle
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeUI(savedTheme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeUI(newTheme);
}

function updateThemeUI(theme) {
    const icon = document.getElementById('themeIcon');
    const text = document.getElementById('themeText');
    if (theme === 'dark') {
        icon.innerText = '☀️';
        text.innerText = 'Light Mode';
    } else {
        icon.innerText = '🌙';
        text.innerText = 'Dark Mode';
    }
}

// Live Countdown Timers (Synchronized Local Time)
function updateTimers() {
    const timerElements = document.querySelectorAll('[data-expected]');
    const now = new Date();

    timerElements.forEach(el => {
        const rawExpected = el.getAttribute('data-expected');
        if (!rawExpected) return;

        // Parse date string in local browser time
        const expectedDate = new Date(rawExpected);
        
        if (isNaN(expectedDate.getTime())) return;

        let diff = expectedDate.getTime() - now.getTime();
        const card = el.closest('.profile-card');

        if (diff <= 0) {
            card.classList.add('overbreak');
            const overSeconds = Math.abs(Math.floor(diff / 1000));
            const mins = Math.floor(overSeconds / 60);
            const secs = overSeconds % 60;
            el.innerText = `+${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')} OVER`;
        } else {
            card.classList.remove('overbreak');
            const totalSeconds = Math.floor(diff / 1000);
            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;
            el.innerText = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
    });
}

// Initialize
initTheme();
setInterval(updateTimers, 1000);
updateTimers();

updateBreakPosition();
updateLivePosition();
startAutoSliders();
</script>

</body>
</html>