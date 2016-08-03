<?php

/*
Plugin Name: AMP 301
Description: Hooks into AMP and Simple 301 Redirects plugins to automatically redirect AMP articles
Version: 1.00
Author: Gamer Network
Author URI: http://www.gamer-network.net/
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

add_action('init', 'amp_301_init');
function amp_301_init()
{
	add_action( 'wp', 'amp_301_redirect' );
}

function amp_301_redirect()
{
	// Check that AMP and Simple 301 Redirects plugins are installed
	if (!is_plugin_active('simple-301-redirects/wp-simple-301-redirects.php') || !is_plugin_active('amp/amp.php')) {
		return false;
	}

	// Check that this is an AMP URL
	if (!is_amp_endpoint()) {
		return false;
	}

	$protocol = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
	$address = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$slug = rtrim(str_ireplace(get_option('home'), '', $address), '/');
	$parts = array_filter(explode('/', $slug));

	if (array_pop($parts) !== AMP_QUERY_VAR) {
		return false;
	}

	$redirects = get_option('301_redirects');

	if (empty($redirects)) {
		return false;
	}

	$from = array_keys($redirects);

	array_walk($from, function (&$item) {
		$item = '/' . trim($item, '/') . '/';
	});

	$redirects = array_combine($from, $redirects);

	$slug = '/' . implode('/', $parts) . '/';

	if (array_key_exists($slug, $redirects)) {
		$redirect = rtrim(home_url(), '/') . '/' . trim($redirects[$slug], '/') . '/' . AMP_QUERY_VAR . '/';
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		die();
	}

	return false;
}