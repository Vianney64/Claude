<?php
/**
 * Catálogo de EXEMPLO. Títulos, preços e fichas não são reais: servem para ver o desenho da loja.
 * Valores técnicos ficam "Por confirmar" de propósito (não se inventam especificações).
 */
defined('ABSPATH') || exit;

function arseco_demo_products(): array
{
    $tbc = 'Por confirmar';
    $specs = fn (string $tipo) => [
        'Tipo'                   => $tipo,
        'Capacidade em 24 h'     => $tbc,
        'Capacidade do depósito' => $tbc,
        'Potência'               => $tbc,
        'Nível de ruído'         => $tbc,
        'Dimensões'              => $tbc,
        'Garantia'               => '3 anos (garantia legal)',
    ];
    return [
        ['slug' => 'desumidificador-compressor-exemplo-a', 'title' => 'Desumidificador compressor, modelo A (exemplo)', 'cat' => 'Desumidificadores', 'price' => '149.00', 'tone' => 'pedra', 'attrs' => $specs('Compressor')],
        ['slug' => 'desumidificador-compressor-exemplo-b', 'title' => 'Desumidificador compressor, modelo B (exemplo)', 'cat' => 'Desumidificadores', 'price' => '219.00', 'tone' => 'azul', 'attrs' => $specs('Compressor')],
        ['slug' => 'desumidificador-exsicante-exemplo', 'title' => 'Desumidificador exsicante (exemplo)', 'cat' => 'Desumidificadores', 'price' => '189.00', 'tone' => 'grafite', 'attrs' => $specs('Exsicante')],
        ['slug' => 'purificador-de-ar-exemplo', 'title' => 'Purificador de ar (exemplo)', 'cat' => 'Purificadores de ar', 'price' => '129.00', 'tone' => 'pedra', 'attrs' => [
            'Tipo' => 'Filtro HEPA', 'Área recomendada' => $tbc, 'Nível de ruído' => $tbc, 'Potência' => $tbc, 'Garantia' => '3 anos (garantia legal)',
        ]],
    ];
}
