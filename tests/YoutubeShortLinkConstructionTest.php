<?php 
require_once('load.php');
class YoutubeShortLinkConstructionTest extends PHPUnit_Framework_TestCase {
	function setup() {
		$this->video = Carbon_Video::create("http://youtu.be/KnL2RJZTdA4?t=1m1s");
	}

	function tearDown() {
		unset($this->video);
	}

	function testIdRetrieval() {
		$this->assertEquals($this->video->get_id(), 'KnL2RJZTdA4');
	}

	function testEmbedFromLinkCreation() {
		$this->assertEquals(
			$this->video->get_embed_code(500, 300),
			'<iframe width="500" height="300" src="//www.youtube.com/embed/KnL2RJZTdA4?start=61" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testEmbedDefalutDimensions() {
		$this->assertEquals(
			$this->video->get_embed_code(),
			'<iframe width="640" height="480" src="//www.youtube.com/embed/KnL2RJZTdA4?start=61" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testShareLink() {
		$this->assertEquals($this->video->get_share_link(), '//youtu.be/KnL2RJZTdA4?t=1m1s');
	}
}