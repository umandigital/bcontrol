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
- Formulário de orçamento com envio real via `fetch` (endpoint formsubmit.co — ver
  pendência 4), roteamento por setor, honeypot anti-spam.
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
3. **E-mails por setor "Águas" e "Alimentos"** — hoje apontam para `bcontrol@bcontrol.com.br`
   (Atendimento Geral, e-mail real já usado no site atual) por não haver um endereço
   dedicado a esses dois setores nem no site nem no material recebido. "Controle de
   qualidade" e "Comercial" já usam e-mails reais e específicos (ver Rodada 3 abaixo).
   Se quiser endereços próprios para Águas/Alimentos, é só avisar.
4. **Confirmar recebimento do formulário de orçamento**: o endpoint (formsubmit.co) está
   configurado, mas a primeira mensagem real enviada pelo site vai disparar um e-mail de
   confirmação para `bcontrol@bcontrol.com.br` — **alguém precisa clicar no link desse
   e-mail** para o formulário passar a funcionar de verdade (é assim que o formsubmit.co
   evita spam; não precisa criar conta, é só esse clique único).
5. **Depoimentos reais** (texto + nome + empresa autorizados) — `content/testimonials.json`.
6. **Números reais** (parâmetros no escopo) — `content/site.json` → `stats`.
7. **GA4** (Measurement ID) e eventual pixel de campanha — não existia no site atual;
   `content/site.json` → `ga4Id`.
8. **Instagram** — não encontrado no site atual (só havia Facebook, já linkado) —
   `content/site.json` → `socials.instagram`.
9. **Revisão jurídica** de `privacidade.html` e `termos.html` (conteúdo modelo).
10. **Cargos da equipe** — `content/team.json` → `role` preenchido com o texto que estava
    no protótipo; só falta confirmar se está correto e atualizado.

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
  não portável para um site estático), configurei um endpoint equivalente e gratuito
  (formsubmit.co) apontando para `bcontrol@bcontrol.com.br` — ver pendência 4 acima sobre
  o clique de confirmação único.

Isso resolve, sem precisar de mais nenhuma informação do cliente, praticamente todas as
integrações que antes dependiam de dados externos.

## Ainda no checklist do briefing (próximos passos, após as pendências acima)

- Minificação de CSS/JS para produção (hoje estão legíveis/comentados; minificar no
  pipeline de deploy é trivial e não muda nada funcionalmente).
  Lighthouse/axe formais (o que dá para validar sem hospedagem real já foi revisado
  manualmente: contraste, foco, labels, hierarquia de headings, alt em imagens).
- Testes finais em produção (HTTPS, 404 real, links/âncoras) após o deploy.
- Publicação, apontamento de domínio e envio do `sitemap.xml` ao Google Search Console.
- Monitoramento pós-publicação, backup e plano de manutenção.
