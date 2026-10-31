<?php defined('ABSPATH') || exit; ?>
</main>
<footer class="site-footer">
  <div class="site-footer__cols">
    <div class="site-footer__id">
      <p class="eyebrow">/Empresa</p>
      <p><?php echo arseco_company_html('razao_social'); ?><br>NIF <?php echo arseco_company_html('nif'); ?><br><?php echo arseco_company_html('morada'); ?>, <?php echo arseco_company_html('cp_localidade'); ?></p>
      <p><?php echo arseco_company_html('email'); ?> · <?php echo arseco_company_html('telefone'); ?></p>
    </div>
    <nav aria-label="Informação"><h2>Informação</h2><?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'fallback_cb' => false, 'depth' => 1]); ?></nav>
    <nav aria-label="Legal"><h2>Legal</h2><?php wp_nav_menu(['theme_location' => 'legal', 'container' => false, 'fallback_cb' => false, 'depth' => 1]); ?>
      <ul><li><button class="as-link" type="button" data-consent-open>Configurar cookies</button></li></ul></nav>
  </div>
  <p class="site-footer__word" aria-hidden="true"><?php echo esc_html(arseco_brand()); ?></p>
  <p class="site-footer__copy">© <?php echo esc_html(wp_date('Y')); ?> <?php echo arseco_company_html('razao_social'); ?>. Todos os direitos reservados. Preços com IVA incluído.</p>
</footer>

<div class="consent" role="dialog" aria-modal="false" aria-labelledby="consent-title" hidden>
  <h2 id="consent-title">Cookies</h2>
  <p>Usamos cookies essenciais para a loja funcionar. Os restantes só com o seu consentimento. <a href="<?php echo esc_url(home_url('/politica-de-cookies/')); ?>">Política de cookies</a></p>
  <div class="consent__opts" hidden>
    <label><input type="checkbox" checked disabled> Essenciais (sempre ativos)</label>
    <label><input type="checkbox" name="analytics"> Análise</label>
    <label><input type="checkbox" name="marketing"> Marketing</label>
  </div>
  <div class="consent__btns">
    <button class="btn btn--line" type="button" data-consent="reject">Rejeitar</button>
    <button class="btn btn--line" type="button" data-consent="config">Configurar</button>
    <button class="btn btn--line" type="button" data-consent="accept">Aceitar</button>
  </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
