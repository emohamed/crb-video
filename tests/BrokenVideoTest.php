<?php 
require_once('load.php');

class BrokenVideoTest extends PHPUnit_Framework_TestCase {
	function setup() {
		
	}

	function tearDown() {
		
	}

	function testEmptyString() {
		$video = Carbon_Video::create("");
		$this->assertTrue($video->is_broken());
		$this->assertEquals('', $video->get_share_link());
	}

	function testMalformatedEmbedCode() {
		$video = Carbon_Video::create('<ifr ame width="640" height="480" src="//www.youtube.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></ifr ame>');
		$this->assertTrue($video->is_broken());
	}

	function testMalformatedYoutubeShareLink() {
		$video = Carbon_Video::create('//youtu.be/whatever/6jCNXASjzMY');
		$this->assertTrue($video->is_broken());
	}

	function testMalformatedVimeoLink() {
		$video = Carbon_Video::create('http://vimeo.com/channels/staffpicks');
		$this->assertTrue($video->is_broken());
	}
}