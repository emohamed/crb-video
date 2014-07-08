## Carbon Video

Video utility library for WordPress Carbon theme. The aim of the library is to ease parsing of various video inputs and to provide consistent interface to work with youtube and vimeo videos that doesn'
t require understanding of all types of URL and embed formats provided by video providers. 

## Usage

```php	
<?php
$youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
try {
    $video = Carbon_Video::create($youtube_link);
  
    // Print HTML video embed code with specified dimensions
    echo $video->get_embed_code(640, 480);
  
    // Remove related videos and print embed code
    echo $video->set_param('rel', '0')->get_embed_code();
  
    // Print video thumbnail
    echo $video->get_thumbnail();
} catch(Carbon_Video_Exception $e) {
    // Handle error: $youtube_link isn't parse-able
}
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

Accept Youtube URL, return 640 x 480 HTML5 Embed:

```php
<?php
try {
    $youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
    echo Carbon_Video::create($youtube_link)->get_embed_code(640, 480);
} catch(Carbon_Video_Exception $e) {
    // Handle error: $youtube_link isn't parse-able
}
?>
```   

Accept Youtube URL, produce embed without related videos:

```php
    <?php
try {
    $youtube_link = "https://www.youtube.com/watch?v=n4RjJKxsamQ";
    echo Carbon_Video::create($youtube_link)
        ->set_param('rel', 0)
        ->get_embed_code();
} catch(Carbon_Video_Exception $e) {
    // Handle error: $youtube_link isn't parse-able
}
?>
```

Accept YouTube shortlink and produce embed code:

```php
<?php
try {
    $youtube_share_url = "http://youtu.be/n4RjJKxsamQ";
    echo Carbon_Video::create($youtube_share_url)->get_embed_code();
} catch(Carbon_Video_Exception $e) {
    // Handle error: $youtube_share_url isn't parse-able
}
?>
```

Accept Flash embed code, remove controls, add autoplay, and print HTML embed:

```php
<?php
try {
    $embed_code = '';
    
    echo Carbon_Video::create($embed_code)
        ->set_param('controls', 0)
        ->set_param('autoplay', 1)
        ->get_embed_code();
} catch(Carbon_Video_Exception $e) {
    // Handle error: $embed_code isn't parse-able
}
?>
```

Accept HTML embed code, remove controls, add autoplay, and print embed:

```php
<?php
try {
    $embed_code = '<iframe width="560" height="315" src="//www.youtube.com/embed/n4RjJKxsamQ?rel=0" frameborder="0" allowfullscreen></iframe>';
    
    echo Carbon_Video::create($embed_code)
        ->set_param('controls', 0)
        ->set_param('autoplay', 1)
        ->get_embed_code();
} catch(Carbon_Video_Exception $e) {
    // Handle error: $embed_code isn't parse-able
}
?>
```

The library understands the "t" param in the shortlinks and translates it to start

```php
<?php
try {
    $embed_code = 'http://youtu.be/XFkzRNyygfk?t=11s';
    
    echo Carbon_Video::create($embed_code)
        ->get_embed_code();
} catch(Carbon_Video_Exception $e) {
    // Handle error: $embed_code isn't parse-able
}
?>
```

You can also construct objects from direct embed URLs(instead of the whole iframe code).

```php
<?php
try {
    $embed_code = '//www.youtube.com/embed/LlhfzIQo-L8?rel=0';
    
    echo Carbon_Video::create($embed_code)
        ->get_embed_code();
} catch(Carbon_Video_Exception $e) {
    // Handle error: $embed_code isn't parse-able
}
?>
```

## API

<table>
    <tr>
        <th><code>set_param($arg, $val)</code></th>
        <td>
<p>Sets GET param of the embed source.</p>

<p><strong>Youtube parameters</strong></p>

<ul>
    <li><code>autohide</code> - whether the video controls will automatically hide after a video begins playing. Values: `2` (default), `1`, and `0`. </li>
    <li><code>autoplay</code> - whether or not the initial video will autoplay when the player loads. Values: `0`(default) or `1`. </li>
    <li><code>color</code> - specifies the color in the player's video progress bar to highlight the amount of the video that the viewer has already seen. Values: red(default), white</li>
    <li><code>controls</code> - this parameter indicates whether the video player controls will display. Values: 0, 1(default), or 2</li>
    <li><code>enablejsapi</code> - setting this to 1 will enable the Javascript API. Values: 0(default) or 1</li>
    <li><code>iv_load_policy</code> - setting to 1 will cause video annotations to be shown by default, whereas setting to 3 will cause video annotations to not be shown by default. Values: 1(default) or 3. </li>
    <li><code>loop</code> - setting of 1 will cause the player to play the initial video again and again. Values: 0(default) or 1</li>
    <li><code>modestbranding</code> - lets you use a YouTube player that does not show a YouTube logo. Set the parameter value to 1 to prevent the YouTube logo from displaying in the control bar.</li>
    <li><code>origin</code> - provides an extra security measure for the IFrame API. If you are using the IFrame API, which means you are setting the enablejsapi parameter value to 1, you should always specify your domain as the origin parameter value.</li>
    <li><code>playlist</code> - value is a comma-separated list of video IDs to play. If you specify a value, the first video that plays will be the VIDEO_ID specified in the URL path, and the videos specified in the playlist parameter will play thereafter.</li>
    <li><code>playsinline</code> - controls whether videos play inline or fullscreen in an HTML5 player on iOS</li>
    <li><code>rel</code> indicates whether the player should show related videos when playback of the initial video ends. Values: 0 or 1(default)</li>
    <li><code>showinfo</code> - If you set the parameter value to 0, then the player will not display information like the video title and uploader before the video starts playing. Values: 0 or 1(default)</li>
    <li><code>start</code> causes the player to begin playing the video at the given number of seconds from the start of the video</li>
    <li><code>theme</code> - indicates whether the embedded player will display player controls (like a play button or volume control) within a dark or light control bar.</li>
</ul>

<a href="https://developers.google.com/youtube/player_parameters#Parameters">Reference</a>

<p><strong>Vimeo parameters</strong></p>

<ul>
    <li><code>autopause</code> - Enables or disables pausing this video when another video is played. Defaults to 1.</li>
    <li><code>autoplay</code> - Play the video automatically on load. Defaults to 0. Note that this won’t work on some devices.</li>
    <li><code>badge</code> - Enables or disables the badge on the video. Defaults to 1.</li>
    <li><code>byline</code> - Show the user’s byline on the video. Defaults to 1.</li>
    <li><code>color</code> - Specify the color of the video controls. Defaults to 00adef. Make sure that you don’t include the #.</li>
    <li><code>loop</code> - Play the video again when it reaches the end. Defaults to 0.</li>
    <li><code>player_id</code> - A unique id for the player that will be passed back with all Javascript API responses.</li>
    <li><code>portrait</code> - Show the user’s portrait on the video. Defaults to 1.</li>
    <li><code>title</code> - Show the title on the video. Defaults to 1.</li>
</ul>
<a href="http://developer.vimeo.com/player/embedding#universal-parameters">Reference</a>
        </td>
    </tr>

    <tr>
        <th><code>get_param($arg)</code></th>
        <td>Returns a parameter value. </td>
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