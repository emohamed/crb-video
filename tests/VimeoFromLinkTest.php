<?php 
require_once('load.php');

class VimeoFromLinkTest extends PHPUnit_Framework_TestCase {
	function setup() {
		
	}

	function tearDown() {
		
	}
	/**
	 * @expectedException Carbon_Video_Exception
	 */
	function testBadLink() {
		$video = Carbon_Video::create("http://vimeo|com/2526536");
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

	function testLinkWithStartTime() {
		$video = Carbon_Video::create("http://vimeo.com/2526536#t=15s");
		$this->assertInstanceOf("Carbon_Video", $video);
		$this->assertEquals(2526536, $video->get_id());
		$this->assertEquals(15, $video->get_start_time());

		$this->assertEquals('http://i.vimeocdn.com/video/86626321_200x150.jpg', $video->get_thumbnail());
	}


}