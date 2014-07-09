<?php
/**
 * Dummy class for broken videos.
 */
class Carbon_Video_Broken extends Carbon_Video {
	public function parse($video_code) {
		return true;
	}
	public function get_link() {
		return false;
	}
	public function get_share_link() {
		return false;
	}
	public function get_embed_url() {
		return false;
	}
	public function get_embed_code($width=null, $height=null) {
		return false;
	}
	public function get_thumbnail() {
		return false;
	}
}