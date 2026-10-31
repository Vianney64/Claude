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
- **WooCommerce não pôde ser instalado** (wordpress.org e GitHub bloqueados). Catálogo, ficha de produto, carrinho, checkout e conta não foram testados. Executar `./setup-loja.sh` com acesso à rede e rever essas páginas.
- Lighthouse, acessibilidade com leitor de ecrã e teste em Safari/Firefox.
