<?php
declare(strict_types=1);
session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$message = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Secure File Exchange Lab</title>
  <style>
    body{font:16px/1.5 system-ui,sans-serif;max-width:760px;margin:4rem auto;padding:0 1rem;color:#202124}
    main{border:1px solid #ddd;border-radius:12px;padding:2rem}
    input,button{font:inherit;padding:.65rem}
    .notice{background:#eef6ff;padding:1rem;border-radius:8px}
  </style>
</head>
<body>
<main>
  <h1>Secure File Exchange Lab</h1>
  <p>Учебный портал поставщика. Файлы сохраняются вне web-root.</p>
  <?php if ($message !== ''): ?><p class="notice"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p><?php endif; ?>
  <form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <p><label>Файл XLSX: <input type="file" name="file" accept=".xlsx" required></label></p>
    <button type="submit">Загрузить</button>
  </form>
  <p><small>Лабораторный пример. Не загружайте реальные персональные или коммерческие данные.</small></p>
</main>
</body>
</html>
