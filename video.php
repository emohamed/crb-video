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

class Carbon_VideoVimeo extends Carbon_Video {
	protected $default_width  = '500';
	protected $default_height = '281';

	function __construct() {
		$this->regex_fragments = array_merge($this->regex_fragments, array(
			'video_id'=>'(?P<video_id>\d+)'
		));
	}

	function parse($video_code) {
		$regexes = array(
			// Matches:
			//  - http://vimeo.com/2526536
			//  - http://vimeo.com/channels/staffpicks/98861259
			//  - http://vimeo.com/2526536#t=15s
			//  - http://vimeo.com/2526536#t=195s
			"url_regex" =>
				'~^' . 
					$this->regex_fragments['protocol'] . 
					'vimeo\.com/.*?/?' . 
					$this->regex_fragments['video_id'] .
					'(?:#t=(?P<start>\d+)s)?' .
				'$~i', 

			// Matches iframe based embed code
			"embed_code_regex" =>
				'~^' . 
					'<iframe.*?src=[\'"]' . 
					$this->regex_fragments['protocol'] . 
					'player\.vimeo\.com/video/' . 
					$this->regex_fragments['video_id'] . 
					$this->regex_fragments['args'] . 
				'[\'"]~i',

			// Matches old flash based embed code generated from vimeo
			"old_embed_code_regex" =>
				'~'.
					'<object.*?' .
					$this->regex_fragments['protocol'] . 
					'vimeo\.com/moogaloop\.swf' . 
					$this->regex_fragments['args'] .
				'[\'"]~i'
		);
		$video_input_type = false;
		foreach ($regexes as $regex_type => $regex) {
			if (preg_match($regex, $video_code, $matches)) {
				$video_input_type = $regex_type;

				// The video ID is in GET arguments when old embed code is used.
				if (isset($matches['video_id'])) {
					$this->video_id = $matches['video_id'];
				}

				// Start in vimeo is in the hash rather than in GET argument, so
				// it's handled differently from youtube's start argument. 
				if (!empty($matches['start'])) {
					$this->start_time = $matches['start'];
				}

				if (isset($matches['arguments'])) {
					// & in the URLs is encoded as &amp;, so fix that before parsing
					$args = htmlspecialchars_decode($matches['arguments']);
					parse_str($args, $arguments);

					if (isset($arguments['clip_id'])) {
						$this->video_id = $arguments['clip_id'];

						unset($matches['clip_id']);
					}

					// These arguments are presented in the old flash embed code, but
					// aren't used in HTTP
					$flash_specific_args = array(
						'force_embed', 'server', 'fullscreen'
					);

					// Some elements have slightly different names in the flash and HTML
					// embed code
					$flash_to_html5_args_map = array(
						'show_title' => 'title',
						'show_byline' => 'byline',
						'show_portrait' => 'portrait',
					);

					foreach ($arguments as $arg_name => $arg_val) {
						if (in_array($arg_name, $flash_specific_args)) {
							// Don't care about those ... 
							continue; 
						}

						if (isset($flash_to_html5_args_map[$arg_name])) {
							// save the HTML argument name rather
							// than flash's argument name
							$arg_name = $flash_to_html5_args_map[$arg_name];
						}

						$this->set_argument($arg_name, $arg_val);
					}
				}

				break;
			}
		}

		// For embed codes, width and height should be extracted
		$is_embed_code = in_array($video_input_type, array(
			'embed_code_regex',
			'old_embed_code_regex'
		));

		if ($is_embed_code) {
			if (preg_match_all('~(?P<dimension>width|height)=[\'"](?P<val>\d+)[\'"]~', $video_code, $matches)) {
				$this->dimensions = array_combine(
					$matches['dimension'],
					$matches['val']
				);
			}
		}

		if (empty($this->video_id)) {
			return false;
		}
		return true;
	}
	function get_thumbnail() {
		// TBD -- requires caching
	}
	
	function get_share_link() {
		return $this->get_link();
	}

	function get_link() {
		$url = "//vimeo.com/" . $this->video_id;
		if (isset($this->start_time)) {
			$url .= "#" . $this->start_time . "s";
		}
		return $url;
	}

	function get_embed_code($width=null, $height=null) {
		$width = $this->get_embed_width($width);
		$height = $this->get_embed_height($height);

		$url = '//player.vimeo.com/video/' . $this->video_id;

		if (!empty($this->arguments)) {
			$url .= '?' . htmlspecialchars(http_build_query($this->arguments));
		}
		
		return '<iframe src="' . $url . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	}
}
class Carbon_VideoYoutube extends Carbon_Video {
	protected $default_width = '640';
	protected $default_height = '360';

	/**
	 * The default domain name for youtube videos
	 */
	const DEFAULT_DOMAIN = 'www.youtube.com';

	/**
	 * The original domain name of the video: either youtube.com or youtube-nocookies.com
	 * @var string
	 */
	public $domain = self::DEFAULT_DOMAIN;

	function __construct() {
		$this->regex_fragments = array_merge($this->regex_fragments, array(
			// Desribe youtube video ID 
			"video_id" => '(?P<video_id>[\w\-]+)',
		));
		parent::__construct();
	}

