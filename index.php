<?php
require_once 'config.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$factionFilter = isset($_GET['faction']) ? (int)$_GET['faction'] : 0;

// فکشن‌ها به همراه تعداد اعضا
$factions = [];
$res = $conn->query("
    SELECT f.id, f.slug, f.name, f.color, COUNT(m.id) AS member_count
    FROM factions f
    LEFT JOIN members m ON m.faction_id = f.id
    GROUP BY f.id
    ORDER BY f.sort_order
");
while ($row = $res->fetch_assoc()) {
    $factions[] = $row;
}

$totalMembers = array_sum(array_column($factions, 'member_count'));

// ساخت کوئری لیست اعضا
$sql = "
    SELECT m.*, f.name AS faction_name, f.color AS faction_color
    FROM members m
    JOIN factions f ON f.id = m.faction_id
    WHERE 1=1
";
$params = [];
$types = '';

if ($factionFilter > 0) {
    $sql .= " AND m.faction_id = ?";
    $params[] = $factionFilter;
    $types .= 'i';
}

if ($search !== '') {
    $sql .= " AND (m.full_name LIKE ? OR m.member_code LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql .= " ORDER BY f.sort_order, m.full_name";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$members = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>مدیریت اعضای فکشن‌ها</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="shell">

  <aside class="sidebar">
    <p class="brand">فکشن‌های کولیدر</p>
    <p class="brand-sub"><?= $totalMembers ?> عضو ثبت‌شده</p>

    <ul class="faction-nav">
      <li>
        <a href="index.php" class="<?= $factionFilter === 0 ? 'active' : '' ?>">
          <span class="faction-label">همه فکشن‌ها</span>
          <span class="faction-count"><?= $totalMembers ?></span>
        </a>
      </li>
      <?php foreach ($factions as $f): ?>
      <li>
        <a href="index.php?faction=<?= $f['id'] ?>" class="<?= $factionFilter === (int)$f['id'] ? 'active' : '' ?>">
          <span class="faction-label">
            <span class="faction-dot" style="background: <?= htmlspecialchars($f['color']) ?>"></span>
            <?= htmlspecialchars($f['name']) ?>
          </span>
          <span class="faction-count"><?= $f['member_count'] ?></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <main class="main">
    <div class="topbar">
      <div>
        <h1>
          <?= $factionFilter > 0
              ? htmlspecialchars(array_values(array_filter($factions, fn($f) => (int)$f['id'] === $factionFilter))[0]['name'])
              : 'همه اعضا' ?>
        </h1>
        <p class="hint">جستجو، افزودن و ویرایش اعضای فکشن‌ها</p>
      </div>
      <div class="search-box">
        <form method="get" style="display:flex; gap:10px;">
          <?php if ($factionFilter > 0): ?>
            <input type="hidden" name="faction" value="<?= $factionFilter ?>">
          <?php endif; ?>
          <input type="text" name="q" placeholder="جستجوی نام یا کد عضویت…" value="<?= htmlspecialchars($search) ?>">
          <button type="submit" class="btn btn-outline">جستجو</button>
        </form>
        <a href="member_form.php<?= $factionFilter > 0 ? '?faction=' . $factionFilter : '' ?>" class="btn btn-primary">+ افزودن عضو</a>
      </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="flash">
        <?php
          $messages = [
            'added'   => 'عضو با موفقیت اضافه شد.',
            'updated' => 'اطلاعات عضو بروزرسانی شد.',
            'deleted' => 'عضو حذف شد.',
          ];
          echo $messages[$_GET['msg']] ?? '';
        ?>
      </div>
    <?php endif; ?>

    <div class="panel">
      <table>
        <thead>
          <tr>
            <th>نام</th>
            <th>فکشن</th>
            <th>رنک / سمت</th>
            <th>وضعیت</th>
            <th>تاریخ عضویت</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if ($members->num_rows === 0): ?>
            <tr><td colspan="6"><div class="empty-state">هیچ عضوی یافت نشد.</div></td></tr>
          <?php else: ?>
            <?php while ($m = $members->fetch_assoc()): ?>
            <tr>
              <td>
                <div class="member-name"><?= htmlspecialchars($m['full_name']) ?></div>
                <?php if ($m['member_code']): ?>
                  <div class="member-code">کد: <?= htmlspecialchars($m['member_code']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="faction-chip">
                  <span class="faction-dot" style="background: <?= htmlspecialchars($m['faction_color']) ?>"></span>
                  <?= htmlspecialchars($m['faction_name']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($m['rank_title'] ?: '—') ?></td>
              <td>
                <span class="badge <?= $m['status'] === 'فعال' ? 'badge-active' : 'badge-inactive' ?>">
                  <?= htmlspecialchars($m['status']) ?>
                </span>
              </td>
              <td><?= $m['joined_date'] ? htmlspecialchars($m['joined_date']) : '—' ?></td>
              <td>
                <div class="row-actions">
                  <a href="member_form.php?id=<?= $m['id'] ?>">ویرایش</a>
                  <a href="delete.php?id=<?= $m['id'] ?>" class="btn-danger-text" onclick="return confirm('این عضو حذف شود؟');">حذف</a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

</div>
</body>
</html>
