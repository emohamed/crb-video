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
 * embed direct links: `//www.youtube.com/embed/tIdIqbv7SPo?rel=0`
 * old embed code: `<object width="420" height="315"><param name="movie" value="//www.youtube.com/v/tIdIqbv7SPo?version=3&amp;hl=en_US&amp;rel=0"> ... snip ... </object>`

Once created, you can modify the dimensions or the params of the video. Then, you can get various resources of the original video:

 * Embed iframe code
 * Embed iframe source URL
 * URL to the video at the source site
 * Share link
 * Thumbnail and image

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

You can also construct objects from direct embed URLs(instead of the whole iframe code). 

```php
<?php
$embed_code = '//www.youtube.com/embed/LlhfzIQo-L8?rel=0';

echo Carbon_Video::create($embed_code)
    ->get_embed_code();
?>
```

## API

<table>
    <tr>
        <th><code>set_param($arg, $val)</code></th>
        <td>
<p>Sets GET param of the embed source. See params section below for reference to currently supported params. </p>

        </td>
    </tr>

    <tr>
        <th><code>get_param($arg)</code></th>
        <td>Returns a parameter value. </td>
    </tr>

    <tr>
        <th><code>set_params($params)</code></th>
        <td>
<p>Sets multiple params of the embed source. The provided parapms array must contain params pairs where the key is the parameter name and the value is the parameter value. See params section below for reference to currently supported params. </p>

        </td>
    </tr>

    <tr>
        <th><code>get_width()</code></th>
        <td>Returns the current width of the embed. </td>
    </tr>

    <tr>
        <th><code>set_width($new_width)</code></th>
        <td>Updates the width of the embed. </td>
    </tr>

    <tr>
        <th><code>get_height()</code></th>
        <td>Returns the current height of the embed. </td>
    </tr>

    <tr>
        <th><code>set_height($new_height)</code></th>
        <td>Updates the height of the embed. </td>
    </tr>

    <tr>
        <th><code>get_link()</code></th>
        <td>Return permalink for the video. </td>
    </tr>

    <tr>
        <th><code>get_share_link()</code></th>
        <td>Returns short link for sharing purposes. For Vimeo, this link is the same as the permalink. </td>
    </tr>

    <tr>
        <th><code>get_embed_code([$embed_width[, $embed_height]])</code></th>
        <td>Generates HTML iframe markup for the object. When width and height are provided, the embed will use them; if they're ommited, the embed will obtain it's original values(whenever the object was constructed from embed code). </td>
    </tr>

    <tr>
        <th><code>get_embed_url()</code></th>
        <td>Returns the URL to the iframe that hosts the video player. </td>
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

## Params
This section provides reference for commonly used parameters for video providers. Note that these are subject of change: the below reference may be inaccurate by the time you read this.

### Youtube parameters

<a href="https://developers.google.com/youtube/player_parameters#Parameters">Full Reference</a>

