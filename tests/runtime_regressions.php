<?php
/** Run with wp eval-file tests/runtime_regressions.php on a development site. */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) {
  exit(1);
}

function cci_categories_assert($condition, $message)
{
  if (!$condition) {
    throw new RuntimeException($message);
  }
}

function cci_categories_ids($terms)
{
  $ids = array();
  foreach ($terms as $term) {
    $ids[] = (int) $term->term_id;
    $ids = array_merge($ids, cci_categories_ids($term->children ?? array()));
  }
  return $ids;
}

// Independent reference: WordPress queries one level at a time.
function cci_categories_reference($args, $parent = 0, $depth = 0)
{
  if ($args['max_depth'] > 0 && $depth >= $args['max_depth']) {
    return array();
  }
  $terms = get_terms(array_merge($args, array('parent' => $parent)));
  cci_categories_assert(!is_wp_error($terms), 'Reference term query failed.');
  $result = array();
  foreach ($terms as $term) {
    if (in_array((int) $term->term_id, $args['exclude'], true)) {
      continue;
    }
    $node = clone $term;
    $node->children = cci_categories_reference($args, (int) $term->term_id, $depth + 1);
    $result[] = $node;
  }
  return $result;
}

$taxonomies = array('category');
if (taxonomy_exists('product_cat')) {
  $taxonomies[] = 'product_cat';
}
$comparisons = 0;
foreach ($taxonomies as $taxonomy) {
  $all = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
  cci_categories_assert(count($all) > 1, 'Run on a demo site with category hierarchies.');
  $parents = array(0, (int) $all[0]->term_id);
  foreach ($parents as $parent) {
    foreach (array(0, 1, 3) as $depth) {
      foreach (array(false, true) as $hide_empty) {
        foreach (array('ASC', 'DESC') as $order) {
          foreach (array(array(), array((int) $all[0]->term_id)) as $exclude) {
            $args = array('taxonomy' => array($taxonomy), 'parent_category' => $parent, 'max_depth' => $depth, 'hide_empty' => $hide_empty, 'orderby' => 'name', 'order' => $order, 'exclude' => $exclude);
            $actual = Categories_All_In_One_Generator::generate($args);
            $expected = cci_categories_reference($args, $parent);
            cci_categories_assert(cci_categories_ids($actual['data']) === cci_categories_ids($expected), 'Hierarchy mismatch: ' . wp_json_encode($args));
            ++$comparisons;
          }
        }
      }
    }
  }
  $calls = 0;
  $count_calls = function ($args) use (&$calls) { ++$calls; return $args; };
  add_filter('get_terms_args', $count_calls, 999);
  Categories_All_In_One_Utils::get_categories(array('taxonomy' => array($taxonomy), 'max_depth' => 0));
  remove_filter('get_terms_args', $count_calls, 999);
  cci_categories_assert(1 === $calls, 'Editor should reuse one term lookup.');
}

$products = get_posts(array('post_type' => 'product', 'fields' => 'ids', 'posts_per_page' => 1));
if ($products && taxonomy_exists('product_cat')) {
  $assigned = wp_get_object_terms($products[0], 'product_cat');
  $expected_ids = array();
  foreach ($assigned as $term) {
    $expected_ids[] = (int) $term->term_id;
    $expected_ids = array_merge($expected_ids, get_ancestors($term->term_id, 'product_cat', 'taxonomy'));
  }
  $actual = Categories_All_In_One_Generator::generate(array('post' => true, 'post_id' => $products[0], 'taxonomy' => array('product_cat'), 'max_depth' => 0));
  $actual_ids = cci_categories_ids($actual['data']);
  $expected_ids = array_values(array_unique($expected_ids));
  sort($actual_ids);
  sort($expected_ids);
  cci_categories_assert($actual_ids === $expected_ids, 'Current product includes unrelated categories.');
}
cci_categories_assert(array() === Categories_All_In_One_Generator::generate(array('post' => true, 'post_id' => PHP_INT_MAX))['data'], 'Missing content must not show all categories.');
Categories_All_In_One_Generator::generate(array('order' => array('asc'), 'taxonomy' => array(array('category')), 'exclude' => array(array(1)), 'custom_class' => array('x')));

$users = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
cci_categories_assert(!empty($users), 'No administrator available for REST tests.');
wp_set_current_user($users[0]);
$request = new WP_REST_Request('POST', '/categories-all-in-one/v1/categories');
$request->set_body_params(array('order' => array('asc')));
$response = rest_do_request($request);
cci_categories_assert(400 === $response->get_status(), 'Malformed order should return HTTP 400.');
$request->set_body_params(array('taxonomy' => array(array('category'))));
cci_categories_assert(400 === rest_do_request($request)->get_status(), 'Nested taxonomy should return HTTP 400.');
$request->set_body_params(array('order' => 'asc', 'hide_empty' => 'false'));
cci_categories_assert(200 === rest_do_request($request)->get_status(), 'Valid preview should succeed.');
wp_set_current_user(0);
cci_categories_assert(401 === rest_do_request($request)->get_status(), 'Anonymous preview must be denied.');

WP_CLI::success('Passed ' . $comparisons . ' hierarchy comparisons, term-lookup limits, current-content isolation, malformed attributes and REST access checks.');
