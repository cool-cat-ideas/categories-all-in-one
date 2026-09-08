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
<section class="cci-product-admin-card cci-product-admin-resources">
  <div class="cci-product-admin-card-heading">
    <h2><?php esc_html_e('Help and resources', 'categories-all-in-one'); ?></h2>
    <p><?php esc_html_e('Read the documentation, ask the community or check release notes.', 'categories-all-in-one'); ?></p>
  </div>
  <nav aria-label="<?php esc_attr_e('Categories All In One resources', 'categories-all-in-one'); ?>">
    <a href="<?php echo esc_url(Categories_All_In_One::PRODUCT_URL); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-store" aria-hidden="true"></span><span><?php esc_html_e('Product page', 'categories-all-in-one'); ?></span></a>
    <a href="<?php echo esc_url(Categories_All_In_One::DOCUMENTATION_URL); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-book-alt" aria-hidden="true"></span><span><?php esc_html_e('Documentation', 'categories-all-in-one'); ?></span></a>
    <a href="<?php echo esc_url(Categories_All_In_One::GITHUB_URL . '/discussions'); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-format-chat" aria-hidden="true"></span><span><?php esc_html_e('Community support', 'categories-all-in-one'); ?></span></a>
    <a href="<?php echo esc_url(Categories_All_In_One::GITHUB_URL . '/releases'); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-update" aria-hidden="true"></span><span><?php esc_html_e('Release notes', 'categories-all-in-one'); ?></span></a>
    <a href="<?php echo esc_url(Categories_All_In_One::GITHUB_URL); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-code-standards" aria-hidden="true"></span><span><?php esc_html_e('GitHub repository', 'categories-all-in-one'); ?></span></a>
  </nav>
</section>

<section class="cci-product-admin-card cci-product-admin-brand-card">
  <img src="<?php echo esc_url(plugins_url('images/cool-cat-ideas-logo.svg', dirname(__DIR__) . '/categories-all-in-one.php')); ?>" width="230" height="72" alt="Cool Cat Ideas">
  <img class="cci-product-admin-teastudio-logo" src="<?php echo esc_url(plugins_url('images/teastudio-logo.png', dirname(__DIR__) . '/categories-all-in-one.php')); ?>" alt="teastudio">
  <h2><?php esc_html_e('About us', 'categories-all-in-one'); ?></h2>
  <p><?php esc_html_e('Part of teastudio. We build WordPress and PrestaShop products backed by practical implementation experience.', 'categories-all-in-one'); ?></p>
  <a class="button button-primary" href="<?php echo esc_url(Categories_All_In_One::HOME_URL); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Cool Cat Ideas', 'categories-all-in-one'); ?></a>
</section>
