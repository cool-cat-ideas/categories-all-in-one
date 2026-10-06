<?php

if (!defined('ABSPATH')) {
	exit;
}
$release = cci_categories_release_status();
$message = !$release
	? __('Version feed is unavailable', 'categories-all-in-one')
	: ($release['hasUpdate'] ? __('New version available', 'categories-all-in-one') : __('You are up to date', 'categories-all-in-one'));
?>
<section class="cci-product-admin-card cci-product-admin-status-card">
	<div class="cci-product-admin-status-heading">
		<span class="cci-product-admin-status-icon"><span class="dashicons dashicons-shield" aria-hidden="true"></span></span>
		<div>
			<span><?php esc_html_e('Plugin status', 'categories-all-in-one'); ?></span>
			<strong><?php esc_html_e('Free', 'categories-all-in-one'); ?></strong>
		</div>
	</div>
	<p class="cci-product-admin-release-message"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><span><?php echo esc_html($message); ?></span></p>
	<dl class="cci-product-admin-facts">
		<div><dt><?php esc_html_e('Installed', 'categories-all-in-one'); ?></dt><dd><?php echo esc_html(Categories_All_In_One::VERSION); ?></dd></div>
		<div><dt><?php esc_html_e('Latest', 'categories-all-in-one'); ?></dt><dd><?php echo esc_html($release ? $release['latest'] : '—'); ?></dd></div>
		<?php if ($release) : ?>
			<div><dt><?php esc_html_e('Last check', 'categories-all-in-one'); ?></dt><dd><?php echo esc_html($release['checkedAt']); ?></dd></div>
		<?php endif; ?>
	</dl>
	<?php if ($release && $release['hasUpdate']) : ?>
		<a class="button cci-product-admin-secondary-button" href="<?php echo esc_url($release['url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('View update', 'categories-all-in-one'); ?></a>
	<?php endif; ?>
</section>
<section class="cci-product-admin-card cci-categories-all-in-one-recommendation">
  <h2><?php esc_html_e('Show what is inside each category', 'categories-all-in-one'); ?></h2>
  <p><?php esc_html_e('Help visitors discover posts from a chosen category with WP Posts Carousel All In One. Add a carousel below your category navigation or on a landing page.', 'categories-all-in-one'); ?></p>
  <a class="button button-primary" href="https://coolcatideas.com/en/products/wp-posts-carousel-all-in-one/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Explore WP Posts Carousel', 'categories-all-in-one'); ?></a>
</section>

<section class="cci-product-admin-card cci-categories-all-in-one-about">
  <h2><?php esc_html_e('About us', 'categories-all-in-one'); ?></h2>
  <div class="cci-categories-all-in-one-about-logos">
    <img src="<?php echo esc_url(plugins_url('images/cool-cat-ideas-logo.svg', dirname(__DIR__) . '/categories-all-in-one.php')); ?>" width="160" height="50" alt="Cool Cat Ideas">
    <img src="<?php echo esc_url(plugins_url('images/teastudio-logo.png', dirname(__DIR__) . '/categories-all-in-one.php')); ?>" width="80" height="50" alt="teastudio">
  </div>
  <p><?php esc_html_e('WordPress plugins and PrestaShop modules by Cool Cat Ideas, part of teastudio.', 'categories-all-in-one'); ?></p>
  <a class="cci-categories-all-in-one-about-link" href="<?php echo esc_url(Categories_All_In_One::HOME_URL); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Visit Cool Cat Ideas', 'categories-all-in-one'); ?></a>
</section>
