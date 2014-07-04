<?php

class Carbon_VideoVimeo extends Carbon_Video {
	protected $default_width  = '500';
	protected $default_height = '281';

	/**
	 * Check whether video code looks remotely like vimeo link or embed code. 
	 * Returning true here doesn't guarantee that the code will be actually paraseable. 
	 * 
	 * @param  string $video_code
	 * @return boolean
	 */
	static function test($video_code) {
		return preg_match('~(https?:)?//[\w.]*vimeo\.com~i', $video_code);
	}

	function __construct() {
		$this->regex_fragments = array_merge($this->regex_fragments, array(
			'video_id'=>'(?P<video_id>\d+)'
		));
		parent::__construct();
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

			// Matches embed code direct link: http://player.vimeo.com/video/98861259
			"embed_direct_link_regex" =>
				'~^' . 
					$this->regex_fragments['protocol'] . 
					'player\.vimeo\.com/video/' . 
					$this->regex_fragments['video_id'] . 
					$this->regex_fragments['args'] . 
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

				// The video ID is in GET params when old embed code is used.
				if (isset($matches['video_id'])) {
					$this->video_id = $matches['video_id'];
				}

				// Start in vimeo is in the hash rather than in GET param, so
				// it's handled differently from youtube's start param. 
				if (!empty($matches['start'])) {
					$this->start_time = $matches['start'];
				}

				if (isset($matches['params'])) {
					// & in the URLs is encoded as &amp;, so fix that before parsing
					$args = htmlspecialchars_decode($matches['params']);
					parse_str($args, $params);

					if (isset($params['clip_id'])) {
						$this->video_id = $params['clip_id'];

						unset($matches['clip_id']);
					}

					// These params are presented in the old flash embed code, but
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

					foreach ($params as $arg_name => $arg_val) {
						if (in_array($arg_name, $flash_specific_args)) {
							// Don't care about those ... 
							continue; 
						}

						if (isset($flash_to_html5_args_map[$arg_name])) {
							// save the HTML param name rather
							// than flash's param name
							$arg_name = $flash_to_html5_args_map[$arg_name];
						}

						$this->set_param($arg_name, $arg_val);
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
	
	private function get_video_data() {
		$transient_id = 'vimeo-thumbnail:' . $this->video_id;

		$video_data = $this->cache->get($transient_id);

		if ($video_data === false) {
			$api_url = 'http://vimeo.com/api/v2/video/' . $this->video_id . '.json';
			
			$json = $this->http->get($api_url);

			$video_data = json_decode($json);
			$video_data = $video_data[0];

			// Set the transient for 30 days. 
			$this->cache->set($transient_id, $video_data, 30 * 86400);
		}

		return $video_data;
	}

	function get_thumbnail() {
		$video_data = $this->get_video_data();

		return $video_data->thumbnail_medium;
	}
	function get_image() {
		$video_data = $this->get_video_data();

		return $video_data->thumbnail_large;
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

		if (!empty($this->params)) {
			$url .= '?' . htmlspecialchars(http_build_query($this->params));
		}
		
		return '<iframe src="' . $url . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	}
}