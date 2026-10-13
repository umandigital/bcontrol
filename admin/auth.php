<?php
/**
 * Autenticação simples (senha única) + CSRF para o painel de edição.
 * Sem Netlify/Git Gateway: funciona em qualquer hospedagem com PHP (cPanel inclusive).
 */

define('ADMIN_ROOT', __DIR__);
define('SITE_ROOT', dirname(__DIR__));
define('AUTH_DATA_FILE', ADMIN_ROOT . '/auth_data.json');

session_name('bcontrol_admin');
session_start();

function admin_load_auth(): array {
  $raw = @file_get_contents(AUTH_DATA_FILE);
  $data = $raw ? json_decode($raw, true) : null;
  if (!is_array($data) || empty($data['password_hash'])) {
    // Fallback de segurança: nunca deixa o painel sem senha nenhuma.
    // Mesma senha inicial documentada em INSTRUCOES_PUBLICACAO.md.
    $data = ['password_hash' => password_hash('bcontrol2026', PASSWORD_DEFAULT)];
  }
  return $data;
}

function admin_save_auth(array $data): void {
  file_put_contents(AUTH_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function admin_is_logged_in(): bool {
  return !empty($_SESSION['admin_ok']);
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
