<?php
/**
 * Descreve a estrutura dos arquivos content/*.json para o painel de edição.
 * Cada "page" vira um menu no painel. "fields" segue a mesma lógica que já
 * existia em admin/config.yml (Decap CMS), só que interpretada por PHP puro,
 * sem precisar de Netlify/Git Gateway.
 */

return [
  'site' => [
    'label' => 'Configurações gerais',
    'file' => 'content/site.json',
    'root' => 'object',
    'fields' => [
      ['name' => 'whatsappNumber', 'label' => 'WhatsApp (DDI+DDD+número, só dígitos, ex. 5551999999999)', 'type' => 'string'],
      ['name' => 'whatsappMessage', 'label' => 'Mensagem padrão do WhatsApp', 'type' => 'string'],
      ['name' => 'phoneDisplay', 'label' => 'Telefone (exibição)', 'type' => 'string'],
      ['name' => 'address', 'label' => 'Endereço', 'type' => 'string'],
      ['name' => 'cadastroUrl', 'label' => 'URL de atualização de cadastro', 'type' => 'string'],
      ['name' => 'ga4Id', 'label' => 'Google Analytics 4 (Measurement ID, ex. G-XXXXXXX)', 'type' => 'string'],
      ['name' => 'testCcEmail', 'label' => 'E-mail de teste (recebe os orçamentos no lugar do setor real) — deixe vazio para desativar', 'type' => 'string'],
      ['name' => 'sectorEmails', 'label' => 'E-mails por setor', 'type' => 'object', 'fields' => [
        ['name' => 'Águas', 'label' => 'Águas', 'type' => 'string'],
        ['name' => 'Alimentos', 'label' => 'Alimentos', 'type' => 'string'],
        ['name' => 'Controle de qualidade', 'label' => 'Controle de qualidade', 'type' => 'string'],
        ['name' => 'Comercial', 'label' => 'Comercial', 'type' => 'string'],
      ]],
      ['name' => 'socials', 'label' => 'Redes sociais', 'type' => 'object', 'fields' => [
        ['name' => 'instagram', 'label' => 'Instagram (URL)', 'type' => 'string'],
        ['name' => 'facebook', 'label' => 'Facebook (URL)', 'type' => 'string'],
      ]],
      ['name' => 'stats', 'label' => 'Números / estatísticas', 'type' => 'list', 'itemType' => 'object', 'itemLabelField' => 'label', 'fields' => [
        ['name' => 'value', 'label' => 'Valor numérico', 'type' => 'number'],
        ['name' => 'prefix', 'label' => 'Prefixo (ex: +)', 'type' => 'string'],
        ['name' => 'suffix', 'label' => 'Sufixo (ex: %)', 'type' => 'string'],
        ['name' => 'label', 'label' => 'Legenda', 'type' => 'string'],
      ]],
    ],
  ],

  'faq' => [
    'label' => 'Perguntas frequentes',
    'file' => 'content/faq.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'q',
    'fields' => [
      ['name' => 'q', 'label' => 'Pergunta', 'type' => 'string'],
      ['name' => 'a', 'label' => 'Resposta', 'type' => 'textarea'],
    ],
  ],

  'services' => [
    'label' => 'Serviços (Água / Alimentos / Controle)',
    'file' => 'content/services.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'title',
    'fields' => [
      ['name' => 'id', 'label' => 'Identificador (sem espaços/acentos)', 'type' => 'string'],
      ['name' => 'title', 'label' => 'Título', 'type' => 'string'],
      ['name' => 'intro', 'label' => 'Texto de introdução', 'type' => 'textarea'],
      ['name' => 'items', 'label' => 'Itens analisados', 'type' => 'list', 'itemType' => 'object', 'itemLabelField' => 'label', 'fields' => [
        ['name' => 'label', 'label' => 'Nome', 'type' => 'string'],
        ['name' => 'norm', 'label' => 'Norma/legislação', 'type' => 'string'],
      ]],
      ['name' => 'chips', 'label' => 'Público-alvo (chips)', 'type' => 'list', 'itemType' => 'string'],
      ['name' => 'image', 'label' => 'Foto do serviço', 'type' => 'image', 'uploadDir' => 'assets', 'maxWidth' => 900, 'fallbackExt' => 'png'],
      ['name' => 'imageAlt', 'label' => 'Texto alternativo da imagem', 'type' => 'string'],
    ],
  ],

  'team' => [
    'label' => 'Equipe',
    'file' => 'content/team.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'name',
    'fields' => [
      ['name' => 'name', 'label' => 'Nome', 'type' => 'string'],
      ['name' => 'role', 'label' => 'Cargo', 'type' => 'string'],
      ['name' => 'image', 'label' => 'Foto', 'type' => 'image', 'uploadDir' => 'assets', 'maxWidth' => 700, 'fallbackExt' => 'png'],
    ],
  ],

  'testimonials' => [
    'label' => 'Depoimentos',
    'file' => 'content/testimonials.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'name',
    'fields' => [
      ['name' => 'quote', 'label' => 'Depoimento', 'type' => 'textarea'],
      ['name' => 'name', 'label' => 'Nome', 'type' => 'string'],
      ['name' => 'company', 'label' => 'Empresa', 'type' => 'string'],
    ],
  ],

  'clients' => [
    'label' => 'Clientes (logos)',
    'file' => 'content/clients.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'name',
    'fields' => [
      ['name' => 'name', 'label' => 'Nome', 'type' => 'string'],
      ['name' => 'logo', 'label' => 'Logo', 'type' => 'image', 'uploadDir' => 'assets', 'maxWidth' => 500, 'fallbackExt' => 'png'],
    ],
  ],

  'segments' => [
    'label' => 'Segmentos (carrossel "Quem somos")',
    'file' => 'content/segments.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'label',
    'fields' => [
      ['name' => 'label', 'label' => 'Nome do segmento', 'type' => 'string'],
      ['name' => 'image', 'label' => 'Foto do segmento', 'type' => 'image', 'uploadDir' => 'assets', 'maxWidth' => 900, 'fallbackExt' => 'jpg'],
    ],
  ],

  'gallery' => [
    'label' => 'Galeria institucional (fotos e vídeos)',
    'file' => 'content/gallery.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'title',
    'fields' => [
      ['name' => 'title', 'label' => 'Título (uso interno — não aparece na tela, só ajuda a identificar o item aqui no painel)', 'type' => 'string'],
      ['name' => 'type', 'label' => 'Tipo', 'type' => 'select', 'options' => [
        ['value' => 'image', 'label' => 'Imagem'],
        ['value' => 'video', 'label' => 'Vídeo do YouTube'],
      ]],
      ['name' => 'image', 'label' => 'Imagem', 'type' => 'image', 'uploadDir' => 'assets', 'maxWidth' => 700, 'fallbackExt' => 'jpg', 'hint' => 'Preencha apenas se o Tipo for Imagem.'],
      ['name' => 'youtubeId', 'label' => 'Vídeo do YouTube', 'type' => 'youtube', 'hint' => 'Preencha apenas se o Tipo for Vídeo. Pode colar a URL completa (ex.: https://youtu.be/XXXXXXXXXXX) ou só o código do vídeo.'],
    ],
  ],

  'documents' => [
    'label' => 'Documentos & certificações',
    'file' => 'content/documents.json',
    'root' => 'list',
    'itemType' => 'object',
    'itemLabelField' => 'title',
    'fields' => [
      ['name' => 'title', 'label' => 'Título', 'type' => 'string'],
      ['name' => 'file', 'label' => 'Arquivo PDF', 'type' => 'file', 'uploadDir' => 'assets/docs', 'accept' => '.pdf'],
      ['name' => 'linkLabel', 'label' => 'Texto do link', 'type' => 'string'],
    ],
  ],
];
