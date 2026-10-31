<?php
/**
 * Tradução de recurso para pt-PT das cadeias do WooCommerce usadas na loja.
 * Serve enquanto o pacote oficial pt_PT não estiver instalado (`wp language plugin install woocommerce pt_PT`);
 * quando existir tradução oficial, estas entradas deixam de ser usadas porque o WordPress já devolve texto traduzido.
 */
defined('ABSPATH') || exit;

add_filter('locale', fn ($l) => is_admin() ? $l : 'pt_PT');

function arseco_woo_strings(): array
{
    return [
        'Add to cart' => 'Adicionar ao carrinho', 'Read more' => 'Ver mais', 'Select options' => 'Escolher opções',
        'View cart' => 'Ver carrinho', 'Showing all %1$d results' => 'A mostrar todos os %1$d resultados',
        'Showing all %1$d result' => 'A mostrar o único resultado',
        'Showing the single result' => 'A mostrar o único resultado',
        'Default sorting' => 'Ordenação por defeito', 'Sort by popularity' => 'Ordenar por popularidade',
        'Sort by average rating' => 'Ordenar por classificação', 'Sort by latest' => 'Ordenar por mais recentes',
        'Sort by price: low to high' => 'Ordenar por preço: do mais baixo ao mais alto',
        'Sort by price: high to low' => 'Ordenar por preço: do mais alto ao mais baixo',
        'Shop' => 'Loja', 'Home' => 'Início', 'Category:' => 'Categoria:', 'Categories:' => 'Categorias:',
        'Related products' => 'Produtos relacionados', 'Additional information' => 'Informação adicional',
        'Description' => 'Descrição', 'Reviews' => 'Avaliações', 'Product' => 'Produto', 'Products' => 'Produtos',
        'Price' => 'Preço', 'Quantity' => 'Quantidade', 'Subtotal' => 'Subtotal', 'Total' => 'Total',
        'Shipping' => 'Envio', 'Shipment' => 'Envio', 'Free shipping' => 'Envio gratuito', 'Flat rate' => 'Taxa fixa',
        'Cart' => 'Carrinho', 'Cart totals' => 'Total do carrinho', 'Update cart' => 'Atualizar carrinho',
        'Proceed to checkout' => 'Finalizar compra', 'Remove this item' => 'Remover este artigo',
        'Your cart is currently empty.' => 'O seu carrinho está vazio.', 'Return to shop' => 'Voltar à loja',
        'Coupon:' => 'Cupão:', 'Apply coupon' => 'Aplicar cupão', 'Coupon code' => 'Código do cupão',
        'Billing details' => 'Dados de faturação', 'Billing &amp; Shipping' => 'Faturação e envio',
        'First name' => 'Nome próprio', 'Last name' => 'Apelido', 'Company name' => 'Empresa',
        'Country / Region' => 'País / Região', 'Street address' => 'Morada',
        'House number and street name' => 'Rua e número', 'Apartment, suite, unit, etc.' => 'Andar, porta, etc.',
        'Apartment, suite, unit, etc. (optional)' => 'Andar, porta, etc. (opcional)',
        'Town / City' => 'Localidade', 'Postcode / ZIP' => 'Código postal', 'Postcode / ZIP (optional)' => 'Código postal (opcional)',
        'Phone' => 'Telefone', 'Phone (optional)' => 'Telefone (opcional)', 'Email address' => 'Endereço de e-mail',
        'State / County' => 'Distrito', 'District' => 'Distrito', 'Province' => 'Distrito',
        'Ship to a different address?' => 'Enviar para outra morada?', 'Additional information' => 'Informação adicional',
        'Order notes' => 'Notas da encomenda', 'Order notes (optional)' => 'Notas da encomenda (opcional)',
        'Notes about your order, e.g. special notes for delivery.' => 'Notas sobre a encomenda, por exemplo instruções de entrega.',
        'Your order' => 'A sua encomenda', 'Pay with cash upon delivery.' => 'Pague em numerário na entrega.',
        'Pay via PayPal; you can pay with your credit card if you don’t have a PayPal account.' => 'Pagamento por PayPal.',
        'Make your payment directly into our bank account.' => 'Pague por transferência para a nossa conta bancária.',
        'Direct bank transfer' => 'Transferência bancária', 'Cash on delivery' => 'Pagamento na entrega',
        'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.' => 'Os seus dados pessoais serão usados para processar a encomenda, apoiar a sua experiência neste site e para outros fins descritos na nossa %s.',
        'I have read and agree to the website %s' => 'Li e aceito os %s do site',
        'terms and conditions' => 'termos e condições', 'privacy policy' => 'política de privacidade',
        'Place order' => 'Encomenda com obrigação de pagar',
        'Thank you. Your order has been received.' => 'Obrigado. Recebemos a sua encomenda.',
        'Order number:' => 'N.º da encomenda:', 'Date:' => 'Data:', 'Email:' => 'E-mail:', 'Total:' => 'Total:',
        'Payment method:' => 'Método de pagamento:', 'Order details' => 'Detalhes da encomenda',
        'Billing address' => 'Morada de faturação', 'Shipping address' => 'Morada de envio',
        'Sorry, no products matched your selection. Please try again.' => 'Não há produtos para a sua seleção.',
        'No products were found matching your selection.' => 'Não há produtos para a sua seleção.',
        'Dashboard' => 'Painel', 'Orders' => 'Encomendas', 'Addresses' => 'Moradas', 'Account details' => 'Dados da conta',
        'Log out' => 'Terminar sessão', 'Login' => 'Entrar', 'Register' => 'Registar', 'Username or email address' => 'Utilizador ou e-mail',
        'Password' => 'Palavra-passe', 'Remember me' => 'Manter sessão', 'Log in' => 'Entrar', 'Lost your password?' => 'Esqueceu a palavra-passe?',
        'Email address' => 'Endereço de e-mail', 'Required' => 'Obrigatório', 'optional' => 'opcional', '(optional)' => '(opcional)',
        'Sale!' => 'Promoção', 'Out of stock' => 'Esgotado', 'In stock' => 'Em stock', 'SKU:' => 'Ref.:',
        'Shop' => 'Loja', 'Search' => 'Pesquisar', 'Search products…' => 'Pesquisar produtos…',
        '&ldquo;%s&rdquo; has been added to your cart.' => '“%s” foi adicionado ao carrinho.',
        '%s has been added to your cart.' => '%s foi adicionado ao carrinho.',
        'Product name' => 'Produto', 'Product Name' => 'Produto', 'Name' => 'Nome', 'Email' => 'E-mail', 'Message' => 'Mensagem',
        'Delivery' => 'Entrega', 'Edit' => 'Editar', 'View' => 'Ver', 'Actions' => 'Ações', 'Status' => 'Estado',
        'Order' => 'Encomenda', 'Date' => 'Data', 'Pay' => 'Pagar', 'Cancel' => 'Cancelar', 'Save changes' => 'Guardar alterações',
        'Continue shopping' => 'Continuar a comprar', 'Apply' => 'Aplicar', 'Update' => 'Atualizar',
        'Have a coupon?' => 'Tem um cupão?', 'Click here to enter your code' => 'Clique para introduzir o código',
        'Returning customer?' => 'Já é cliente?', 'Click here to login' => 'Clique para entrar',
        'Create an account?' => 'Criar uma conta?', 'Ship to' => 'Enviar para', 'Select a country / region&hellip;' => 'Escolha um país / região…',
        'Select an option&hellip;' => 'Escolha uma opção…', 'Select a state / county&hellip;' => 'Escolha um distrito…',
        'Calculate shipping' => 'Calcular envio', 'Update totals' => 'Atualizar totais', 'Estimated for %s' => 'Estimado para %s',
        'Shipping options will be updated during checkout.' => 'As opções de envio são atualizadas na finalização da compra.',
        'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.' => 'Não há opções de envio para esta morada. Verifique os dados ou contacte-nos.',
        'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => 'Não há meios de pagamento disponíveis. Contacte-nos para obter ajuda.',
        'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.' => 'Não há meios de pagamento disponíveis. Contacte-nos para obter ajuda.',
        'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.' => 'Não há meios de pagamento disponíveis. Contacte-nos para obter ajuda.',
        'No order has been made yet.' => 'Ainda não fez nenhuma encomenda.', 'Browse products' => 'Ver produtos',
        'Hello %1$s (not %1$s? %2$s)' => 'Olá %1$s (não é %1$s? %2$s)',
        'Showing %1$d&ndash;%2$d of %3$d results' => 'A mostrar %1$d–%2$d de %3$d resultados',
    ];
}

