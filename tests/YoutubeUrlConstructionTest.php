<?php 
require_once('load.php');

class YoutubeUrlConstructionTest extends PHPUnit_Framework_TestCase {
	function setup() {
		$this->video = Carbon_Video::create("https://www.youtube.com/watch?v=lsSC2vx7zFQ");
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

	function testEmbedDefaultDimensions() {
		$this->assertEquals(
			$this->video->get_embed_code(),
			'<iframe width="640" height="360" src="//www.youtube.com/embed/lsSC2vx7zFQ" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testShareLink() {
		$this->assertEquals($this->video->get_share_link(), '//youtu.be/lsSC2vx7zFQ');
	}

	function testImages() {
		$this->assertEquals($this->video->get_thumbnail(), '//img.youtube.com/vi/lsSC2vx7zFQ/default.jpg');
		$this->assertEquals($this->video->get_image(), '//img.youtube.com/vi/lsSC2vx7zFQ/0.jpg');
	}
	
	function testDirectEmbedLinkConstruction() {
		$video = Carbon_Video::create("//www.youtube.com/embed/LlhfzIQo-L8?rel=0");
		$this->assertInstanceOf("Carbon_Video", $video);
	}
}