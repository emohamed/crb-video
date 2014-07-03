<?php
if (!class_exists('WP_Query')) {
	// Some dependencies from WordPress when the code doesn't run in WordPress envoirement

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
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Carbon_Video.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Carbon_VideoVimeo.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Carbon_VideoYoutube.php");