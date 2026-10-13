<?php
/**
 * Autenticação multiusuário (e-mail + senha) + CSRF para o painel de edição.
 * Sem Netlify/Git Gateway: funciona em qualquer hospedagem com PHP (cPanel inclusive).
 * Usuários ficam em admin/users.json (bloqueado para acesso direto via .htaccess).
 */

define('ADMIN_ROOT', __DIR__);
define('SITE_ROOT', dirname(__DIR__));
define('USERS_FILE', ADMIN_ROOT . '/users.json');
define('LEGACY_AUTH_DATA_FILE', ADMIN_ROOT . '/auth_data.json');

session_name('bcontrol_admin');
session_start();

/** Lê a lista de usuários, migrando o arquivo antigo de senha única (se existir) ou
 *  criando o primeiro acesso, se o arquivo de usuários ainda não existir. */
function admin_load_users(): array {
  $raw = @file_get_contents(USERS_FILE);
  $data = $raw ? json_decode($raw, true) : null;
  if (is_array($data) && !empty($data['users']) && is_array($data['users'])) {
    return $data['users'];
  }

  // Migração do painel antigo (senha única, sem e-mail).
  $legacyRaw = @file_get_contents(LEGACY_AUTH_DATA_FILE);
  $legacy = $legacyRaw ? json_decode($legacyRaw, true) : null;
  if (is_array($legacy) && !empty($legacy['password_hash'])) {
    $users = [[
      'email' => 'luciano@umandigital.com.br',
      'password_hash' => $legacy['password_hash'],
      'created_at' => date('c'),
    ]];
    admin_save_users($users);
    return $users;
  }

  // Primeiro acesso: nenhum arquivo de usuários ainda.
  $users = [[
    'email' => 'luciano@umandigital.com.br',
    'password_hash' => password_hash('bcontrol2026', PASSWORD_DEFAULT),
    'created_at' => date('c'),
  ]];
  admin_save_users($users);
  return $users;
}

function admin_save_users(array $users): void {
  file_put_contents(USERS_FILE, json_encode(['users' => $users], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function admin_find_user(array $users, string $email): ?array {
  $email = mb_strtolower(trim($email));
  foreach ($users as $u) {
    if (mb_strtolower($u['email']) === $email) return $u;
  }
  return null;
}

function admin_is_logged_in(): bool {
  return !empty($_SESSION['admin_email']);
}

function admin_current_email(): string {
  return $_SESSION['admin_email'] ?? '';
}

function admin_require_login(): void {
  if (!admin_is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}

function admin_csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function admin_csrf_check(): bool {
  return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function admin_csrf_field(): string {
  return '<input type="hidden" name="csrf" value="' . htmlspecialchars(admin_csrf_token()) . '">';
}

function h(?string $s): string {
  return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** Limite simples de tentativas de login para dificultar força bruta. */
function admin_login_throttle_check(): bool {
  $fails = $_SESSION['login_fails'] ?? 0;
  $lastFail = $_SESSION['login_last_fail'] ?? 0;
  if ($fails >= 5 && (time() - $lastFail) < 60) {
    return false; // bloqueado por 60s após 5 tentativas erradas
  }
  return true;
}

function admin_login_register_fail(): void {
  $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
  $_SESSION['login_last_fail'] = time();
}

function admin_login_reset_fails(): void {
  unset($_SESSION['login_fails'], $_SESSION['login_last_fail']);
}