<table>
    <tr>
        <th>Parameter</th>
        <th>Values</th>
        <th>Default</th>
        <th>Description</th>
    </tr>
    <tr>
        <th><code>autohide</code></th> 
        <td>2, 1, 0</td>
        <td>2</td>
        <td>whether the video controls will automatically hide after a video begins playing. </td>
    </tr>
    <tr>
        <th><code>autoplay</code></th>
        <td>0, 1</td>
        <td>0</td>
        <td>whether or not the initial video will autoplay when the player loads. </td>
    </tr>
    <tr>
        <th><code>color</code></th>
        <td>red, white</td>
        <td>red</td>
        <td>specifies the color in the player's video progress bar to highlight the amount of the video that the viewer has already seen. </td>
    </tr>
    <tr>
        <th><code>controls</code></th>
        <td>0, 1, 2</td>
        <td>1</td>
        <td>this parameter indicates whether the video player controls will display. </td>
    </tr>
    <tr>
        <th><code>enablejsapi</code></th>
        <td>0, 1</td>
        <td>0</td>
        <td>setting this to 1 will enable the Javascript API. </td>
    </tr>
    <tr>
        <th><code>iv_load_policy</code></th>
        <td>1, 3</td>
        <td>1</td>
        <td>setting to 1 will cause video annotations to be shown by default, whereas setting to 3 will cause video annotations to not be shown by default. </td>
    </tr>
    <tr>
        <th><code>loop</code></th>
        <td>0, 1</td>
        <td>0</td>
        <td>setting of 1 will cause the player to play the initial video again and again. </td>
    </tr>
    <tr>
        <th><code>modestbranding</code></th>
        <td>0, 1</td>
        <td>0</td>
        <td>lets you use a YouTube player that does not show a YouTube logo. Set the parameter value to 1 to prevent the YouTube logo from displaying in the control bar.</td>
    </tr>
    <tr>
        <th><code>origin</code></th>
        <td>0, 1</td>
        <td>0</td>
        <td>provides an extra security measure for the IFrame API. If you are using the IFrame API, which means you are setting the enablejsapi parameter value to 1, you should always specify your domain as the origin parameter value.</td>
    </tr>
    <tr>
        <th><code>playlist</code></th>
        <td>N/A</td>
        <td>N/A</td>
        <td>value is a comma-separated list of video IDs to play. If you specify a value, the first video that plays will be the VIDEO_ID specified in the URL path, and the videos specified in the playlist parameter will play thereafter.</td>
    </tr>
    <tr>
        <th><code>playsinline</code></th>
        <td>0, 1</td>
        <td>0</td>
        <td>controls whether videos play inline or fullscreen in an HTML5 player on iOS</td>
    </tr>
    <tr>
        <th><code>rel</code> </th>
        <td>0, 1</td>
        <td>1</td>
        <td>indicates whether the player should show related videos when playback of the initial video ends. </td>
    </tr>
    <tr>
        <th><code>showinfo</code></th>
        <td>0, 1</td>
        <td>1</td>
        <td>if you set the parameter value to 0, then the player will not display information like the video title and uploader before the video starts playing. </td>
    </tr>
    <tr>
        <th><code>start</code></th>
        <td>N/A</td>
        <td>0</td>
        <td>causes the player to begin playing the video at the given number of seconds from the start of the video</td>
    </tr>
    <tr>
        <th><code>theme</code></th>
        <td>dark, light</td>
        <td>dark</td>
        <td>indicates whether the embedded player will display player controls (like a play button or volume control) within a dark or light control bar.</td>
    </tr>
</table>


### Vimeo parameters

<a href="http://developer.vimeo.com/player/embedding#universal-parameters">Full Reference</a>

<table>
    <tr>
        <th><code>autopause</code></th>
        <td>enables or disables pausing this video when another video is played. Defaults to 1.</td>
    </tr>
    <tr>
        <th><code>autoplay</code></th>
        <td>play the video automatically on load. Defaults to 0. Note that this won’t work on some devices.</td>
    </tr>
    <tr>
        <th><code>badge</code></th>
        <td>enables or disables the badge on the video. Defaults to 1.</td>
    </tr>
    <tr>
        <th><code>byline</code></th>
        <td>show the user’s byline on the video. Defaults to 1.</td>
    </tr>
    <tr>
        <th><code>color</code></th>
        <td>specify the color of the video controls. Defaults to <code>00adef</code>. Make sure that you don’t include the <code>#</code>.</td>
    </tr>
    <tr>
        <th><code>loop</code></th>
        <td>play the video again when it reaches the end. Defaults to 0.</td>
    </tr>
    <tr>
        <th><code>player_id</code></th>
        <td>a unique id for the player that will be passed back with all Javascript API responses.</td>
    </tr>
    <tr>
        <th><code>portrait</code></th>
        <td>show the user’s portrait on the video. Defaults to 1.</td>
    </tr>
    <tr>
        <th><code>title</code></th>
        <td>show the title on the video. Defaults to 1.</td>
    </tr>
</table>
