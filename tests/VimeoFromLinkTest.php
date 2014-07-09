<?php 
require_once('load.php');

class VimeoFromLinkTest extends PHPUnit_Framework_TestCase {
	function setup() {
		
	}

	function tearDown() {
		
	}
	function testBadLink() {
		$video = Carbon_Video::create("http://vimeo|com/2526536");
		$this->assertTrue($video->is_broken());
		$this->assertEquals('', $video->get_embed_code());
	}

	function testStandardLink() {
		$video = Carbon_Video::create("http://vimeo.com/2526536");
		$this->assertInstanceOf("Carbon_Video", $video);
		$this->assertEquals(2526536, $video->get_id());
	}

	function testChannelLink() {
		$video = Carbon_Video::create("http://vimeo.com/channels/staffpicks/98861259");
		$this->assertInstanceOf("Carbon_Video", $video);
		$this->assertEquals(98861259, $video->get_id());
	}

	function testEmbedUrl() {
		$video = Carbon_Video::create("http://player.vimeo.com/video/98861259");
		$this->assertInstanceOf("Carbon_Video", $video);
		$this->assertEquals(98861259, $video->get_id());
	}

	function testLinkWithStartTime() {
		$video = Carbon_Video::create("http://vimeo.com/2526536#t=15s");
		$this->assertInstanceOf("Carbon_Video", $video);
		$this->assertEquals(2526536, $video->get_id());
		$this->assertEquals(15, $video->get_start_time());
		
		$vimeo_api_response_file = dirname(__FILE__) . '/data/2526536.json';
		$vimeo_api_response = file_get_contents($vimeo_api_response_file);

		$http = $this->getMock('Carbon_Video_Http');
		$http->expects($this->any())
		     ->method('get')
		     ->will($this->returnValue($vimeo_api_response));

		$video->http = $http;

		$this->assertEquals(
			'http://i.vimeocdn.com/video/86626321_200x150.jpg',
			$video->get_thumbnail()
		);
	}

}