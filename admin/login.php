<?php
require __DIR__ . '/auth.php';

if (admin_is_logged_in()) {
  header('Location: index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!admin_login_throttle_check()) {
    $error = 'Muitas tentativas erradas. Aguarde 1 minuto e tente novamente.';
  } elseif (!admin_csrf_check()) {
    $error = 'Sessão expirada, tente novamente.';
  } else {
    $users = admin_load_users();
    $email = (string)($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $user = admin_find_user($users, $email);
    if ($user && password_verify($password, $user['password_hash'])) {
      admin_login_reset_fails();
      session_regenerate_id(true);
      $_SESSION['admin_email'] = $user['email'];
      header('Location: index.php');
      exit;
    } else {
      admin_login_register_fail();
      $error = 'E-mail ou senha incorretos.';
    }
  }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — Painel bcontrol</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
  <form class="login-box" method="post" autocomplete="off">
    <h1>Painel de conteúdo</h1>
    <p class="muted">bcontrol — acesso restrito</p>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
    <?= admin_csrf_field() ?>
    <label for="email">E-mail</label>
    <input type="email" id="email" name="email" required autofocus>
    <label for="password">Senha</label>
    <input type="password" id="password" name="password" required>
    <button class="btn" type="submit">Entrar</button>
  </form>
</body>
</html>
