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
css/style.css             todo o CSS (antes embutido no HTML)
js/main.js                todo o JS (antes embutido no HTML) — carrega o conteúdo dos JSON,
                           acessibilidade, formulários, LGPD, analytics, parallax etc.
content/*.json            conteúdo editável (FAQ, serviços, equipe, clientes, depoimentos,
                           documentos, configurações gerais) — ver seção CMS abaixo
admin/                     painel de edição próprio (PHP, sem dependências), em /admin —
                           ver seção "CMS / edição de conteúdo" abaixo
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
- Formulário de orçamento com envio real via `fetch` para `envio.php` (endpoint próprio,
  no mesmo domínio — ver Rodada 8), roteamento por setor, honeypot anti-spam.
- Link do WhatsApp (`wa.me`) com número e mensagem reais.
- "Acessar resultados" envia direto (POST real) para o portal de laudos externo;
  "Atualizar cadastro" leva ao Google Forms real — ambos recuperados do site atual
  (ver "Rodada 3" mais abaixo).

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
- **Painel `/admin` funcional, pronto para a hospedagem cPanel** (ver Rodada 7 abaixo) —
  substituiu o painel baseado em Decap CMS/Netlify cogitado antes, que dependia de um tipo
  de hospedagem que não é o deste projeto.

## Pendências que só o cliente pode resolver

Estas dependem de informação/decisão do cliente e não foram assumidas (conforme o
briefing) — o site já está preparado para recebê-las assim que chegarem, bastando editar
`content/site.json` (ou o `/admin`):

1. ~~**Hospedagem final**~~ — **resolvido**: cPanel. O painel `/admin` já foi construído
   especificamente para esse cenário (PHP puro, sem Netlify/build) — ver Rodada 7.
2. **Domínio e HTTPS** — para atualizar `canonical`, Open Graph, `sitemap.xml`, `robots.txt`.
3. **E-mails por setor "Águas" e "Alimentos"** — hoje apontam para `bcontrol@bcontrol.com.br`
   (Atendimento Geral, e-mail real já usado no site atual) por não haver um endereço
   dedicado a esses dois setores nem no site nem no material recebido. "Controle de
   qualidade" e "Comercial" já usam e-mails reais e específicos (ver Rodada 3 abaixo).
   Se quiser endereços próprios para Águas/Alimentos, é só avisar.
4. ~~**Confirmar recebimento do formulário de orçamento**~~ — **resolvido** e **trocado**:
   o formulário agora envia por um script próprio (`envio.php`, PHP `mail()` nativo do
   servidor), sem depender de serviço de terceiro — ver Rodada 8. **Antes de publicar em
   definitivo**, ver o item "e-mail de teste" no checklist final.
5. **Depoimentos reais** (texto + nome + empresa autorizados) — `content/testimonials.json`.
6. **Números reais** (parâmetros no escopo) — `content/site.json` → `stats`.
7. ~~**GA4**~~ — **resolvido**: Measurement ID da propriedade criada na conta GA4 da
   agência (Uman) configurado em `content/site.json` → `ga4Id` (Rodada 14). Eventual pixel
   de campanha (Meta/Google Ads) segue em aberto, se for do interesse.
8. **Instagram** — não encontrado no site atual (só havia Facebook, já linkado) —
   `content/site.json` → `socials.instagram`.
9. **Revisão jurídica** de `privacidade.html` e `termos.html` (conteúdo modelo).
10. **Cargos da equipe** — `content/team.json` → `role` preenchido com o texto que estava
    no protótipo; só falta confirmar se está correto e atualizado.

Nenhuma dessas pendências quebra o site: todos os pontos aparecem com um aviso claro para
o visitante (ex.: "aguardando configuração") em vez de fingir uma ação que não acontece.

## Como editar o conteúdo (para o cliente)

**Painel `/admin` (recomendado):** acesse `https://SEUDOMINIO/admin` e faça login com a
senha do painel (ver Rodada 7 — **troque a senha padrão assim que publicar**, pelo próprio
painel em "Alterar senha"). Dá para editar FAQ, serviços, equipe, clientes, depoimentos,
documentos (inclusive subir um PDF novo direto pelo navegador) e as configurações gerais
(e-mails por setor, WhatsApp, redes sociais, números, GA4) sem tocar em código. Cada vez
que algo é salvo, a versão anterior do arquivo fica guardada automaticamente em
`content/.backups/` (não acessível pelo navegador), como uma rede de segurança.

**Direto no arquivo (alternativa):** qualquer um dos arquivos em `content/*.json` também
pode ser editado diretamente (é texto simples) e publicado por FTP/File Manager — útil
para quem já tem prática com isso.

**Trocar fotos/logos:** também dá para fazer direto pelo painel (equipe, segmentos,
clientes, serviços) — ele redimensiona e otimiza a imagem automaticamente (ver Rodada 10).
Continua existindo a opção de subir por FTP/File Manager uma imagem já tratada, para quem
preferir.

**Cache do navegador ao trocar `assets/*`, `css/style.css` ou `js/main.js`:** esses
arquivos são referenciados com `?v=N` (ex.: `logo.png?v=4`) — é assim que o navegador sabe
buscar a versão nova em vez de usar uma cópia antiga guardada em cache. Ao substituir um
desses arquivos mantendo o mesmo nome, suba o número da versão: `ASSET_VERSION` no topo de
`js/main.js` (cobre as imagens carregadas via `content/*.json`) e o `?v=` nas tags
`<link>`/`<script>` de `index.html`, `privacidade.html`, `termos.html` e `404.html`.

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

## Rodada 3 — integrações reais recuperadas do site atual (bcontrol.com.br)

Em vez de esperar a resposta da empresa terceirizada (Sline) sobre um iframe novo para o
portal de resultados, fui direto no site atual (`www.bcontrol.com.br`) e encontrei que ele
já tem uma integração real e funcionando com o portal de laudos — bem mais simples do que
um iframe: um formulário HTML puro que envia (POST) para `resultados.com.br`. Aproveitei
essa mesma integração, e de quebra recuperei outros elementos reais que já existiam no ar:

- **Portal de resultados**: formulário "Chave/Senha" agora envia direto para
  `https://www.resultados.com.br/index.aspx?origem=BIOCONTROL` (mesmo endpoint que o site
  atual já usa) — **funciona de verdade, não depende mais da Sline**.
- **Atualizar cadastro**: linkado para o Google Forms real que já estava em uso
  (`forms.gle/hZG2yAordd6pP9uJ8`).
- **Kit de coleta**: PDF real baixado do site atual e disponível para download
  (`assets/docs/coleta_de_amostras.pdf`).
- **Documentos & certificações**: os 4 PDFs reais (Alvará de Funcionamento, Alvará de
  Saúde, Reconhecimento Rede Metrológica/ISO 17025, Responsável Técnica) baixados do site
  atual e hospedados em `assets/docs/`.
- **Pesquisa de satisfação**: link real do Google Forms recolocado no rodapé.
- **Facebook**: link real (`facebook.com/biocontrolrs`) — Instagram não existe no site atual.
- **WhatsApp**: número real (mesmo telefone fixo usado como WhatsApp Business no site atual)
  — `(51) 3434-2401`.
- **E-mails**: `comercial@bcontrol.com.br` (setor Comercial) e `daniela@bcontrol.com.br`
  (Controle de Qualidade) confirmados reais; `bcontrol@bcontrol.com.br` (Atendimento
  Geral) usado como padrão para Águas/Alimentos, que não tinham endereço próprio.
- **Formulário de orçamento**: como o site atual usa Contact Form 7 (plugin de WordPress,
  não portável para um site estático), configurei um envio equivalente, hoje feito por
  `envio.php` (script próprio — ver Rodada 8) apontando para `bcontrol@bcontrol.com.br`.

Isso resolve, sem precisar de mais nenhuma informação do cliente, praticamente todas as
integrações que antes dependiam de dados externos.

## Rodada 4 — cache do navegador resolvido na raiz (.htaccess)

Vários "bugs" reportados nas rodadas de teste (logo antigo, foto com endereço embutido,
aviso de formulário desatualizado, e o mais grave: o botão "Acessar resultados" abrindo uma
aba em branco) tinham a mesma causa real: o **navegador de quem testava guardava uma cópia
antiga do `index.html`** em cache, mesmo com o arquivo já atualizado no servidor. O caso da
aba em branco em particular era o `index.html` de uma versão bem anterior (antes da Rodada 3),
que ainda tentava abrir uma URL de placeholder numa aba nova — testei o login com as
credenciais reais fornecidas e confirmei que a integração funciona perfeitamente (login
bem-sucedido, navega para `resultados.com.br/Lista/Index` na mesma aba).

Correção definitiva: adicionado `.htaccess` na raiz do site instruindo o Apache a nunca
deixar o navegador guardar páginas `.html` em cache (`Cache-Control: no-cache, no-store,
must-revalidate`), mantendo cache normal só para os arquivos versionados (`css`, `js`,
imagens, PDFs, vídeo). Também adicionadas as mesmas diretivas como `<meta http-equiv>` no
`<head>` de cada página, como reforço para o caso de o `.htaccess` não ser aplicado (ex.:
módulo `mod_headers` desativado na hospedagem). **A partir de agora, qualquer atualização
enviada aparece automaticamente no próximo carregamento da página, sem precisar de
Ctrl+Shift+R.**

## Rodada 5 — carrossel de segmentos e limpeza da faixa de números

- Seção "Quem somos": a foto única deu lugar a um **carrossel automático** (troca sozinha
  a cada 4s, com transição suave) com as 5 fotos de segmentos enviadas pelo cliente
  (Indústrias, Food service, Condomínios, Farmácias, Clínicas), cada uma já com o rótulo
  aplicado na própria imagem. Editável em `content/segments.json` (ou pelo `/admin`).
  Pausa automaticamente para quem usa "reduzir movimento" no sistema.
- Removido o indicador "+[a conf.] parâmetros no escopo" da faixa de números (ficaram só os
  3 números reais: anos de atuação, clientes ativos, crescimento médio).

## Rodada 6 — texto novo do "Quem somos" (maior, reestruturado)

O cliente enviou um texto bem mais completo para "Quem somos", que não cabia mais no
layout compacto anterior (duas colunas, texto curto + foto). Reestruturado assim, mantendo
100% do texto novo:

- O parágrafo de abertura ficou mais enxuto visualmente: a lista de segmentos
  ("Atuamos lado a lado com: Indústrias, Food service...") virou **chips** (tags),
  reaproveitando um padrão visual que já existia no site, em vez de uma lista com
  marcadores — fica mais compacto e ainda conecta com o carrossel de fotos ao lado.
- "Excelência técnica e atendimento consultivo" virou um bloco de texto logo abaixo,
  dentro da mesma seção "Quem somos".
- "Nosso Propósito Institucional" (Missão / Visão / Valores) virou uma **seção nova**,
  com os três em cartões lado a lado (empilham no mobile). Os textos de Missão e Visão
  batem com o que já existia no site atual da bcontrol, então é conteúdo real e
  consistente, não inventado.

## Rodada 7 — painel de edição próprio para cPanel + empacotamento final

Com a hospedagem confirmada como cPanel (sem Netlify/build), o painel que estava pensado
para Decap CMS + Git Gateway não funcionaria — essa dependência está documentada desde o
início como a pendência nº 1. Decisão tomada: construir um painel próprio em PHP puro
(`admin/`), que funciona em qualquer hospedagem cPanel/Apache com PHP (a praticamente
totalidade delas), sem precisar de Node, build ou serviço externo.

**O que o painel faz:**
- Login por **e-mail + senha** (multiusuário — ver Rodada 9), protegido por sessão e
  proteção simples contra força bruta.
- Um formulário por tipo de conteúdo (Configurações gerais, FAQ, Serviços, Equipe,
  Depoimentos, Clientes, Segmentos, Documentos), com campos organizados (não é preciso
  editar JSON manualmente), incluindo listas que podem ganhar ou perder itens
  ("+ Adicionar item" / "Remover") direto na página.
- Upload de PDF novo para a seção "Documentos & certificações" direto pelo navegador.
- Backup automático: antes de qualquer gravação, a versão anterior do arquivo é copiada
  para `content/.backups/` (pasta bloqueada para acesso externo via `.htaccess`).
- Testado localmente (PHP 8.4) ponta a ponta: login, senha incorreta, proteção CSRF,
  edição simples (FAQ), edição com listas aninhadas (Serviços — itens + chips), upload de
  PDF (Documentos) e troca de senha — todos funcionando e o conteúdo final conferido
  byte a byte contra o original (sem perda de dados).

**O que foi removido:** `admin/config.yml` e `admin/index.html` (Decap CMS), `netlify.toml`
e o script do Netlify Identity que estava em `index.html` — todos dependiam do cenário
Netlify, que não se aplica a esta hospedagem.

## Rodada 9 — painel passou a ser multiusuário (e-mail + senha)

A pedido, o painel deixou de ter uma senha única compartilhada e passou a ter **contas
individuais** (e-mail + senha), guardadas em `admin/users.json` (bloqueado para acesso
externo, igual ao antigo `auth_data.json`, que foi removido):

- O primeiro acesso já vem configurado para `luciano@umandigital.com.br`, com a senha
  combinada diretamente (não registrada em texto neste relatório, só o hash fica salvo
  no servidor).
- Depois de logado, qualquer usuário pode criar outros em `/admin` → **"Usuários"** — todos
  com os mesmos poderes (editar qualquer conteúdo e criar/remover outros usuários), sem
  distinção de papel/cargo.
- Não é possível remover o próprio usuário logado nem o último usuário restante (trava de
  segurança para nunca ficar sem acesso nenhum ao painel).
- Cada usuário troca a própria senha em "Alterar senha" (exige a senha atual).
- Testado localmente: login com o usuário inicial, criação de usuário novo + login com
  ele, remoção de usuário, proteção contra remover o último/o próprio usuário logado, e
  troca de senha — tudo funcionando.

## Rodada 10 — upload de imagem direto pelo painel (sem precisar de FTP)

Até aqui, trocar uma foto (equipe, segmentos, clientes, serviços) exigia acesso à
hospedagem (FTP/File Manager) e conhecimento do pipeline de otimização (gerar `.webp` +
redimensionar). Como quem vai operar o painel no dia a dia não tem (nem deve precisar ter)
acesso à hospedagem, os campos de imagem desses 4 conteúdos (Serviços, Equipe, Clientes,
Segmentos) agora aceitam **upload direto pelo navegador**:

- Mostra uma prévia da imagem atual antes de trocar.
- Ao enviar um arquivo novo (JPG, PNG ou WEBP), o servidor: corrige a rotação de fotos de
  celular (quando o aparelho grava a informação de orientação), redimensiona para o
  tamanho real de exibição (sem aumentar imagens que já são menores) e gera as duas
  versões que o site usa — `.webp` leve e o formato de reserva (`.png`, preservando
  transparência de logos, ou `.jpg` para os segmentos) — exatamente como as imagens já
  tratadas manualmente nas rodadas anteriores.
- O nome do arquivo final é gerado automaticamente (com um sufixo único), e os arquivos
  antigos desse mesmo campo são apagados — sem acumular lixo nem precisar mexer em
  `ASSET_VERSION`/cache do navegador, porque o nome novo já força o carregamento fresco.
- Continua dando pra editar o caminho manualmente (sem subir arquivo), para quem preferir
  seguir subindo a imagem já tratada por FTP, como antes.
- Depende da extensão **GD do PHP** (padrão em praticamente toda hospedagem cPanel); se
  não estiver disponível, o painel avisa com uma mensagem clara em vez de falhar calado.

**Testado localmente**: upload com redimensionamento correto (1200×900 → 700×525 para
Equipe), não ampliação de imagem menor que o tamanho máximo, preservação de transparência
em logo PNG, achatamento correto para fundo branco no fallback `.jpg` dos Segmentos, e
remoção dos arquivos antigos após a troca.

Aproveitada a rodada para trocar também a foto do escritório (seção de Orçamento) pela
nova enviada pelo cliente (`assets/escritorio.png`/`.webp`, versão `?v=5`, depois corrigida
na Rodada 12 abaixo).

## Rodada 11 — galeria institucional (fotos e vídeos) em "Quem somos"

Nova seção "Conheça toda a estrutura da Bcontrol" logo abaixo de "Excelência técnica",
dentro da mesma dobra institucional — um carrossel paginado (setas + indicadores) com
fotos e vídeos do YouTube, cada item abrindo ampliado em um modal ao clique. Totalmente
administrável pelo painel (`/admin` → "Galeria institucional"), sem precisar mexer em
código:

- Cada item tem um **Título** (uso só interno/acessibilidade — não aparece na tela, a
  pedido do cliente: o layout de referência tinha legenda visível, mas ficou definido
  que não é necessária), um **Tipo** (Imagem ou Vídeo do YouTube), um campo de **upload
  de imagem** (mesmo mecanismo de redimensionamento/otimização das Rodadas 10) e um campo
  de **vídeo do YouTube** — aceita colar a URL completa (`youtube.com/watch?v=...`,
  `youtu.be/...`, `/embed/...`, `/shorts/...`) ou só o código do vídeo; o painel extrai o
  código sozinho.
- No site, vídeos mostram a miniatura oficial do YouTube (sem precisar fazer upload de
  capa) com um ícone de "play"; imagens mostram um ícone de foto — ambos no canto inferior
  direito da miniatura, como no layout de referência.
- Carrossel com paginação por página inteira (não item a item), setas e bolinhas
  indicadoras, que só aparecem quando há mais itens do que cabem na tela.
- Modal (lightbox): imagem em tamanho ampliado, ou o vídeo do YouTube incorporado
  (`iframe`) já em reprodução; fecha pelo X, clicando fora, ou com a tecla Esc.
- Se não houver nenhum item cadastrado, a seção inteira fica oculta (não aparece uma
  faixa vazia) — assim que o primeiro item for cadastrado pelo painel, ela aparece
  automaticamente.
- `content/gallery.json` começa vazio (`"items": []`) — as fotos/vídeos reais devem ser
  cadastrados pelo cliente ou pela agência direto no painel.

**Testado**: renderização e paginação (8 itens de teste, 2 páginas), abertura/fechamento
do modal para imagem e para vídeo (Esc, X e clique fora), extração do código do YouTube a
partir de 4 formatos de URL diferentes — tudo via Playwright/Chromium e testes unitários
da extração.

## Rodada 12 — corrigida sombra duplicada na foto do escritório

A imagem enviada pelo cliente (Rodada 10) já vinha com uma moldura branca arredondada e
sombra próprias "embutidas" (um card/mockup pronto), que o CSS do site duplicava ao
aplicar seu próprio `border-radius` + `box-shadow` por cima. Recortada a moldura/sombra
original da imagem antes de redimensionar, mantendo só a foto real — agora só a sombra do
CSS do site aparece.

## Rodada 13 — ajustes finos na galeria (setas do modal + posição das setas do carrossel)

- O modal (lightbox) ganhou **setas de navegação** (anterior/próximo) para passar entre
  as fotos/vídeos sem precisar fechar e abrir de novo — também funciona pelas setas
  `←`/`→` do teclado. Só aparecem quando há mais de um item.
- As setas do **carrossel** da galeria estavam sobrepondo a primeira/última foto (ficavam
  "em cima" da imagem). Reposicionadas para fora da área das fotos, com espaço próprio.

## Rodada 8 — formulário de orçamento passou a usar envio próprio (`envio.php`)

O endpoint formsubmit.co (serviço gratuito de terceiro) funcionava, mas tinha três
limitações que um script próprio no mesmo servidor resolve de vez: anúncio de patrocinador
nos e-mails recebidos (só sai no plano pago), textos padrão em inglês (contornado com um
parâmetro, mas sempre meio remendado) e a exigência de clique de confirmação para cada
endereço novo. Seguindo o mesmo padrão já usado em outros projetos da agência (ex.: Uman),
criado `envio.php` na raiz do site:

- Recebe o POST do formulário (`#formOrc`), valida nome/e-mail/setor e confere o campo
  escondido anti-spam (honeypot) — igual já funcionava antes.
- **O destinatário é decidido no servidor**, lendo `content/site.json` (`sectorEmails` e,
  enquanto preenchido, `testCcEmail`) — o navegador não escolhe mais pra onde o e-mail vai,
  o que fecha a possibilidade de alguém usar o formulário pra mandar e-mail para um
  endereço arbitrário.
- Envia com `mail()` nativo do PHP (sem precisar de conta em serviço nenhum, nem SMTP) —
  funciona de forma simples na grande maioria das hospedagens cPanel, que já vêm com
  sendmail/Exim configurado.
- Sanitiza os campos (remove quebras de linha) antes de usar em assunto/cabeçalho, para
  impedir injeção de cabeçalho de e-mail.
- Resposta em JSON (`{"ok":true}` ou `{"ok":false,"error":"..."}`), consumida pelo mesmo
  `fetch()` que já existia em `js/main.js` (só trocou o endereço de destino e removeu a
  lógica que agora é só do servidor).

**Teste realizado**: validação de método (só aceita POST), honeypot, campos obrigatórios,
e a lógica de resolução de destinatário (confirmado que `testCcEmail` é priorizado quando
preenchido) — tudo conferido localmente com PHP. O envio de e-mail de verdade (`mail()`)
só pode ser validado na hospedagem final, porque depende do sendmail/Exim do servidor, que
não existe neste ambiente de desenvolvimento — funciona de forma padrão em qualquer cPanel.

**Recomendação para a primeira ativação em produção**: abra `envio.php` e confirme que o
e-mail definido em `FROM_EMAIL` (hoje `nao-responda@bcontrol.com.br`) é um endereço real
do domínio — isso ajuda a não cair em spam. Depois, mande um pedido de teste pelo próprio
site com `testCcEmail` preenchido para confirmar a entrega antes de liberar de vez.

## Rodada 14 — e-mail de teste desativado + Google Analytics 4 ativado

- `content/site.json` → `testCcEmail` esvaziado: os pedidos de orçamento, que durante os
  testes todos caíam no e-mail de teste (`luciano@umandigital.com.br`), agora chegam
  direto nos e-mails reais por setor (`sectorEmails`), como já estava configurado.
- `content/site.json` → `ga4Id` preenchido com o Measurement ID (`G-RZ1NK1PP6T`) da
  propriedade "Site Bcontrol" criada na conta GA4 da agência (Uman). A integração já
  existia pronta desde o início (só carrega depois que o visitante aceita o banner de
  cookies, requisito de LGPD) — confirmado localmente que o script do GA4 é injetado com
  o ID correto assim que o consentimento é dado.

## Rodada 15 — assunto do e-mail diferente por setor

A pedido, o e-mail que cada setor recebe agora tem um assunto próprio (antes era sempre
"Novo pedido de orçamento — bcontrol (setor)"):

| Setor | Assunto do e-mail |
|---|---|
| Águas | Dúvidas/Sugestões |
| Alimentos | Dúvidas/Sugestões |
| Controle de qualidade | Controle de Qualidade |
| Comercial | Pedido de Orçamento |

(prefixo "bcontrol - " testado inicialmente e removido a pedido do cliente)

Novo campo `content/site.json` → `sectorSubjects` (mesmo formato de `sectorEmails`),
editável também pelo `/admin` → "Configurações gerais". `envio.php` usa esse assunto na
hora de montar o e-mail; se um setor não tiver assunto configurado, usa o nome do próprio
setor como reserva.

## Rodada 16 — revisão geral mobile

A pedido, revisão completa da versão mobile do site (390px, 768px e 900px testados via
Playwright/Chromium), com dois bugs reais encontrados e corrigidos no cabeçalho, o
reposicionamento pedido do link de pesquisa de satisfação, e uma melhoria de usabilidade
nos carrosséis:

- **Cabeçalho mobile "mal organizado" (o problema relatado):** a barra utilitária ("Área
  do cliente": login de resultados, atualizar cadastro, solicitar kit, solicitar
  orçamento) ficava **sempre visível acima do logo**, empurrando a marca/menu pra baixo da
  tela e quebrando em várias linhas numa bagunça de campos e botões antes mesmo do
  visitante ver o site. Agora essa barra só aparece dentro da gaveta do menu (☰), junto
  com os links de navegação — o cabeçalho fechado mostra só logo + ☰, enxuto.
- **Bug real no menu aberto:** ao abrir o menu (☰), os links de navegação apareciam
  encolhidos numa coluna à direita, sobrepondo o logo por baixo — causado por duas regras
  de CSS do desktop (`flex:1` e um `margin:0 auto` de centralização) que não tinham sido
  resetadas para o layout em coluna do mobile. Corrigido: o menu agora ocupa a largura
  toda, abaixo do logo, sem sobreposição, em qualquer largura de tela (testado em 390px,
  768px e 900px).
- **"Pesquisa de satisfação"** saiu da lista de links "Institucional" no rodapé e virou um
  botão de destaque (CTA) ao lado dos ícones de Instagram/Facebook, como pedido.
- **Carrosséis de Equipe e Depoimentos**: no mobile, as setas de navegação somem (não há
  espaço) e o "espiadela" do próximo card é bem sutil — sem nenhuma pista de que dava pra
  arrastar pros lados. Adicionadas bolinhas indicadoras (reaproveitando uma classe
  `.dots`/`.dot` que já existia pronta no CSS, mas nunca tinha sido usada em lugar
  nenhum), que mostram quantos itens existem e em qual o visitante está, clicáveis para
  navegar direto. Só aparecem no mobile e só quando há mais itens do que cabem na tela.

**Testado**: nenhuma regressão no desktop (cabeçalho e rodapé conferidos em 1440px),
sem scroll horizontal indevido na página (só os carrosséis, que são assim de propósito),
menu mobile funcional em 390/768/900px, dots clicáveis sincronizando com a posição do
scroll.

## Rodada 17 — revisão final de SEO e limpeza antes da publicação

Revisão completa de tudo que foi implementado para SEO, mais uma varredura geral por
pendências antes do empacotamento final:

**SEO verificado:**
- `<title>`, meta description, `canonical`, Open Graph/Twitter cards e dados estruturados
  (`LocalBusiness`, JSON-LD validado) na página principal — todos presentes e corretos.
- **Corrigido**: `privacidade.html` e `termos.html` **não tinham `<link rel="canonical">`**
  (só a página principal tinha) — adicionado nas duas.
- Hierarquia de headings conferida (um único `<h1>`, `<h2>`/`<h3>` em ordem lógica, sem
  pular nível) — ok.
- Todas as imagens com `alt` preenchido — ok.
- Todos os links internos (`#quem`, `#serv` etc.) batem com um `id` existente — ok.
- `robots.txt` e `sitemap.xml` consistentes (apontam pro domínio final
  `www.bcontrol.com.br`, `/admin/` bloqueado) — adicionado `<lastmod>` no sitemap.
- Adicionado `image` nos dados estruturados `LocalBusiness` (não existia, ajuda o Google a
  mostrar uma imagem no resultado de busca).
- Todos os arquivos de `content/*.json` validados (JSON sem erro de sintaxe) e **todas as
  imagens/PDFs que eles referenciam conferidos como existentes** no disco (nenhuma
  imagem/arquivo quebrado).
- Teste automatizado (Playwright) em todas as 4 páginas, desktop e mobile: nenhum erro de
  console/JavaScript em nenhuma.

**Limpeza de código morto/texto desatualizado encontrada e corrigida:**
- 3 botões de WhatsApp (`#cta`, `#contato`, flutuante) ainda tinham um aviso
  `data-toast="WhatsApp: aguardando número oficial"` no HTML — **desatualizado**, o número
  real já está configurado desde a Rodada 3 e o JavaScript já sobrescrevia esse aviso em
  tempo de execução; removido da fonte para não ficar um texto incorreto visível em caso
  de falha do JS ou inspeção de código.
- Removida uma variável e um comentário mortos em `js/main.js` (referência a um aviso do
  "kit de coleta" que também já tinha sido resolvido na Rodada 3, mas a variável/comentário
  continuavam no código sem efeito nenhum).

**Pendências reais, que só o cliente pode resolver (nenhuma é um bug):** seguem listadas
e sem alteração no checklist abaixo — Instagram, revisão jurídica das páginas legais,
depoimentos/números reais (se os atuais ainda não forem definitivos), e confirmar o
domínio final.

## Rodada 18 — 3 bugs reportados no mobile

Três problemas relatados depois de testar no celular, todos investigados e confirmados
como bugs reais (não só "impressão"), com causa raiz identificada antes de corrigir:

- **Vídeo "não aparecia" no mobile.** Causa: por economia de dados móveis, o vídeo do
  topo (`hero.mp4`/`.webm`, ~10MB) nunca é tocado no celular — isso é de propósito (o
  próprio código já tinha esse comentário), e em vez do vídeo é mostrada uma imagem
  estática (`assets/hero_fundo.png/webp`) no lugar. O problema real: essa imagem estática
  era só uma textura abstrata de "bolhas" desfocadas, sem o gotejador (o elemento visual
  principal da marca) — por isso parecia que "não tinha nada ali", mesmo a imagem
  carregando certinho. **Corrigido**: a imagem estática foi trocada por um frame real
  extraído do próprio vídeo (o gotejador bem nítido), então agora o celular mostra o
  gotejador parado como pano de fundo do topo, em vez de uma textura genérica — sem
  gastar os 10MB do vídeo. Mantido o enquadramento (`object-position`) ajustado para a
  faixa mobile, que já tinha sido corrigido numa rodada anterior para não cortar o
  gotejador fora da tela.
- **Cor de fundo do botão "Pesquisa de satisfação" com problema.** Conferido pelo
  navegador (inspecionando o CSS calculado) que a regra de cor está correta
  (`var(--teal)`, a mesma cor verde-azulada usada em outros botões do site) — não é um
  erro no código do botão em si. A explicação mais provável é o recurso "Forçar tema
  escuro" do Chrome para Android (ativado por padrão em muitos aparelhos), que recolore
  páginas que não avisam explicitamente que são um site "claro". Adicionada a tag
  `<meta name="color-scheme" content="light">` nas 4 páginas do site — é a forma padrão
  de dizer ao navegador "não recolorir isso", e resolve esse tipo de caso.
- **Faltava uma profissional no carrossel da Equipe.** Confirmado que as 3 profissionais
  estão certinhas no arquivo de conteúdo (`content/team.json`) — não é perda de dado.
  O bug real: no mobile, o primeiro card (Fabíola) estava **impossível de alcançar** por
  scroll — não só "difícil", testado diretamente no navegador e o carrossel não se movia
  nem um pixel para trás do segundo card. Causa: a regra de CSS que centralizava os
  cards (`justify-content:center`) empurrava o primeiro card para antes do ponto onde o
  navegador consegue rolar (não existe "scroll negativo"), uma particularidade conhecida
  de contêineres flexbox com scroll e conteúdo maior que a tela. Corrigido para alinhar
  à esquerda só na faixa mobile, e testado clicando em cada bolinha indicadora: as 3
  profissionais agora aparecem, nessa ordem — Fabíola, Vanessa, Daniela.

**Testado**: as 3 correções verificadas com teste automatizado (Playwright, 390px de
largura) — carrossel de equipe percorrendo as 3 bolinhas sem travar, imagem nova do topo
carregando e mostrando o gotejador, meta tag de `color-scheme` presente nas 4 páginas.
CSS cacheado atualizado de `?v=10` para `?v=11` nas 4 páginas, para o visitante não ficar
com a versão antiga guardada no navegador.

## Rodada 19 — checagem de segurança do painel `/admin`

Revisão linha a linha de toda a autenticação e do `.htaccess`, à procura de brechas
reais (não só teóricas). Achados dois problemas de verdade, já corrigidos:

- **O bloqueio por tentativas erradas de login não bloqueava de verdade.** Ele contava
  as tentativas erradas na própria sessão do navegador (cookie) de quem estava tentando
  — então bastava o atacante **não enviar o cookie** (ou simplesmente usar um cookie
  novo a cada tentativa) para o contador voltar a zero sempre, testando senha
  infinitamente sem nunca ser bloqueado de fato. **Corrigido**: o contador agora é
  guardado num arquivo no servidor por **IP** (`admin/login_attempts.json`, bloqueado
  de acesso direto pelo `.htaccess` do mesmo jeito que `users.json`), então o bloqueio
  de 5 tentativas/60s vale mesmo que o atacante troque de sessão. Testado na prática:
  simulei 5 tentativas erradas, troquei o cookie como se fosse um navegador novo, e a
  6ª tentativa (de qualquer cookie) continuou bloqueada.
- **O cookie de sessão do painel não tinha as proteções básicas** (`HttpOnly`,
  `SameSite`, e `Secure` quando em HTTPS) — isso deixava a sessão de login mais exposta
  a ser roubada via um eventual XSS ou rede insegura. Corrigido: o cookie agora sai com
  `HttpOnly` (JavaScript não consegue ler), `SameSite=Lax`, `Secure` automático quando o
  site estiver em HTTPS (detecta sozinho, não quebra se ainda estiver em HTTP durante
  testes), e restrito ao caminho `/admin` (não é enviado nas páginas públicas do site).
  `logout.php` também passou a expirar esse cookie explicitamente, não só destruir a
  sessão no servidor.

Mais três reforços (sem bug concreto por trás, mas defesa em profundidade, zero risco
de quebrar algo):
- `Options -Indexes` no `.htaccess` — impede listagem de arquivos de uma pasta (ex.:
  `/content/`) caso a hospedagem tenha esse recurso ligado por padrão.
- Bloqueado acesso direto a `.git/`, `.env`, `.htaccess`, `.htpasswd` pelo `.htaccess` —
  proteção extra caso algum dia os arquivos internos do repositório (não os do zip de
  entrega) acabem indo parar no servidor por engano.
- Cabeçalhos `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` e
  `Referrer-Policy` adicionados em todas as respostas.
- Login agora sempre faz a verificação de senha (contra um hash fixo, se o e-mail não
  existir) — antes, um e-mail inexistente respondia mais rápido que um existente, o
  que teoricamente permitiria descobrir por tempo de resposta quais contas existem.

**O que já estava correto e não precisou mexer**: senhas com `bcrypt`
(`password_hash`/`password_verify`), proteção CSRF em todo formulário que grava dado,
`session_regenerate_id()` no login (evita fixação de sessão), `users.json` e
`auth_data.json` bloqueados de acesso direto, upload de imagem validado de verdade (lê
os bytes da imagem com `getimagesize()`, não só a extensão do nome do arquivo — então
não dá pra disfarçar um arquivo malicioso de imagem só trocando a extensão), nomes de
arquivo enviados sempre sanitizados antes de salvar (sem como escrever fora da pasta
pretendida), e todo texto exibido de volta na tela passa por `htmlspecialchars`.

**Decisão de design, não bug**: qualquer usuário logado pode criar/remover outros
usuários do painel — isso foi um pedido explícito (sem hierarquia entre contas), então
não é uma falha a corrigir, só vale lembrar: se uma conta for comprometida, quem a
controla pode criar outras contas. Não há log de quem fez o quê — também aceitável para
uma equipe pequena, mas registrar aqui para o caso de precisar de auditoria no futuro.

**Testado**: todo o fluxo de login/logout/CSRF/senha errada verificado após as
mudanças, com servidor PHP local — login legítimo continua funcionando normal, bloqueio
por tentativas erradas confirmado funcionando mesmo trocando de cookie, nenhuma
regressão nas outras páginas do painel.

## Rodada 20 — achado importante: zip de atualização apagando conteúdo do admin

Foto/depoimento inserido pelo `/admin` no ambiente de teste
(`umandigital.com.br/bcontrol_sitev2`) sumiu depois de uma atualização. Causa raiz
confirmada: **os arquivos `content/*.json` fazem parte do repositório** (é assim que a
primeira publicação já vai com o conteúdo inicial pronto). Isso significa que todo zip
"site completo" gerado a partir do repositório — como os que mandei nas Rodadas 18 e 19
— carrega a versão **original** desses arquivos, não o que foi editado depois ao vivo
pelo painel. Quando esse zip é reenviado por cima do site, ele sobrescreve exatamente
os arquivos que o `/admin` tinha acabado de atualizar, revertendo para o conteúdo de
exemplo — sem aviso nenhum, porque do ponto de vista do zip é só "o site completo de
novo".

**Infelizmente não tem como recuperar automaticamente** o que foi perdido: o sistema de
backup do próprio painel (`content/.backups/`) só guarda a versão de **antes** de cada
salvamento feito pelo `/admin` — e a sobrescrita por zip não passa pelo `/admin`, então
não gerou backup nenhum do que foi perdido. Se quem editou ainda tiver as fotos/textos
originais à mão, só editar de novo.

**Corrigido o processo daqui pra frente**: qualquer atualização futura de código (bug
fix, ajuste de CSS, etc.) enviada para um ambiente onde o `/admin` já estiver em uso
**não deve incluir `content/*.json`** — só o primeiro deploy de um domínio novo (onde
ainda não existe conteúdo editado) deve levar esses arquivos. Daqui pra frente vou
gerar dois tipos de pacote:
- **Pacote de primeira publicação** (um domínio que nunca recebeu o site): inclui tudo,
  `content/*.json` incluso, porque é o ponto de partida.
- **Pacote de atualização** (um domínio que já está no ar e já tem conteúdo editado via
  `/admin`): todo o código (HTML/CSS/JS/admin), **sem** `content/*.json` — assim o
  conteúdo já inserido nunca é tocado.

## Rodada 21 — causa raiz real do CTA, TECNOPUC e logo com fundo transparente

- **Achada a causa real do bug do CTA "Pesquisa de satisfação".** Nas rodadas
  anteriores eu tinha corrigido só um efeito colateral (modo escuro forçado do
  Android); o print mandado desta vez mostrou o problema de verdade: o botão estava
  virando um quadrado de 38×38px (do tamanho dos ícones de Instagram/Facebook do lado),
  com o texto "Pesquisa de satisfação" vazando pra fora da área colorida — por isso
  parecia que faltava cor de fundo. Causa: a regra `.social a` (que estiliza os ícones)
  é mais específica no CSS do que `.satisfacao-cta`, então vencia e forçava o tamanho
  de ícone no botão, mesmo ele tendo sua própria regra de pílula. Corrigido excluindo
  explicitamente o `.satisfacao-cta` da regra `.social a`. Testado: o botão agora mede
  ~200px de largura (não 38px), com fundo verde-azulado preenchendo atrás do texto
  inteiro.
- **"TECNOPUC" removido** da linha de endereço que fica embaixo da foto do escritório,
  ao lado do formulário de orçamento (continua aparecendo nos outros lugares do site —
  topo, "quem somos", rodapé — só foi tirado desse local específico, como pedido).
- **Logos de clientes agora ficam com fundo transparente automaticamente.** Ao enviar
  uma logo pelo campo "Clientes" no admin, o sistema detecta a cor de fundo (lendo os 4
  cantos da imagem) e apaga só o fundo — a parte conectada à borda da imagem — deixando
  transparente, sem mexer em partes internas da logo (ex.: o miolo de uma letra "O" ou
  "R" continua do jeito que está, só o fundo ao redor da logo é que vira transparente).
  Funciona com qualquer cor de fundo sólida (branco é o caso mais comum, mas não é a
  única). Testado com uma imagem sintética (fundo branco + uma forma com um furo
  branco por dentro) e depois com uma simulação de upload real pelo painel: o fundo
  externo saiu transparente e o furo interno continuou intacto.

**Testado**: as 3 correções verificadas com teste automatizado antes de subir — CTA
medido via `getBoundingClientRect` (confirma a largura/formato corretos), remoção de
fundo testada isoladamente e depois pelo fluxo real de upload do admin (login, POST
multipart, conferência do PNG/WEBP gerados sobre um fundo escuro para enxergar a
transparência).

## Rodada 22 — achado: cache do navegador escondendo as correções

Depois de confirmar que o servidor já estava com o código certo (CSS com a correção
do CTA, imagem do gotejador certa), o problema ainda aparecia no celular. Causa: **eu
mesmo esqueci de atualizar o número de versão** depois de editar `css/style.css` de
novo na Rodada 21 (continuava `?v=11`, o mesmo da rodada anterior) — e a imagem
`assets/hero_fundo.png`/`.webp` nunca teve nenhum número de versão, desde que foi
trocada pela primeira vez. Como esses arquivos ficam guardados no navegador por até 7
dias (`Cache-Control: public, max-age=604800`, de propósito, pra não pesar o site),
qualquer celular que já tinha visitado o site antes continuava vendo a versão antiga
desses dois arquivos específicos — o resto do site (HTML, que não tem cache) sempre
aparecia atualizado, por isso só esses dois pontos pareciam "travados".

**Corrigido**: `css/style.css` foi de `?v=11` para `?v=12` (toda vez que o CSS mudar
de novo, o número tem que subir — isso ficou documentado aqui pra não esquecer de
novo), e a imagem do topo passou a ter `?v=2` em todos os lugares onde é referenciada
(o `poster` do vídeo, Open Graph, Twitter Card e dados estruturados) — antes ela nunca
tinha nenhum número, por isso nunca se atualizava sozinha no navegador de quem já
tinha visitado o site.

**Para quem só visitou o site depois da Rodada 21** (sem ter em cache a versão
antiga), nada disso é visível — o problema só afeta quem abriu o site antes e está
revisitando o mesmo link.

## Rodada 23 — logo da Uman no rodapé

O texto "Feito por Uman" no rodapé virou um link clicável com o logo oficial da Uman
(`assets/uman-logo.svg`, fornecido pelo cliente), apontando para
`https://www.umandigital.com.br` — **assumi essa URL por ser a mesma usada no
ambiente de teste do projeto; avisar se for outro endereço**. Testado em desktop e
conferido que não corta nem quebra linha no rodapé.

CSS de novo em `?v=13` (lição da Rodada 22 aplicada: toda edição em
`css/style.css` sobe o número junto, nesta mesma rodada, sem esperar o usuário
reportar que não viu a mudança).

## Rodada 24 — logo da Uman maior e sem a frase ilegível

A pedido: aumentado o logo "Uman" no rodapé (16px → 22px de altura) e removida a
frase "Presença e Performance Digital" que vinha embutida no SVG abaixo do nome —
nesse tamanho pequeno ela não dava pra ler mesmo, só ficava como ruído visual.
Recortei o `viewBox` do SVG para enquadrar só a marca (nome + bolinha laranja), sem
sobrar espaço em branco onde estava o texto. CSS em `?v=14`.

## Rodada 25 — lista de alterações de texto/conteúdo (PDF do cliente)

Aplicadas todas as alterações pedidas no PDF enviado:

- **"Laboratório acreditado" → "Laboratório reconhecido"** e **"Acreditação ISO/IEC
  17025" → "Reconhecimento ISO/IEC 17025"**, em todos os lugares do site onde isso
  aparecia (topo, meta tags, dados estruturados, Diferenciais, Visão institucional) —
  é uma distinção importante para auditoria (acreditação formal tem exigências
  diferentes de reconhecimento), então troquei em todas as ocorrências, não só nos
  pontos citados no PDF.
- **"TECNOPUC" → "IFRS TecnoParque"**, em todos os lugares (topo, "Quem somos",
  endereço do rodapé, dados estruturados para SEO).
- **"Quem somos"**: parágrafo substituído pelo texto novo enviado; "Food service" →
  "Serviços de alimentação"; removido "Farmácias"; adicionados "Hospitais" e
  "Escolas" na lista de segmentos atendidos (texto). O carrossel de fotos ao lado
  (`segments.json`) foi ajustado do mesmo jeito (removida a foto de farmácia, mantido
  "Food service" renomeado), mas **não incluí fotos para Hospitais/Escolas no
  carrossel** porque não temos essas imagens — só entram lá quando o cliente mandar.
- **"ABNT NBR ISO IEC 17025:2005" → "ABNT NBR ISO IEC 17025"** (sem a data) no texto
  da Missão institucional.
- **Diferenciais**: "Orçamento no mesmo dia" → "Orçamento em até 24 horas"; "Laudo
  com parecer técnico" → "Laudo com parecer técnico conforme solicitação" — e essa
  mesma troca de "mesmo dia" → "em até 24 horas" foi replicada em todos os outros
  lugares do site que usavam a mesma frase (topo, faixa de CTA, seção de orçamento,
  FAQ).
- **Serviços — Água**: texto de introdução trocado pelo pedido; removidas as
  referências a normativas específicas (Portaria GM/MS 888, CONAMA 274 etc. — o
  próprio cliente observou que essas leis mudam com frequência); removido o item
  "Água de farmácia de manipulação"; removida a lista "Para quem" (indústria, food
  service, farmácias etc.) que aparecia junto.
- **Serviços — Alimentos**: lista de ensaios totalmente refeita com os itens pedidos
  (Salmonella, Escherichia coli, Enterobactérias, Bolores e leveduras, Bacillus
  cereus, Staphylococcus coagulase positiva, Clostridium perfringens, Listeria
  monocytogenes, Coliformes totais, Bactérias gram negativas, Identificação de
  Bolores e leveduras a nível de gênero), com os nomes científicos em itálico como
  pedido (precisei ensinar o site a entender itálico nesse campo específico, que
  antes só aceitava texto simples).
- **Serviços — Controle de qualidade**: "Indicadores biológicos" → "Indicadores
  biológicos e químicos"; adicionados "Eficiência de lavadoras de garrafões
  retornáveis" e "Sanitizantes em geral".
- **Documentos e certificações**: reordenado com "Reconhecimento..." primeiro;
  adicionados REBLAS, CNES e Licenciamento Ambiental — como ainda não temos os PDFs
  desses 3, eles aparecem no site com o aviso "em atualização", e viram baixáveis
  automaticamente assim que alguém subir o PDF de cada um pelo admin (em
  Documentos).
- **FAQ**: respostas atualizadas conforme o PDF (coleta de amostra, prazo do laudo,
  aceitação pela fiscalização, acesso aos laudos — trocado "portal Resultados
  online" por "área do cliente", já que o cabeçalho não tem mais essa aba separada).
- **Cabeçalho**: "Solicitar kit" → "Ficha de coleta".
- **Rodapé — CTA "Pesquisa de satisfação"**: aumentado e com cor trocada para laranja
  (mais contraste que o verde-azulado anterior), ícone de estrela, fundo com sombra —
  bem mais chamativo, já que vocês reforçaram que é item cobrado em auditoria.
- **Nova função no admin**: campo para **habilitar/desabilitar** as seções "Nossa
  equipe" e "Clientes" direto pelo painel (Configurações gerais → duas opções Sim/Não).
  Deixei as duas **desabilitadas agora**, como pedido — a seção some do site inteiro
  (inclusive os links do menu e do rodapé que apontavam pra ela), sem precisar mexer
  em código; quando tiver o conteúdo completo, é só entrar no admin e marcar "Sim,
  exibir".

**Pendências que não consegui resolver sozinho (preciso de informação sua):**
- **Links do Instagram/Facebook no rodapé**: o Facebook configurado hoje
  (`facebook.com/biocontrolrs`) é do nome antigo da marca ("Biocontrol") e pode estar
  quebrado por isso; o Instagram nunca foi configurado (ainda está como pendência
  desde o início do projeto). Preciso dos dois links reais e atualizados para
  corrigir — não adivinho URL de rede social de vocês.
- **Depoimento da Anelise** ("trocar Biocontrol por Bcontrol"): esse depoimento foi
  cadastrado direto no admin do site (não existe no conteúdo padrão do repositório),
  então essa troca de uma palavra é mais rápida de fazer direto por lá: Depoimentos
  → encontrar o da Anelise → editar o texto → salvar.
- **Nova pergunta de FAQ "Condições de pagamento"**: o PDF cita só "Para novos
  clientes" e o texto parece cortado — não criei essa pergunta porque não quis
  inventar uma condição comercial. Me manda o texto completo que eu adiciono.

**Atualização importante antes de montar o zip desta rodada**: como essa rodada mexe
em `content/*.json` (coisa que normalmente evito, pela lição da Rodada 20), comparei
cada arquivo direto com o que está hoje no site de teste antes de sobrescrever
qualquer coisa — `documents.json`, `segments.json` e `services.json` estavam
idênticos ao conteúdo original (podem ser sobrescritos sem perda). Mas
`content/faq.json` e `content/site.json` **já tinham sido editados ao vivo** pelo
admin, com informação real que eu não tinha:
- Havia uma pergunta de FAQ "Quais são as condições de pagamento?" com a resposta
  completa (prazos de boleto, Pix, Sebrae/Sebratec) — isso resolve exatamente o item
  que eu tinha deixado pendente no PDF (achei que o texto estivesse cortado, mas na
  verdade já tinha sido preenchido direto no site). Importei essa pergunta para o
  arquivo novo, mantendo o conteúdo, só com uma revisão leve de pontuação/capitalização
  (sem mexer em nenhum prazo ou valor), e adicionei `white-space:pre-line` no CSS do
  FAQ pra essa resposta, que tem várias linhas, exibir com quebra de parágrafo em vez
  de um bloco só de texto corrido.
- O Instagram **já tinha um link real cadastrado** (`instagram.com/bcontrolrs`), ao
  contrário do que eu tinha no repositório (ainda com o placeholder "TODO"). Importei
  esse link real — então o problema do Instagram "não funcionar" relatado no PDF já
  deve estar resolvido (o link só não tinha chegado ainda nos arquivos que eu estava
  usando como base).
- O Facebook continua `facebook.com/biocontrolrs` nos dois lugares (sem mudança) —
  segue como pendência eu precisar do link certo, se esse realmente estiver errado.

CSS em `?v=16`, JS em `?v=14`.

## Checklist final antes de publicar em produção

- [ ] Criar uma conta própria para cada pessoa que for mexer no painel `/admin` → "Usuários"
      (ver Rodada 9), em vez de todo mundo usar a mesma conta.
- [ ] Conferir em `envio.php` se `FROM_EMAIL` é um endereço real do domínio final (ver
      Rodada 8) e mandar um pedido de teste pelo formulário antes de liberar de vez.
- [x] ~~Decidir sobre `content/site.json` → `testCcEmail`~~ — esvaziado (Rodada 14): os
      orçamentos já chegam direto nos e-mails reais por setor.
- [ ] Preencher `content/site.json` → `socials.instagram` (ainda não informado).
- [x] ~~Preencher `content/site.json` → `ga4Id`~~ — feito (Rodada 14).
- [ ] Revisão jurídica de `privacidade.html` e `termos.html` (conteúdo modelo).
- [ ] Depoimentos reais e números reais (`content/testimonials.json`, `content/site.json`
      → `stats`), quando disponíveis.
- [ ] Confirmar domínio/HTTPS final para atualizar `canonical`, Open Graph, `sitemap.xml`
      e `robots.txt` (hoje apontam para `www.bcontrol.com.br`, usados como referência).
- [ ] Upload de todos os arquivos do zip para a raiz pública do domínio final (ver
      `INSTRUCOES_PUBLICACAO.md` incluso no pacote).

## Ainda no checklist do briefing (próximos passos, após as pendências acima)

- Minificação de CSS/JS para produção (hoje estão legíveis/comentados; minificar no
  pipeline de deploy é trivial e não muda nada funcionalmente).
  Lighthouse/axe formais (o que dá para validar sem hospedagem real já foi revisado
  manualmente: contraste, foco, labels, hierarquia de headings, alt em imagens).
- Testes finais em produção (HTTPS, 404 real, links/âncoras) após o deploy.
- Publicação, apontamento de domínio e envio do `sitemap.xml` ao Google Search Console.
- Monitoramento pós-publicação, backup e plano de manutenção.
