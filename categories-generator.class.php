<?php

/**
 * Builds category output for the block, shortcode and widget.
 *
 * @package Categories All In One
 */

if (!defined('ABSPATH')) {
  exit;
}

class Categories_All_In_One_Generator
{
  public static function get_defaults()
  {
    return array(
      'lang' => '',
      'layout' => 'list',
      'columns' => 3,
      'list' => 'bullet',
      'separator' => '',
      'orderby' => 'name',
      'order' => 'asc',
      'taxonomy' => array('category'),
      'post' => false,
      'post_id' => 0,
      'parent_category' => '',
      'hide_empty' => false,
      'show_count' => false,
      'show_image' => false,
      'show_description' => false,
      'description_length' => 0,
      'description_link' => false,
      'counter_brackets' => 'round',
      'custom_class' => '',
      'max_depth' => 1,
      'exclude' => array(),
      'sortable' => '',
      'block_custom_class' => '',
    );
  }

  public static function sanitize_css_classes($classes)
  {
    $tokens = preg_split('/\s+/', (string) $classes, -1, PREG_SPLIT_NO_EMPTY);
    return implode(' ', array_filter(array_map('sanitize_html_class', $tokens)));
  }

  public static function sanitize_attributes($atts)
  {
    $atts = wp_parse_args(is_array($atts) ? $atts : array(), self::get_defaults());
    $boolean_keys = array('post', 'hide_empty', 'show_count', 'show_image', 'show_description', 'description_link');
    foreach ($boolean_keys as $key) {
      $atts[$key] = filter_var($atts[$key], FILTER_VALIDATE_BOOLEAN);
    }

    $atts['layout'] = in_array($atts['layout'], array('list', 'columns', 'cards'), true) ? $atts['layout'] : 'list';
    $atts['columns'] = max(1, min(4, absint($atts['columns'])));
    $atts['list'] = in_array($atts['list'], array('bullet', 'numbered', ''), true) ? $atts['list'] : 'bullet';
    $atts['orderby'] = in_array($atts['orderby'], array('name', 'count'), true) ? $atts['orderby'] : 'name';
    $atts['order'] = in_array(strtolower($atts['order']), array('asc', 'desc', 'rand'), true) ? strtolower($atts['order']) : 'asc';
    $atts['counter_brackets'] = in_array($atts['counter_brackets'], array('round', 'curly', 'square', 'angle', ''), true) ? $atts['counter_brackets'] : 'round';
    $taxonomies = is_array($atts['taxonomy']) ? array_values($atts['taxonomy']) : explode(',', (string) $atts['taxonomy']);
    $atts['taxonomy'] = array_values(array_filter(array_map('sanitize_key', $taxonomies), function ($taxonomy) {
      $object = get_taxonomy($taxonomy);
      return $object && $object->public && $object->hierarchical;
    }));
    $atts['taxonomy'] = $atts['taxonomy'] ?: array('category');
    $atts['parent_category'] = '' === $atts['parent_category'] ? '' : absint($atts['parent_category']);
    $atts['post_id'] = absint($atts['post_id']);
    $atts['max_depth'] = max(0, absint($atts['max_depth']));
    $atts['description_length'] = max(0, absint($atts['description_length']));
    $atts['exclude'] = array_values(array_filter(array_map('absint', is_array($atts['exclude']) ? $atts['exclude'] : explode(',', (string) $atts['exclude']))));
    $atts['separator'] = sanitize_text_field($atts['separator']);
    $atts['lang'] = sanitize_text_field($atts['lang']);
    $atts['custom_class'] = self::sanitize_css_classes($atts['custom_class']);
    $atts['block_custom_class'] = self::sanitize_css_classes($atts['block_custom_class']);

    if (!empty($atts['sortable'])) {
      $decoded = base64_decode((string) $atts['sortable'], true);
      $atts['sortable'] = $decoded && is_array(json_decode($decoded, true)) ? (string) $atts['sortable'] : '';
    }

    return $atts;
  }

  private static function counter($count, $brackets)
  {
    $count = absint($count);
    $formats = array('round' => '(%d)', 'curly' => '{%d}', 'square' => '[%d]', 'angle' => '<%d>', '' => '%d');
    return sprintf($formats[$brackets] ?? $formats['round'], $count);
  }

  private static function category_image($category, $params, $level, $link)
  {
    if (!$params['show_image']) {
      return '';
    }

    $image_id = absint(get_term_meta($category->term_id, 'thumbnail_id', true));
    $image_id = absint(apply_filters('categories_all_in_one_category_image_id', $image_id, $category, $params, $level));
    if (!$image_id) {
      return '';
    }

    $size = apply_filters('categories_all_in_one_category_image_size', 'medium_large', $category, $params, $level);
    $image = wp_get_attachment_image(
      $image_id,
      $size,
      false,
      array(
        'class' => 'cci-categories-all-in-one-image',
        'loading' => 'lazy',
      )
    );
    $image = apply_filters('categories_all_in_one_category_image_html', $image, $category, $params, $level);

    if (!is_string($image) || '' === trim($image)) {
      return '';
    }

    $link_label = sprintf(__('Open %s category', 'categories-all-in-one'), $category->name);

    return '<a class="cci-categories-all-in-one-image-link" href="' . esc_url($link) . '" aria-label="' . esc_attr($link_label) . '">' . wp_kses_post($image) . '</a>';
  }

