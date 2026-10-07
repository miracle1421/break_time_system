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

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Action Handlers
$action = $_POST['action'] ?? '';

function handleFileUpload($currentPhoto = '') {
    global $uploadDir;
    if (isset($_FILES['seller_photo']) && $_FILES['seller_photo']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['seller_photo']['tmp_name'];
        $ext = pathinfo($_FILES['seller_photo']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('seller_', true) . '.' . strtolower($ext);
        $targetPath = $uploadDir . $newName;
        
        if (move_uploaded_file($tmpName, $targetPath)) {
            return $targetPath;
        }
    }
    return $currentPhoto;
}

if ($action === 'create') {
    $photoPath = handleFileUpload();
    $stmt = $db->prepare("INSERT INTO queue (client_name, date_schedule, time_schedule, staff_confirmed, seller_photo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['client_name'], $_POST['date_schedule'], $_POST['time_schedule'], $_POST['staff_confirmed'], $photoPath]);
    header("Location: index.php?tab=creation&success=1");
    exit;
}

if ($action === 'update') {
    $existingPhoto = $_POST['existing_photo'] ?? '';
    $photoPath = handleFileUpload($existingPhoto);
    
    $stmt = $db->prepare("UPDATE queue SET client_name = ?, date_schedule = ?, time_schedule = ?, staff_confirmed = ?, seller_photo = ? WHERE id = ?");
    $stmt->execute([$_POST['client_name'], $_POST['date_schedule'], $_POST['time_schedule'], $_POST['staff_confirmed'], $photoPath, $_POST['id']]);
    header("Location: index.php?tab=creation&updated=1");
    exit;
}

if ($action === 'delete') {
    $stmt = $db->prepare("DELETE FROM queue WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    header("Location: index.php?tab=creation&deleted=1");
    exit;
}

$today = date('Y-m-d');
$active_tab = $_GET['tab'] ?? 'creation';

// All Records Query
$all_stmt = $db->query("SELECT * FROM queue ORDER BY date_schedule DESC, time_schedule ASC");
$all_schedules = $all_stmt->fetchAll(PDO::FETCH_ASSOC);

// Report Query with Filtering and Sorting
$allowed_columns = [
    'date'  => 'date_schedule',
    'name'  => 'client_name',
    'staff' => 'staff_confirmed'
];

$sort_key = $_GET['sort_by'] ?? 'date';
$sort_column = $allowed_columns[$sort_key] ?? 'date_schedule';

$sort_order = strtoupper($_GET['sort'] ?? 'DESC');
$sort_order = ($sort_order === 'ASC') ? 'ASC' : 'DESC';

// Filter Inputs
$filter_date = trim($_GET['filter_date'] ?? '');
$filter_name = trim($_GET['filter_name'] ?? '');
$filter_staff = trim($_GET['filter_staff'] ?? '');

$sql = "SELECT * FROM queue WHERE date_schedule < ?";
$params = [$today];

if ($filter_date !== '') {
    $sql .= " AND date_schedule = ?";
    $params[] = $filter_date;
}
if ($filter_name !== '') {
    $sql .= " AND client_name LIKE ?";
    $params[] = '%' . $filter_name . '%';
}
if ($filter_staff !== '') {
    $sql .= " AND staff_confirmed LIKE ?";
    $params[] = '%' . $filter_staff . '%';
}

$sql .= " ORDER BY $sort_column $sort_order, time_schedule ASC";

$report_stmt = $db->prepare($sql);
$report_stmt->execute($params);
$report_schedules = $report_stmt->fetchAll(PDO::FETCH_ASSOC);

// Edit Item Fetch
$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_stmt = $db->prepare("SELECT * FROM queue WHERE id = ?");
    $edit_stmt->execute([$_GET['edit']]);
    $edit_item = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Panel - onelivesched</title>
    <style>
        :root {
            --bg-color: #f4f7f6;
            --text-color: #333;
            --card-bg: #ffffff;
            --header-bg: #2c3e50;
            --header-text: #ffffff;
            --border-color: #e0e0e0;
            --btn-accent: #3498db;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, sans-serif; margin: 0; padding: 20px; background: var(--bg-color); color: var(--text-color); }

        header {
            background-color: var(--header-bg);
            color: var(--header-text);
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 { margin: 0; font-size: 1.5rem; }

        .header-controls { display: flex; gap: 10px; align-items: center; }

        nav button, .btn-link {
            background: transparent;
            border: 2px solid var(--header-text);
            color: var(--header-text);
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        nav button.active, nav button:hover, .btn-link:hover {
            background-color: var(--header-text);
            color: var(--header-bg);
        }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .form-container, .table-container {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px var(--shadow);
            margin-bottom: 20px;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .btn {
            background-color: var(--btn-accent);
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }

        .btn:hover { opacity: 0.9; }
        .btn-active { background-color: #27ae60; }
        .btn-danger { background-color: #e74c3c; }
        .btn-warning { background-color: #f39c12; }
        .btn-secondary { background-color: #7f8c8d; }

        .filter-bar {
            background: #eef2f5;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            margin-bottom: 15px;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-item label {
            font-weight: bold;
            font-size: 0.85rem;
        }

        .filter-item input {
            padding: 7px 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .sort-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
            background: #f8f9fa;
            padding: 12px 18px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            margin-bottom: 15px;
        }

        .sort-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sort-group label {
            font-weight: bold;
            font-size: 0.9rem;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid var(--border-color); }
        th, td { padding: 12px; text-align: left; }
        th { background-color: var(--header-bg); color: var(--header-text); }

        /* Square Auto-Resizing Table Thumbnails */
        .table-avatar {
            width: 60px;
            height: 60px;
            aspect-ratio: 1 / 1;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border-color);
        }
    </style>
</head>
<body>

<header>
    <h1>onelivesched Admin Panel</h1>
    <div class="header-controls">
        <nav>
            <button class="<?= $active_tab === 'creation' ? 'active' : '' ?>" onclick="switchTab('creation')">Creation & Management</button>
            <button class="<?= $active_tab === 'report' ? 'active' : '' ?>" onclick="switchTab('report')">Report Area</button>
        </nav>
        <a href="display.php" target="_blank" class="btn-link">📺 Open Live Display Screen</a>
    </div>
</header>

<!-- CREATION / MANAGEMENT AREA -->
<div id="creation" class="tab-content <?= $active_tab === 'creation' ? 'active' : '' ?>">
    <div class="form-container">
        <h2><?= $edit_item ? 'Edit Schedule' : 'Create New Schedule' ?></h2>
        <form id="scheduleForm" action="index.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit_item ? 'update' : 'create' ?>">
            <?php if ($edit_item): ?>
                <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($edit_item['seller_photo']) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Client's Name</label>
                <input type="text" id="client_name" name="client_name" value="<?= htmlspecialchars($edit_item['client_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Live Seller Photo</label>
                <input type="file" name="seller_photo" accept="image/*">
                <?php if (!empty($edit_item['seller_photo'])): ?>
                    <p style="margin-top: 8px;"><small>Current Photo: <img src="<?= htmlspecialchars($edit_item['seller_photo']) ?>" class="table-avatar" style="vertical-align:middle;"></small></p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Date Schedule</label>
                <input type="date" name="date_schedule" value="<?= htmlspecialchars($edit_item['date_schedule'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Time Schedule</label>
                <input type="time" name="time_schedule" value="<?= htmlspecialchars($edit_item['time_schedule'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Staff Who Confirmed</label>
                <input type="text" name="staff_confirmed" value="<?= htmlspecialchars($edit_item['staff_confirmed'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn"><?= $edit_item ? 'Update Schedule' : 'Add to Queue' ?></button>
            <?php if ($edit_item): ?>
                <a href="index.php?tab=creation" class="btn btn-danger">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <h2>Manage All Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Client Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Staff</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_schedules as $row): 
                    $photo = !empty($row['seller_photo']) ? htmlspecialchars($row['seller_photo']) : 'https://via.placeholder.com/150?text=N/A';
                ?>
                    <tr>
                        <td><img src="<?= $photo ?>" alt="Seller" class="table-avatar"></td>
                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                        <td><?= htmlspecialchars($row['date_schedule']) ?></td>
                        <td><?= date("g:i A", strtotime($row['time_schedule'])) ?></td>
                        <td><?= htmlspecialchars($row['staff_confirmed']) ?></td>
                        <td>
                            <a href="index.php?tab=creation&edit=<?= $row['id'] ?>"><button class="btn btn-warning">Edit</button></a>
                            <form action="index.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- REPORT AREA -->
<div id="report" class="tab-content <?= $active_tab === 'report' ? 'active' : '' ?>">
    <div class="table-container">
        <h2>Expired / Past Schedules Report</h2>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="index.php" class="filter-form">
                <input type="hidden" name="tab" value="report">
                <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_key) ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_order) ?>">

                <div class="filter-item">
                    <label for="filter_date">Filter Date:</label>
                    <input type="date" id="filter_date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>">
                </div>

                <div class="filter-item">
                    <label for="filter_name">Client's Name:</label>
                    <input type="text" id="filter_name" name="filter_name" placeholder="Search name..." value="<?= htmlspecialchars($filter_name) ?>">
                </div>

                <div class="filter-item">
                    <label for="filter_staff">Staff Confirmed:</label>
                    <input type="text" id="filter_staff" name="filter_staff" placeholder="Search staff..." value="<?= htmlspecialchars($filter_staff) ?>">
                </div>

                <button type="submit" class="btn">Apply Filter</button>
                <a href="index.php?tab=report" class="btn btn-secondary">Reset Filter</a>
            </form>
        </div>

        <!-- Sorting Bar -->
        <div class="sort-bar">
            <div class="sort-group">
                <label>Sort Field:</label>
                <a href="?tab=report&sort_by=date&sort=<?= $sort_order ?>&filter_date=<?= urlencode($filter_date) ?>&filter_name=<?= urlencode($filter_name) ?>&filter_staff=<?= urlencode($filter_staff) ?>" class="btn <?= $sort_key === 'date' ? 'btn-active' : '' ?>">Date</a>
                <a href="?tab=report&sort_by=name&sort=<?= $sort_order ?>&filter_date=<?= urlencode($filter_date) ?>&filter_name=<?= urlencode($filter_name) ?>&filter_staff=<?= urlencode($filter_staff) ?>" class="btn <?= $sort_key === 'name' ? 'btn-active' : '' ?>">Client's Name</a>
                <a href="?tab=report&sort_by=staff&sort=<?= $sort_order ?>&filter_date=<?= urlencode($filter_date) ?>&filter_name=<?= urlencode($filter_name) ?>&filter_staff=<?= urlencode($filter_staff) ?>" class="btn <?= $sort_key === 'staff' ? 'btn-active' : '' ?>">Staff Confirmed</a>
            </div>

            <div style="border-left: 1px solid var(--border-color); height: 25px; margin: 0 5px;"></div>

            <div class="sort-group">
                <label>Order:</label>
                <a href="?tab=report&sort_by=<?= $sort_key ?>&sort=ASC&filter_date=<?= urlencode($filter_date) ?>&filter_name=<?= urlencode($filter_name) ?>&filter_staff=<?= urlencode($filter_staff) ?>" class="btn <?= $sort_order === 'ASC' ? 'btn-active' : '' ?>">
                    <?= $sort_key === 'date' ? 'Oldest First (ASC)' : 'A to Z (ASC)' ?>
                </a>
                <a href="?tab=report&sort_by=<?= $sort_key ?>&sort=DESC&filter_date=<?= urlencode($filter_date) ?>&filter_name=<?= urlencode($filter_name) ?>&filter_staff=<?= urlencode($filter_staff) ?>" class="btn <?= $sort_order === 'DESC' ? 'btn-active' : '' ?>">
                    <?= $sort_key === 'date' ? 'Newest First (DESC)' : 'Z to A (DESC)' ?>
                </a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Client Name <?= $sort_key === 'name' ? ($sort_order === 'ASC' ? '▲' : '▼') : '' ?></th>
                    <th>Date Schedule <?= $sort_key === 'date' ? ($sort_order === 'ASC' ? '▲' : '▼') : '' ?></th>
                    <th>Time</th>
                    <th>Staff Confirmed <?= $sort_key === 'staff' ? ($sort_order === 'ASC' ? '▲' : '▼') : '' ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report_schedules)): ?>
                    <tr><td colspan="5">No historical records found matching your filters.</td></tr>
                <?php else: ?>
                    <?php foreach ($report_schedules as $report): 
                        $photo = !empty($report['seller_photo']) ? htmlspecialchars($report['seller_photo']) : 'https://via.placeholder.com/150?text=N/A';
                    ?>
                        <tr>
                            <td><img src="<?= $photo ?>" alt="Seller" class="table-avatar"></td>
                            <td><?= htmlspecialchars($report['client_name']) ?></td>
                            <td><?= htmlspecialchars($report['date_schedule']) ?></td>
                            <td><?= date("g:i A", strtotime($report['time_schedule'])) ?></td>
                            <td><?= htmlspecialchars($report['staff_confirmed']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('nav button').forEach(el => el.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        
        const eventBtn = Array.from(document.querySelectorAll('nav button')).find(btn => btn.getAttribute('onclick').includes(tabId));
        if (eventBtn) eventBtn.classList.add('active');
    }

    // Auto-refresh creation tab when display advances to next page
    const queueChannel = new BroadcastChannel('queue_updates');
    queueChannel.onmessage = (event) => {
        if (event.data && event.data.type === 'PAGE_ADVANCED') {
            const clientInput = document.getElementById('client_name');
            if (!clientInput || clientInput.value.trim() === '') {
                window.location.reload();
            }
        }
    };
</script>

</body>
</html>