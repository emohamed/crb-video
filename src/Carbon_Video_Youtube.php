<?php
class Carbon_Video_Youtube extends Carbon_Video {
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

	/**
	 * Check whether video code looks remotely like youtube link, short link or embed code. 
	 * Returning true here doesn't guarantee that the code will be actually paraseable. 
	 * 
	 * @param  string $video_code
	 * @return boolean
	 */
	static function test($video_code) {
		return preg_match('~(https?:)?//(www\.)?(youtube(-nocookie)?\.com|youtu\.be)~i', $video_code);
	}

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
			// //www.youtube.com/embed/LlhfzIQo-L8?rel=0
			"direct_embed_code_regex" =>
				'~^'.
					$this->regex_fragments['protocol'] . 
					'(?P<domain>(www\.)?youtube(?:-nocookie)?\.com)/(?:embed|v)/' . 
					$this->regex_fragments['video_id'] .
					$this->regex_fragments['args'] .
				'$~i',

			// Youtube embed iframe code: 
			// <iframe width="560" height="315" src="//www.youtube.com/embed/LlhfzIQo-L8?rel=0" frameborder="0" allowfullscreen></iframe>
			"embed_code_regex" =>
				'~'.
					'<iframe.*?src=[\'"]' .
					$this->regex_fragments['protocol'] . 
					'(?P<domain>(www\.)?youtube(?:-nocookie)?\.com)/(?:embed|v)/' . 
					$this->regex_fragments['video_id'] .
					$this->regex_fragments['args'] .
				'[\'"]~i',

			// Youtube old embed flash code:
			// <object width="234" height="132"><param name="movie" ....
			// .. type="application/x-shockwave-flash" width="234" heighGt="132" allowscriptaccess="always" allowfullscreen="true"></embed></object>
			"old_embed_code_regex" =>
				'~'.
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

				if (isset($matches['params'])) {
					// & in the URLs is encoded as &amp;, so fix that before parsing
					$args = htmlspecialchars_decode($matches['params']);
					parse_str($args, $params);

					if ($video_input_type === 'old_embed_code_regex') {
						// Those are legacy params for the flash player
						unset($params['hl'], $params['version']);
					}

					foreach ($params as $arg_name => $arg_val) {
						$this->set_param($arg_name, $arg_val);
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
	 * Override set_param in order to catch a special `t` and `start` params in youtube:
	 *   - `t` param is optional for share shortened links and is in format "3m2s" -- that is 
	 *     start playback 3 minutes and 2 seconds
	 *   - `start` is the same thing, but is used as embed code params
	 */
	function set_param($arg, $val) {
		// "t" param is special case since it's the only one in the share links
		// and it's translated differently to embed code params
		// (see https://developers.google.com/youtube/player_parameters#start)
		if ($arg === 't') {
			$this->start_time = $val;
			
			$arg = 'start';
			$val = $this->calc_time_in_seconds($val);

		} else if ($arg === 'start') {
			$this->start_time = $this->calc_shortlink_time($val);
		}

		return parent::set_param($arg, $val);
	}
	/**
	 * Returns share link for the video, e.g. http://youtu.be/6jCNXASjzMY?t=1s
	 */
	function get_share_link() {
		$url = '//youtu.be/' . $this->video_id;
		$time = $this->get_param('t');

		if ($this->start_time) {
			$url .= '?t=' . $this->start_time;
		}

		return $url;
	}

	function get_link() {
		$url = '//' . self::DEFAULT_DOMAIN . '/watch?v=' . $this->video_id;
		$time = $this->get_param('t');

		if ($this->start_time) {
			$url .= '?t=' . $this->start_time;
		}

		return $url;
	}
	function get_embed_url() {
		$url = '//' . $this->domain . '/embed/' . $this->video_id;

		if (!empty($this->params)) {
			$url .= '?' . htmlspecialchars(http_build_query($this->params));
		}

		return $url;
	}
	/**
	 * Returns iframe-based embed code.
	 */
	function get_embed_code($width=null, $height=null) {
		$width = $this->get_embed_width($width);
		$height = $this->get_embed_height($height);

		return '<iframe width="' . $width . '" height="' . $height . '" src="' . $this->get_embed_url() . '" frameborder="0" allowfullscreen></iframe>';
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
