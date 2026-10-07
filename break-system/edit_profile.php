<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: profiles.php');
    exit;
}

// Fetch existing profile
$stmt = $pdo->prepare("SELECT * FROM profiles WHERE id = ?");
$stmt->execute([$id]);
$profile = $stmt->fetch();

if (!$profile) {
    header('Location: profiles.php');
    exit;
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if (!empty($name) && !empty($department)) {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
            $updateStmt = $pdo->prepare("UPDATE profiles SET name = ?, department = ?, photo = ? WHERE id = ?");
            $updateStmt->execute([$name, $department, $photoData, $id]);
        } else {
            $updateStmt = $pdo->prepare("UPDATE profiles SET name = ?, department = ? WHERE id = ?");
            $updateStmt->execute([$name, $department, $id]);
        }

        header('Location: profiles.php?msg=updated');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        :root {
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --primary: #d97706;
            --text: #1f2937;
            --subtext: #6b7280;
            --border-color: #e5e7eb;
            --info-bg: #f9fafb;
            --shadow: rgba(0, 0, 0, 0.08);
        }

        [data-theme="dark"] {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --primary: #f59e0b;
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
            padding: 20px 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            transition: background-color 0.3s ease, color 0.3s ease;
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
        }

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
        }

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

<div class="card">
    <h2>Edit Profile</h2>

    <form action="edit_profile.php?id=<?= $profile['id'] ?>" method="POST" enctype="multipart/form-data">
        <div class="avatar-preview-container">
            <img id="preview" src="view_photo.php?id=<?= $profile['id'] ?>" alt="Preview" class="avatar-preview">
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" value="<?= htmlspecialchars($profile['department']) ?>" required>
        </div>

        <div class="form-group">
            <label>Replace Photo (Optional)</label>
            <input type="file" name="photo" id="photoInput" accept="image/*" onchange="previewImage(event)">
        </div>

        <button type="submit" class="btn-submit">Update Profile</button>
        <a href="profiles.php" class="btn-cancel">Cancel</a>
    </form>
</div>

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