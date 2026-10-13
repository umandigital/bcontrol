<?php
require __DIR__ . '/auth.php';
admin_require_login();
$schema = require __DIR__ . '/schema.php';
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Painel de conteúdo — bcontrol</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<main class="wrap">
  <h1>Editar conteúdo do site</h1>
  <p class="muted">Escolha o que deseja editar. As mudanças aparecem no site assim que salvar — uma cópia de segurança do arquivo anterior fica guardada automaticamente em <code>content/.backups/</code>.</p>
  <div class="cards">
    <?php foreach ($schema as $key => $page): ?>
      <a class="card" href="edit.php?page=<?= h($key) ?>">
        <strong><?= h($page['label']) ?></strong>
        <span class="muted"><?= h($page['file']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</main>
</body>
</html>
