<?php
require_once 'config.php';

$editMode = isset($_GET['id']);
$member = [
    'id' => '',
    'faction_id' => isset($_GET['faction']) ? (int)$_GET['faction'] : '',
    'full_name' => '',
    'rank_title' => '',
    'member_code' => '',
    'status' => 'فعال',
    'joined_date' => '',
    'notes' => '',
];
$errors = [];

if ($editMode) {
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $member = $row;
    } else {
        $editMode = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member['faction_id'] = (int)$_POST['faction_id'];
    $member['full_name'] = trim($_POST['full_name']);
    $member['rank_title'] = trim($_POST['rank_title']);
    $member['member_code'] = trim($_POST['member_code']);
    $member['status'] = $_POST['status'];
    $member['joined_date'] = $_POST['joined_date'];
    $member['notes'] = trim($_POST['notes']);

    if ($member['full_name'] === '') {
        $errors[] = 'نام عضو الزامی است.';
    }
    if ($member['faction_id'] <= 0) {
        $errors[] = 'انتخاب فکشن الزامی است.';
    }

    if (!$errors) {
        $joinedDate = $member['joined_date'] !== '' ? $member['joined_date'] : null;

        if ($editMode) {
            $stmt = $conn->prepare("
                UPDATE members
                SET faction_id=?, full_name=?, rank_title=?, member_code=?, status=?, joined_date=?, notes=?
                WHERE id=?
            ");
            $stmt->bind_param(
                'issssssi',
                $member['faction_id'], $member['full_name'], $member['rank_title'],
                $member['member_code'], $member['status'], $joinedDate, $member['notes'],
                $_GET['id']
            );
            $stmt->execute();
            header('Location: index.php?msg=updated');
            exit;
        } else {
            $stmt = $conn->prepare("
                INSERT INTO members (faction_id, full_name, rank_title, member_code, status, joined_date, notes)
                VALUES (?,?,?,?,?,?,?)
            ");
            $stmt->bind_param(
                'issssss',
                $member['faction_id'], $member['full_name'], $member['rank_title'],
                $member['member_code'], $member['status'], $joinedDate, $member['notes']
            );
            $stmt->execute();
            header('Location: index.php?msg=added');
            exit;
        }
    }
}

$factions = $conn->query("SELECT id, name FROM factions ORDER BY sort_order");
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $editMode ? 'ویرایش عضو' : 'افزودن عضو' ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="shell">
  <main class="main" style="max-width: 640px;">
    <a href="index.php" class="back-link">← بازگشت به لیست</a>
    <div class="topbar">
      <h1><?= $editMode ? 'ویرایش عضو' : 'افزودن عضو جدید' ?></h1>
    </div>

    <?php if ($errors): ?>
      <div class="flash" style="background:#F4E9E6; color:#9A4B2E; border-color:#E4C9C1;">
        <?= implode(' — ', array_map('htmlspecialchars', $errors)) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="form-panel">
      <div class="field">
        <label>فکشن</label>
        <select name="faction_id" required>
          <option value="">— انتخاب کنید —</option>
          <?php while ($f = $factions->fetch_assoc()): ?>
            <option value="<?= $f['id'] ?>" <?= (int)$member['faction_id'] === (int)$f['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($f['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="field">
        <label>نام کامل</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required>
      </div>

      <div class="field">
        <label>رنک / سمت</label>
        <input type="text" name="rank_title" value="<?= htmlspecialchars($member['rank_title']) ?>" placeholder="مثلاً سرگروه، افسر، تکنسین…">
      </div>

      <div class="field">
        <label>کد عضویت (اختیاری)</label>
        <input type="text" name="member_code" value="<?= htmlspecialchars($member['member_code']) ?>">
      </div>

      <div class="field">
        <label>وضعیت</label>
        <select name="status">
          <option value="فعال" <?= $member['status'] === 'فعال' ? 'selected' : '' ?>>فعال</option>
          <option value="غیرفعال" <?= $member['status'] === 'غیرفعال' ? 'selected' : '' ?>>غیرفعال</option>
        </select>
      </div>

      <div class="field">
        <label>تاریخ عضویت</label>
        <input type="date" name="joined_date" value="<?= htmlspecialchars($member['joined_date']) ?>">
      </div>

      <div class="field">
        <label>یادداشت (اختیاری)</label>
        <textarea name="notes"><?= htmlspecialchars($member['notes']) ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $editMode ? 'ذخیره تغییرات' : 'افزودن عضو' ?></button>
        <a href="index.php" class="btn btn-outline">انصراف</a>
      </div>
    </form>
  </main>
</div>
</body>
</html>
