## Carbon Video

Video utility library for WordPress Carbon theme. The aim of the library is to ease the work with youtube and vimeo videos.

## Examples

Accept Youtube URL, produce HTML5 Embed with 640 x 480:

```php	
<?php
$youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
echo Carbon_Video::create($youtube_link)->get_embed_code(640, 480);
?>
```

Accept Youtube URL, produce embed without related videos:

```php
<?php
$youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
echo Carbon_Video::create($youtube_link)
    ->set_argument('rel', 0)
    ->get_embed_code();
?>
```

Accept YouTube shortlink Produce embed code:

```php
<?php
$youtube_share_url = "http://youtu.be/n4RjJKxsamQ";
echo Carbon_Video::create($youtube_share_url)->get_embed_code();
?>
```

Accept Flash Embed code, remove controls, add autoplay, and print HTML embed:

```php
<?php
$embed_code = '<object width="560" height="315"><param name="movie" value="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>';

echo Carbon_Video::create($embed_code)
    ->set_argument('controls', 0)
    ->set_argument('autoplay', 1)
    ->get_embed_code();
?>
```

Accept HTML Embed code, remove controls, add autoplay, and print embed:

```php
<?php
$embed_code = '<iframe width="560" height="315" src="//www.youtube.com/embed/n4RjJKxsamQ?rel=0" frameborder="0" allowfullscreen></iframe>';

echo Carbon_Video::create($embed_code)
    ->set_argument('controls', 0)
    ->set_argument('autoplay', 1)
    ->get_embed_code();
?>
```

## TO DO
 
 - Write some documentation and add more examples(especially for vimeo)