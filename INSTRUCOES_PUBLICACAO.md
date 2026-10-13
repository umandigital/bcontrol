# Como publicar este site (cPanel)

Guia rápido para quem vai subir os arquivos no servidor final. Não é preciso saber
programar — é só upload de arquivos e dois cliques no painel.

## 1. Enviar os arquivos

1. Entre no **cPanel** do domínio final → **Gerenciador de Arquivos (File Manager)**.
2. Vá até a pasta pública do domínio (geralmente `public_html/`, ou a subpasta do
   domínio/subdomínio escolhido).
3. Envie o arquivo `.zip` deste pacote e use a opção **Extrair (Extract)** do próprio
   Gerenciador de Arquivos, direto ali dentro da pasta pública.
4. Confirme que os arquivos ficaram **direto** na pasta pública (ex.: `public_html/index.html`,
   `public_html/css/`, `public_html/admin/` etc.) — e não dentro de uma subpasta extra tipo
   `public_html/bcontrol/index.html`. Se a extração criar uma subpasta, mova o conteúdo de
   dentro dela para o nível certo.

## 2. Confirmar permissões (normalmente já vem certo)

O painel de edição (`/admin`) precisa poder **escrever** nestas pastas:
- `content/` (onde ficam os arquivos de conteúdo editáveis)
- `content/.backups/` (cópias de segurança automáticas)
- `assets/docs/` (PDFs enviados pelo painel)

Na grande maioria das hospedagens cPanel isso já funciona sem fazer nada (o PHP roda como
o dono do próprio site). Se o painel der erro de "não foi possível gravar" ao salvar,
peça para o suporte da hospedagem ajustar a permissão dessas 3 pastas para **755** (ou,
em último caso, **775**).

## 3. Acessar o painel de edição e trocar a senha

1. Acesse `https://SEUDOMINIO/admin` (troque pelo domínio real).
2. **Senha inicial:** `bcontrol2026`
3. **Troque essa senha imediatamente** em "Alterar senha" (menu no topo do painel).
4. A partir daí, qualquer pessoa autorizada pode editar FAQ, serviços, equipe,
   depoimentos, clientes, segmentos, documentos (incluindo subir PDF novo) e as
   configurações gerais do site (e-mails por setor, WhatsApp, redes sociais etc.) direto
   pelo navegador, sem mexer em código.

## 4. Antes de considerar "no ar de vez"

Veja a lista completa em `RELATORIO.md` → seção **"Checklist final antes de publicar em
produção"**. Os pontos principais:

- Trocar a senha padrão do painel (item 3 acima).
- Abrir `envio.php` e conferir se `FROM_EMAIL` é um endereço real do domínio final (ajuda
  a não cair em spam), depois mandar um pedido de teste pelo próprio formulário do site.
- Decidir se o e-mail de teste do formulário de orçamento (`testCcEmail`, em
  `content/site.json`) continua ativo ou se já pode ser removido, para os orçamentos
  caírem direto nos e-mails reais por setor.
- Checar se o domínio/HTTPS batem com o que está em `sitemap.xml`, `robots.txt` e a tag
  `canonical` de cada página (hoje configurados para `www.bcontrol.com.br`).
- Revisão jurídica de `privacidade.html` e `termos.html` (conteúdo modelo, ainda não
  revisado por advogado).

## 5. Dúvidas comuns

- **"Troquei uma imagem e ela não aparece."** O navegador guarda imagens em cache por 7
  dias. Suba a imagem com um nome diferente, ou aumente o número de versão (`?v=`) nos
  lugares que referenciam esse arquivo — ver detalhes em `RELATORIO.md`.
- **"Mudei algo e o site não atualizou."** As páginas HTML (`index.html` etc.) já não
  ficam em cache no navegador (configurado via `.htaccess`), então isso não deve mais
  acontecer — se acontecer, confirme que o `.htaccess` foi enviado junto (ele é um arquivo
  "escondido", começa com ponto; ative "Mostrar arquivos ocultos" no Gerenciador de
  Arquivos para conferir).
- **"Esqueci a senha do painel."** Peça para alguém com acesso ao Gerenciador de Arquivos
  apagar o arquivo `admin/auth_data.json` — o painel volta a aceitar a senha inicial
  `bcontrol2026` (troque de novo em seguida).
- **"O formulário de orçamento não chega por e-mail."** O envio usa a função `mail()`
  nativa do PHP (`envio.php`), que funciona por padrão em praticamente toda hospedagem
  cPanel. Se mesmo assim não chegar: confirme com o suporte da hospedagem que a função
  `mail()` do PHP está habilitada para o domínio (raramente vem desligada) e confira a
  caixa de spam do destinatário na primeira tentativa.
