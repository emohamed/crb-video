<?php
class Crb_Video {
	const DEFAULT_WIDTH = 640;
	const DEFAULT_HEIGHT = 480;

	protected $dimensions = array(
		'width'=>null,
		'height'=>null
	);

	protected $video_id;
	protected $shortlink_start = false;

	protected $arguments = array();

	/**
	 * Parses embed code, url, or video ID and creates new object based on it. 
	 * 
	 * @param string $video embed code, url, or video ID
	 * @return object Crb_Video
	 **/
	static function create($video) {
		$video = trim($video);

		// Try to catch youtube.com, youtu.be, youtube-nocookie
		if (preg_match('~(https?:)?//(www\.)?(youtube(-nocookie)?\.com|youtu\.be)~i', $video)) {
			return new Crb_VideoYoutube($video);

		}

		// Try to catch vimeo
		if (preg_match('~(https?:)?//(www.|)(vimeo\.com)~i', $video)) { 
			return new Crb_VideoVimeo($video);
		}

		throw new Crb_Video_Exception("Can't recognize video: $video");
	}

	function get_thumbnail() {

	}

	function get_embed_code() {
		
	}

	function set_param() {

	}

	/**
	 * Whether a video is autoplay  
	 *
	 * @param bool $autoplay
	 * @return object $this
	 **/
	function set_autoplay($autoplay) {
		$this->autoplay = (bool) $autoplay;
		return $this;
	}

	/**
	 * @return property $autoplay
	 */
	function get_autoplay() {
		return $this->autoplay;
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
		// "t" argument is special case since it's the only one in the shortlinks
		// and it's translated differently to embed code arguments
		// (see https://developers.google.com/youtube/player_parameters#start)
		if ($arg === 't') {
			$this->shortlink_start = $val;
			
			$arg = 'start';
			$val = $this->calc_time_in_seconds($val);

		} else if ($arg === 'start') {
			$this->shortlink_start = $this->calc_shortlink_time($val);
		}

		$this->arguments[$arg] = $val;
		return $this;
	}

	/**
	 * Calculates how many seconds are there in the string in format "3m2s". 
	 * @return int seconds
	 */
	function calc_time_in_seconds($time) {
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
	function calc_shortlink_time($seconds) {
		$result = '';
		if ($seconds > 60) {
			$result .= floor($seconds / 60) . "m";
		}
		return $result . ($seconds % 60) . "s";
	}

	function get_id() {
		return $this->video_id;
	}
}

class Crb_Video_Exception extends Exception {}

class Crb_VideoVimeo extends Crb_Video {
	public $cache_lifetime = 300;
	


	/**
	 * @param integer $width
	 * @param integer $height
	 * @return vimeo embed code
	 */
	function get_embed_code($width=self::DEFAULT_WIDTH, $height=self::DEFAULT_HEIGHT) {

		$width = intval($width);
		$height = intval($height);

		preg_match('~vimeo.com/([\d]+)~', $this->url, $video_id);
	
		return '<iframe src="http://player.vimeo.com/video/' . $video_id[1] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff' . ($this->autoplay ? '&amp;autoplay=autoplay' : '') . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe>';
	}

	/**
	 * Whether to return a large vimeo thumbnail.
	 * @param bool $large
	 * @return vimeo thumbnail
	 **/
	function get_thumbnail($large = false) {
		$embed_code = $this->get_embed_code();
		if (preg_match('~iframe~', $embed_code)) {
			preg_match('~src="[^"]*video/([^?&]*)[^"]*"~', $embed_code, $video_id);
		} else {
			preg_match('~clip_id=(.*?)&~', $embed_code, $video_id);
		}

		if(empty($video_id[1])) {
			throw new Crb_Video_Exception("This is not valid vimeo url address. ");
		}

		$url = "http://vimeo.com/api/v2/video/".$video_id[1].".php";
		$cache_id = 'vimeocache::' . md5($url);

		$cached = get_option($cache_id, false);
		$has_cache = (bool)$cached;
		$is_expired = isset($cached['expires']) && time() > $cached['expires'];
		$has_cache = false;
		if (!$has_cache || $is_expired) {
			$data = wp_remote_get($url);
			if (!is_wp_error($data)) {
				$data = $data['body'];
				
				$video_cache = array(
					'data'=>$data,
					'expires'=>time() + $this->cache_lifetime,
				);
				
				update_option($cache_id, $video_cache);

			} else {
				$data = false;
			}
		} else {
			$data = $cached['data'];
		}
		$finaldata = unserialize($data);
		$size = 'thumbnail_medium';
		if($large) {
			$size = 'thumbnail_large';
		}
		return $finaldata[0][$size];

	}

}

class Crb_VideoYoutube extends Crb_Video {
	/**
	 * The original domain name of the video: either youtube.com or youtube-nocookies.com
	 * @var string
	 */
	public $domain = 'www.youtube.com';

