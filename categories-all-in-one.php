<?php
/*
Plugin Name: Categories All In One
Plugin URI: https://coolcatideas.com/en/products/categories-all-in-one/
Description: Help visitors browse posts and products with category lists, columns and cards.
Version: 1.2.0
Requires at least: 6.2
Requires PHP: 7.4
Text Domain: categories-all-in-one
Domain Path: /languages
Author: Cool Cat Ideas
Author URI: https://coolcatideas.com
Author Email: info@coolcatideas.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
SPDX-License-Identifier: GPL-2.0-or-later

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

add_action("init", "categories_all_in_one_init");
function categories_all_in_one_init()
{
  load_plugin_textdomain("categories-all-in-one", false, dirname(plugin_basename(__FILE__)) .  "/languages/");
}


/*
 * plugin
 */
require_once __DIR__ . '/includes/admin-release-status.php';
$categories_All_In_One = new Categories_All_In_One();
class Categories_All_In_One
{
  const VERSION = '1.2.0';
  const HOME_URL = 'https://coolcatideas.com/en/';
  const PRODUCT_URL = 'https://coolcatideas.com/en/products/categories-all-in-one/';
  const DOCUMENTATION_URL = 'https://coolcatideas.com/en/docs/categories-all-in-one/1.2.0/';
  const GITHUB_URL = 'https://github.com/cool-cat-ideas/categories-all-in-one';
  const WORDPRESS_REVIEW_URL = 'https://wordpress.org/support/plugin/categories-all-in-one/reviews/#new-post';
  private $options = array();

  public function __construct()
  {
    /*
    * get options
    */
    $this->options = array_merge($this->get_defaults(), get_option('categories-all-in-one_options') ? get_option('categories-all-in-one_options') : array());

    /*
    * include utils
    */
    require_once("includes/utils.class.php");
    add_action('rest_api_init', array($this, 'custom_endpoint_api'));
    //include required files based on admin or site
    if (is_admin()) {
      /*
      * activate plugin
      */
      add_action('init', array($this, 'categories_all_in_one_button'));

      /*
      * register scripts and stylesheets for admin
      */
      add_action("admin_enqueue_scripts", array($this, "admin_categories_all_in_one_register_scripts"));

      /*
      * ajax page for shortcode generator
      */
      add_action("wp_ajax_categories_all_in_one_shortcode_generator", array($this, "categories_all_in_one_shortcode_generator"));

      add_action('enqueue_block_editor_assets', array($this, 'custom_nonce_localize_script'));


      /*
      * settings
      */
      add_action('admin_init', array($this, 'register_settings'));
      add_action('admin_menu', array($this, 'admin_menu_options'));

      /*
      * clear settings
      */
      register_deactivation_hook(__FILE__,  array($this, 'deactivation'));
    } else {
      require_once("shortcode-decode.class.php");

      add_action("wp_enqueue_scripts", array($this, "categories_all_in_one_register_scripts"));
    }

    /*
    * widget
    */
    require_once("categories-generator.class.php");
    require_once("categories-widget.class.php");
  }


  /**
   * deactivate the plugin
   */
  public function deactivation()
  {
    if (! current_user_can('activate_plugins')) {
      return;
    }
  }


  /**
   * retrieves the plugin options from the database.
   */
  private function get_defaults()
  {
    return array(
      'include_plugin_styles' => 1,
    );
  }

  public function categories_all_in_one_shortcode_generator()
  {
    if (!current_user_can('edit_posts')) {
      wp_die(
        esc_html__('You are not allowed to create category shortcodes.', 'categories-all-in-one'),
        esc_html__('Access denied', 'categories-all-in-one'),
        array('response' => 403)
      );
    }

    check_ajax_referer('categories_all_in_one_shortcode_generator');

    require_once("shortcode-generator.php");
    exit();
  }

  /*
  * registers the scripts and styles for admin
  */
  public function admin_categories_all_in_one_register_scripts()
  {
    $asset_path = plugin_dir_path(__FILE__) . 'css/categories-all-in-one-admin.css';
    wp_register_style(
      'categories-all-in-one-admin',
      plugin_dir_url(__FILE__) . 'css/categories-all-in-one-admin.css',
      array(),
      file_exists($asset_path) ? (string) filemtime($asset_path) : self::VERSION
    );
    wp_enqueue_style("categories-all-in-one-admin");

  }


