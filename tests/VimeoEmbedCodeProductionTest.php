<?php 
require_once('load.php');

class VimeoEmbedCodeProductionTest extends PHPUnit_Framework_TestCase {
	function setup() {
		
	}

	function tearDown() {
		
	}
	
	function testEmbedCodeProduction() {
		$video = Carbon_Video::create('<iframe src="//player.vimeo.com/video/99401340" width="500" height="213" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> <p><a href="http://vimeo.com/99401340">Dreamwalking Barcelona</a> from <a href="http://vimeo.com/rungunshoot">Brandon Li</a> on <a href="https://vimeo.com">Vimeo</a>.</p>');
		
		$this->assertEquals(
			'<iframe src="//player.vimeo.com/video/99401340" width="500" height="213" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
			$video->get_embed_code()
		);
	}

	function testEmbedCodeFromUrlProduction() {
		$video = Carbon_Video::create('http://vimeo.com/99401340');
		
		$this->assertEquals(
			'<iframe src="//player.vimeo.com/video/99401340" width="500" height="213" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
			$video->get_embed_code(500, 213) // Dimensions are not calculated automatically
		);
	}
}