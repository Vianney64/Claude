# Informações em falta e pontos por validar

Regra seguida: nada é inventado. Tudo o que falta fica aqui e aparece no site como `[a preencher]`.

## Bloqueante antes de qualquer publicação
- **Dados da empresa**: denominação, NIF, morada, registo comercial, capital social, e-mail, telefone, horário, entidade RAL, IBAN. Preencher em Aspeto > Dados da empresa. Alimentam rodapé, páginas legais e e-mails.
- **Nome da marca**: o wordmark mostra "MARCA" por omissão (campo "Nome da marca").
- **Catálogo**: os 4 produtos são **de exemplo** (título com "(exemplo)", preços inventados, fichas "Por confirmar"). Substituir por produtos reais com ficha técnica verificada. Identificáveis pela meta `_arseco_demo`.
- **Fotografias**: não existem. As imagens são ilustrações SVG provisórias. Fotos de produto com direitos de utilização são necessárias (e para o Google Merchant Center).
- **Texto jurídico**: as páginas legais são texto-base e têm de ser revistas por um jurista (termos, privacidade, cookies, devoluções, garantia).
- **Prazos e custos de entrega**: "[a definir]" na página Entregas.

## A validar
- **IVA**: `calc_taxes` está desligado; confirmar regime e taxa (23 % em Portugal continental) e preços com IVA incluído.
- **Meios de pagamento**: nenhum configurado. Sem IBAN não há transferência. O checkout cita os meios ativos.
- **Resíduos de equipamentos elétricos (REEE)**: confirmar obrigações do vendedor (registo, recolha de equipamento usado na compra de novo).
- **Livro de Reclamações Eletrónico**: confirmar registo da empresa na plataforma.
- **Entidade RAL**: escolher e indicar.
- **Cookies**: o banner regista a escolha mas não carrega nenhum serviço. Se vier a usar análise/publicidade, listar na política e ligar ao consentimento.
- **E-mail**: sem SMTP configurado; `wp_mail` (livre resolução, encomendas) pode não chegar ao destino.
- **Referência casa-bazar.com**: o site não estava acessível a partir deste ambiente. A estrutura legal foi inferida do `JOURNAL-COMPLET.md`, não copiada do site; comparar páginas uma a uma.

## Não verificado
- **Testado** com WooCommerce 11.2.1 (zip fornecido): loja, categoria, ficha, carrinho, checkout e encomenda concluída em Chromium, com um meio de pagamento e uma zona de envio **temporários**, já removidos. Não testado: conta de cliente com sessão iniciada, e-mails do WooCommerce, cupões, variações, stock.
- **E-mails do WooCommerce em inglês**: o dicionário do tema cobre o site, não os modelos de e-mail. Instalar o pacote oficial: `wp language plugin install woocommerce pt_PT` (precisa de rede).
- **Nenhum meio de pagamento nem zona de envio configurados**: sem eles o checkout não conclui encomendas.
- **Integridade do zip do WooCommerce**: usado tal como fornecido; não foi possível comparar com a soma de verificação oficial.
- Lighthouse, acessibilidade com leitor de ecrã e teste em Safari/Firefox.

## Archive .wpress
- Archive générée par `tools/build-wpress.py`, **non importée avec All-in-One WP Migration** (indisponible dans l'environnement). Vérifiée par un lecteur écrit à part (structure, fin de fichier, thème identique octet pour octet) et par l'import de `database.sql` dans un MySQL 8.0 vierge.
- Écrase la base de destination. Mot de passe `admin` aléatoire, à changer. Adresse `admin@example.test` à remplacer.
- Catalogue encore factice: ne pas importer en production tel quel.
