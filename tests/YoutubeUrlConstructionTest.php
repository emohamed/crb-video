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

	function testEmbedDefalutDimensions() {
		$this->assertEquals(
			$this->video->get_embed_code(),
			'<iframe width="640" height="480" src="//www.youtube.com/embed/lsSC2vx7zFQ" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testShareLink() {
		$this->assertEquals($this->video->get_share_link(), '//youtu.be/lsSC2vx7zFQ');
	}

	function testImages() {
		$this->assertEquals($this->video->get_thumbnail(), '//img.youtube.com/vi/lsSC2vx7zFQ/default.jpg');
		$this->assertEquals($this->video->get_image(), '//img.youtube.com/vi/lsSC2vx7zFQ/0.jpg');
	}

	function testFlashEmbedCode() {
		$this->assertEquals($this->video->get_flash_embed_code(560, 315), '<object width="560" height="315"><param name="movie" value="//www.youtube.com/v/lsSC2vx7zFQ?version=3&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/lsSC2vx7zFQ?version=3&amp;hl=en_US" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>');
	}
}