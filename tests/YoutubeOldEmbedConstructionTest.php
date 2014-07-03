<?php 
require_once('load.php');

class YoutubeOldEmbedConstructionTest extends PHPUnit_Framework_TestCase {
	function setup() {
		$this->orig_html = '<object width="420" height="315"><param name="movie" value="//www.youtube-nocookie.com/v/6jCNXASjzMY?version=3&amp;hl=en_US&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube-nocookie.com/v/6jCNXASjzMY?version=3&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" width="420" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
		$this->video = Carbon_Video::create($this->orig_html);
	}

	function tearDown() {
		unset($this->video);
	}

	function testIdRetrieval() {
		$this->assertEquals($this->video->get_id(), '6jCNXASjzMY');
	}

	function testEmbedFromLinkCreation() {
		$this->assertEquals(
			$this->video->get_embed_code(500, 300),
			'<iframe width="500" height="300" src="//www.youtube-nocookie.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></iframe>'
		);
	}

	function testEmbedDefalutDimensions() {
		$this->assertEquals(
			$this->video->get_embed_code(),
			'<iframe width="420" height="315" src="//www.youtube-nocookie.com/embed/6jCNXASjzMY?rel=0" frameborder="0" allowfullscreen></iframe>'
		);
	}
	
	function testShareLink() {
		$this->assertEquals($this->video->get_share_link(), '//youtu.be/6jCNXASjzMY');
	}
}