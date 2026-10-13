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
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'add') {
      $newEmail = trim((string)($_POST['new_email'] ?? ''));
      $newPassword = (string)($_POST['new_password'] ?? '');
      if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Informe um e-mail válido.';
      } elseif (mb_strlen($newPassword) < 8) {
        $error = 'A senha precisa ter pelo menos 8 caracteres.';
      } elseif (admin_find_user($users, $newEmail)) {
        $error = 'Já existe um usuário com esse e-mail.';
      } else {
        $users[] = [
          'email' => $newEmail,
          'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
          'created_at' => date('c'),
        ];
        admin_save_users($users);
        $success = 'Usuário ' . $newEmail . ' criado com sucesso — já pode fazer login com a senha definida.';
      }
    } elseif ($action === 'remove') {
      $targetEmail = (string)($_POST['target_email'] ?? '');
      if (count($users) <= 1) {
        $error = 'Não é possível remover o único usuário do painel.';
      } elseif (mb_strtolower($targetEmail) === mb_strtolower($myEmail)) {
        $error = 'Você não pode remover seu próprio usuário enquanto estiver logado com ele.';
      } else {
        $remaining = array_values(array_filter($users, function ($u) use ($targetEmail) {
          return mb_strtolower($u['email']) !== mb_strtolower($targetEmail);
        }));
        if (count($remaining) === count($users)) {
          $error = 'Usuário não encontrado.';
        } else {
          admin_save_users($remaining);
          $success = 'Usuário removido.';
        }
      }
    }
  }
}

$users = admin_load_users();
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Usuários — Painel bcontrol</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<main class="wrap">
  <p><a href="index.php">&larr; Voltar</a></p>
  <h1>Usuários do painel</h1>
  <p class="muted">Todos os usuários têm os mesmos poderes: podem editar qualquer conteúdo e criar ou remover outros usuários.</p>
  <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?>

  <div class="form-box">
    <table class="user-table">
      <thead><tr><th>E-mail</th><th>Criado em</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= h($u['email']) ?><?php if (mb_strtolower($u['email']) === mb_strtolower($myEmail)): ?> <span class="muted">(você)</span><?php endif; ?></td>
            <td class="muted"><?= h(date('d/m/Y', strtotime($u['created_at'] ?? 'now'))) ?></td>
            <td>
              <?php if (count($users) > 1 && mb_strtolower($u['email']) !== mb_strtolower($myEmail)): ?>
              <form method="post" onsubmit="return confirm('Remover o usuário <?= h($u['email']) ?>?');" style="margin:0">
                <?= admin_csrf_field() ?>
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="target_email" value="<?= h($u['email']) ?>">
                <button type="submit" class="btn-remove">Remover</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="form-box">
    <h2 style="font-size:16px;margin-top:0">Adicionar novo usuário</h2>
    <form method="post" autocomplete="off">
      <?= admin_csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <label for="new_email">E-mail</label>
      <input type="email" id="new_email" name="new_email" required>
      <label for="new_password">Senha (mínimo 8 caracteres)</label>
      <input type="password" id="new_password" name="new_password" required minlength="8">
      <button class="btn" type="submit" style="margin-top:10px">Criar usuário</button>
    </form>
  </div>
</main>
</body>
</html>
