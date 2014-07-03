<?php 
require_once('load.php');

class VimeoFromOldEmbedTest extends PHPUnit_Framework_TestCase {
	function setup() {
		
	}

	function tearDown() {
		
	}
	
	function testEmbedCodeCreation() {
		$video = Carbon_Video::create('<!-- This version of the embed code is no longer supported. Learn more: https://vimeo.com/s/tnm --> <object width="500" height="213"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=99401340&amp;force_embed=1&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=c9ff23&amp;fullscreen=1&amp;autoplay=1&amp;loop=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=99401340&amp;force_embed=1&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=c9ff23&amp;fullscreen=1&amp;autoplay=1&amp;loop=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="500" height="213"></embed></object>');

		$this->assertInstanceOf('Carbon_Video', $video);
		return;
		$this->assertEquals('99401340', $video->get_id());
		$this->assertEquals('500', $video->get_width());
		$this->assertEquals('213', $video->get_height());

		$this->assertEquals('0', $video->get_argument('byline'));
		$this->assertEquals('0', $video->get_argument('title'));
		$this->assertEquals('0', $video->get_argument('portrait'));
		$this->assertEquals('c9ff23', $video->get_argument('color'));
		$this->assertEquals('1', $video->get_argument('autoplay'));
		$this->assertEquals('1', $video->get_argument('loop'));
		
	}
	
	
}