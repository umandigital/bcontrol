# Relatório de produção — Site bcontrol

Este documento acompanha a evolução do apresentável (`bcontrol-site/index.html`) para o
site final publicável, conforme o briefing de produção. Ele lista o que já está pronto,
o que está pendente e por quê, e como editar o conteúdo.

## Estrutura do projeto

```
index.html            página principal
privacidade.html       Política de Privacidade (LGPD) — modelo, pendente revisão jurídica
termos.html             Termos de Uso — modelo, pendente revisão jurídica
404.html                 página de erro 404
robots.txt, sitemap.xml  SEO técnico
netlify.toml             config. de deploy (caso a hospedagem final seja Netlify)
css/style.css             todo o CSS (antes embutido no HTML)
js/main.js                todo o JS (antes embutido no HTML) — carrega o conteúdo dos JSON,
                           acessibilidade, formulários, LGPD, analytics, parallax etc.
content/*.json            conteúdo editável (FAQ, serviços, equipe, clientes, depoimentos,
                           documentos, configurações gerais) — ver seção CMS abaixo
admin/                     painel de edição (Decap CMS) para o cenário Netlify/Cloudflare
assets/                    imagens (.webp otimizado + .png de fallback) e favicons
```

## O que foi feito nesta etapa

### Conteúdo real
- Texto já era HTML real em quase todo o site (herói, quem somos, serviços, FAQ); mantido.
- **Depoimentos** deixaram de ser imagens com texto embutido e viraram cards de texto real
  (bom para SEO e acessibilidade), com placeholders sinalizados até vir o conteúdo real.
- Nomes da equipe agora aparecem como legenda em HTML (antes só existiam como `alt` da imagem).

### Responsividade
- Menu mobile, grades de números/diferenciais/documentos e sliders ajustados para telas
  pequenas (breakpoints adicionais em 640/560/480/420px).
- Parallax (`#dif`, `#cta`) desativado em telas ≤900px e quando o usuário pede "reduzir
  movimento" no sistema — já existia parcialmente, foi reforçado também no JS.

### Mídia
- Todas as imagens convertidas para **WebP** (com fallback `.png` via `<picture>`) e
  redimensionadas para o tamanho real de exibição. Redução de **~23MB para ~1MB** de
  peso transferido em navegadores modernos.
- `loading="lazy"` em todas as imagens fora da dobra inicial.
- **Vídeo do herói recebido e integrado**: `assets/hero.mp4` + `assets/hero.webm`
  (o navegador escolhe o WebM quando suporta, menor). Comprimido de **45MB para ~11MB/10MB**
  sem perda perceptível (áudio removido — o vídeo é mudo no site de qualquer forma).
  Em mobile e com "reduzir movimento" o vídeo não autoplay (sem o atributo `autoplay` no
  HTML, só `preload="metadata"`), então o arquivo inteiro não é baixado à toa nesses casos.

### Formulários
- Formulário de orçamento tem envio real via `fetch`, roteamento por setor (campo oculto
  `setor_email` preenchido a partir de `content/site.json`), honeypot anti-spam.
  **Falta apenas o endpoint** (Formspree/Web3Forms) e os e-mails de cada setor — enquanto
  isso, o formulário avisa o visitante de forma clara em vez de fingir que enviou.
- Link do WhatsApp (`wa.me`) já pré-preenche a mensagem; só falta o número oficial.
- "Acessar resultados" e "Atualizar cadastro" levam ao destino informado em `site.json`
  assim que as URLs forem preenchidas; até lá avisam o visitante em vez de simular.

### SEO técnico
- `<title>` e meta description reais, Open Graph/Twitter cards, `canonical`, dados
  estruturados `LocalBusiness` (JSON-LD), `sitemap.xml`, `robots.txt`, favicons.
- Um único `<h1>` no herói; hierarquia de headings mantida.

### LGPD e acessibilidade
- Banner de cookies agora é **funcional** (aceitar/recusar grava a escolha em
  `localStorage`; GA4 só carrega se o usuário aceitar **e** o ID estiver configurado).
- Páginas `privacidade.html` e `termos.html` publicadas (conteúdo modelo — **precisa de
  revisão jurídica** antes de ir ao ar).
- Foco visível em toda a página, `skip-link`, `aria-expanded`/`aria-label` no menu,
  acordeões e widget de acessibilidade, labels associados aos campos de formulário,
  `role="status"` no toast e nas mensagens do formulário.
- Widget de acessibilidade (fonte/alto contraste) mantido; VLibras segue como pendência
  (depende de decisão do cliente sobre incluir o widget oficial).

### Performance
- CSS/JS extraídos do HTML (cache de navegador entre páginas), `preconnect` de fontes,
  headers de cache configurados no `netlify.toml`, imagens leves (ver Mídia).

### Analytics
- Integração GA4 pronta em `js/main.js`, só ativa com `ga4Id` preenchido em
  `content/site.json` e com consentimento de cookies do visitante.

### CMS / edição de conteúdo
- Conteúdo (FAQ, serviços, equipe, clientes, depoimentos, documentos, configurações
  gerais) foi extraído para `content/*.json` e a página carrega tudo dinamicamente — ou
  seja, **dá para editar sem mexer no HTML**.
