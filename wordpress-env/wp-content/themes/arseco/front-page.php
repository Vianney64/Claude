<?php
defined('ABSPATH') || exit;
get_header();

$brand = arseco_brand();
$products = arseco_home_products(4);
$featured = $products[0] ?? null;
$shop = arseco_has_woo() ? wc_get_page_permalink('shop') : home_url('/loja/');

$features = [
    ['Sensor de humidade', 'Um higrómetro integrado permite manter a humidade relativa entre 40 % e 60 %, a faixa habitualmente recomendada para o conforto e para evitar bolor.'],
    ['Funcionamento silencioso', 'O nível de ruído é indicado na ficha de cada produto. Compare-o antes de escolher um aparelho para um quarto.'],
    ['Drenagem contínua', 'Alguns modelos permitem ligar uma mangueira de drenagem e dispensam esvaziar o depósito.'],
    ['Compressor ou exsicante', 'Compressor para divisões temperadas; exsicante para espaços frios como garagens e arrecadações.'],
];
$faq = [
    ['Que capacidade escolher?', 'Depende da área, da humidade e da ventilação da divisão. A ficha de cada produto indica a capacidade e a área recomendadas.'],
    ['Compressor ou exsicante?', 'Compressor: ambientes temperados e húmidos. Exsicante: divisões frias, como garagens e arrecadações.'],
    ['Qual o nível de ruído?', 'Está indicado na ficha técnica de cada produto, em dB(A). Para quartos, compare esse valor entre modelos.'],
    ['Como limpar e manter?', 'Esvazie o depósito e limpe o filtro com regularidade, seguindo o manual do fabricante.'],
    ['Que garantia têm os produtos?', 'Os bens novos têm garantia legal de 3 anos. Detalhes na página Garantia.'],
];
?>
<section class="hero" aria-labelledby="hero-title">
  <p class="hero__word" aria-hidden="true"><?php echo esc_html($brand); ?></p>
  <div class="hero__art"><?php echo arseco_art('pedra', 'art'); ?></div>
  <h1 id="hero-title" class="sr">Desumidificadores e eletrodomésticos para uma casa mais saudável</h1>
  <aside class="glass glass--l">
    <p class="glass__title">Menos humidade, menos bolor</p>
    <p>Um desumidificador ajuda a manter o ar da casa dentro da faixa de conforto.</p>
  </aside>
  <aside class="glass glass--r">
    <p class="glass__title">Garantia legal de 3 anos</p>
    <p>Em todos os bens novos.</p>
  </aside>
  <ul class="hero__tags">
    <li><a href="<?php echo esc_url(arseco_cat_url('desumidificadores')); ?>">Desumidificadores</a></li>
    <li><a href="<?php echo esc_url(arseco_cat_url('purificadores-de-ar')); ?>">Purificadores de ar</a></li>
  </ul>
  <a class="hero__cta pill pill--light" href="<?php echo esc_url($shop); ?>">Ver produtos <span class="arrow" aria-hidden="true">↗</span></a>
  <a class="hero__scroll" href="#sobre"><span>Descer</span></a>
</section>

<section id="sobre" class="about sheet">
  <p class="eyebrow">/Sobre</p>
  <h2 class="display">Uma casa<br>mais seca,<br>mais saudável</h2>
  <figure class="about__img"><?php echo arseco_art('azul', 'art'); ?></figure>
  <div class="about__side">
    <div class="about__thumb"><?php echo arseco_art('grafite', 'art'); ?></div>
    <p>Eletrodomésticos escolhidos para controlar a humidade e melhorar o ar interior, com informação técnica clara e sem promessas que não possamos provar.</p>
    <a class="pill pill--line" href="<?php echo esc_url(home_url('/sobre-nos/')); ?>">Saber mais <span class="arrow" aria-hidden="true">↗</span></a>
  </div>
  <p class="about__mark" aria-hidden="true"><?php echo esc_html($brand); ?></p>
</section>

<section class="features sheet" data-tabs>
  <p class="eyebrow">/Características</p>
  <h2 class="display display--r">O que procurar<br>num desumidificador</h2>
  <div class="features__stage">
    <?php echo arseco_art('pedra', 'art'); ?>
    <div class="features__tabs" role="tablist" aria-label="Características">
      <?php foreach ($features as $i => [$name]) : ?>
        <button class="chip" role="tab" id="tab-<?php echo $i; ?>" aria-selected="<?php echo $i === 2 ? 'true' : 'false'; ?>" aria-controls="panel-feat"><?php echo esc_html($name); ?></button>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="features__panel" id="panel-feat" role="tabpanel" aria-live="polite">
    <h3 data-tab-title><?php echo esc_html($features[2][0]); ?></h3>
    <p data-tab-text><?php echo esc_html($features[2][1]); ?></p>
  </div>
  <script type="application/json" data-tab-data><?php echo wp_json_encode($features, JSON_UNESCAPED_UNICODE); ?></script>
</section>

<?php if ($featured) : ?>
<section class="products" aria-labelledby="prod-title">
  <p class="eyebrow">/Produtos</p>
  <h2 id="prod-title" class="display display--light">Os nossos<br>desumidificadores</h2>
  <div class="spec-stage">
    <table class="spec">
      <caption class="sr">Ficha técnica: <?php echo esc_html($featured['title']); ?></caption>
      <?php foreach ($featured['attrs'] as $label => $value) : ?>
        <tr><th scope="row"><?php echo esc_html($label); ?></th><td><?php echo esc_html($value); ?></td></tr>
      <?php endforeach; ?>
    </table>
    <div class="spec-stage__art"><?php echo $featured['image']; ?></div>
    <a class="buy pill pill--light" href="<?php echo esc_url($featured['url']); ?>"><?php echo esc_html($featured['title']); ?> · <?php echo wp_kses_post($featured['price']); ?> <span class="arrow" aria-hidden="true">↗</span></a>
  </div>
  <?php if (count($products) > 1) : ?>
  <ul class="cards">
    <?php foreach (array_slice($products, 1) as $p) : ?>
      <li class="card"><a href="<?php echo esc_url($p['url']); ?>">
        <span class="card__img"><?php echo $p['image']; ?></span>
        <span class="card__t"><?php echo esc_html($p['title']); ?></span>
        <span class="card__p"><?php echo wp_kses_post($p['price']); ?></span>
      </a></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>
<?php endif; ?>

<section class="faq sheet" aria-labelledby="faq-title">
  <p class="eyebrow">/Dúvidas</p>
  <h2 id="faq-title" class="display">As suas perguntas,<br>as nossas respostas</h2>
  <p class="faq__lead">As respostas mais comuns sobre escolha, utilização e garantia. Para outras questões, <a href="<?php echo esc_url(home_url('/contactos/')); ?>">contacte-nos</a>.</p>
  <ol class="acc">
    <?php foreach ($faq as $i => [$q, $a]) : ?>
      <li class="acc__item">
        <h3><button class="acc__btn" type="button" aria-expanded="<?php echo $i === 2 ? 'true' : 'false'; ?>" aria-controls="acc-<?php echo $i; ?>"><span class="acc__n">/0<?php echo $i + 1; ?></span> <?php echo esc_html($q); ?><span class="arrow" aria-hidden="true">↗</span></button></h3>
        <div id="acc-<?php echo $i; ?>" class="acc__panel" <?php echo $i === 2 ? '' : 'hidden'; ?>><p><?php echo esc_html($a); ?></p></div>
      </li>
    <?php endforeach; ?>
  </ol>
</section>
<?php get_footer();
