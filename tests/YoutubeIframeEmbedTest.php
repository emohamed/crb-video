<?php 
require_once('load.php');

class YoutubeIframeEmbedTest extends PHPUnit_Framework_TestCase {

	function setUp() {
		$this->video = Crb_Video::create('<iframe width="640" height="480" src="//www.youtube.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></iframe>');
	}

	function tearDown() {
		unset($this->video);
	}

	function testConstructor() {
		$this->assertTrue($this->video instanceof Crb_VideoYoutube);
		
	}

	function testDimensions() {
		$this->assertEquals($this->video->get_width(), 640);
		$this->assertEquals($this->video->get_height(), 480);
	}
	function testIdExtraction() {
		$this->assertEquals($this->video->get_id(), '6jCNXASjzMY');
	}
	
	function testArgumentsExtraction() {
		$this->assertEquals($this->video->get_argument('rel'), '0');
	}

	function testSharelink() {
		$this->assertEquals($this->video->get_share_link(), '//youtu.be/6jCNXASjzMY');
	}

	function testSharelinkWithTime() {
		$time = '1m2s';
		$this->video->set_argument('t', $time);
		$this->assertEquals($this->video->get_share_link(), "//youtu.be/6jCNXASjzMY?t=$time");
	}

	function testEmbedCode() {
		$this->assertEquals(
			$this->video->get_embed_code(), 
			'<iframe width="640" height="480" src="//www.youtube.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testEmbedCodeWithDifferentArgument() {
		$this->video->set_argument('rel', 1);

		$this->assertEquals(
			$this->video->get_embed_code(), 
			'<iframe width="640" height="480" src="//www.youtube.com/embed/6jCNXASjzMY?rel=1" frameborder="0" allowfullscreen></iframe>'
		);
		
	}

	function testEmbedDimensionsChange() {
		$this->assertEquals(
			$this->video->get_embed_code(100, 200), 
			'<iframe width="100" height="200" src="//www.youtube.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></iframe>'
		);
	}
}