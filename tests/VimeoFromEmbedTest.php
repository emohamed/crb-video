<?php 
require_once('load.php');

class VimeoFromEmbedTest extends PHPUnit_Framework_TestCase {
	function setup() {
		
	}

	function tearDown() {
		
	}
	
	function testEmbedCodeCreation() {
		$video = Carbon_Video::create('<iframe src="//player.vimeo.com/video/99401340" width="500" height="213" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> <p><a href="http://vimeo.com/99401340">Dreamwalking Barcelona</a> from <a href="http://vimeo.com/rungunshoot">Brandon Li</a> on <a href="https://vimeo.com">Vimeo</a>.</p>');

		$this->assertInstanceOf('Carbon_Video', $video);
		$this->assertEquals('99401340', $video->get_id());
		$this->assertEquals('500', $video->get_width());
		$this->assertEquals('213', $video->get_height());
	}

	function testEmbedparamsParsing() {
		$video = Carbon_Video::create('<iframe src="//player.vimeo.com/video/99401340?title=0&amp;byline=0&amp;portrait=0&amp;color=c9ff23&amp;autoplay=1&amp;loop=1" width="500" height="213" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');

		$this->assertInstanceOf('Carbon_Video', $video);

		$this->assertEquals('c9ff23', $video->get_param('color'));
		$this->assertEquals('1', $video->get_param('loop'));
		$this->assertEquals('1', $video->get_param('autoplay'));
		$this->assertEquals('0', $video->get_param('portrait'));

		$this->assertEquals('500', $video->get_width());
		$this->assertEquals('213', $video->get_height());
	}
	
}