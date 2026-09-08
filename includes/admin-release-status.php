<?php

if (!defined('ABSPATH')) {
	exit;
}

/** Read public release metadata once per settings-page request. */
function cci_categories_release_status()
{
	static $checked = false;
	static $result = null;
	if ($checked || !current_user_can('manage_options')) {
		return $result;
	}
	$checked = true;
	$response = wp_remote_get('https://api.github.com/repos/cool-cat-ideas/categories-all-in-one/releases/latest', array(
		'timeout' => 3,
		'redirection' => 0,
		'limit_response_size' => 131072,
		'headers' => array('Accept' => 'application/vnd.github+json'),
	));
	if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
		return null;
	}
	$data = json_decode(wp_remote_retrieve_body($response), true);
	if (!is_array($data) || !empty($data['draft']) || !empty($data['prerelease'])
		|| empty($data['tag_name']) || !preg_match('/^v?(\d+\.\d+\.\d+)$/', $data['tag_name'], $matches)) {
		return null;
	}
	$result = array(
		'latest' => $matches[1],
		'hasUpdate' => version_compare($matches[1], Categories_All_In_One::VERSION, '>'),
		'url' => Categories_All_In_One::GITHUB_URL . '/releases/tag/' . rawurlencode($data['tag_name']),
		'checkedAt' => wp_date(get_option('date_format') . ' ' . get_option('time_format')),
	);
	return $result;
}
