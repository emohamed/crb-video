<?php 
require_once('load.php');

class YoutubeUrlConstructionTest extends PHPUnit_Framework_TestCase {
	function setup() {
		$this->video = Crb_Video::create("https://www.youtube.com/watch?v=lsSC2vx7zFQ");
	}

	function tearDown() {
		unset($this->video);
	}

	function testIdRetrieval() {
		$this->assertEquals($this->video->get_id(), 'lsSC2vx7zFQ');
	}

	function testEmbedFromLinkCreation() {
		$this->assertEquals(
			$this->video->get_embed_code(500, 300),
			'<iframe width="500" height="300" src="//www.youtube.com/embed/lsSC2vx7zFQ" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testEmbedDefalutDimensions() {
		$this->assertEquals(
			$this->video->get_embed_code(),
			'<iframe width="640" height="480" src="//www.youtube.com/embed/lsSC2vx7zFQ" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testShareLink() {
		$this->assertEquals($this->video->get_share_link(), '//youtu.be/lsSC2vx7zFQ');
	}
}