# Histórico de sessões — site bcontrol

Registro resumido do trabalho feito neste repositório. O detalhe técnico de cada rodada
(28 no total) está em `RELATORIO.md`; aqui ficam a visão geral, as decisões e as pendências.
Atualizar este arquivo em todo commit (regra no `CLAUDE.md`).

## Visão geral

Site institucional da bcontrol (laboratório de análises), em HTML/CSS/JS estático com
conteúdo em `content/*.json`, painel de edição próprio em PHP (`/admin`) e envio de
formulário por `envio.php`. Hospedagem final: cPanel. Domínio de referência:
`www.bcontrol.com.br`.

## Linha do tempo (de `git log`, 24/09 a 28/10/2026)

- **Base (24/09):** evolução do apresentável para site de produção; correção de imagens
  esticadas, sliders e legendas embutidas; vídeo do hero comprimido (sem download no mobile).
- **Integrações e formulário (25/09):** integrações reais recuperadas do site atual;
  orçamento com roteamento por setor, CC de teste e texto padrão do e-mail em PT-BR.
- **Cache:** cache-busting em `style.css`/`main.js` + `.htaccess` com no-cache para HTML.
- **"Quem somos":** novo texto do cliente, carrossel de segmentos, galeria institucional
  (fotos/vídeos) com modal e setas.
- **Painel admin:** Decap CMS substituído por painel próprio em PHP; multiusuário
  (e-mail + senha); upload de imagem; bloqueio de login por IP e cookie seguro.
- **Formulário:** `formsubmit.co` trocado por `envio.php` (PHP `mail()`); assunto por setor;
  e-mail de teste desativado; GA4 ativado.
- **Mobile e SEO:** revisão geral mobile, 3 bugs de hero/CTA/carrossel, limpeza de código
  morto, revisão final de SEO.
- **Conteúdo (PDF do cliente):** revisão aplicada + toggle de seções no admin; logo da Uman
  clicável no rodapé; TECNOPUC removido do endereço; logos com fundo transparente.
- **Sincronização final:** conteúdo real (depoimentos, logos, galeria, documentos) trazido
  do ambiente de teste; imagens de rascunho removidas.
- **Auditoria final (28):** schema `FAQPage` dinâmico, sitemap atualizado, HTTPS/HSTS no
  `.htaccess`, limite de envios por IP no formulário, `rel="noopener"` no form de resultados.

## Decisões técnicas

- Sem build nem dependências: PHP puro para admin e e-mail, compatível com cPanel.
- JSON-LD `FAQPage` montado em runtime a partir de `content/faq.json` para nunca ficar
  defasado após edição pelo admin.
- Cache-busting por query string (`?v=`), hoje CSS `?v=16` e JS `?v=15`.
- Admin: bcrypt, CSRF, sessão segura, bloqueio de força bruta, upload validado por bytes.
- CSP completo adiado de propósito (risco de quebrar GA4, YouTube, Google Fonts).
- Zip de atualização podia apagar conteúdo editado no admin (causa raiz na Rodada 20);
  `gallery.json` ao vivo tinha fotos que não estavam no repo (resolvido na Rodada 27).

## Decisões de copy

- Textos vêm do cliente (PDF e texto novo de "Quem somos"); nada inventado: depoimentos,
  números e descrições de fotos só entram com conteúdo real.
- E-mails por setor: Águas e Alimentos usam `bcontrol@bcontrol.com.br` por falta de
  endereço dedicado; Controle de qualidade e Comercial têm e-mails próprios.

## Arquivos principais

`index.html`, `privacidade.html`, `termos.html`, `404.html`, `css/style.css`, `js/main.js`,
`content/*.json`, `admin/`, `envio.php`, `.htaccess`, `robots.txt`, `sitemap.xml`,
`RELATORIO.md`, `INSTRUCOES_PUBLICACAO.md`.

## Pendências abertas

- Ativar HTTPS/SSL do domínio **antes** de publicar o `.htaccess` com redirect + HSTS
  (senão o site fica inacessível); confirmar domínio final (canonical, OG, sitemap, robots).
- Conferir `FROM_EMAIL` em `envio.php` e fazer um pedido de teste antes de liberar.
- Criar uma conta por pessoa no admin → "Usuários".
- Preencher `socials.instagram` em `content/site.json`.
- Preencher o campo "Título" (alt) das 13 fotos da galeria.
- Revisão jurídica de `privacidade.html` e `termos.html`.
- Números reais (`stats`) e novos depoimentos, quando o cliente enviar.
- Confirmar cargos da equipe (`content/team.json`).
- Pós-publicação: minificação CSS/JS, Lighthouse/axe, testes em produção, Search Console,
  backup e manutenção.

## Sessão de 28/10/2026 — registro de histórico

- Criados `docs/historico-sessoes.md` e a seção "Histórico" no `CLAUDE.md`.
- Estado do git: branch `claude/gracious-planck-pdpy0w`; o remoto não tem branch principal
  (`main`/`master`), só `claude/gallant-clarke-gycpuc` e esta, ambas no mesmo commit antes
  deste registro.
