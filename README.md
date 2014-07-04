## Carbon Video

Video utility library for WordPress Carbon theme. The aim of the library is to ease parsing of various video inputs and to provide consistent interface to work with youtube and vimeo videos that doesn'
t require understanding of all types of URL and embed formats provided by video providers. 

## Usage

```php	
<?php
$youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
$video = Carbon_Video::create($youtube_link);

// Print HTML video embed code with specified dimensions
echo $video->get_embed_code(640, 480);

// Remove related videos and print embed code
echo $video->set_param('rel', '0')->get_embed_code();

// Print video thumbnail
echo $video->get_thumbnail();
?>
```

## Concepts

You can create new objects from various formats:

 * URLs: `https://www.youtube.com/watch?v=tIdIqbv7SPo` or `http://vimeo.com/99541639`
 * share links: `http://youtu.be/tIdIqbv7SPo`
 * embed code: `<iframe width="420" height="315" src="//www.youtube.com/embed/tIdIqbv7SPo?rel=0" frameborder="0" allowfullscreen></iframe>`
 * old embed code: `<object width="420" height="315"><param name="movie" value="//www.youtube.com/v/tIdIqbv7SPo?version=3&amp;hl=en_US&amp;rel=0"> ... snip ... </object>`

Once created, you can modify the dimensions or the params of the video. Then, you can get various resources of the original video:

 * Embed code
 * URL
 * Share link
 * Thumbnail

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
    ->set_param('rel', 0)
    ->get_embed_code();
?>
```

Accept YouTube shortlink and produce embed code:

```php
<?php
$youtube_share_url = "http://youtu.be/n4RjJKxsamQ";
echo Carbon_Video::create($youtube_share_url)->get_embed_code();
?>
```

Accept Flash embed code, remove controls, add autoplay, and print HTML embed:

```php
<?php
$embed_code = '<object width="560" height="315"><param name="movie" value="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/n4RjJKxsamQ?version=3&amp;hl=en_US&amp;rel=0" type="application/x-shockwave-flash" width="560" height="315" allowscriptaccess="always" allowfullscreen="true"></embed></object>';

echo Carbon_Video::create($embed_code)
    ->set_param('controls', 0)
    ->set_param('autoplay', 1)
    ->get_embed_code();
?>
```

Accept HTML embed code, remove controls, add autoplay, and print embed:

```php
<?php
$embed_code = '<iframe width="560" height="315" src="//www.youtube.com/embed/n4RjJKxsamQ?rel=0" frameborder="0" allowfullscreen></iframe>';

echo Carbon_Video::create($embed_code)
    ->set_param('controls', 0)
    ->set_param('autoplay', 1)
    ->get_embed_code();
?>
```

The library understands the "t" param in the shortlinks and translates it to start 

```php
<?php
$embed_code = 'http://youtu.be/XFkzRNyygfk?t=11s';

echo Carbon_Video::create($embed_code)
    ->get_embed_code();
?>
```

## API

<table>
    <tr>
        <th><code>set_param($arg, $val)</code></th>
        <td>Sets GET param of the embed source. Here is a reference for the Youtube parameters: <https://developers.google.com/youtube/player_parameters#Parameters>. Here is a reference for Vimeo arguments: <http://developer.vimeo.com/player/embedding#universal-parameters></td>
    </tr>

    <tr>
        <th><code>get_param($arg)</code></th>
        <td>Reads a parameter from the parsed initial code. </td>
    </tr>

    <tr>
        <th><code>get_width()</code></th>
        <td>Returns the current width of the player. </td>
    </tr>

    <tr>
        <th><code>set_width($new_width)</code></th>
        <td>Updates the width of the player. </td>
    </tr>

    <tr>
        <th><code>get_height()</code></th>
        <td>Returns the current height of the player. </td>
    </tr>

    <tr>
        <th><code>set_height($new_height)</code></th>
        <td>Updates the height of the player. </td>
    </tr>

    <tr>
        <th><code>get_link()</code></th>
        <td>Return perma link for the player. </td>
    </tr>

    <tr>
        <th><code>get_share_link()</code></th>
        <td>Returns short link for sharing purposes. For Vimeo, this link is the same as the permalink. </td>
    </tr>

    <tr>
        <th><code>get_embed_code([$embed_width[, $embed_height]])</code></th>
        <td>Generates HTML iframe markup for the obejct. </td>
    </tr>

    <tr>
        <th><code>get_image()</code></th>
        <td>Returns big image for the video. </td>
    </tr>

    <tr>
        <th><code>get_thumbnail()</code></th>
        <td>Returns thumbnail for the video. </td>
    </tr>
</table>