<?php
/**
 * Processa o formulário de orçamento (#formOrc) e envia por e-mail via mail() nativo do
 * PHP — sem depender de serviço de terceiro (formsubmit.co). Mesmo padrão usado no
 * projeto Uman: um endpoint próprio no mesmo domínio, que o site chama via fetch().
 *
 * O destinatário é resolvido AQUI (a partir de content/site.json), nunca a partir do que
 * o navegador envia — assim o formulário não pode ser usado para mandar e-mail pra
 * qualquer endereço arbitrário.
 */

declare(strict_types=1);

// Endereço que aparece como remetente nos e-mails enviados pelo site. Idealmente uma
// caixa real do próprio domínio (ajuda a não cair em spam/SPF). Pode ser trocado aqui
// sem afetar o restante do site.
const FROM_EMAIL = 'nao-responda@bcontrol.com.br';
const FROM_NAME = 'Site bcontrol';
const FALLBACK_EMAIL = 'bcontrol@bcontrol.com.br';

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void {
  http_response_code($status);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

/** Remove quebras de linha para impedir injeção de cabeçalhos de e-mail. */
function clean_line(string $s): string {
  return trim(preg_replace('/[\r\n]+/', ' ', $s));
}

/** Limite simples de envios por IP, pra um script não conseguir disparar e-mails em
 *  massa por esse formulário (nem vira "spam bomb" pro mail() do servidor nem lota a
 *  caixa de quem recebe os orçamentos). Guardado em arquivo, igual ao bloqueio de
 *  login do /admin. */
function envio_rate_limit_check(): bool {
  $file = __DIR__ . '/content/.backups/envio_rate.json';
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $data = [];
  $raw = @file_get_contents($file);
  if ($raw) { $decoded = json_decode($raw, true); if (is_array($decoded)) $data = $decoded; }
  $entry = $data[$ip] ?? ['count' => 0, 'since' => time()];
  if (time() - $entry['since'] > 600) { $entry = ['count' => 0, 'since' => time()]; }
  $entry['count']++;
  $data[$ip] = $entry;
  foreach ($data as $k => $v) { if (time() - ($v['since'] ?? 0) > 3600) unset($data[$k]); }
  $fh = @fopen($file, 'c+');
  if ($fh) {
    if (flock($fh, LOCK_EX)) { ftruncate($fh, 0); fwrite($fh, json_encode($data)); fflush($fh); flock($fh, LOCK_UN); }
    fclose($fh);
  }
  return $entry['count'] <= 8; // até 8 envios por IP a cada 10 minutos
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(405, ['ok' => false, 'error' => 'Método não permitido.']);
}

if (!envio_rate_limit_check()) {
  respond(429, ['ok' => false, 'error' => 'Muitos envios em pouco tempo. Aguarde alguns minutos e tente novamente.']);
}

// Honeypot anti-spam: campo escondido que só um robô preenche.
if (!empty($_POST['empresa_site'])) {
  // Finge sucesso pro bot, sem enviar nada de verdade.
  respond(200, ['ok' => true]);
}

$nome = mb_substr(clean_line((string)($_POST['nome'] ?? '')), 0, 150);
$email = mb_substr(clean_line((string)($_POST['email'] ?? '')), 0, 150);
$telefone = mb_substr(clean_line((string)($_POST['telefone'] ?? '')), 0, 40);
$setor = mb_substr(clean_line((string)($_POST['setor'] ?? '')), 0, 60);
$mensagem = mb_substr(trim((string)($_POST['mensagem'] ?? '')), 0, 4000);

if ($nome === '' || $setor === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  respond(422, ['ok' => false, 'error' => 'Preencha nome, e-mail válido e setor.']);
}

// Destinatário real: resolvido no servidor a partir de content/site.json, nunca confiando
// em dado vindo do navegador. Mesma lógica de "e-mail de teste" documentada no RELATORIO.md:
// enquanto content/site.json -> testCcEmail estiver preenchido, ele recebe no lugar do
// setor real (pra validar o envio sem precisar de acesso às caixas da bcontrol).
$siteConfig = @json_decode((string)@file_get_contents(__DIR__ . '/content/site.json'), true) ?: [];
$sectorEmails = $siteConfig['sectorEmails'] ?? [];
$realTarget = $sectorEmails[$setor] ?? FALLBACK_EMAIL;
$targetEmail = !empty($siteConfig['testCcEmail']) ? $siteConfig['testCcEmail'] : $realTarget;

if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
  respond(500, ['ok' => false, 'error' => 'Destinatário mal configurado. Avise o suporte técnico.']);
}

$sectorSubjects = $siteConfig['sectorSubjects'] ?? [];
$subjectLabel = $sectorSubjects[$setor] ?? $setor;
$subject = '=?UTF-8?B?' . base64_encode($subjectLabel) . '?=';

$body = "Novo pedido de orçamento pelo site da bcontrol\n\n"
  . "Setor: {$setor}\n"
  . "Nome: {$nome}\n"
  . "E-mail: {$email}\n"
  . "Telefone: " . ($telefone !== '' ? $telefone : '(não informado)') . "\n\n"
  . "Mensagem:\n" . ($mensagem !== '' ? $mensagem : '(sem mensagem)') . "\n";

$headers = "MIME-Version: 1.0\r\n"
  . "Content-Type: text/plain; charset=UTF-8\r\n"
  . "From: " . FROM_NAME . ' <' . FROM_EMAIL . ">\r\n"
  . 'Reply-To: ' . $nome . ' <' . $email . ">\r\n";

$sent = @mail($targetEmail, $subject, $body, $headers);

if (!$sent) {
  respond(500, ['ok' => false, 'error' => 'Não foi possível enviar o e-mail agora. Tente novamente mais tarde.']);
}

respond(200, ['ok' => true]);
