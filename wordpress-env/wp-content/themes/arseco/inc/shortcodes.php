<?php
defined('ABSPATH') || exit;

add_shortcode('empresa', function ($atts) {
    $atts = shortcode_atts(['campo' => ''], $atts);
    return array_key_exists($atts['campo'], arseco_company_fields()) ? arseco_company_html($atts['campo']) : '';
});

/** Bloco com o pedido de livre resolução em linha (obrigatório em venda à distância). */
add_shortcode('livre_resolucao', function () {
    $status = sanitize_key($_GET['lr'] ?? '');
    $out = '';
    if ($status === 'ok') {
        $out .= '<p class="notice notice--ok" role="status">Pedido registado. Guarde esta mensagem como comprovativo da data e hora. A confirmação é também enviada para o e-mail indicado.</p>';
    } elseif ($status === 'erro') {
        $out .= '<p class="notice notice--erro" role="alert">Não foi possível enviar o pedido. Verifique os campos e tente novamente.</p>';
    }
    ob_start(); ?>
    <form class="form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="arseco_livre_resolucao">
        <?php wp_nonce_field('arseco_lr', 'arseco_lr_nonce'); ?>
        <p><label>Nome completo<input name="nome" required autocomplete="name"></label></p>
        <p><label>E-mail<input type="email" name="email" required autocomplete="email"></label></p>
        <p><label>N.º da encomenda<input name="encomenda" required></label></p>
        <p><label>Produtos a que respeita (opcional)<textarea name="produtos" rows="3"></textarea></label></p>
        <p class="hp" aria-hidden="true"><label>Não preencher<input name="website" tabindex="-1" autocomplete="off"></label></p>
        <p><button class="btn btn--dark" type="submit">Confirmar livre resolução</button></p>
    </form>
    <?php
    return $out . ob_get_clean();
});

function arseco_handle_lr(): void
{
    $back = wp_get_referer() ?: home_url('/livre-resolucao/');
    $back = remove_query_arg('lr', $back);

    $valid = isset($_POST['arseco_lr_nonce']) && wp_verify_nonce($_POST['arseco_lr_nonce'], 'arseco_lr');
    $nome = sanitize_text_field(wp_unslash($_POST['nome'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $enc = sanitize_text_field(wp_unslash($_POST['encomenda'] ?? ''));
    $prod = sanitize_textarea_field(wp_unslash($_POST['produtos'] ?? ''));

    if (!empty($_POST['website'])) { // armadilha anti-robôs: responde "ok" sem enviar
        wp_safe_redirect(add_query_arg('lr', 'ok', $back));
        exit;
    }
    if (!$valid || $nome === '' || !is_email($email) || $enc === '') {
        wp_safe_redirect(add_query_arg('lr', 'erro', $back));
        exit;
    }

    // O pedido fica registado primeiro (prova de data e hora); os e-mails são secundários e podem falhar sem perder o pedido.
    $when = wp_date('d/m/Y H:i');
    $post_id = wp_insert_post([
        'post_type'    => 'livre_resolucao',
        'post_status'  => 'private',
        'post_title'   => "Encomenda {$enc}: {$nome}",
        'post_content' => "Nome: {$nome}\nE-mail: {$email}\nEncomenda: {$enc}\nProdutos: {$prod}\nRecebido em: {$when}",
    ]);
    if (!$post_id || is_wp_error($post_id)) {
        wp_safe_redirect(add_query_arg('lr', 'erro', $back));
        exit;
    }

    $to = arseco_company('email') ?: get_option('admin_email');
    wp_mail($to, 'Livre resolução: encomenda ' . $enc, "Pedido recebido em {$when}\n\nNome: {$nome}\nE-mail: {$email}\nEncomenda: {$enc}\nProdutos: {$prod}\n", ['Reply-To: ' . $email]);
    wp_mail($email, 'Confirmação do seu pedido de livre resolução', "Recebemos o seu pedido em {$when} relativo à encomenda {$enc}.\nEste e-mail serve de confirmação da receção.\n\n" . get_bloginfo('name'));

    wp_safe_redirect(add_query_arg('lr', 'ok', $back));
    exit;
}
add_action('admin_post_nopriv_arseco_livre_resolucao', 'arseco_handle_lr');
add_action('admin_post_arseco_livre_resolucao', 'arseco_handle_lr');

add_action('init', function () {
    register_post_type('livre_resolucao', [
        'labels'       => ['name' => 'Livre resolução', 'singular_name' => 'Pedido de livre resolução'],
        'public'       => false,
        'show_ui'      => true,
        'menu_icon'    => 'dashicons-undo',
        'supports'     => ['title', 'editor'],
        'capabilities' => ['create_posts' => 'do_not_allow'],
        'map_meta_cap' => true,
    ]);
});
