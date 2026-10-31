<?php
/**
 * Dados da empresa: ficam vazios até serem preenchidos em Aspeto > Dados da empresa.
 * Todas as páginas legais leem daqui através de [empresa campo="..."].
 */
defined('ABSPATH') || exit;

function arseco_company_fields(): array
{
    return [
        'marca'         => 'Nome da marca (aparece nos títulos grandes)',
        'razao_social'  => 'Denominação social',
        'nif'           => 'NIF',
        'morada'        => 'Morada da sede',
        'cp_localidade' => 'Código postal e localidade',
        'registo'       => 'Conservatória / n.º de registo comercial',
        'capital'       => 'Capital social',
        'email'         => 'E-mail de contacto',
        'telefone'      => 'Telefone',
        'horario'       => 'Horário de atendimento',
        'entidade_ral'  => 'Entidade de resolução alternativa de litígios (RAL)',
        'iban'          => 'IBAN (transferência bancária)',
    ];
}

function arseco_company(string $field): string
{
    $all = get_option('arseco_company', []);
    return trim((string) ($all[$field] ?? ''));
}

function arseco_brand(): string
{
    return arseco_company('marca') ?: 'MARCA';
}

/** Valor ou marcador visível "[a preencher]", para ninguém publicar uma página incompleta sem reparar. */
function arseco_company_html(string $field): string
{
    $value = arseco_company($field);
    if ($value !== '') {
        return esc_html($value);
    }
    return '<mark class="por-preencher">[a preencher]</mark>';
}

add_action('admin_menu', function () {
    add_theme_page('Dados da empresa', 'Dados da empresa', 'manage_options', 'arseco-company', function () {
        echo '<div class="wrap"><h1>Dados da empresa</h1>';
        echo '<p>Campos vazios aparecem como <em>[a preencher]</em> nas páginas legais, no rodapé e nos e-mails.</p>';
        echo '<form method="post" action="options.php">';
        settings_fields('arseco_company_group');
        $values = get_option('arseco_company', []);
        echo '<table class="form-table">';
        foreach (arseco_company_fields() as $key => $label) {
            printf(
                '<tr><th><label for="ac_%1$s">%2$s</label></th><td><input class="regular-text" id="ac_%1$s" name="arseco_company[%1$s]" value="%3$s"></td></tr>',
                esc_attr($key),
                esc_html($label),
                esc_attr($values[$key] ?? '')
            );
        }
        echo '</table>';
        submit_button();
        echo '</form></div>';
    });
});

add_action('admin_init', function () {
    register_setting('arseco_company_group', 'arseco_company', [
        'type'              => 'array',
        'sanitize_callback' => function ($input) {
            $clean = [];
            foreach (array_keys(arseco_company_fields()) as $key) {
                $clean[$key] = sanitize_text_field($input[$key] ?? '');
            }
            return $clean;
        },
    ]);
});
