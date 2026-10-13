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
    // Hash fixo (de uma senha que não existe) usado quando o e-mail não é encontrado,
    // para o password_verify() gastar o mesmo tempo de um login com e-mail existente —
    // sem isso, dava pra descobrir por tempo de resposta quais e-mails têm conta.
    $dummyHash = '$2y$12$dAgECLE.BXW.bs3qp6fXMuY7n/TvR5caTQ4/GHDmVP7nGwaN4UzY6';
    $hashToCheck = $user['password_hash'] ?? $dummyHash;
    $passwordOk = password_verify($password, $hashToCheck);
    if ($user && $passwordOk) {
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
