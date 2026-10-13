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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(405, ['ok' => false, 'error' => 'Método não permitido.']);
}

// Honeypot anti-spam: campo escondido que só um robô preenche.
if (!empty($_POST['empresa_site'])) {
  // Finge sucesso pro bot, sem enviar nada de verdade.
  respond(200, ['ok' => true]);
}

$nome = clean_line((string)($_POST['nome'] ?? ''));
$email = clean_line((string)($_POST['email'] ?? ''));
$telefone = clean_line((string)($_POST['telefone'] ?? ''));
$setor = clean_line((string)($_POST['setor'] ?? ''));
$mensagem = trim((string)($_POST['mensagem'] ?? ''));

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

$subject = '=?UTF-8?B?' . base64_encode('Novo pedido de orçamento — bcontrol (' . $setor . ')') . '?=';

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
