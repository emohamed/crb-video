<?php
abstract class Carbon_Video {
	/**
	 * Width and height container
	 * @var array
	 */
	protected $dimensions = array(
		'width'=>null,
		'height'=>null
	);

	/**
	 * The ID of the video in the site that hosts it
	 * @var string
	 */
	protected $video_id;

	/**
	 * URL GET arguments. 
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * The time that video should start
	 * @var boolean|integer
	 */
	protected $start_time = false;

	/**
	 * Commonly used fragments in the regular expressions that parse
	 * @var array
	 */
	protected $regex_fragments = array(
		// Describe "http://" or "https://" or "//"
		"protocol" => '(?:https?:)?//',

		// Describe GET args list
		"args" => '(?:\?(?P<arguments>.+?))?',
	);

	/**
	 * Parses embed code, url, or video ID and creates new object based on it. 
	 * 
	 * @param string $video embed code, url, or video ID
	 * @return object Carbon_Video
	 **/
	static function create($video_code) {
		$video_code = trim($video_code);

		$video = null;

		// Try to catch youtube.com, youtu.be, youtube-nocookie.com
		if (preg_match('~(https?:)?//(www\.)?(youtube(-nocookie)?\.com|youtu\.be)~i', $video_code)) {
			$video = new Carbon_VideoYoutube();
		} elseif (preg_match('~(https?:)?//[\w.]*vimeo\.com~i', $video_code)) { 
			$video = new Carbon_VideoVimeo();
		} else {
			return false;
		}

		$result = $video->parse($video_code);
		if (!$result) {
			// Couldn't parse the video code. 
			return false;
		}

		return $video;
	}

	abstract public function parse($video_code);
	abstract public function get_thumbnail();
	abstract public function get_share_link();
	abstract public function get_link();
	abstract public function get_embed_code($width=null, $height=null);

	function __construct() {

	}

	public function get_width() {
		return $this->dimensions['width'];
	}

	public function set_width($new_width) {
		$this->dimensions['width'] = $new_width;

		return $this;
	}

	public function get_height() {
		return $this->dimensions['height'];
	}

	public function set_height($new_height) {
		$this->dimensions['height'] = $new_height;
		
		return $this;
	}

	function get_argument($arg) {
		if (isset($this->arguments[$arg])) {
			return $this->arguments[$arg];
		}
		return null;
	}

	function set_argument($arg, $val) {
		$this->arguments[$arg] = $val;
		return $this;
	}
	
	function get_id() {
		return $this->video_id;
	}

	function get_start_time() {
		return $this->start_time;
	}

	// If width and height are not provided in the function parameters,
	// get them from the initial video code; if the object wasn't constructed
	// from an embed code(and doesn't have initial width and height), use 
	// the default, hard-coded dimensions. 

	protected function get_embed_width($user_supplied_width) {
		if (!is_null($user_supplied_width)) {
			return $user_supplied_width;
		}
		if (!empty($this->dimensions['width'])) {
			return $this->dimensions['width'];
		}
		return $this->default_width;
	}

	function get_embed_height($user_supplied_height) {
		if (!is_null($user_supplied_height)) {
			return $user_supplied_height;
		}
		if (!empty($this->dimensions['height'])) {
			return $this->dimensions['height'];
		}
		return $this->default_height;
	}
}

class Carbon_Video_Exception extends Exception {}