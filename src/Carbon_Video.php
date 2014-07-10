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
	 * Caching object
	 * @var Carbon_Video_Cache
	 */
	public $cache;

	/**
	 * Http-related object
	 * @var Carbon_Video_Http
	 */
	public $http;

	/**
	 * The ID of the video in the site that hosts it
	 * @var string
	 */
	protected $video_id;

	/**
	 * URL GET params. 
	 * @var array
	 */
	protected $params = array();

	/**
	 * The time that video should start playback at
	 * @var boolean|integer
	 */
	protected $start_time = false;

	/**
	 * Commonly used fragments in the parsing regular expressions
	 * @var array
	 */
	protected $regex_fragments = array(
		// Describe "http://" or "https://" or "//"
		"protocol" => '(?:https?:)?//',

		// Describe GET args list
		"args" => '(?:\?(?P<params>.+?))?',
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

		$video_providers = array("Youtube", "Vimeo");

		foreach ($video_providers as $video_provider) {
			$class_name = "Carbon_Video_$video_provider";

			if (call_user_func(array($class_name, 'test'), $video_code)) {
				$video = new $class_name();
				break;
			}
		}

		if (is_null($video)) {
			// No video provider recognized the video
			$video = new Carbon_Video_Broken();
		}

		$result = $video->parse($video_code);

		if (!$result) {
			// Couldn't parse the video code. 
			$video = new Carbon_Video_Broken();
		}

		return $video;
	}

	// Abstract methods implemented in each concrete class
	abstract public function parse($video_code);
	abstract public function get_link();
	abstract public function get_share_link();
	/**
	 * Return direct URL to the iframe embed(without the iframe tag HTML)
	 * @return string URL to youtube embed
	 */
	abstract public function get_embed_url();
	abstract public function get_embed_code($width=null, $height=null);
	abstract public function get_thumbnail();

	function __construct() {
		$this->cache = new Carbon_Video_Cache();
		$this->http = new Carbon_Video_Http();
	}

	public function is_broken() {
		return empty($this->video_id);
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

	function get_param($arg) {
		if (isset($this->params[$arg])) {
			return $this->params[$arg];
		}
		return null;
	}

	function set_param($arg, $val) {
		$this->params[$arg] = $val;
		return $this;
	}
	
	function get_id() {
		return $this->video_id;
	}

	function get_start_time() {
		return $this->start_time;
	}

	/**
	 * Set multiple parameters in one call
	 * @param array $params associative array where keys are param
	 *                      names and values are param values
	 */
	public function set_params($params) {
		foreach ($params as $param_name=>$param_val) {
			$this->set_param($param_name, $param_val);
		}
		return $this;
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