  /*
  * registers the scripts and styles
  */
  function categories_all_in_one_register_scripts()
  {
    if (
      !empty($this->options['include_plugin_styles'])
      && $this->should_enqueue_frontend_styles()
    ) {
      $asset_path = plugin_dir_path(__FILE__) . 'css/categories-all-in-one.css';
      wp_enqueue_style(
        'categories-all-in-one',
        plugin_dir_url(__FILE__) . 'css/categories-all-in-one.css',
        array(),
        file_exists($asset_path) ? (string) filemtime($asset_path) : self::VERSION
      );
    }
  }

  private function should_enqueue_frontend_styles()
  {
    global $wp_query;

    $posts = array();
    $queried_object = get_queried_object();

    if ($queried_object instanceof WP_Post) {
      $posts[$queried_object->ID] = $queried_object;
    }

    if ($wp_query instanceof WP_Query && is_array($wp_query->posts)) {
      foreach ($wp_query->posts as $post) {
        if ($post instanceof WP_Post) {
          $posts[$post->ID] = $post;
        }
      }
    }

    $should_enqueue = is_active_widget(false, false, 'categories_all_in_one', true);

    foreach ($posts as $post) {
      $content = (string) $post->post_content;

      if (
        has_shortcode($content, 'categories_all_in_one')
        || has_block('categories-all-in-one/block', $content)
      ) {
        $should_enqueue = true;
        break;
      }
    }

    return (bool) apply_filters(
      'categories_all_in_one_should_enqueue_frontend_styles',
      $should_enqueue,
      array_values($posts)
    );
  }


