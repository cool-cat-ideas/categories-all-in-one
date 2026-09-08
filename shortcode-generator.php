<?php

/**
 * @package Categories All In One
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

$locale = esc_attr(Categories_All_In_One_Utils::get_language());
$languages = Categories_All_In_One_Utils::get_languages();

$language = $locale;
$defaults = Categories_All_In_One_Generator::get_defaults();

$categories = Categories_All_In_One_Utils::get_categories(array(
  'lang' => $language,
  'taxonomy' => $defaults['taxonomy'],
  'parent' => $defaults['parent_category'],
  'orderby' => $defaults['orderby'],
  'order' => $defaults['order'],
  'max_depth' => $defaults['max_depth'],
  'hide_empty' => $defaults['hide_empty'],
  'exclude' => $defaults['exclude'],
));

$categoriesForSelect = $categories['categoriesForSelect'];
$categoriesForExclude = $categories['categoriesForExclude'];
$categoriesForSortable = $categories['categoriesForSortable'];
?>

<form action="" method="post" class="categories-all-in-one-form-widget">
  <div class=" categories-all-in-one-form">
    <aside class="categories-all-in-one-sidebar">
      <?php include('includes/plugin-info.php'); ?>
    </aside>

    <main class="categories-all-in-one-content" style="position:relative;">
      <div class="categories-all-in-one-block-loader" style="visibility: hidden;"></div>
      <div class="categories-all-in-one-block-spinner" style="visibility: hidden;"></div>

      <?php if (is_array($languages) && !empty($languages)): ?>
        <div class="form-group">
          <label for="lang"><?php esc_html_e('Language', 'categories-all-in-one'); ?>:</label>
          <select name="widget-categories_all_in_one[1][lang]" id="lang" class="categories-all-in-one-field-request">
            <?php
            foreach ($languages as $item) {
              echo "<option value=\"" . esc_attr($item['language_code']) . "\" " . ($language === esc_attr($item['language_code']) ? 'selected="selected"' : null) . ">" . esc_html($item['native_name']) . "</option>";
            }
            ?>
          </select>
        </div>
      <?php endif ?>

      <div class="form-group checkbox-group">
        <label for="post">
          <input type="checkbox" name="widget-categories_all_in_one[1][post]" id="post" value="1" class="checkbox categories-all-in-one-field categories-all-in-one-field-post categories-all-in-one-field-request">
          <?php esc_html_e('Only categories from this content', 'categories-all-in-one'); ?>
        </label>
      </div>

      <div class="form-group ">
        <label><?php esc_html_e('Category source', 'categories-all-in-one'); ?>:</label>
        <ul class="categories-all-in-one-tree">
          <?php
          $taxanomies_list = Categories_All_In_One_Utils::get_taxonomies();
          if (is_array($taxanomies_list) && !empty($taxanomies_list)) {
            foreach ($taxanomies_list as $item) {
              echo "<li>";

              if (array_key_exists('taxonomy', $defaults)) {

                $checked = (is_array($defaults['taxonomy']) && in_array($item['value'], $defaults['taxonomy'])) ||
                  ($item['value'] === $defaults['taxonomy'])
                  ? 'checked="checked"'
                  : null;
              } else {
                $checked = null;
              }

              echo "<label for=\"taxonomy-" . esc_attr($item['value']) . "\">";
              echo "<input class=\"checkbox categories-all-in-one-field categories-all-in-one-field-taxonomy categories-all-in-one-field-request\" type=\"checkbox\" value=\"" . esc_attr($item['value']) . "\" id=\"taxonomy-" . esc_attr($item['value']) . "\" " . esc_html($checked) . " name=\"" . 'widget-categories_all_in_one[1][taxonomy]' . "[" . esc_attr($item['value']) . "]\" />";
              echo esc_html($item['label']);
              echo "</label>";

              echo "</li>";
            }
          }
          ?>
        </ul>
      </div>

      <div class="form-group">
        <label for="parent_category"><?php esc_html_e('Categories from parent', 'categories-all-in-one'); ?>:</label>
        <select name="widget-categories_all_in_one[1][parent_category]" id="parent_category" class="select categories-all-in-one-field categories-all-in-one-field-parent categories-all-in-one-field-request">
          <?php

          if (is_array($categoriesForSelect) && !empty($categoriesForSelect)) {
            foreach ($categoriesForSelect as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="orderby"><?php esc_html_e('Order by', 'categories-all-in-one'); ?>:</label>
        <select name="widget-categories_all_in_one[1][orderby]" id="orderby" class="categories-all-in-one-field-request">
          <?php
          $orderby_list = Categories_All_In_One_Utils::get_orders_by();
          if (is_array($orderby_list) && !empty($orderby_list)) {
            foreach ($orderby_list as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="order"><?php esc_html_e('Order', 'categories-all-in-one'); ?>:</label>
        <select name="widget-categories_all_in_one[1][order]" id="order" class="categories-all-in-one-field-request">
          <?php
          $order_list = Categories_All_In_One_Utils::get_orders();
          if (is_array($order_list) && !empty($order_list)) {
            foreach ($order_list as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="max_depth"><?php esc_html_e('Category hierarchy depth', 'categories-all-in-one'); ?>:</label>
        <input id="max_depth" name="widget-categories_all_in_one[1][max_depth]" type="number" min="0" size="5" class="checkbox categories-all-in-one-field categories-all-in-one-field-request categories-all-in-one-field-max_depth" value="<?php echo esc_attr($defaults['max_depth']); ?>" />
        <p style="margin-top:0;"><small><i><?php esc_html_e('0 means no depth limit.', 'categories-all-in-one') ?></i></small></p>
      </div>

      <div class="form-group checkbox-group">
        <input type="checkbox" name="widget-categories_all_in_one[1][hide_empty]" id="hide_empty" value="<?php echo esc_attr($defaults['hide_empty']); ?>" class="categories-all-in-one-field-request">
        <label for="hide_empty"><?php esc_html_e('Hide empty', 'categories-all-in-one'); ?></label>
      </div>

      <div class="form-group">
        <label for="exclude"><?php esc_html_e('Exclude categories', 'categories-all-in-one'); ?>:</label>
        <div class="exclude">
          <?php if (is_array($categoriesForExclude) && !empty($categoriesForExclude)): ?>
            <ul class="categories-all-in-one-exclude-container categories-all-in-one-exclude-root categories-all-in-one-tree">
              <?php echo Categories_All_In_One_Utils::render_category_tree_for_exclude($categoriesForExclude, $defaults['exclude'],  'exclude') ?>
            </ul>
          <?php else: ?>
            <?php esc_html_e('No categories', 'categories-all-in-one'); ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group ">
        <label><?php esc_html_e('Customize category order', 'categories-all-in-one'); ?>:</label>
        <div class="categories-all-in-one-sortable">
          <?php if (is_array($categoriesForSortable) && !empty($categoriesForSortable)): ?>
            <ul class="categories-all-in-one-sortable-container categories-all-in-one-sortable-root">
              <?php echo Categories_All_In_One_Utils::render_category_tree_for_sortable($categoriesForSortable, $defaults['exclude']) ?>
            </ul>
          <?php else: ?>
            <?php esc_html_e('No categories', 'categories-all-in-one'); ?>
          <?php endif; ?>
        </div>
        <textarea style="display:none;" class="categories-all-in-one-field-sortable" id="sortable" name="sortable"></textarea>
      </div>

      <div class="form-group">
        <label for="layout"><?php esc_html_e('Layout', 'categories-all-in-one'); ?>:</label>
        <select name="widget-categories_all_in_one[1][layout]" id="layout">
          <?php foreach (Categories_All_In_One_Utils::get_layouts() as $item): ?>
            <option value="<?php echo esc_attr($item['value']); ?>"><?php echo esc_html($item['label']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="columns"><?php esc_html_e('Columns', 'categories-all-in-one'); ?>:</label>
        <select name="widget-categories_all_in_one[1][columns]" id="columns">
          <?php foreach (Categories_All_In_One_Utils::get_columns() as $item): ?>
            <option value="<?php echo esc_attr($item['value']); ?>" <?php selected((int) $defaults['columns'], (int) $item['value']); ?>><?php echo esc_html($item['label']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="list"><?php esc_html_e('List type', 'categories-all-in-one'); ?>:</label>
        <select name="widget-categories_all_in_one[1][list]" id="list">
          <?php
          $lists = Categories_All_In_One_Utils::get_lists();
          if (is_array($lists) && !empty($lists)) {
            foreach ($lists as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div class="form-group checkbox-group">
        <input type="checkbox" name="widget-categories_all_in_one[1][show_count]" id="show_count" value="<?php echo esc_attr($defaults['show_count']); ?>">
        <label for="show_count"><?php esc_html_e('Show count', 'categories-all-in-one'); ?></label>
      </div>

      <div class="form-group checkbox-group">
        <input type="checkbox" name="widget-categories_all_in_one[1][show_image]" id="show_image" value="<?php echo esc_attr($defaults['show_image']); ?>">
        <label for="show_image"><?php esc_html_e('Show category image', 'categories-all-in-one'); ?></label>
      </div>

      <div class="form-group">
        <label for="counter_brackets"><?php esc_html_e('Counter\'s brackets', 'categories-all-in-one'); ?>:</label>
        <select name="widget-categories_all_in_one[1][counter_brackets]" id="counter_brackets">
          <?php
          $brackets_list = Categories_All_In_One_Utils::get_counters_brackets();
          if (is_array($brackets_list) && !empty($brackets_list)) {
            foreach ($brackets_list as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div class="form-group checkbox-group">
        <input type="checkbox" name="widget-categories_all_in_one[1][show_description]" id="show_description" value="<?php echo esc_attr($defaults['show_description']); ?>">
        <label for="show_description"><?php esc_html_e('Show description', 'categories-all-in-one'); ?></label>
      </div>

      <div class="form-group">
        <label for="description_length"><?php esc_html_e('Description length in chars', 'categories-all-in-one'); ?>:</label>
        <input id="description_length" name="widget-categories_all_in_one[1][description_length]" type="number" min="0" size="5" class="checkbox categories-all-in-one-field categories-all-in-one-field-description_length" value="<?php echo esc_attr($defaults['description_length']); ?>" />
        <p style="margin-top:0;"><small><i><?php esc_html_e('0 means no description limit.', 'categories-all-in-one') ?></i></small></p>
      </div>

      <div class="form-group checkbox-group">
        <input type="checkbox" name="widget-categories_all_in_one[1][description_link]" id="description_link" value="<?php echo esc_attr($defaults['description_link']); ?>">
        <label for="description_link"><?php esc_html_e('Description as link', 'categories-all-in-one'); ?></label>
      </div>

      <div class="form-group">
        <label for="separator"><?php esc_html_e('Separator for each category', 'categories-all-in-one'); ?>:</label>
        <input type="text" name="widget-categories_all_in_one[1][separator]" id="separator" value="<?php echo esc_attr($defaults['separator']); ?>">
      </div>

      <div class="form-group">
        <label for="custom_class"><?php esc_html_e('Custom CSS class(es) for each category', 'categories-all-in-one'); ?>:</label>
        <input type="text" name="widget-categories_all_in_one[1][custom_class]" id="custom_class" value="<?php echo esc_attr($defaults['custom_class']); ?>">
        <p style="margin-top:0;"><small><i><?php esc_html_e('Separate multiple classes with spaces.', 'categories-all-in-one') ?></i></small></p>
      </div>

      <div class="form-group">
        <label for="block_custom_class"><?php esc_html_e('Additional CSS class(es) for block', 'categories-all-in-one'); ?>:</label>
        <input class="categories-all-in-one-field" id="block_custom_class" name="widget-categories_all_in_one[1][block_custom_class]" type="text" size="5" value="<?php echo esc_html(array_key_exists('block_custom_class', $defaults) && !empty(esc_attr($defaults['block_custom_class']))) ? esc_attr($defaults["block_custom_class"]) : ''; ?>" />
        <p style="margin-top:0;"><small><i><?php esc_html_e('Separate multiple classes with spaces.', 'categories-all-in-one') ?></i></small></p>
      </div>

      <button class="button-primary categories-all-in-one-insert-shortcode" style="margin-top:20px">
        <?php esc_html_e('Insert Shortcode', 'categories-all-in-one'); ?>
      </button>
    </main>
  </div>
</form>