  private static function render_category_list($categories, $params, $level = 0, $styles = '')
  {
    $tag = 'bullet' === $params['list'] ? 'ul' : ('numbered' === $params['list'] ? 'ol' : 'div');
    $row_tag = 'div' === $tag ? 'div' : 'li';
    $html = '<' . $tag . ' class="categories-all-in-one-list cci-categories-all-in-one-list">';

    foreach ($categories as $key => $category) {
      $link = get_term_link($category);
      if (is_wp_error($link)) {
        continue;
      }

      $classes = array('cat-item', 'cat-item-' . absint($category->term_id), 'cci-categories-all-in-one-item');
      if ($params['custom_class']) {
        $classes = array_merge($classes, explode(' ', $params['custom_class']));
      }
      $classes = apply_filters('categories_all_in_one_item_classes', $classes, $category, $params, $level);
      $style = true === $styles ? ' style="margin-left:' . absint($level * 20) . 'px"' : '';
      $html .= '<' . $row_tag . ' class="' . esc_attr(implode(' ', array_map('sanitize_html_class', $classes))) . '"' . $style . '>';
      $html .= self::category_image($category, $params, $level, $link);
      $html .= '<div class="cat-details cci-categories-all-in-one-details">';
      $html .= '<a href="' . esc_url($link) . '" title="' . esc_attr(sprintf(__('View posts in category %s', 'categories-all-in-one'), $category->name)) . '">' . esc_html($category->name) . '</a>';
      if ($params['show_count']) {
        $html .= '<span class="cat-counter cci-categories-all-in-one-counter">' . esc_html(self::counter($category->count, $params['counter_brackets'])) . '</span>';
      }
      $html .= '</div>';

      if ($params['show_description'] && '' !== trim(wp_strip_all_tags($category->description))) {
        $description = $params['description_length'] ? wp_html_excerpt($category->description, $params['description_length'], '…') : $category->description;
        $description = wp_kses_post($description);
        $html .= '<div class="cat-description cci-categories-all-in-one-description">';
        $html .= $params['description_link'] ? '<a href="' . esc_url($link) . '">' . $description . '</a>' : $description;
        $html .= '</div>';
      }

      if (!empty($category->children)) {
        $html .= self::render_category_list($category->children, $params, $level + 1, $styles);
      }
      if ($params['separator'] && $key < count($categories) - 1) {
        $html .= '<span class="cci-categories-all-in-one-separator">' . esc_html($params['separator']) . '</span>';
      }
      $html .= '</' . $row_tag . '>';
    }

    return $html . '</' . $tag . '>';
  }

  public static function sort_categories_manually($categories, $sortable)
  {
    $sorted = array();
    $index = array();
    foreach ($categories as $category) {
      $index[$category->term_id] = $category;
    }
    $used_ids = array();
    foreach ((array) $sortable as $item) {
      $term_id = isset($item['term_id']) ? absint($item['term_id']) : 0;
      if (!$term_id || !isset($index[$term_id])) {
        continue;
      }
      $matched = $index[$term_id];
      $used_ids[] = $term_id;
      if (!empty($item['children']) && !empty($matched->children)) {
        $matched->children = self::sort_categories_manually($matched->children, $item['children']);
      }
      $sorted[] = $matched;
    }
    foreach ($categories as $category) {
      if (!in_array($category->term_id, $used_ids, true)) {
        $sorted[] = $category;
      }
    }
    return $sorted;
  }

  public static function generate($atts, $styles = '')
  {
    $output = array('data' => array(), 'formattedHtmlData' => '');
    $params = self::sanitize_attributes($atts);
    $params['order'] = strtoupper($params['order']);

    if ($params['post']) {
      $post_id = $params['post_id'] ?: get_the_ID();
      if ($post_id) {
        $params['include'] = wp_get_post_categories($post_id, array('fields' => 'ids'));
      }
    }

    $query_params = apply_filters('categories_all_in_one_query_args', $params, $atts);
    $categories = Categories_All_In_One_Utils::get_categoriesForFrontend($query_params);
    if ('RAND' === $params['order']) {
      shuffle($categories);
    }
    $categories = apply_filters('categories_all_in_one_terms', $categories, $params);
    if (empty($categories) || is_wp_error($categories)) {
      return $output;
    }

    if ($params['sortable']) {
      $categories = self::sort_categories_manually($categories, json_decode(base64_decode($params['sortable']), true));
    }
    $output['data'] = $categories;
    $root_classes = array(
      'categories-all-in-one',
      'cci-categories-all-in-one',
      'cci-categories-all-in-one-layout-' . $params['layout'],
      'cci-categories-all-in-one-columns-' . $params['columns'],
    );
    if ($params['block_custom_class']) {
      $root_classes = array_merge($root_classes, explode(' ', $params['block_custom_class']));
    }
    $html = '<div class="' . esc_attr(implode(' ', $root_classes)) . '">';
    $html .= self::render_category_list($categories, $params, 0, $styles);
    $html .= '</div>';
    $output['formattedHtmlData'] = apply_filters('categories_all_in_one_output_html', $html, $categories, $params);
    return $output;
  }

  public static function prepareSettings($atts)
  {
    return self::sanitize_attributes($atts);
  }
}
