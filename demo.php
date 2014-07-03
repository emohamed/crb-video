<?php
require "video.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Video Lib demo</title>
	<style>
		.shell { width: 960px; margin: 0 auto; padding: 10px }
	</style>
	<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" />
</head>
<body>
	<div class="shell">
		<h3>Accept Youtube URL, produce HTML5 Embed with 640 x 480</h3>
		<?php echo Carbon_Video::create("https://www.youtube.com/watch?v=n4RjJKxsamQ")->get_embed_code(640, 480); ?>

		<h3>Accept Youtube URL, produce Flash Embed 640 x 480</h3>
		<?php echo Carbon_Video::create("https://www.youtube.com/watch?v=n4RjJKxsamQ")->get_flash_embed_code(640, 480);
		?>

		<h3>Accept Youtube URL, produce embed without related videos</h3>
		<?php echo Carbon_Video::create("https://www.youtube.com/watch?v=n4RjJKxsamQ")->set_argument('rel', 0)->get_embed_code(); ?>

		<h3>Accept YouTube shortlink Produce embed code</h3>
		<?php echo Carbon_Video::create("http://youtu.be/n4RjJKxsamQ")->get_embed_code(); ?>

		<h3>Accept Flash Embed code, remove controls, add autoplay, and print HTML5 embed</h3>
		<?php
			$embed_code = '<object width="560" height="315"><param name="movie" value="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>';

			echo Carbon_Video::create($embed_code)->set_argument('controls', 0)->get_embed_code();
		?>
		
		<h3>Accept HTML Embed code and get screenshot</h3>
		<img src="<?php echo Carbon_Video::create('<iframe width="560" height="315" src="//www.youtube.com/embed/n4RjJKxsamQ?rel=0" frameborder="0" allowfullscreen></iframe>')->get_image(); ?>" alt="">

		<h3>Accept HTML Embed code, remove controls, add autoplay, and print flash embed</h3>
		<?php
			$embed_code = '<iframe width="560" height="315" src="//www.youtube.com/embed/n4RjJKxsamQ?rel=0" frameborder="0" allowfullscreen></iframe>';

			echo Carbon_Video::create($embed_code)
			    ->set_argument('controls', 0)
			    ->get_flash_embed_code();
		?>
	</div>
</body>
</html>