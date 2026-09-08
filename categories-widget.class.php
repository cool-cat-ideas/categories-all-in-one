<?php

/**
 * @package Categories All In One
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/*
 * widget
 */
class Categories_All_In_One_Widget extends WP_Widget
{
  public function __construct()
  {
    parent::__construct(
      'categories_all_in_one',
      'Categories All In One',
      array(
        'classname'  => 'widget-categories-all-in-one',
        'description' =>  esc_html__('Display customizable category list from selected taxonomies with various sorting and styling options', 'categories-all-in-one')
      )
    );
  }

  public function widget($args, $instance)
  {


    $title = apply_filters('widget_title', $instance['title']);

    echo wp_kses_post($args['before_widget']);

    if ($title) {
      echo wp_kses_post($args['before_title'] . $title . $args['after_title']);
    }

    $html = Categories_All_In_One_Generator::generate($instance, false);

    if (!empty($html) && array_key_exists('formattedHtmlData', $html)) {
      echo wp_kses_post($html['formattedHtmlData']);
    }

    echo wp_kses_post($args['after_widget']);
  }

  public function update($new_instance, $old_instance)
  {
    $clean = Categories_All_In_One_Generator::sanitize_attributes($new_instance);
    $clean['title'] = isset($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
    return $clean;
  }

  /**
   * The configuration form.
   */
  public function form($instance)
  {
    /*
     * load defaults if new
     */
    $instance = wp_parse_args((array) $instance, Categories_All_In_One_Generator::get_defaults());

    $categories = Categories_All_In_One_Utils::get_categories($instance);
    $categoriesForSelect = $categories['categoriesForSelect'];
    $categoriesForExclude = $categories['categoriesForExclude'];
    $categoriesForSortable = $categories['categoriesForSortable'];

    $locale = esc_attr(Categories_All_In_One_Utils::get_language());
    $languages = Categories_All_In_One_Utils::get_languages();

    $language = $locale;
    if (esc_attr($instance["lang"]) !== '') {
      $language = esc_attr($instance["lang"]);
    }
?>
    <div class="categories-all-in-one-form categories-all-in-one-form-widget" style="position:relative;">
      <div class="categories-all-in-one-block-loader" style="visibility: hidden;"></div>
      <div class="categories-all-in-one-block-spinner" style="visibility: hidden;"></div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("title")); ?>"><?php esc_html_e('Title', 'categories-all-in-one'); ?>:</label>
        <input class="widefat categories-all-in-one-field" id="<?php echo esc_attr($this->get_field_id("title")); ?>" name="<?php echo esc_attr($this->get_field_name("title")); ?>" type="text" value="<?php echo (array_key_exists('title', $instance) && !empty($instance['title'])) ? esc_attr($instance["title"]) : ''; ?>" />
      </div>

      <?php if (is_array($languages) && !empty($languages)): ?>
        <div>
          <label for="<?php echo esc_attr($this->get_field_id("lang")); ?>"><?php esc_html_e('Language', 'categories-all-in-one'); ?>:</label>
          <br />
          <select class="select categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("lang")); ?>" name="<?php echo esc_attr($this->get_field_name("lang")); ?>">
            <?php
            foreach ($languages as $item) {
              echo "<option value=\"" . esc_attr($item['language_code']) . "\" " . ($language === esc_attr($item['language_code']) ? 'selected="selected"' : null) . ">" . esc_html($item['native_name']) . "</option>";
            }
            ?>
          </select>
        </div>
      <?php endif ?>

      <div>
        <label><?php esc_html_e('Category source', 'categories-all-in-one'); ?>:</label>
        <br />
        <ul class="categories-all-in-one-tree">
          <?php
          $taxanomies_list = Categories_All_In_One_Utils::get_taxonomies();
          if (is_array($taxanomies_list) && !empty($taxanomies_list)) {
            foreach ($taxanomies_list as $item) {
              echo "<li>";

              if (array_key_exists('taxonomy', $instance)) {
                $checked = (is_array($instance['taxonomy']) && (in_array($item['value'], $instance['taxonomy'], true) || array_key_exists($item['value'], $instance['taxonomy']))) ||
                  ($item['value'] === $instance['taxonomy'])
                  ? 'checked="checked"'
                  : null;
              } else {
                $checked = null;
              }

              echo "<input class=\"checkbox categories-all-in-one-field categories-all-in-one-field-taxonomy categories-all-in-one-field-request\" type=\"checkbox\" value=\"" . esc_attr($item['value']) . "\" id=\"taxonomy-" . esc_attr($item['value']) . "\" " . esc_html($checked) . " name=\"" . esc_attr($this->get_field_name('taxonomy')) . "[" . esc_attr($item['value']) . "]\" />";
              echo "<label for=\"taxonomy-" . esc_attr($item['value']) . "\">" . esc_html($item['label']) . "</label>";
              echo "</li>";
            }
          }
          ?>
        </ul>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("parent_category")); ?>"><?php esc_html_e('Categories from parent', 'categories-all-in-one'); ?>:</label>
        <br />
        <select class="select categories-all-in-one-field categories-all-in-one-field-parent categories-all-in-one-field-request" name="<?php echo esc_attr($this->get_field_name("parent_category")); ?>" id="<?php echo esc_attr($this->get_field_id("parent_category")); ?>">
          <?php if (is_array($categoriesForSelect) && !empty($categoriesForSelect)) {
            foreach ($categoriesForSelect as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\" " . (esc_attr($instance["parent_category"]) == esc_attr($item['value']) ? 'selected="selected"' : null) . ">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("orderby")); ?>"><?php esc_html_e('Order by', 'categories-all-in-one'); ?>:</label>
        <br />
        <select class="select categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("orderby")); ?>" name="<?php echo esc_attr($this->get_field_name("orderby")); ?>">
          <?php
          $orderby_list = Categories_All_In_One_Utils::get_orders_by();
          if (is_array($orderby_list) && !empty($orderby_list)) {
            foreach ($orderby_list as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\" " . (esc_attr($instance["orderby"]) == esc_attr($item['value']) ? 'selected="selected"' : null) . ">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("order")); ?>"><?php esc_html_e('Order', 'categories-all-in-one'); ?>:</label>
        <br />
        <select class="select categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("order")); ?>" name="<?php echo esc_attr($this->get_field_name("order")); ?>">
          <?php
          $order_list = Categories_All_In_One_Utils::get_orders();
          if (is_array($order_list) && !empty($order_list)) {
            foreach ($order_list as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\" " . (esc_attr($instance["order"]) == esc_attr($item['value']) ? 'selected="selected"' : null) . ">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("max_depth")); ?>"><?php esc_html_e('Category hierarchy depth', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request categories-all-in-one-field-max_depth" id="<?php echo esc_attr($this->get_field_id("max_depth")); ?>" name="<?php echo esc_attr($this->get_field_name("max_depth")); ?>" type="number" min="0" size="5" value="<?php echo array_key_exists('max_depth', $instance) ? esc_attr($instance["max_depth"]) : ''; ?>" />
        <p><small><i><?php esc_html_e('0 means no depth limit.', 'categories-all-in-one') ?></i></small></p>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("hide_empty")); ?>"><?php esc_html_e('Hide empty', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("hide_empty")); ?>" name="<?php echo esc_attr($this->get_field_name("hide_empty")); ?>" type="checkbox" value="true" <?php if (array_key_exists('hide_empty', $instance) && !empty($instance['hide_empty'])): ?> checked <?php endif; ?> />
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("exclude")); ?>"><?php esc_html_e('Exclude categories', 'categories-all-in-one'); ?>:</label>
        <br />
        <div class="exclude">
          <?php if (is_array($categoriesForExclude) && !empty($categoriesForExclude)): ?>
            <ul class="categories-all-in-one-exclude-container categories-all-in-one-exclude-root categories-all-in-one-tree">
              <?php echo Categories_All_In_One_Utils::render_category_tree_for_exclude($categoriesForExclude, $instance['exclude'],  $this->get_field_name('exclude')) ?>
            </ul>
          <?php else: ?>
            <?php esc_html_e('No categories', 'categories-all-in-one'); ?>
          <?php endif; ?>
        </div>
        </select>
      </div>

      <div>
        <label><?php esc_html_e('Customize category order', 'categories-all-in-one'); ?>:</label>
        <br />
        <div class="categories-all-in-one-sortable">
          <?php if (is_array($categoriesForSortable) && !empty($categoriesForSortable)): ?>
            <ul class="categories-all-in-one-sortable-container categories-all-in-one-sortable-root">
              <?php echo Categories_All_In_One_Utils::render_category_tree_for_sortable($categoriesForSortable, $instance['exclude']) ?>
            </ul>
          <?php else: ?>
            <?php esc_html_e('No categories', 'categories-all-in-one'); ?>
          <?php endif; ?>
        </div>
        <textarea style="display:none;" class="categories-all-in-one-field-sortable" id="<?php echo esc_attr($this->get_field_id("sortable")); ?>" name="<?php echo esc_attr($this->get_field_name("sortable")); ?>"><?php echo array_key_exists('sortable', $instance) ? $instance["sortable"] : ''; ?></textarea>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("layout")); ?>"><?php esc_html_e('Layout', 'categories-all-in-one'); ?>:</label>
        <br />
        <select class="select categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("layout")); ?>" name="<?php echo esc_attr($this->get_field_name("layout")); ?>">
          <?php foreach (Categories_All_In_One_Utils::get_layouts() as $item): ?>
            <option value="<?php echo esc_attr($item['value']); ?>" <?php selected($instance['layout'], $item['value']); ?>><?php echo esc_html($item['label']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("columns")); ?>"><?php esc_html_e('Columns', 'categories-all-in-one'); ?>:</label>
        <br />
        <select class="select categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("columns")); ?>" name="<?php echo esc_attr($this->get_field_name("columns")); ?>">
          <?php foreach (Categories_All_In_One_Utils::get_columns() as $item): ?>
            <option value="<?php echo esc_attr($item['value']); ?>" <?php selected((int) $instance['columns'], (int) $item['value']); ?>><?php echo esc_html($item['label']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("list")); ?>"><?php esc_html_e('List type', 'categories-all-in-one'); ?>:</label>
        <br />
        <select class="select categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("list")); ?>" name="<?php echo esc_attr($this->get_field_name("list")); ?>">
          <?php
          $lists = Categories_All_In_One_Utils::get_lists();
          if (is_array($lists) && !empty($lists)) {
            foreach ($lists as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\" " . (esc_attr($instance["list"]) == esc_attr($item['value']) ? 'selected="selected"' : null) . ">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("show_count")); ?>"><?php esc_html_e('Show count', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("show_count")); ?>" name="<?php echo esc_attr($this->get_field_name("show_count")); ?>" type="checkbox" value="true" <?php if (array_key_exists('show_count', $instance) && !empty($instance['show_count'])): ?> checked <?php endif; ?> />
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("show_image")); ?>"><?php esc_html_e('Show category image', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("show_image")); ?>" name="<?php echo esc_attr($this->get_field_name("show_image")); ?>" type="checkbox" value="true" <?php if (array_key_exists('show_image', $instance) && !empty($instance['show_image'])): ?> checked <?php endif; ?> />
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("counter_brackets")); ?>"><?php esc_html_e('Counter\'s brackets', 'categories-all-in-one'); ?>:</label>
        <br />
        <select class="select categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("counter_brackets")); ?>" name="<?php echo esc_attr($this->get_field_name("counter_brackets")); ?>">
          <?php
          $brackets_list = Categories_All_In_One_Utils::get_counters_brackets();
          if (is_array($brackets_list) && !empty($brackets_list)) {
            foreach ($brackets_list as $item) {
              echo "<option value=\"" . esc_attr($item['value']) . "\" " . (esc_attr($instance["counter_brackets"]) == esc_attr($item['value']) ? 'selected="selected"' : null) . ">" . esc_html($item['label']) . "</option>";
            }
          }
          ?>
        </select>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("show_description")); ?>"><?php esc_html_e('Show description', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("show_description")); ?>" name="<?php echo esc_attr($this->get_field_name("show_description")); ?>" type="checkbox" value="true" <?php if (array_key_exists('show_description', $instance) && !empty($instance['show_description'])): ?> checked <?php endif; ?> />
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("description_length")); ?>"><?php esc_html_e('Description length in chars', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request categories-all-in-one-field-description_length" id="<?php echo esc_attr($this->get_field_id("description_length")); ?>" name="<?php echo esc_attr($this->get_field_name("description_length")); ?>" type="number" min="0" size="5" value="<?php echo array_key_exists('description_length', $instance) ? esc_attr($instance["description_length"]) : ''; ?>" />
        <p><small><i><?php esc_html_e('0 means no description limit.', 'categories-all-in-one') ?></i></small></p>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("description_link")); ?>"><?php esc_html_e('Description as link', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("description_link")); ?>" name="<?php echo esc_attr($this->get_field_name("description_link")); ?>" type="checkbox" value="true" <?php if (array_key_exists('description_link', $instance) && !empty($instance['description_link'])): ?> checked <?php endif; ?> />
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("separator")); ?>"><?php esc_html_e('Separator for each category', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("separator")); ?>" name="<?php echo esc_attr($this->get_field_name("separator")); ?>" type="text" size="5" value="<?php echo (array_key_exists('separator', $instance) && !empty(esc_attr($instance['separator']))) ? esc_attr($instance["separator"]) : ''; ?>" />
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("custom_class")); ?>"><?php esc_html_e('Custom CSS class(es) for each category', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("custom_class")); ?>" name="<?php echo esc_attr($this->get_field_name("custom_class")); ?>" type="text" size="5" value="<?php echo array_key_exists('custom_class', $instance) && !empty(esc_attr($instance['custom_class'])) ? esc_attr($instance["custom_class"]) : ''; ?>" />
        <p><small><i><?php esc_html_e('Separate multiple classes with spaces.', 'categories-all-in-one') ?></i></small></p>
      </div>

      <div>
        <label for="<?php echo esc_attr($this->get_field_id("block_custom_class")); ?>"><?php esc_html_e('Additional CSS class(es) for block', 'categories-all-in-one'); ?>:</label>
        <br />
        <input class="categories-all-in-one-field categories-all-in-one-field-request" id="<?php echo esc_attr($this->get_field_id("block_custom_class")); ?>" name="<?php echo esc_attr($this->get_field_name("block_custom_class")); ?>" type="text" size="5" value="<?php echo (array_key_exists('block_custom_class', $instance) && !empty(esc_attr($instance['block_custom_class']))) ? esc_attr($instance["block_custom_class"]) : ''; ?>" />
        <p><small><i><?php esc_html_e('Separate multiple classes with spaces.', 'categories-all-in-one') ?></i></small></p>
      </div>
    </div>

    <p class="categories-all-in-one-ad">
      <a href="<?php echo esc_url(Categories_All_In_One::PRODUCT_URL); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open the product page', 'categories-all-in-one'); ?></a>
    </p>
<?php
  }
}
add_action('widgets_init', function () {
  return register_widget('Categories_All_In_One_Widget');
});
?>
