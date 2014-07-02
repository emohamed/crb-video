## Carbon Video

Video utility library for WordPress Carbon theme. The aim of the library is to ease the work with youtube and vimeo videos.

## Examples

Accept Youtube URL, produce HTML5 Embed with 640 x 480:

```php	
<?php
$youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
echo Crb_Video::create($youtube_link)->get_embed_code(640, 480);
?>
```

Accept Youtube URL, produce Flash Embed 640 x 480:

```php
<?php
$youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
echo Crb_Video::create($youtube_link)->get_flash_embed_code(640, 480);
?>
```

Accept Youtube URL, produce embed without related videos:

```php
<?php
$youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
echo Crb_Video::create($youtube_link)
    ->set_argument('rel', 0)
    ->get_embed_code();
?>
```

Accept YouTube shortlink Produce embed code:

```php
<?php
$youtube_share_url = "http://youtu.be/n4RjJKxsamQ";
echo Crb_Video::create($youtube_share_url)->get_embed_code();
?>
```

Accept Flash Embed code, remove controls, add autoplay, and print HTML5 embed:

```php
<?php
$embed_code = '<object width="560" height="315"><param name="movie" value="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>';

echo Crb_Video::create($embed_code)
    ->set_argument('controls', 0)
    ->set_argument('autoplay', 1)
    ->get_embed_code();
?>
```