<?php 
require_once('load.php');

class YoutubeIframeEmbedNoCookieTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		$this->video = Carbon_Video::create('<iframe width="420" height="315" src="//www.youtube-nocookie.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></iframe>');
	}
	function tearDown() {
		unset($this->video);
	}
	function testNoCookieDomain() {
		$this->assertEquals($this->video->domain, 'www.youtube-nocookie.com');
	}
	function testLink() {
		$this->assertEquals($this->video->get_link(), '//www.youtube.com/watch?v=6jCNXASjzMY');
	}
	function testShareLink() {
		$this->assertEquals($this->video->get_share_link(), '//youtu.be/6jCNXASjzMY');
	}
}