	/**
	 * Constructs new object from various video inputs. 
	 * 
	 */
	function __construct($video) {
		// Try to parse the video code in the following order:
		// Youtube video URL(https://www.youtube.com/watch?v=lsSC2vx7zFQ)
		// http://youtu.be/lsSC2vx7zFQ
		// New embed code
		// Old Embed code ()
		
		// Describe "http://" or "https://" or "//"
		$protocol_regex_fragment = '(?:https?:)?//';

		// Desribe youtube video ID 
		$video_id_regex_fragment = '(?P<video_id>[\w\-]*)';

		// Desribe youtube video arguments(e.g. rel, time, etc.)
		$args_regex_fragment = '(?:\?(?P<arguments>.+?))?';

		$regexes = array(
			// Something like: https://www.youtube.com/watch?v=lsSC2vx7zFQ
			"youtube_url_regex" =>
				'~^' . 
					$protocol_regex_fragment . 
					'(?P<domain>(?:www\.)?youtube\.com)/watch\?v=' . 
					$video_id_regex_fragment .
				'~i', 

			// Something like "http://youtu.be/lsSC2vx7zFQ" or "http://youtu.be/6jCNXASjzMY?t=3m11s"
			"youtube_share_url_regex" =>
				'~^' .
					$protocol_regex_fragment .
					'youtu\.be/' .
					$video_id_regex_fragment .
					$args_regex_fragment . 
				'$~i',

			// Youtube embed iframe code: 
			// <iframe width="560" height="315" src="//www.youtube.com/embed/LlhfzIQo-L8?rel=0" frameborder="0" allowfullscreen></iframe>
			"youtube_embed_code_regex" =>
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
			"youtube_old_embed_code_regex" =>
				'~^'.
					'<object.*?' .
					$protocol_regex_fragment .
					'(?P<domain>(www\.)?youtube(?:-nocookie)?\.com)/embed/' . 
					$video_id_regex_fragment .
					$args_regex_fragment . 
				'~[\'"]i'
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
			'youtube_embed_code_regex',
			'youtube_old_embed_code_regex'
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
			throw new Crb_Video_Exception("Couldn't parse video input. ");
		}
	}

	function get_share_link() {

		$url = '//youtu.be/' . $this->video_id;
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
		// If width and height are not provided in the function parameters,
		// get them from the initial video code; if the object wasn't constructed
		// from embed code(and doesn't have initial dimentions), use the default
		// hard-coded dimensions. 
		if (!isset($width)) {
			$width = empty($this->dimensions['width']) ? self::DEFAULT_WIDTH : $this->dimensions['width'];
		}
		if (!isset($height)) {
			$height = empty($this->dimensions['height']) ? self::DEFAULT_HEIGHT : $this->dimensions['height'];
		}

		$url = '//' . $this->domain . '/embed/' . $this->video_id;

		if (!empty($this->arguments)) {
			$url .= '?' . htmlspecialchars(http_build_query($this->arguments));
		}
		
		return '<iframe width="' . $width . '" height="' . $height . '" src="' . $url . '" frameborder="0" allowfullscreen></iframe>';
	}

	/**
	 * Whether to return a large youtube thumbnail.
	 * @param bool $large
	 * @return youtube thumbnail
	 **/
	function get_thumbnail($large = false) {
		$embed_code = $this->get_embed_code();
		if (preg_match('~iframe~', $embed_code)) {
			preg_match('~src="[^"]*(embed/)([^\?&"]*)~', $embed_code, $video_id);
		} else {
			preg_match('~(v/|v=)([a-zA-Z0-9]+)(\?|&|\z)~', $embed_code, $video_id);
		}

		$size = 'mqdefault';
		if($large) {
			$size = '0';
		}

		if(empty($video_id[2])) {
			throw new Crb_Video_Exception("This is not valid youtube url address.");
		}

		return 'http://img.youtube.com/vi/' . $video_id[2] . '/' . $size . '.jpg';
	}

}
