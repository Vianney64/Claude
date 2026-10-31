<?php
/**
 * Ilustração SVG de um desumidificador. Serve de imagem provisória até haver fotografias reais
 * (não existem fotos de produto neste projeto; ver MANQUANTS.md).
 */
defined('ABSPATH') || exit;

function arseco_art(string $tone = 'pedra', string $class = 'art'): string
{
    $tones = [
        'pedra'  => ['#dcd8cf', '#b9b4a8', '#8f8a7e'],
        'azul'   => ['#cfd6dc', '#a7b2bc', '#7d8a96'],
        'grafite' => ['#8d8c8a', '#5f5e5c', '#3c3b39'],
    ];
    [$hi, $mid, $lo] = $tones[$tone] ?? $tones['pedra'];
    $id = 'g' . substr(md5($tone . $class), 0, 6);

    $slots = '';
    for ($i = 0; $i < 9; $i++) {
        $y = 138 + $i * 13;
        $slots .= sprintf('<rect x="118" y="%d" width="164" height="5" rx="2.5" fill="%s" opacity=".55"/>', $y, $lo);
    }
    $vents = '';
    for ($i = 0; $i < 7; $i++) {
        $vents .= sprintf('<rect x="%d" y="396" width="6" height="34" rx="3" fill="%s" opacity=".5"/>', 124 + $i * 24, $lo);
    }

    return <<<SVG
<svg class="{$class}" viewBox="0 0 400 520" role="img" aria-label="Ilustração de um desumidificador" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="{$id}b" x1="0" x2="1"><stop offset="0" stop-color="{$hi}"/><stop offset=".62" stop-color="{$mid}"/><stop offset="1" stop-color="{$lo}"/></linearGradient>
    <radialGradient id="{$id}s"><stop offset="0" stop-color="#000" stop-opacity=".35"/><stop offset="1" stop-color="#000" stop-opacity="0"/></radialGradient>
  </defs>
  <ellipse cx="200" cy="486" rx="150" ry="20" fill="url(#{$id}s)"/>
  <rect x="86" y="36" width="228" height="440" rx="52" fill="url(#{$id}b)"/>
  <rect x="104" y="52" width="192" height="64" rx="26" fill="{$lo}" opacity=".28"/>
  <rect x="124" y="68" width="64" height="32" rx="10" fill="#26252a"/>
  <text x="156" y="91" text-anchor="middle" font-family="Inter Tight, sans-serif" font-size="19" font-weight="700" fill="#e8e4da">50%</text>
  <circle cx="226" cy="84" r="9" fill="{$hi}"/>
  <circle cx="258" cy="84" r="9" fill="{$hi}" opacity=".7"/>
  {$slots}
  <rect x="106" y="372" width="188" height="78" rx="22" fill="{$lo}" opacity=".3"/>
  {$vents}
  <rect x="108" y="40" width="30" height="430" rx="15" fill="#fff" opacity=".14"/>
</svg>
SVG;
}
