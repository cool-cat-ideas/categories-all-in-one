<?php

/**
 * @package Categories All In One
 */

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Categories_All_In_One_Shortcode_Decode
{
  public static function initialize($atts, $content = null, $code = "")
  {
    $html = Categories_All_In_One_Generator::generate($atts, '');

    if (!empty($html) && array_key_exists('formattedHtmlData', $html)) {
      return $html['formattedHtmlData'];
    } else {
      return '';
    }
  }
}
add_shortcode("categories_all_in_one", array("Categories_All_In_One_Shortcode_Decode", "initialize"));
