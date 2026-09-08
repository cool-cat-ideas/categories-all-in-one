<?php

/**
 * @package Categories All In One
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Categories_All_In_One_Utils
{
  public static function allowed_html()
  {
    return array(
      'a' => array(
        'href' => true,
        'title' => true,
      ),
      'b' => array(),
      'i' => array(),
      'strong' => array(),
      'em' => array(),
      'p' => array(),
      'br' => array(),
      'div' => array(),
      'span' => array(),
      'ul' => array(),
      'ol' => array(),
      'li' => array(),
    );
  }

  public static function get_language($postId = null)
  {
    $locale = get_user_locale();
    $lang = $locale ? explode('_', $locale)[0] : 'en';

    if ($postId === null) {
      return $lang;
    }

    //WPML
    if (defined('ICL_SITEPRESS_VERSION')) {
      $language_details = apply_filters('wpml_post_language_details', null, $postId);
      $lang = $language_details['code'];
    }

    //Polylang
    if (function_exists('pll_get_post')) {
      $lang = pll_get_post_language($postId, 'slug');
    }

    return $lang;
  }

  public static function get_languages()
  {
    // WPML
    if (has_filter('wpml_active_languages')) {
      return apply_filters('wpml_active_languages', NULL, array('skip_missing' => 0));
    }

    // Polylang
    if (function_exists('pll_the_languages')) {
      return pll_the_languages(array('raw' => 1));
    }

    // TranslatePress
    if (function_exists('trp_get_available_languages')) {
      return trp_get_available_languages();
    }

    return array();
  }

  public static function get_taxonomies()
  {
    $taxonomies = array();
    $taxonomies_list = get_taxonomies(
      array(
        'public' => true,
        'show_ui' => true,
        'hierarchical' => true,
      ),
      'objects'
    );

    if (!empty($taxonomies_list)) {
      foreach ($taxonomies_list as $key => $taxonomy) {
        $object_type = !empty($taxonomy->object_type[0]) ? get_post_type_object($taxonomy->object_type[0]) : null;
        $prefix = $object_type ? $object_type->labels->singular_name . ' / ' : '';

        $taxonomies[] = array(
          'value' => sanitize_key($key),
          'label' => $prefix . $taxonomy->labels->singular_name,
        );
      }
    }

    return $taxonomies;
  }

  private static function sanitize_ids($data)
  {
    if (!empty($data)) {
      if (is_array($data) && count($data)) {
        return array_map('intval', $data);
      } else if (is_string($data)) {
        return array_map('intval', explode(",", $data));
      }
    }
    return [];
  }

  private static function prepare_args($atts = [])
  {
    $postId = null;
    if (isset($atts['post_id'])) {
      $postId = (int) $atts['post_id'];
      // $lang = self::get_language($postId);
    }

    if (!empty($atts['post']) && $postId) {
      $atts['include'] = wp_get_post_categories($postId, array('fields' => 'ids'));
    }

    $include = [];
    if (isset($atts['include'])) {
      $include = self::sanitize_ids($atts['include']);
    }
    $exclude = [];
    if (isset($atts['exclude'])) {
      $exclude = self::sanitize_ids($atts['exclude']);
    }

    $args = [
      'lang'       => !empty($atts['lang']) ? sanitize_text_field($atts['lang']) : 'en',
      'post'       => !empty($atts['post']) && $postId !== null ? $postId : '',
      'taxonomy'   => !empty($atts['taxonomy']) ? array_map('sanitize_key', (array) $atts['taxonomy']) : array('category'),
      'parent'     => !empty($atts['parent_category']) ? (int) $atts['parent_category'] : null,
      'orderby'    => !empty($atts['orderby']) ? sanitize_text_field($atts['orderby']) : 'name',
      'order'      => !empty($atts['order']) && in_array(strtoupper($atts['order']), ['ASC', 'DESC', 'RAND'], true) ? strtoupper($atts['order']) : 'ASC',
      'max_depth'  => isset($atts['max_depth']) ? (int) $atts['max_depth'] : 1,
      'hide_empty' => !empty($atts['hide_empty']) ? (bool) $atts['hide_empty'] : false,
      'exclude'    => $exclude,
      'include'    => $include,
      'list'       => !empty($atts['list']) ? sanitize_text_field($atts['list']) : '',
      'count'      => !empty($atts['show_count']) ? (bool) $atts['show_count'] : false,
      'counter_brackets' => !empty($atts['counter_brackets']) ? sanitize_text_field($atts['counter_brackets']) : '',
      'show_description' => !empty($atts['show_description']) ? (bool) $atts['show_description'] : false,
      'description_length' => !empty($atts['description_length']) ? (int) $atts['description_length'] : 0,
      'description_link' => !empty($atts['description_link']) ? (bool)$atts['description_link'] : false,
      'separator' => !empty($atts['separator']) ? sanitize_text_field($atts['separator']) : '',
      'custom_class' => !empty($atts['custom_class']) ? Categories_All_In_One_Generator::sanitize_css_classes($atts['custom_class']) : '',
      'block_custom_class' => !empty($atts['block_custom_class']) ? Categories_All_In_One_Generator::sanitize_css_classes($atts['block_custom_class']) : '',
    ];
    return $args;
  }

  private static function get_hierarchical_terms_for_select($categories, $depth = 0)
  {
    $result = array();

    foreach ($categories as $term) {
      $result[] = array(
        'value' => $term->term_id,
        'label' => str_repeat('-', $depth) . ' ' . $term->name . ' (' . $term->count . ')',
      );

      if ($term->children) {
        $result = array_merge($result, self::get_hierarchical_terms_for_select($term->children,  $depth + 1));
      }
    }

    return $result;
  }

  private static function get_hierarchical_terms($atts, $parent = 0, $depth = 0)
  {
    $result = array();
    $atts['parent'] = $parent;

    $terms = get_terms($atts);

    if (is_wp_error($terms)) {
      return $result;
    }

    $maxDepth = isset($atts['max_depth']) ? (int) $atts['max_depth'] : 0;

    if ($maxDepth > 0 && $depth >= $maxDepth) {
      return $result;
    }

    foreach ($terms as $term) {
      if (array_key_exists('exclude', $atts)) {
        $exclude = is_array($atts['exclude']) ? $atts['exclude'] : array_map('intval', explode(',', $atts['exclude']));
        if (count($exclude) > 0 && in_array($term->term_id, $exclude, true)) {
          continue;
        }
      }
      $child_atts = array_merge($atts, ['parent' => $term->term_id]);
      $children = self::get_hierarchical_terms($child_atts, $term->term_id, $depth + 1);

      if (! empty($children)) {
        $term->children = $children;
      }

      $result[] = $term;
    }

    return $result;
  }

  public static function get_categories($atts)
  {
    $firstElement = array(array('value' => '', 'label' => ''));

    $atts = wp_parse_args($atts, Categories_All_In_One_Generator::get_defaults());
    $args = self::prepare_args($atts);
    $tmp_args = $args;

    $parent = 0;
    $tmp_args['max_depth'] = 0;
    $categories = self::get_hierarchical_terms($tmp_args, $parent);
    $categoriesForSelect = self::get_hierarchical_terms_for_select($categories);


    if (!empty($atts['sortable'])) {
      $args['sortable'] = sanitize_text_field($atts['sortable']);
    }

    $args['parent_category'] = $args['parent'];
    $args['show_count'] = $args['count'];
    $data = Categories_All_In_One_Generator::generate($atts);
    $categoriesForSortable = array_key_exists('data', $data) ? $data['data'] : [];

    unset($args['exclude']);
    $categoriesForExclude = self::get_hierarchical_terms($args, $args['parent']);;
    return array_merge(
      array(
        'categories' => $categories,
        'categoriesForSelect' => array_merge($firstElement, $categoriesForSelect),
        'categoriesForExclude' => $categoriesForExclude,
        'categoriesForSortable' => $categoriesForSortable,
      ),
      $data
    );
  }

  public static function get_categoriesForFrontend($atts)
  {
    if (array_key_exists('parent_category', $atts) && $atts['parent_category'] !== '') {
      $atts['parent_category'] = (int) $atts['parent_category'];
    } else {
      $atts['parent_category'] = 0;
    }
    return self::get_hierarchical_terms($atts, $atts['parent_category'], 0);
  }

  public static function render_category_tree_for_exclude($categories, $selectedExclude, $fieldName)
  {
    $html = '';

    foreach ($categories as $category) {
      $isChecked =
        (is_array($selectedExclude) && array_key_exists($category->term_id, $selectedExclude)) ||
        ($category->term_id === $selectedExclude);

      $checkedAttr = $isChecked ? 'checked="checked"' : '';

      $html .= '<li>';
      $html .= '<div>';
      $html .= '<label>';
      $html .= sprintf(
        '<input type="checkbox" value="%1$d" class="checkbox categories-all-in-one-field categories-all-in-one-field-exclude categories-all-in-one-field-request" name="%2$s[%1$d]" %3$s>',
        (int) $category->term_id,
        esc_attr($fieldName),
        $checkedAttr
      );
      $html .= esc_html($category->name) . ' (' . (int) $category->count . ')';
      $html .= '</label>';
      $html .= '</div>';

      if (!empty($category->children) && is_array($category->children)) {
        $html .= '<ul class="categories-all-in-one-exclude-item-children-container">';
        $html .= self::render_category_tree_for_exclude($category->children, $selectedExclude, $fieldName);
        $html .= '</ul>';
      }
      $html .= '</li>';
    }

    return $html;
  }

  public static function render_category_tree_for_sortable($categoriesForSortable, $exclude)
  {
    $html = '';

    foreach ($categoriesForSortable as $category) {
      if (!empty($exclude) && in_array($category->term_id, $exclude)) {
        continue;
      }

      $html .= '<li data-term-id="' . (int) $category->term_id . '">';
      $html .= '<div class="categories-all-in-one-sortable-item">';
      $html .= '<svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" class="sortable-item-handle">
                  <circle cx="5" cy="6" r="2" />
                  <circle cx="5" cy="12" r="2" />
                  <circle cx="5" cy="18" r="2" />
                  <circle cx="13" cy="6" r="2" />
                  <circle cx="13" cy="12" r="2" />
                  <circle cx="13" cy="18" r="2" />
                </svg>';
      $html .= esc_html($category->name) . ' (' . (int) $category->count . ')';

      if (!empty($category->children) && is_array($category->children)) {
        $html .= '<ul class="categories-all-in-one-sortable-item-children-container">';
        $html .= self::render_category_tree_for_sortable($category->children, $exclude);
        $html .= '</ul>';
      }

      $html .= '</div>';
      $html .= '</li>';
    }

    return $html;
  }

  public static function get_lists()
  {
    return [
      ['value' => 'bullet', 'label' => esc_html__('Bullet list', 'categories-all-in-one')],
      ['value' => 'numbered', 'label' => esc_html__('Numbered list', 'categories-all-in-one')],
      ['value' => '', 'label' => esc_html__('Without list', 'categories-all-in-one')],
    ];
  }

  public static function get_layouts()
  {
    return [
      ['value' => 'list', 'label' => esc_html__('List', 'categories-all-in-one')],
      ['value' => 'columns', 'label' => esc_html__('Columns', 'categories-all-in-one')],
      ['value' => 'cards', 'label' => esc_html__('Cards', 'categories-all-in-one')],
    ];
  }

  public static function get_columns()
  {
    return [
      ['value' => 1, 'label' => esc_html__('One column', 'categories-all-in-one')],
      ['value' => 2, 'label' => esc_html__('Two columns', 'categories-all-in-one')],
      ['value' => 3, 'label' => esc_html__('Three columns', 'categories-all-in-one')],
      ['value' => 4, 'label' => esc_html__('Four columns', 'categories-all-in-one')],
    ];
  }

  public static function get_orders_by()
  {
    return [
      ['value' => 'name', 'label' => esc_html__('Name', 'categories-all-in-one')],
      ['value' => 'count', 'label' => esc_html__('Count', 'categories-all-in-one')],
    ];
  }

  public static function get_orders()
  {
    return [
      ['value' => 'asc', 'label' => esc_html__('Ascending', 'categories-all-in-one')],
      ['value' => 'desc', 'label' => esc_html__('Descending', 'categories-all-in-one')],
      ['value' => 'rand', 'label' => esc_html__('Random', 'categories-all-in-one')],
    ];
  }

  public static function get_counters_brackets()
  {
    return [
      ['value' => 'round', 'label' => __('Round ()', 'categories-all-in-one')],
      ['value' => 'curly', 'label' => __('Curly {}', 'categories-all-in-one')],
      ['value' => 'square', 'label' => __('Square []', 'categories-all-in-one')],
      ['value' => 'angle', 'label' => __('Angle <>', 'categories-all-in-one')],
      ['value' => '', 'label' => __('Without brackets', 'categories-all-in-one')],
    ];
  }
}
