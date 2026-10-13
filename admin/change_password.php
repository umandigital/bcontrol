<?php
require __DIR__ . '/auth.php';
admin_require_login();

$error = '';
$success = '';
$myEmail = admin_current_email();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!admin_csrf_check()) {
    $error = 'Sessão expirada, tente novamente.';
  } else {
    $users = admin_load_users();
    $me = admin_find_user($users, $myEmail);
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (!$me || !password_verify($current, $me['password_hash'])) {
      $error = 'Senha atual incorreta.';
    } elseif (mb_strlen($new) < 8) {
      $error = 'A nova senha precisa ter pelo menos 8 caracteres.';
    } elseif ($new !== $confirm) {
      $error = 'A confirmação não bate com a nova senha.';
    } else {
      foreach ($users as &$u) {
        if (mb_strtolower($u['email']) === mb_strtolower($myEmail)) {
          $u['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
        }
      }
      unset($u);
      admin_save_users($users);
      $success = 'Senha alterada com sucesso.';
    }
  }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Alterar senha — Painel bcontrol</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<main class="wrap narrow">
  <p><a href="index.php">&larr; Voltar</a></p>
  <h1>Alterar minha senha</h1>
  <p class="muted">Conta: <?= h($myEmail) ?></p>
  <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?>
  <form method="post" class="form-box" autocomplete="off">
    <?= admin_csrf_field() ?>
    <label for="current_password">Senha atual</label>
    <input type="password" id="current_password" name="current_password" required>
    <label for="new_password">Nova senha (mínimo 8 caracteres)</label>
    <input type="password" id="new_password" name="new_password" required minlength="8">
    <label for="confirm_password">Confirmar nova senha</label>
    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
    <button class="btn" type="submit">Salvar nova senha</button>
  </form>
</main>
</body>
</html>
