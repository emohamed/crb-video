<?php 
require_once('load.php');

class YoutubeIframeEmbedNoCookieTest extends PHPUnit_Framework_TestCase {
	function testNoCookieDomain() {
		$video = Carbon_Video::create('<iframe width="420" height="315" src="//www.youtube-nocookie.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></iframe>');
		$this->assertEquals($video->domain, 'www.youtube-nocookie.com');
	}
}