add_filter('gettext', function ($translated, $text, $domain) {
    static $map = null;
    if ($domain !== 'woocommerce' || $translated !== $text) {
        return $translated; // já traduzido (pacote oficial instalado)
    }
    $map ??= arseco_woo_strings();
    return $map[$text] ?? $translated;
}, 10, 3);

add_filter('ngettext', function ($translated, $single, $plural, $number, $domain) {
    if ($domain !== 'woocommerce') {
        return $translated;
    }
    $map = arseco_woo_strings();
    $key = $number == 1 ? $single : $plural;
    return ($translated === $key && isset($map[$key])) ? $map[$key] : $translated;
}, 10, 5);

add_filter('gettext_with_context', function ($translated, $text, $context, $domain) {
    if ($domain !== 'woocommerce' || $translated !== $text) {
        return $translated;
    }
    return arseco_woo_strings()[$text] ?? $translated;
}, 10, 4);

// Nomes de países em português, a partir dos dados ICU do PHP (extensão intl).
add_filter('woocommerce_countries', function ($countries) {
    if (!class_exists('Locale')) {
        return $countries;
    }
    foreach ($countries as $code => $name) {
        $pt = Locale::getDisplayRegion('-' . $code, 'pt_PT');
        if ($pt && $pt !== $code && $pt !== $name && !str_contains($pt, '-')) {
            $countries[$code] = $pt;
        }
    }
    return $countries;
});
