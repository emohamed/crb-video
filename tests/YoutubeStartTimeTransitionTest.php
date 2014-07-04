<?php 
require_once('load.php');

class YoutubeStartTimeTransitionTest extends PHPUnit_Framework_TestCase {
	function setup() {
		
	}

	function tearDown() {
		
	}
	
	function testStartTimeFromShortLink() {
		$video = Carbon_Video::create("http://youtu.be/tIdIqbv7SPo?t=1m2s");

		$this->assertEquals(
			62,
			$video->get_argument('start')
		);

		$this->assertEquals(
			'1m2s',
			$video->get_start_time()
		);

		$this->assertContains(
			'start=62',
			$video->get_embed_code()
		);


	}

	function testStartTimeFromEmbed() {
		$iframe_code = '<iframe width="560" height="315" src="//www.youtube.com/embed/7ADgCeYJMN4?start=75" frameborder="0" allowfullscreen></iframe>';
		
		$video = Carbon_Video::create($iframe_code);

		$this->assertEquals(
			'1m15s',
			$video->get_start_time()
		);

		$this->assertEquals(
			'75',
			$video->get_argument('start')
		);

		$this->assertContains(
			'start=75',
			$video->get_embed_code()
		);

	}
}