  /*
  * add button to editor
  */
  function categories_all_in_one_button()
  {

    // Gutenberg block
    if (function_exists('register_block_type')) {
      wp_register_script(
        'categories-all-in-one-blocks',
        plugins_url('js/block/block-categories-all-in-one.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'jquery', 'jquery-ui-sortable'),
        filemtime(plugin_dir_path(__FILE__) . 'js/block/block-categories-all-in-one.js'),
        true
      );

      wp_set_script_translations('categories-all-in-one-blocks', 'categories-all-in-one', plugin_dir_path(__FILE__) . '/languages');

      wp_register_style(
        'categories-all-in-one-editor-style',
        plugins_url('css/categories-all-in-one-editor.css', __FILE__),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(__FILE__) . '/css/categories-all-in-one-editor.css')
      );

      $styles = array('categories-all-in-one-editor-style');

      if (!empty($this->options['include_plugin_styles'])) {
        wp_register_style(
          'categories-all-in-one-view-style',
          plugins_url('css/categories-all-in-one.css', __FILE__),
          array('wp-edit-blocks'),
          filemtime(plugin_dir_path(__FILE__) . '/css/categories-all-in-one.css')
        );

        $styles = array_merge($styles, array('categories-all-in-one-view-style'));
      }

      register_block_type('categories-all-in-one/block', array(
        'editor_script' => 'categories-all-in-one-blocks',
        'editor_style' => $styles,
      ));
    }


    // adds button to the visual editor
    add_filter("mce_external_plugins", array($this, "add_categories_all_in_one_plugin"));
    add_filter("mce_buttons", array($this, "register_add_categories_all_in_one_button"));

    //legacy widget
    wp_enqueue_script(
      'categories-all-in-one-widget',
      plugin_dir_url(__FILE__) . 'js/legacy-widget.js',
      ['jquery', 'jquery-ui-sortable'],
      filemtime(plugin_dir_path(__FILE__) . '/js/legacy-widget.js'),
      true
    );

    wp_add_inline_script(
      'categories-all-in-one-widget',
      'window.CategoriesAllInOneClassicEditor = ' . wp_json_encode(array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('categories_all_in_one_shortcode_generator'),
      )) . ';',
      'before'
    );
  }

  function custom_nonce_localize_script()
  {
		wp_enqueue_script(
			'categories-all-in-one-shortcode-inspector',
			plugins_url('js/block/shortcode-inspector.js', __FILE__),
			array('categories-all-in-one-blocks', 'wp-hooks', 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'),
			filemtime(__DIR__ . '/js/block/shortcode-inspector.js'),
			true
		);
    global $post;
    $postId = !empty($post) ? $post->ID : 0;
    $locale = Categories_All_In_One_Utils::get_language();

    wp_localize_script('categories-all-in-one-widget', 'Categories_All_In_One_Widget', [
      'apiUrl' => site_url() . '/wp-json/categories-all-in-one/v1',
      'nonce'    => wp_create_nonce('wp_rest'),
      'postId' => $postId,
    ]);

    wp_localize_script(
      'categories-all-in-one-blocks',
      'CategoriesAllInOnePluginData',
      array(
        'settingsUrl' => admin_url('options-general.php?page=categories-all-in-one'),
        'productUrl' => self::PRODUCT_URL,
        'documentationUrl' => self::DOCUMENTATION_URL,
        'brandLogo' => plugins_url('/images/cci-categories-all-in-one-icon.webp', __FILE__),
        'apiUrl' => site_url() . '/wp-json/categories-all-in-one/v1',
        'permalinkStructure' => get_option('permalink_structure'),
        'postId' => $postId,
        'locale' => $locale,
        'dictionaries' => array(
          'defaults' => Categories_All_In_One_Generator::get_defaults(),
          'list' => Categories_All_In_One_Utils::get_lists(),
          'taxonomies' => Categories_All_In_One_Utils::get_taxonomies(),
          'ordersBy' => Categories_All_In_One_Utils::get_orders_by(),
          'orders' => Categories_All_In_One_Utils::get_orders(),
          'counterBrackets' => Categories_All_In_One_Utils::get_counters_brackets(),
          'layouts' => Categories_All_In_One_Utils::get_layouts(),
          'columns' => Categories_All_In_One_Utils::get_columns(),
          'languages' => Categories_All_In_One_Utils::get_languages(),
        ),
        'translations' => array(
          'shortcodeLabel' => esc_html__('Shortcode', 'categories-all-in-one'),
          'shortcodeHelp' => esc_html__('Copy this shortcode to reuse the current block settings elsewhere.', 'categories-all-in-one'),
          'noCategories' => esc_html__('No categories', 'categories-all-in-one'),
          'brandTitle' => esc_html__('More tools for your WordPress site', 'categories-all-in-one'),
          'brandText' => esc_html__('Cool Cat Ideas is part of teastudio. We build and maintain online stores, WordPress websites, web applications and integrations for growing businesses.', 'categories-all-in-one'),
          'brandButton' => esc_html__('See what we build', 'categories-all-in-one'),
        ),
        'nonce' => wp_create_nonce('wp_rest')
      )
    );
  }


  /*
  * callback function
  */
  function add_categories_all_in_one_plugin($plugin_array)
  {
    $plugin_array["categories_all_in_one_button"] = plugin_dir_url(__FILE__) . "js/plugin-4.0.js";
    return $plugin_array;
  }

  /*
  * callback function
  */
  public function register_add_categories_all_in_one_button($buttons)
  {
    array_push($buttons, "categories_all_in_one_button");
    return $buttons;
  }

  public function custom_endpoint_api()
  {
    register_rest_route('categories-all-in-one/v1', '/categories', array(
      'methods'  => 'POST',
      'callback' => array($this, 'categories_callback'),
      'permission_callback' => function (WP_REST_Request $request) {
        $permissions = ['edit_posts'];

        foreach ($permissions as $cap) {
          if (current_user_can($cap)) {
            return true;
          }
        }

        return false;
      }
    ));
  }

  public function categories_callback($request)
  {
    $atts = $request->get_params();
    $output = Categories_All_In_One_Utils::get_categories($atts);

    return new WP_REST_Response($output, 200);
  }

  /**
   * add submenu
   */
  public function admin_menu_options()
  {
    add_options_page(
      esc_html__('Categories All In One', 'categories-all-in-one'),
      esc_html__('Categories All In One', 'categories-all-in-one'),
      'manage_options',
      'categories-all-in-one',
      array($this, 'settings_page')
    );
  }

  /**
   * register plugin settings
   */
  public function register_settings()
  {
    register_setting('categories-all-in-one', 'categories-all-in-one_options', function ($input) {
      return $this->sanitize_settings($input);
    });
  }

  public function sanitize_settings($input)
  {
    $sanitized = array();

    if (isset($input['include_plugin_styles'])) {
      $sanitized['include_plugin_styles'] = absint($input['include_plugin_styles']);
    } else {
      $sanitized['include_plugin_styles'] = 0;
    }

    return $sanitized;
  }

  public function settings_page()
  {
?>
    <div class="wrap cci-product-admin cci-categories-all-in-one-admin">
      <h1 class="screen-reader-text">Categories All In One</h1>

      <header class="cci-product-admin-header">
        <div class="cci-product-admin-identity">
          <img src="<?php echo esc_url(plugins_url('/images/cci-categories-all-in-one-icon.webp', __FILE__)); ?>" width="56" height="56" alt="">
          <div>
            <span class="cci-product-admin-title">Categories All In One</span>
            <p><?php esc_html_e('Build clear category navigation from the taxonomies you already manage in WordPress.', 'categories-all-in-one'); ?></p>
          </div>
        </div>
      </header>

      <div class="cci-product-admin-layout">
        <main class="cci-product-admin-main">
          <section class="cci-product-admin-metrics" aria-label="<?php esc_attr_e('Plugin summary', 'categories-all-in-one'); ?>">
            <article class="cci-product-admin-metric cci-product-admin-metric-primary">
              <span class="dashicons dashicons-block-default" aria-hidden="true"></span>
              <div><span><?php esc_html_e('Editor', 'categories-all-in-one'); ?></span><strong><?php esc_html_e('Block', 'categories-all-in-one'); ?></strong></div>
            </article>
            <article class="cci-product-admin-metric">
              <span class="dashicons dashicons-shortcode" aria-hidden="true"></span>
              <div><span><?php esc_html_e('Reusable embeds', 'categories-all-in-one'); ?></span><strong><?php esc_html_e('Shortcode', 'categories-all-in-one'); ?></strong></div>
            </article>
            <article class="cci-product-admin-metric">
              <span class="dashicons dashicons-screenoptions" aria-hidden="true"></span>
              <div><span><?php esc_html_e('Legacy support', 'categories-all-in-one'); ?></span><strong><?php esc_html_e('Widget', 'categories-all-in-one'); ?></strong></div>
            </article>
          </section>

          <section class="cci-product-admin-review">
            <div class="cci-product-admin-review-content">
              <span class="cci-product-admin-review-label"><span class="dashicons dashicons-format-chat" aria-hidden="true"></span><?php esc_html_e('Quick feedback', 'categories-all-in-one'); ?></span>
              <h2><?php esc_html_e('How is Categories All In One working for you?', 'categories-all-in-one'); ?></h2>
              <p><?php esc_html_e('If this plugin helps your site, a short review helps us keep improving it.', 'categories-all-in-one'); ?></p>
              <div class="cci-product-admin-review-actions">
                <span class="cci-product-admin-stars" aria-hidden="true">
                  <?php for ($star = 0; $star < 5; $star++) : ?><span class="dashicons dashicons-star-filled"></span><?php endfor; ?>
                </span>
                <a class="button button-primary" href="<?php echo esc_url(self::WORDPRESS_REVIEW_URL); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Rate on WordPress.org', 'categories-all-in-one'); ?></a>
              </div>
            </div>
            <img class="cci-product-admin-mascot" src="<?php echo esc_url(plugins_url('/images/cci-hello.png', __FILE__)); ?>" width="300" height="216" alt="">
          </section>

          <section class="cci-product-admin-card">
            <div class="cci-product-admin-card-heading">
              <h2><?php esc_html_e('Start here', 'categories-all-in-one'); ?></h2>
              <p><?php esc_html_e('Add the block, choose its source and layout, then check the result at every breakpoint.', 'categories-all-in-one'); ?></p>
            </div>
            <div class="cci-product-admin-steps">
              <div><strong>1</strong><p><?php esc_html_e('Open a page or post and add the Categories All In One block.', 'categories-all-in-one'); ?></p></div>
              <div><strong>2</strong><p><?php esc_html_e('Choose a taxonomy, layout and the hierarchy depth you want to show.', 'categories-all-in-one'); ?></p></div>
              <div><strong>3</strong><p><?php esc_html_e('Publish the page and check the category links on desktop and mobile.', 'categories-all-in-one'); ?></p></div>
            </div>
          </section>

          <section class="cci-product-admin-card">
            <div class="cci-product-admin-card-heading">
              <h2><?php esc_html_e('Style loading', 'categories-all-in-one'); ?></h2>
              <p><?php esc_html_e('Choose whether this plugin supplies its complete frontend presentation.', 'categories-all-in-one'); ?></p>
            </div>
            <form method="post" action="options.php">
              <?php settings_fields('categories-all-in-one'); ?>
              <div class="cci-product-admin-card-body">
                <label class="cci-product-admin-toggle" for="categories-all-in-one_include_plugin_styles">
                  <input type="checkbox" name="categories-all-in-one_options[include_plugin_styles]" id="categories-all-in-one_include_plugin_styles" value="1" <?php checked(!empty($this->options['include_plugin_styles'])); ?> />
                  <span>
                    <strong><?php esc_html_e('Load the plugin styles', 'categories-all-in-one'); ?></strong>
                    <small><?php esc_html_e('Keep this enabled unless your theme supplies the complete category layout.', 'categories-all-in-one'); ?></small>
                  </span>
                </label>
              </div>
              <div class="cci-product-admin-card-footer">
                <?php submit_button(esc_html__('Save changes', 'categories-all-in-one'), 'primary', 'submit', false); ?>
              </div>
            </form>
          </section>
        </main>

        <aside class="cci-product-admin-sidebar">
          <?php include plugin_dir_path(__FILE__) . 'includes/admin-sidebar.php'; ?>
        </aside>
      </div>
    </div>
<?php
  }
}
