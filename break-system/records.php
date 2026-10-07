<?php
require_once 'db.php';

// Set default timezone
date_default_timezone_set('Asia/Manila');

// Default date filters (Default to today)
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$search = trim($_GET['search'] ?? '');

// Build query joining records_log with profiles
$query = "
    SELECT 
        l.id,
        l.profile_id,
        l.time_out,
        l.expected_in,
        l.time_in,
        p.name AS profile_name,
        p.department
    FROM records_log l
    JOIN profiles p ON l.profile_id = p.id
    WHERE DATE(l.time_out) BETWEEN ? AND ?
";
$params = [$start_date, $end_date];

if ($search !== '') {
    $query .= " AND (LOWER(p.name) LIKE LOWER(?) OR LOWER(p.department) LIKE LOWER(?))";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY l.time_out DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Process metrics and row statuses dynamically
$total_breaks = count($records);
$overbreaks = 0;
$active_breaks = 0;

foreach ($records as &$r) {
    if (empty($r['time_in'])) {
        // Person is still on break
        $r['display_status'] = 'ON_BREAK';
        $r['duration_text'] = 'In Progress';
        $active_breaks++;
    } else {
        // Calculate duration in minutes
        $time_out_ts = strtotime($r['time_out']);
        $time_in_ts = strtotime($r['time_in']);
        $expected_in_ts = strtotime($r['expected_in']);

        $duration_mins = round(($time_in_ts - $time_out_ts) / 60);
        $r['duration_text'] = $duration_mins . ' mins';

        // Check for overbreak (Time In > Expected In)
        if ($time_in_ts > $expected_in_ts) {
            $r['display_status'] = 'OVERBREAK';
            $overbreaks++;
        } else {
            $r['display_status'] = 'ON_TIME';
        }
    }
}
unset($r); // Unset reference
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Break Time Records</title>
    <style>
        :root {
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --primary: #2563eb;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #d97706;
            --text: #1f2937;
            --subtext: #6b7280;
            --border-color: #e5e7eb;
            --info-bg: #f9fafb;
            --shadow: rgba(0, 0, 0, 0.08);
        }

        [data-theme="dark"] {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --primary: #3b82f6;
            --danger: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
            --text: #f8fafc;
            --subtext: #94a3b8;
            --border-color: #334155;
            --info-bg: #0f172a;
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
            max-width: 1200px;
            margin: 0 auto 20px auto;
        }

        .header-controls {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .btn-secondary {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text);
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 16px;
            border-radius: 14px;
            box-shadow: 0 4px 12px var(--shadow);
            border: 1px solid var(--border-color);
        }

        /* Metrics summary bar */
        .metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .metric-card {
            background: var(--info-bg);
            padding: 14px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .metric-card .num {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
        }

        .metric-card.danger .num { color: var(--danger); }
        .metric-card.warning .num { color: var(--warning); }
        .metric-card .label { font-size: 0.8rem; color: var(--subtext); text-transform: uppercase; font-weight: 600; }

        /* Filter Controls Form */
        .filter-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 20px;
            background: var(--info-bg);
            padding: 14px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 0.8rem;
            font-weight: bold;
            color: var(--subtext);
        }

        .filter-form input, .filter-form button {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--card-bg);
            color: var(--text);
            font-size: 0.95rem;
            min-height: 44px;
            box-sizing: border-box;
        }

        .filter-form button {
            background-color: var(--primary);
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        /* Responsive Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            min-width: 650px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            font-size: 0.9rem;
        }

        th {
            background-color: var(--info-bg);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--subtext);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .badge-ontime { background: #dcfce7; color: #15803d; }
        .badge-overbreak { background: #fee2e2; color: #b91c1c; }
        .badge-onbreak { background: #fef3c7; color: #b45309; }

        [data-theme="dark"] .badge-ontime { background: #064e3b; color: #6ee7b7; }
        [data-theme="dark"] .badge-overbreak { background: #7f1d1d; color: #fca5a5; }
        [data-theme="dark"] .badge-onbreak { background: #78350f; color: #fcd34d; }

        @media (min-width: 768px) {
            body { padding: 20px; }
            .container { padding: 24px; }
            .header-actions {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            .header-controls { width: auto; }
            .btn-secondary { flex: 0 0 auto; }
            .filter-form {
                grid-template-columns: 1fr 1fr 1fr auto;
                align-items: end;
            }
        }
    </style>
</head>
<body>

<div class="header-actions">
    <h1 style="margin:0; font-size: 1.5rem;">Break Time Records</h1>
    <div class="header-controls">
        <a href="index.php" class="btn-secondary">&larr; Dashboard</a>
        <a href="profiles.php" class="btn-secondary">Profiles</a>
    </div>
</div>

<div class="container">
    <form method="GET" action="records.php" class="filter-form">
        <div class="filter-group">
            <label>Start Date</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
        </div>
        <div class="filter-group">
            <label>End Date</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
        </div>
        <div class="filter-group">
            <label>Search Name/Dept</label>
            <input type="text" name="search" placeholder="Filter employee..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit">Filter Records</button>
    </form>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="num"><?= $total_breaks ?></div>
            <div class="label">Total Sessions</div>
        </div>
        <div class="metric-card warning">
            <div class="num"><?= $active_breaks ?></div>
            <div class="label">Active Breaks</div>
        </div>
        <div class="metric-card danger">
            <div class="num"><?= $overbreaks ?></div>
            <div class="label">Overbreaks</div>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Time Out</th>
                    <th>Time In</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--subtext); padding: 30px;">
                            No records found for the selected date range.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['profile_name']) ?></strong></td>
                            <td><?= htmlspecialchars($r['department']) ?></td>
                            <td><?= date('M d, Y - h:i A', strtotime($r['time_out'])) ?></td>
                            <td>
                                <?= $r['time_in'] ? date('M d, Y - h:i A', strtotime($r['time_in'])) : '<em>Ongoing...</em>' ?>
                            </td>
                            <td><strong><?= $r['duration_text'] ?></strong></td>
                            <td>
                                <?php if ($r['display_status'] === 'ON_TIME'): ?>
                                    <span class="badge badge-ontime">ON TIME</span>
                                <?php elseif ($r['display_status'] === 'ON_BREAK'): ?>
                                    <span class="badge badge-onbreak">ON BREAK</span>
                                <?php else: ?>
                                    <span class="badge badge-overbreak">OVERBREAK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);
</script>

</body>
</html>