	/**
	 * Constructs new object from various video inputs. 
	 */
	function parse($video_code) {
		$regexes = array(
			// Something like: https://www.youtube.com/watch?v=lsSC2vx7zFQ
			"url_regex" =>
				'~^' . 
					$this->regex_fragments['protocol'] . 
					'(?P<domain>(?:www\.)?youtube\.com)/watch\?v=' . 
					$this->regex_fragments['video_id'] .
				'~i', 

			// Something like "http://youtu.be/lsSC2vx7zFQ" or "http://youtu.be/6jCNXASjzMY?t=3m11s"
			"share_url_regex" =>
				'~^' .
					$this->regex_fragments['protocol'] . 
					'youtu\.be/' .
					$this->regex_fragments['video_id'] .
					$this->regex_fragments['args'] .
				'$~i',

			// Youtube embed iframe code: 
			// <iframe width="560" height="315" src="//www.youtube.com/embed/LlhfzIQo-L8?rel=0" frameborder="0" allowfullscreen></iframe>
			"embed_code_regex" =>
				'~^'.
					'<iframe.*?src=[\'"]' .
					$this->regex_fragments['protocol'] . 
					'(?P<domain>(www\.)?youtube(?:-nocookie)?\.com)/embed/' . 
					$this->regex_fragments['video_id'] .
					$this->regex_fragments['args'] .
				'[\'"]~i',

			// Youtube old embed flash code:
			// <object width="234" height="132"><param name="movie" ....
			// .. type="application/x-shockwave-flash" width="234" heighGt="132" allowscriptaccess="always" allowfullscreen="true"></embed></object>
			"old_embed_code_regex" =>
				'~^'.
					'<object.*?' .
					$this->regex_fragments['protocol'] . 
					'(?P<domain>(www\.)?youtube(?:-nocookie)?\.com)/v/' . 
					$this->regex_fragments['video_id'] .
					$this->regex_fragments['args'] .
				'[\'"]~i'
		);

		$args = array();
		$video_input_type = null;

		foreach ($regexes as $regex_type => $regex) {
			if (preg_match($regex, $video_code, $matches)) {
				$video_input_type = $regex_type;
				$this->video_id = $matches['video_id'];

				if (isset($matches['arguments'])) {
					// & in the URLs is encoded as &amp;, so fix that before parsing
					$args = htmlspecialchars_decode($matches['arguments']);
					parse_str($args, $arguments);

					if ($video_input_type === 'old_embed_code_regex') {
						// Those are legacy arguments for the flash player
						unset($arguments['hl'], $arguments['version']);
					}

					foreach ($arguments as $arg_name => $arg_val) {
						$this->set_argument($arg_name, $arg_val);
					}
				}

				if (isset($matches['domain'])) {
					$this->domain = $matches['domain'];
				}

				// Stop after the first match
				break;
			}
		}

		// For embed codes, width and height should be extracted
		$is_embed_code = in_array($video_input_type, array(
			'embed_code_regex',
			'old_embed_code_regex'
		));

		if ($is_embed_code) {
			if (preg_match_all('~(?P<dimension>width|height)=[\'"](?P<val>\d+)[\'"]~', $video_code, $matches)) {
				$this->dimensions = array_combine(
					$matches['dimension'],
					$matches['val']
				);
			}
		}

		if (!isset($this->video_id)) {
			return false;
		}
		return true;
	}
	/**
	 * Override set_argument in order to catch a special `t` and `start` arguments in youtube:
	 *   - `t` argument is optional for share shortened links and is in format "3m2s" -- that is 
	 *     start playback 3 minutes and 2 seconds
	 *   - `start` is the same thing, but is used as embed code arguments
	 */
	function set_argument($arg, $val) {
		// "t" argument is special case since it's the only one in the share links
		// and it's translated differently to embed code arguments
		// (see https://developers.google.com/youtube/player_parameters#start)
		if ($arg === 't') {
			$this->start_time = $val;
			
			$arg = 'start';
			$val = $this->calc_time_in_seconds($val);

		} else if ($arg === 'start') {
			$this->start_time = $this->calc_shortlink_time($val);
		}

		parent::set_argument($arg, $val);
	}
	/**
	 * Returns share link for the video, e.g. http://youtu.be/6jCNXASjzMY?t=1s
	 */
	function get_share_link() {
		$url = '//youtu.be/' . $this->video_id;
		$time = $this->get_argument('t');

		if ($this->start_time) {
			$url .= '?t=' . $this->start_time;
		}

		return $url;
	}

	function get_link() {
		$url = '//' . self::DEFAULT_DOMAIN . '/watch?v=' . $this->video_id;
		$time = $this->get_argument('t');

		if ($this->start_time) {
			$url .= '?t=' . $this->start_time;
		}

		return $url;
	}

	/**
	 * Returns iframe-based embed code.
	 */
	function get_embed_code($width=null, $height=null) {
		$width = $this->get_embed_width($width);
		$height = $this->get_embed_height($height);

		$url = '//' . $this->domain . '/embed/' . $this->video_id;

		if (!empty($this->arguments)) {
			$url .= '?' . htmlspecialchars(http_build_query($this->arguments));
		}
		
		return '<iframe width="' . $width . '" height="' . $height . '" src="' . $url . '" frameborder="0" allowfullscreen></iframe>';
	}

	/**
	 * Returns image for the video
	 **/
	function get_image() {
		return '//img.youtube.com/vi/' . $this->video_id . '/0.jpg';
	}

	/**
	 * Returns thumbnail for the video
	 **/
	function get_thumbnail() {
		return '//img.youtube.com/vi/' . $this->video_id . '/default.jpg';
	}

	/**
	 * Calculates how many seconds are there in the string in format "3m2s". 
	 * @return int seconds
	 */
	private function calc_time_in_seconds($time) {
		if (preg_match('~(?:(?P<minutes>\d+)m)?(?:(?P<seconds>\d+)s)?~', $time, $matches)) {
			return $matches['minutes'] * 60 + $matches['seconds'];
		}
		// Doesn't match the format...
		return null;
	}

	/**
	 * Transforms seconds to string like "3m2s"
	 * @return int seconds
	 */
	private function calc_shortlink_time($seconds) {
		$result = '';
		if ($seconds > 60) {
			$result .= floor($seconds / 60) . "m";
		}
		return $result . ($seconds % 60) . "s";
	}

}
