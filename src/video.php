<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Carbon_Video.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Carbon_VideoVimeo.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Carbon_VideoYoutube.php");

// Some dependencies from WordPress when the code doesn't run in WordPress envoirement
if (!class_exists('WP_Query')) {
	function get_transient($transient) {
		return false;
	}

	function set_transient($transient, $value, $expires) {

	}
	function wp_remote_get($url) {
		return array(
			'body'=>file_get_contents($url)
		);
	}
}


/**
 * Simple class that forwards requests to wp_remote_get.
 * Allows testing.
 */
class Carbon_Video_HTTP {
	function get($url) {
		$res = wp_remote_get($url);
		return $res['body'];
	}
}

/**
 * Simple cache object that forwards requests to 
 * WordPress transients. Allows testing
 */
class Carbon_Video_Cache {
	function set($name, $value, $expires) {
		return set_transient($name, $value, $expires);
	}
	function get($name) {
		return get_transient($name);
	}
	
}