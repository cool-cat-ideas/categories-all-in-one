<?php

/**
 * @package Categories All In One
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}
?>
<section class="cci-categories-all-in-one-admin-card cci-categories-all-in-one-admin-brand">
  <div class="cci-categories-all-in-one-admin-brand-heading">
    <img src="<?php echo esc_url(plugins_url('images/cci-categories-all-in-one-icon.webp', dirname(__DIR__) . '/categories-all-in-one.php')); ?>" width="48" height="48" alt="">
    <div>
      <span>Cool Cat Ideas</span>
      <small><?php esc_html_e('Part of teastudio', 'categories-all-in-one'); ?></small>
    </div>
  </div>
  <p><?php esc_html_e('This plugin is one small part of what we build. We create and maintain online stores, WordPress websites, web applications and API integrations.', 'categories-all-in-one'); ?></p>
  <a class="button button-primary" href="https://coolcatideas.com/en/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('See what we build', 'categories-all-in-one'); ?></a>
</section>

<section class="cci-categories-all-in-one-admin-card cci-categories-all-in-one-admin-links">
  <h2><?php esc_html_e('Help and links', 'categories-all-in-one'); ?></h2>
  <a href="<?php echo esc_url(Categories_All_In_One::DOCUMENTATION_URL); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Documentation', 'categories-all-in-one'); ?></a>
  <a href="<?php echo esc_url(Categories_All_In_One::PRODUCT_URL); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Product page', 'categories-all-in-one'); ?></a>
  <a href="https://wordpress.org/plugins/categories-all-in-one/#reviews" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Leave a review', 'categories-all-in-one'); ?></a>
</section>