- Painel `/admin` (Decap CMS) já configurado em `admin/config.yml` apontando para esses
  arquivos, pronto para o cenário recomendado (Netlify/Cloudflare Pages + Git Gateway).
  Ainda depende da decisão de hospedagem para ser ativado (ver pendência 1 abaixo).

## Pendências que só o cliente pode resolver

Estas dependem de informação/decisão do cliente e não foram assumidas (conforme o
briefing) — o site já está preparado para recebê-las assim que chegarem, bastando editar
`content/site.json` (ou o `/admin`, quando ativo):

1. **Hospedagem final** — cPanel/FTP ou Netlify/Cloudflare Pages? Define se o `/admin`
   (Decap CMS) pode ser ativado como está, ou se precisamos de uma alternativa de painel
   próprio para JSON (cenário cPanel).
2. **Domínio e HTTPS** — para atualizar `canonical`, Open Graph, `sitemap.xml`, `robots.txt`.
3. **E-mails por setor** (Águas/Alimentos/Controle/Comercial) e **endpoint de formulário**
   (Formspree/Web3Forms ou SMTP) — `content/site.json` → `sectorEmails` / `formEndpoint`.
4. **URL do portal de resultados (LIMS)** e como funciona "esqueci a senha" —
   `content/site.json` → `portalResultadosUrl`.
5. **Destino de "Atualizar cadastro"** — `content/site.json` → `cadastroUrl`.
6. **PDFs**: certificado ISO/IEC 17025 (escopo), alvarás, responsável técnica, kit de
   coleta — subir em `assets/docs/` e preencher `content/documents.json` → `file`.
7. **Depoimentos reais** (texto + nome + empresa autorizados) — `content/testimonials.json`.
8. **Números reais** (parâmetros no escopo) — `content/site.json` → `stats`.
9. **GA4** (Measurement ID) e eventual pixel de campanha — `content/site.json` → `ga4Id`.
10. **WhatsApp oficial** — `content/site.json` → `whatsappNumber`.
11. **Redes sociais** (Instagram/Facebook) — `content/site.json` → `socials`.
12. **Revisão jurídica** de `privacidade.html` e `termos.html` (conteúdo modelo).
13. **Cargos da equipe** — `content/team.json` → `role` já preenchido com o texto que estava
    no protótipo; só falta o cliente confirmar se está correto e atualizado.

Nenhuma dessas pendências quebra o site: todos os pontos aparecem com um aviso claro para
o visitante (ex.: "aguardando configuração") em vez de fingir uma ação que não acontece.

## Como editar o conteúdo (para o cliente)

**Sem CMS ativo ainda:** qualquer um dos arquivos em `content/*.json` pode ser editado
diretamente (é texto simples) e publicado — FAQ, serviços, equipe, clientes, depoimentos,
documentos e as configurações gerais (e-mails, WhatsApp, portal, GA4 etc.).

**Com o CMS ativo (`/admin`, após a hospedagem ser definida):** um painel visual permite
editar os mesmos itens sem tocar em código, com convite de usuário controlado pelo
próprio cliente.

## Testes realizados nesta etapa

- Renderização e contagem de itens de cada seção dinâmica (FAQ, serviços, equipe,
  clientes, depoimentos, documentos) validada via teste automatizado (Playwright/Chromium).
- Menu mobile, acordeões, sliders, banner de cookies (aceitar/recusar + persistência),
  envio do formulário (com aviso correto enquanto não há endpoint) testados
  interativamente em desktop (1440px) e mobile (390px).
- Nenhum erro de console/JS encontrado (os únicos erros de rede observados no ambiente de
  teste são bloqueios de rede do próprio sandbox de desenvolvimento para fontes do Google e
  Netlify Identity — **não ocorrem em produção** com acesso normal à internet).
- Vídeo do herói testado depois de integrado: toca automaticamente em desktop (WebM
  escolhido pelo navegador), fica pausado em mobile/reduced-motion sem baixar o arquivo
  todo à toa.

## Rodada 2 de correções (a partir do teste no domínio de teste)

- Fotos da equipe e do escritório vinham do protótipo com texto (nome/cargo/endereço) já
  "queimado" nos pixels, duplicando o texto real renderizado por HTML — corrigido
  recortando a faixa de legenda das imagens.
- Bug de CSS que cortava as fotos de Água/Alimentos/Controle mais do que deveria
  (`height:100%` sem altura de referência) — corrigido com `aspect-ratio`.
- Sliders de Equipe e Depoimentos não respondiam ao clique (IDs desatualizados do
  protótipo) — corrigido; agora também escondem as setas quando não há o que rolar.
- Logo do cabeçalho/rodapé substituído pela marca nova enviada pelo cliente.
- Vídeo do herói (`hero.mp4`/`hero.webm`) integrado e comprimido (45MB → ~10-11MB).

## Ainda no checklist do briefing (próximos passos, após as pendências acima)

- Minificação de CSS/JS para produção (hoje estão legíveis/comentados; minificar no
  pipeline de deploy é trivial e não muda nada funcionalmente).
  Lighthouse/axe formais (o que dá para validar sem hospedagem real já foi revisado
  manualmente: contraste, foco, labels, hierarquia de headings, alt em imagens).
- Testes finais em produção (HTTPS, 404 real, links/âncoras) após o deploy.
- Publicação, apontamento de domínio e envio do `sitemap.xml` ao Google Search Console.
- Monitoramento pós-publicação, backup e plano de manutenção.
