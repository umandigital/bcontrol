<?php
require __DIR__ . '/auth.php';
admin_require_login();
$schema = require __DIR__ . '/schema.php';

$pageKey = $_GET['page'] ?? '';
if (!isset($schema[$pageKey])) {
  header('Location: index.php');
  exit;
}
$page = $schema[$pageKey];
$filePath = SITE_ROOT . '/' . $page['file'];

$error = '';
$success = '';

/* ---------- Helpers de leitura/gravação ---------- */

function admin_read_json(string $path) {
  if (!file_exists($path)) return null;
  $raw = file_get_contents($path);
  $data = json_decode($raw, true);
  return json_last_error() === JSON_ERROR_NONE ? $data : null;
}

function admin_backup_file(string $path): void {
  if (!file_exists($path)) return;
  $dir = dirname($path) . '/.backups';
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  $name = basename($path) . '.' . date('Ymd-His') . '.json';
  copy($path, $dir . '/' . $name);
}

function admin_handle_upload(array $fileInfo, string $uploadDirRel): ?string {
  if (empty($fileInfo['name']) || ($fileInfo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
  $origName = basename($fileInfo['name']);
  $ext = strtolower((string)pathinfo($origName, PATHINFO_EXTENSION));
  if ($ext !== 'pdf') {
    throw new RuntimeException('Só é permitido enviar arquivos PDF.');
  }
  $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '-', (string)pathinfo($origName, PATHINFO_FILENAME));
  $safeBase = trim((string)$safeBase, '-');
  if ($safeBase === '') $safeBase = 'documento-' . substr(md5((string)microtime(true)), 0, 6);
  $destRel = rtrim($uploadDirRel, '/') . '/' . $safeBase . '.' . $ext;
  $destAbs = SITE_ROOT . '/' . $destRel;
  if (!is_dir(dirname($destAbs))) mkdir(dirname($destAbs), 0775, true);
  if (!is_uploaded_file($fileInfo['tmp_name'])) {
    throw new RuntimeException('Upload inválido.');
  }
  if (!move_uploaded_file($fileInfo['tmp_name'], $destAbs)) {
    throw new RuntimeException('Falha ao salvar o arquivo enviado no servidor (verifique permissões de escrita em ' . $uploadDirRel . ').');
  }
  return $destRel;
}

/**
 * Recebe uma imagem enviada pelo navegador, redimensiona (sem aumentar imagens já
 * menores), corrige a rotação de fotos de celular (EXIF) e grava duas versões —
 * .webp (leve) e o formato de fallback (.png, preservando transparência de logos, ou
 * .jpg) — exatamente como o resto do site já espera via o helper picture() do main.js.
 * Apaga os arquivos antigos desse mesmo campo (se houver) para não acumular lixo.
 */
function admin_process_image_upload(array $fileInfo, string $uploadDirRel, int $maxWidth, string $fallbackExt, ?string $oldBasePath): ?string {
  if (empty($fileInfo['name']) || ($fileInfo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
  if (!function_exists('imagecreatetruecolor')) {
    throw new RuntimeException('O servidor não tem a extensão de imagens (GD) do PHP habilitada. Peça ao suporte da hospedagem para ativá-la.');
  }
  if (!is_uploaded_file($fileInfo['tmp_name'])) {
    throw new RuntimeException('Upload inválido.');
  }

  $info = @getimagesize($fileInfo['tmp_name']);
  if (!$info) {
    throw new RuntimeException('O arquivo enviado não é uma imagem válida.');
  }
  $mime = $info['mime'];
  switch ($mime) {
    case 'image/jpeg': $im = @imagecreatefromjpeg($fileInfo['tmp_name']); break;
    case 'image/png': $im = @imagecreatefrompng($fileInfo['tmp_name']); break;
    case 'image/webp': $im = @imagecreatefromwebp($fileInfo['tmp_name']); break;
    case 'image/gif': $im = @imagecreatefromgif($fileInfo['tmp_name']); break;
    default:
      throw new RuntimeException('Formato de imagem não suportado. Envie um arquivo JPG, PNG ou WEBP.');
  }
  if (!$im) {
    throw new RuntimeException('Não foi possível ler a imagem enviada (arquivo corrompido?).');
  }

  // Corrige fotos de celular giradas (comum em JPEG com metadado de orientação).
  if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
    $exif = @exif_read_data($fileInfo['tmp_name']);
    $orientation = $exif['Orientation'] ?? 1;
    if ($orientation === 3) { $im = imagerotate($im, 180, 0); }
    elseif ($orientation === 6) { $im = imagerotate($im, -90, 0); }
    elseif ($orientation === 8) { $im = imagerotate($im, 90, 0); }
  }

  imagealphablending($im, true);
  imagesavealpha($im, true);

  $w = imagesx($im);
  $h = imagesy($im);
  if ($w > $maxWidth) {
    $newW = $maxWidth;
    $newH = (int)round($h * $maxWidth / $w);
    $resized = imagecreatetruecolor($newW, $newH);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    imagecopyresampled($resized, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagedestroy($im);
    $im = $resized;
  }

  $origName = basename($fileInfo['name']);
  $safeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '-', (string)pathinfo($origName, PATHINFO_FILENAME));
  $safeBase = trim((string)$safeBase, '-');
  if ($safeBase === '') $safeBase = 'imagem';
  $safeBase .= '-' . substr(md5((string)microtime(true) . random_int(0, 999999)), 0, 8);

  $destDirAbs = rtrim(SITE_ROOT . '/' . $uploadDirRel, '/');
  if (!is_dir($destDirAbs)) mkdir($destDirAbs, 0775, true);

  $webpAbs = $destDirAbs . '/' . $safeBase . '.webp';
  $fallbackAbs = $destDirAbs . '/' . $safeBase . '.' . $fallbackExt;

  if (!imagewebp($im, $webpAbs, 80)) {
    imagedestroy($im);
    throw new RuntimeException('Falha ao gerar a versão .webp da imagem no servidor.');
  }

  if ($fallbackExt === 'jpg') {
    // JPEG não tem transparência: achata sobre fundo branco antes de salvar.
    $flat = imagecreatetruecolor(imagesx($im), imagesy($im));
    $white = imagecolorallocate($flat, 255, 255, 255);
    imagefill($flat, 0, 0, $white);
    imagealphablending($flat, true);
    imagecopy($flat, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
    $ok = imagejpeg($flat, $fallbackAbs, 85);
    imagedestroy($flat);
  } else {
    $ok = imagepng($im, $fallbackAbs, 6);
  }
  imagedestroy($im);

  if (!$ok) {
    throw new RuntimeException('Falha ao gravar a imagem no servidor (verifique permissões de escrita em ' . $uploadDirRel . ').');
  }

  // Remove os arquivos antigos desse mesmo campo, se existiam.
  if ($oldBasePath) {
    foreach (['webp', $fallbackExt] as $oldExt) {
      $oldAbs = SITE_ROOT . '/' . $oldBasePath . '.' . $oldExt;
      if (is_file($oldAbs)) @unlink($oldAbs);
    }
  }

  return rtrim($uploadDirRel, '/') . '/' . $safeBase;
}

/** Reorganiza a bagunça clássica do $_FILES multidimensional em algo navegável como o $_POST. */
function admin_reshape_files(array $filesNode): array {
  if (!isset($filesNode['name'])) return [];
  $out = [];
  foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key) {
    admin_reshape_files_walk($filesNode[$key] ?? [], $key, $out);
  }
  return $out;
}
function admin_reshape_files_walk($node, string $key, array &$out): void {
  if (is_array($node)) {
    foreach ($node as $k => $v) {
      if (!isset($out[$k]) || !is_array($out[$k])) $out[$k] = [];
      admin_reshape_files_walk($v, $key, $out[$k]);
    }
  } else {
    $out[$key] = $node;
  }
}

function build_field_value(array $field, $postValue, $filesValue) {
  $type = $field['type'];
  switch ($type) {
    case 'string':
      return trim((string)(is_array($postValue) ? '' : ($postValue ?? '')));
    case 'number':
      $v = is_array($postValue) ? '' : ($postValue ?? '');
      return is_numeric($v) ? $v + 0 : 0;
    case 'textarea':
      return (string)(is_array($postValue) ? '' : ($postValue ?? ''));
    case 'file':
      $destRelative = null;
      if (is_array($filesValue) && !empty($filesValue['name'])) {
        $destRelative = admin_handle_upload($filesValue, $field['uploadDir']);
      }
      if ($destRelative) return $destRelative;
      return trim((string)(is_array($postValue) ? '' : ($postValue ?? '')));
    case 'image':
      $destRelative = null;
      $oldValue = trim((string)(is_array($postValue) ? '' : ($postValue ?? '')));
      if (is_array($filesValue) && !empty($filesValue['name'])) {
        $destRelative = admin_process_image_upload($filesValue, $field['uploadDir'], $field['maxWidth'], $field['fallbackExt'], $oldValue !== '' ? $oldValue : null);
      }
      if ($destRelative) return $destRelative;
      return $oldValue;
    case 'object':
      $out = [];
      foreach ($field['fields'] as $sub) {
        $out[$sub['name']] = build_field_value(
          $sub,
          is_array($postValue) ? ($postValue[$sub['name']] ?? null) : null,
          is_array($filesValue) ? ($filesValue[$sub['name']] ?? null) : null
        );
      }
      return $out;
    case 'list':
      $itemsPost = is_array($postValue) ? $postValue : [];
      $itemsFiles = is_array($filesValue) ? $filesValue : [];
      $result = [];
      foreach ($itemsPost as $idx => $itemPostVal) {
        if (($field['itemType'] ?? 'object') === 'string') {
          $s = trim((string)(is_array($itemPostVal) ? '' : $itemPostVal));
          if ($s !== '') $result[] = $s;
        } else {
          $itemOut = [];
          foreach ($field['fields'] as $sub) {
            $itemOut[$sub['name']] = build_field_value(
              $sub,
              is_array($itemPostVal) ? ($itemPostVal[$sub['name']] ?? null) : null,
              $itemsFiles[$idx][$sub['name']] ?? null
            );
          }
          $result[] = $itemOut;
        }
      }
      return array_values($result);
  }
  return null;
}

/* ---------- Salvar (POST) ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!admin_csrf_check()) {
    $error = 'Sessão expirada, tente novamente.';
  } else {
    try {
      $filesReshaped = admin_reshape_files($_FILES['upload'] ?? []);
      if (($page['root'] ?? 'object') === 'object') {
        $rootField = ['name' => 'root', 'type' => 'object', 'fields' => $page['fields']];
        $built = build_field_value($rootField, $_POST['data'] ?? [], $filesReshaped);
      } else {
        $rootField = ['name' => 'root', 'type' => 'list', 'itemType' => $page['itemType'] ?? 'object', 'fields' => $page['fields']];
        $items = build_field_value($rootField, $_POST['data'] ?? [], $filesReshaped);
        $built = ['items' => $items];
      }
      admin_backup_file($filePath);
      $json = json_encode($built, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      if (@file_put_contents($filePath, $json) === false) {
        throw new RuntimeException('Não foi possível gravar ' . $page['file'] . '. Verifique as permissões de escrita da pasta content/ no servidor.');
      }
      $success = 'Salvo com sucesso.';
    } catch (RuntimeException $e) {
      $error = $e->getMessage();
    }
  }
}

$data = admin_read_json($filePath);
if (($page['root'] ?? 'object') === 'object') {
  $viewData = is_array($data) ? $data : [];
} else {
  $viewData = (is_array($data) && isset($data['items']) && is_array($data['items'])) ? $data['items'] : [];
}

/* ---------- Renderização do formulário ---------- */

function render_text_input(string $name, $value, string $type = 'text'): string {
  return '<input type="' . h($type) . '" name="' . h($name) . '" value="' . h((string)$value) . '">';
}

function render_field(array $field, $value, string $dataPath, string $uploadPath, int $depth): string {
  $name = $dataPath . '[' . $field['name'] . ']';
  $label = '<label>' . h($field['label']) . '</label>';
  $hint = !empty($field['hint']) ? '<p class="hint">' . h($field['hint']) . '</p>' : '';
  switch ($field['type']) {
    case 'string':
      return '<div class="field">' . $label . render_text_input($name, $value) . $hint . '</div>';
    case 'number':
      return '<div class="field">' . $label . render_text_input($name, $value, 'number') . $hint . '</div>';
    case 'textarea':
      return '<div class="field">' . $label . '<textarea name="' . h($name) . '" rows="4">' . h((string)$value) . '</textarea>' . $hint . '</div>';
    case 'file':
      $uploadName = $uploadPath . '[' . $field['name'] . ']';
      return '<div class="field">' . $label
        . render_text_input($name, $value)
        . '<p class="hint">Caminho atual do arquivo. ' . h($field['hint'] ?? '') . ' Para substituir, selecione um novo PDF abaixo:</p>'
        . '<input type="file" name="' . h($uploadName) . '" accept="' . h($field['accept'] ?? '') . '">'
        . '</div>';
    case 'image':
      $uploadName = $uploadPath . '[' . $field['name'] . ']';
      $preview = '';
      if ($value) {
        $previewSrc = '../' . h($value) . '.' . h($field['fallbackExt']) . '?v=' . time();
        $preview = '<img src="' . $previewSrc . '" alt="" class="img-preview">';
      }
      return '<div class="field">' . $label
        . $preview
        . render_text_input($name, $value)
        . '<p class="hint">Caminho atual da imagem. Para trocar, selecione um novo arquivo (JPG, PNG ou WEBP) — o site gera a versão otimizada automaticamente:</p>'
        . '<input type="file" name="' . h($uploadName) . '" accept="image/jpeg,image/png,image/webp">'
        . '</div>';
    case 'object':
      $inner = '';
      foreach ($field['fields'] as $sub) {
        $inner .= render_field($sub, is_array($value) ? ($value[$sub['name']] ?? '') : '', $name, $uploadPath . '[' . $field['name'] . ']', $depth);
      }
      return '<fieldset class="field-object">' . $label . '<div class="field-grid">' . $inner . '</div></fieldset>';
    case 'list':
      return render_list_field($field, is_array($value) ? $value : [], $name, $uploadPath . '[' . $field['name'] . ']', $depth);
  }
  return '';
}

function render_list_item(array $field, $itemVal, string $dataPath, string $uploadPath, $indexOrToken, int $depth, bool $isTemplate): string {
  $itemType = $field['itemType'] ?? 'object';
  $itemDataPath = $dataPath . '[' . $indexOrToken . ']';
  $itemUploadPath = $uploadPath . '[' . $indexOrToken . ']';
  if ($itemType === 'string') {
    $val = is_string($itemVal) ? $itemVal : '';
    return '<div class="list-item list-item-inline" data-item>'
      . '<input type="text" name="' . h($itemDataPath) . '" value="' . h($val) . '">'
      . '<button type="button" class="btn-remove" data-remove title="Remover">&times;</button>'
      . '</div>';
  }
  $preview = '';
  if (!$isTemplate && !empty($field['itemLabelField'])) {
    $previewVal = is_array($itemVal) ? ($itemVal[$field['itemLabelField']] ?? '') : '';
    if ($previewVal !== '') $preview = ' — ' . h((string)$previewVal);
  }
  $body = '';
  foreach ($field['fields'] as $sub) {
    $subVal = is_array($itemVal) ? ($itemVal[$sub['name']] ?? '') : '';
    $body .= render_field($sub, $subVal, $itemDataPath, $itemUploadPath, $depth + 1);
  }
  return '<div class="list-item" data-item>'
    . '<div class="list-item-head"><span class="muted">Item' . $preview . '</span>'
    . '<button type="button" class="btn-remove" data-remove>Remover</button></div>'
    . '<div class="field-grid">' . $body . '</div>'
    . '</div>';
}

function render_list_field(array $field, array $values, string $dataPath, string $uploadPath, int $depth): string {
  $containerId = 'list_' . preg_replace('/[^a-zA-Z0-9]+/', '_', $dataPath . '_' . $depth . '_' . uniqid());
  $idxToken = '{{IDX' . $depth . '}}';
  $html = '<div class="list-field">';
  if (!empty($field['label'])) $html .= '<label>' . h($field['label']) . '</label>';
  $html .= '<div class="list-container" id="' . h($containerId) . '" data-idx-token="' . h($idxToken) . '" data-next-index="' . count($values) . '">';
  foreach ($values as $i => $itemVal) {
    $html .= render_list_item($field, $itemVal, $dataPath, $uploadPath, $i, $depth, false);
  }
  $templateItem = render_list_item($field, ($field['itemType'] ?? 'object') === 'string' ? '' : [], $dataPath, $uploadPath, $idxToken, $depth, true);
  $html .= '<template>' . $templateItem . '</template>';
  $html .= '</div>';
  $html .= '<button type="button" class="btn small" data-add="' . h($containerId) . '">+ Adicionar item</button>';
  $html .= '</div>';
  return $html;
}

if (($page['root'] ?? 'object') === 'object') {
  $formHtml = '<div class="field-grid">';
  foreach ($page['fields'] as $f) {
    $formHtml .= render_field($f, $viewData[$f['name']] ?? '', 'data', 'upload', 0);
  }
  $formHtml .= '</div>';
} else {
  $listField = [
    'label' => '',
    'itemType' => $page['itemType'] ?? 'object',
    'itemLabelField' => $page['itemLabelField'] ?? null,
    'fields' => $page['fields'],
  ];
  $formHtml = render_list_field($listField, $viewData, 'data', 'upload', 0);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($page['label']) ?> — Painel bcontrol</title>
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<main class="wrap">
  <p><a href="index.php">&larr; Voltar</a></p>
  <h1><?= h($page['label']) ?></h1>
  <p class="muted"><code><?= h($page['file']) ?></code></p>
  <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="form-box">
    <?= admin_csrf_field() ?>
    <?= $formHtml ?>
    <div class="form-actions">
      <button class="btn" type="submit">Salvar alterações</button>
    </div>
  </form>
</main>
<script src="admin.js"></script>
</body>
</html>
