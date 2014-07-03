<?php
abstract class Carbon_Video {
	const DEFAULT_WIDTH = 640;
	const DEFAULT_HEIGHT = 480;

	protected $dimensions = array(
		'width'=>null,
		'height'=>null
	);

	protected $video_id;

	protected $arguments = array();

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
		} elseif (preg_match('~(https?:)?//(www.|)(vimeo\.com)~i', $video_code)) { 
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

	abstract function parse($video_code);
	abstract function get_thumbnail();
	abstract function get_share_link();
	abstract function get_link();
	abstract function get_embed_code($width=null, $height=null);
	abstract function get_flash_embed_code($width=null, $height=null);

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
		return self::DEFAULT_WIDTH;
	}

	function get_embed_height($user_supplied_height) {
		if (!is_null($user_supplied_height)) {
			return $user_supplied_height;
		}
		if (!empty($this->dimensions['height'])) {
			return $this->dimensions['height'];
		}
		return self::DEFAULT_HEIGHT;
	}
}

class Carbon_Video_Exception extends Exception {}

class Carbon_VideoYoutube extends Carbon_Video {
	/**
	 * The default domain name for youtube videos
	 */
	const DEFAULT_DOMAIN = 'www.youtube.com';

	/**
	 * The original domain name of the video: either youtube.com or youtube-nocookies.com
	 * @var string
	 */
	public $domain = self::DEFAULT_DOMAIN;

	/**
	 * The time that video should start
	 * @var boolean|integer
	 */
	protected $shortlink_start = false;

	/**
	 * Constructs new object from various video inputs. 
	 */
	function __construct() {
		
	}

	function parse($video) {
		// Describe "http://" or "https://" or "//"
		$protocol_regex_fragment = '(?:https?:)?//';

		// Desribe youtube video ID 
		$video_id_regex_fragment = '(?P<video_id>[\w\-]*)';

		// Desribe youtube video arguments(e.g. rel, time, etc.)
		$args_regex_fragment = '(?:\?(?P<arguments>.+?))?';

		$regexes = array(
			// Something like: https://www.youtube.com/watch?v=lsSC2vx7zFQ
			"url_regex" =>
				'~^' . 
					$protocol_regex_fragment . 
					'(?P<domain>(?:www\.)?youtube\.com)/watch\?v=' . 
					$video_id_regex_fragment .
				'~i', 

			// Something like "http://youtu.be/lsSC2vx7zFQ" or "http://youtu.be/6jCNXASjzMY?t=3m11s"
			"share_url_regex" =>
				'~^' .
					$protocol_regex_fragment .
					'youtu\.be/' .
					$video_id_regex_fragment .
					$args_regex_fragment . 
				'$~i',

			// Youtube embed iframe code: 
			// <iframe width="560" height="315" src="//www.youtube.com/embed/LlhfzIQo-L8?rel=0" frameborder="0" allowfullscreen></iframe>
			"embed_code_regex" =>
				'~^'.
					'<iframe.*?src=[\'"]' .
					$protocol_regex_fragment .
					'(?P<domain>(www\.)?youtube(?:-nocookie)?\.com)/embed/' . 
					$video_id_regex_fragment .
					$args_regex_fragment .
				'[\'"]~i',

			// Youtube old embed flash code:
			// <object width="234" height="132"><param name="movie" ....
			// .. type="application/x-shockwave-flash" width="234" height="132" allowscriptaccess="always" allowfullscreen="true"></embed></object>
			"old_embed_code_regex" =>
				'~^'.
					'<object.*?' .
					$protocol_regex_fragment .
					'(?P<domain>(www\.)?youtube(?:-nocookie)?\.com)/v/' . 
					$video_id_regex_fragment .
					$args_regex_fragment . 
				'[\'"]~i'
		);

		$args = array();
		$video_input_type = null;

		foreach ($regexes as $regex_type => $regex) {
			if (preg_match($regex, $video, $matches)) {
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
			if (preg_match_all('~(?P<dimension>width|height)=[\'"](?P<val>\d+)[\'"]~', $video, $matches)) {
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
			$this->shortlink_start = $val;
			
			$arg = 'start';
			$val = $this->calc_time_in_seconds($val);

		} else if ($arg === 'start') {
			$this->shortlink_start = $this->calc_shortlink_time($val);
		}

		parent::set_argument($arg, $val);
	}
	/**
	 * Returns share link for the video, e.g. http://youtu.be/6jCNXASjzMY?t=1s
	 */
	function get_share_link() {
		$url = '//youtu.be/' . $this->video_id;
		$time = $this->get_argument('t');

		if ($this->shortlink_start) {
			$url .= '?t=' . $this->shortlink_start;
		}

		return $url;
	}

	function get_link() {
		$url = '//' . self::DEFAULT_DOMAIN . '/watch?v=' . $this->video_id;
		$time = $this->get_argument('t');

		if ($this->shortlink_start) {
			$url .= '?t=' . $this->shortlink_start;
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
	 * Returns flash-based embed code.
	 */
	function get_flash_embed_code($width=null, $height=null) {
		$width = $this->get_embed_width($width);		
		$height = $this->get_embed_height($height);		

		$url = '//' . $this->domain . '/v/' . $this->video_id;

		$args = array_merge(array(
			'version'=>'3',
			'hl'=>'en_US',
		), $this->arguments);

		$url .= '?' . htmlspecialchars(http_build_query($args));
		
		return 
			'<object width="' . $width . '" height="' . $height . '">' . 
				'<param name="movie" value="' . $url . '"></param>' . 
				'<param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param>' . 
				'<embed src="' . $url . '" type="application/x-shockwave-flash" width="' . $width . '" height="' . $height . '" allowscriptaccess="always" allowfullscreen="true"></embed>' . 
			'</object>';
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
