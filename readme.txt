=== Categories All In One ===
Contributors: coolcatideas, teastudio.pl
Tags: categories, taxonomy, block, widget, shortcode
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Help visitors find posts and products with category lists, columns and cards.

== Description ==

Help visitors choose a topic or product category without searching through a long menu. Categories All In One turns your existing WordPress categories into a list, a set of columns or cards that link to category archives.

Use it for blog topics, WooCommerce product categories or another public hierarchical taxonomy. Add the category section with a block, widget or shortcode.

Use it when a plain menu or sidebar list is not enough. Add blog categories above a post list, show nested categories on a page with many articles, or display WooCommerce product categories with images and short descriptions before the product archive.

You decide which categories appear, how deep the list goes and which details should be visible. Keep parent and child categories together, hide empty terms, show content counts and set your own order without renaming categories in WordPress.

You can also:

* use any public hierarchical taxonomy registered by WordPress or another plugin;
* arrange categories as a list, columns or cards;
* order categories by name or post count;
* drag categories into your preferred order;
* leave out categories that should not appear;
* show short category descriptions;
* show WooCommerce category images or provide an image through a public filter;
* add the same category section with a block, widget or shortcode;
* copy a shortcode matching the current block settings directly from the Gutenberg sidebar.

Get the Free ZIP from [GitHub Releases](https://github.com/cool-cat-ideas/categories-all-in-one/releases) and ask questions in [GitHub Discussions](https://github.com/cool-cat-ideas/categories-all-in-one/discussions).

Read the [documentation](https://coolcatideas.com/en/docs/categories-all-in-one/1.2.0/) or open the [product page](https://coolcatideas.com/en/products/categories-all-in-one/).

== Examples ==

Show blog categories in three columns:

`[categories_all_in_one taxonomy="category" layout="columns" columns="3" hide_empty="true" show_count="true"]`

Show WooCommerce product categories as cards with their category images:

`[categories_all_in_one taxonomy="product_cat" layout="cards" columns="3" show_image="true" hide_empty="true"]`

WooCommerce must be active for `product_cat`. Images appear when a category has an assigned image. To show only the children of one category, add `parent_category="123"` and replace `123` with that category's ID.

== Installation ==

1. In WordPress, open Plugins > Add New Plugin.
2. Choose Upload Plugin and select the ZIP file.
3. Activate Categories All In One.
4. Edit a page and add the Categories All In One block.
5. Choose your category source and layout, then publish the page.

== Frequently Asked Questions ==

= Can I show WooCommerce product categories? =

Yes. Product categories are available when WooCommerce is active because they are a public hierarchical taxonomy.

= Can I use it without the block editor? =

Yes. Use the Categories All In One widget or the `[categories_all_in_one]` shortcode.

= Where can I generate a shortcode? =

In Gutenberg, select the Categories All In One block and open the Shortcode section in its settings sidebar. The read-only field updates with the block settings. Click the field to select the code, then copy it to a Shortcode block or another shortcode-compatible area.

In the classic editor, click the Categories All In One toolbar button to open the shortcode generator.

= Can I add the block to a widget area? =

Yes. In Appearance > Widgets, add the Categories All In One block to an available widget area. The block supports the same category and layout settings as in the page editor. The classic Categories All In One widget is also available.

Your theme must register and display a widget area. If Appearance > Widgets is missing, use the block or a shortcode in a page, or use a theme with widget areas.

= Why is a category missing? =

Check Hide empty, the selected parent, hierarchy depth and excluded categories. Also make sure the taxonomy is public and hierarchical.

== Screenshots ==

1. Plugin settings in WordPress, including style loading, version information and help links.
2. The Categories All In One block in Gutenberg with category source, sorting and hierarchy settings.
3. Category cards in the WordPress widget editor with the block settings sidebar.
4. The shortcode generator opened from the classic editor toolbar.
5. WooCommerce product category cards with images, descriptions and product counts.
6. Parent and child product categories displayed in four columns with their hierarchy and counts.
7. Blog category links arranged in four columns with post counts.

== Changelog ==

= 1.2.0 =

* Added responsive columns and card layouts alongside category lists.
* Added support for public hierarchical taxonomies, including WooCommerce product categories.
* Added category images and public filters for category queries, images and rendered output.
* Load frontend styles only where needed, with a filter for category sections rendered by theme templates.
* Improved output escaping and settings validation; protected the classic editor shortcode generator with permission and nonce checks.
* Updated the product description, shortcode examples, branding and support links.
* Aligned the settings sidebar with the shared admin panels: installed/latest versions, server-side release lookup on page load, help links and save feedback.
* Added a read-only shortcode field to the Gutenberg block settings, with automatic updates and select-on-focus copying.
* Fixed block previews to use the complete layout and display settings, including cards, columns and category images.
* Preserved theme-provided widget wrappers and heading markup.
* Replaced the product screenshots with seven current examples of settings, editors and category layouts.

= 1.1.0 =

* Added independent language support.
* Added drag-and-drop category sorting.
* Added a parameter to display category descriptions.
* Added a parameter for custom block classes.
* Improved plugin styles.

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 1.2.0 =

Adds category columns, cards, images and broader taxonomy support. After updating, check category sections on desktop and mobile, especially if your theme customizes the plugin's output.
