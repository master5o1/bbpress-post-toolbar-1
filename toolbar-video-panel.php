<?php

// Add panel entry to toolbar:
add_filter( 'bbp_5o1_toolbar_add_items' , array('bbp_5o1_video_panel', 'panel_entry'), 2 );

// Add shortcodes to bbPress replies text:
add_filter( 'bbp_get_reply_content', array('bbp_5o1_video_panel', 'add_video_shortcodes'), 0);

// Add shortcodes to WordPress pages/posts/etc:
add_shortcode( 'youtube', array('bbp_5o1_video_panel', 'youtube') ); // Keep the [youtube] shortcode for backwards-compat, and because why not?
add_shortcode( 'video', array ( 'bbp_5o1_video_panel', 'video_shortcode' ) );

class bbp_5o1_video_panel {

	function panel_entry($items) {
		$item['action'] = 'switch_panel';
		$item['inside_anchor'] = '<img src="' . plugins_url( '/images', __FILE__ ) . '/youtube.png" title="Video" alt="Video" />';
		
		$random_video[] = "http://www.youtube.com/watch?v=RSJbYWPEaxw"; // Hallelujah (Bon Jovi)
		$random_video[] = "http://www.youtube.com/watch?v=XCspzg9-bAg"; // Batroll'd
		$random_video[] = "http://www.youtube.com/watch?v=RZ-uV72pQKI"; // Pure Imagination
		$random_video[] = "http://www.youtube.com/watch?v=rgUrqGFxV3Q";	// Lights Out
		$random_video[] = "http://www.vimeo.com/26753142"; // Share the Rainbow
		$random_video[] = "http://megavideo.com/?v=LYWNYM1J"; // Megavideo something.
		
		$video_provider['YouTube'] = "http://www.youtube.com/";
		$video_provider['Dailymotion'] = "http://www.dailymotion.com/";
		$video_provider['Vimeo'] = "http://www.vimeo.com/";
		$video_provider['Metacafe'] = "http://www.metacafe.com/";
		$video_provider['Megavideo'] = "http://www.megavideo.com/";
		foreach ($video_provider as $key => $value) {
			$video_providers .= '<a href="' . $value . '" title="' . $key . '">' . $key . '</a> ';
		}
		$item['data'] = <<<HTML
<div style="width: 310px; display: inline-block;"><span>Online Video URL:</span><br />
<input style="display:inline-block;width:300px;" type="text" id="video_url" value="" /></div>
<a class="toolbar-apply" style="margin-top: 1.4em;" onclick="insert_panel('video');">Apply Link</a>
<p style="font-size: x-small;"><span>Supported video providers: ${video_providers}</span><br />
<span>Random Example: [video]${random_video[rand(0, (count($random_video)-1))]}[/video]</span></p>
HTML;
		$items[] = $item;
		return $items;
	}

	function add_video_shortcodes($content) {
		$shortcode_tags['youtube'] = array ( 'bbp_5o1_video_panel', 'youtube' );
		$shortcode_tags['video'] = array ( 'bbp_5o1_video_panel', 'video_shortcode' );
		
		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;
		$tagnames = array_keys($shortcode_tags);
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
		$pattern = '(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
		return preg_replace_callback('/'.$pattern.'/s', 'do_shortcode_tag', $content);
	}
	
	function video_shortcode( $atts = null, $content = null ) {
		$host = parse_url($content, PHP_URL_HOST);
		// YouTube:
		if ( $host == "youtube.com" || $host == "www.youtube.com" ) {
			return bbp_5o1_video_panel::youtube( $atts, $content );
		}
		// Dailymotion:
		if ( $host == "dailymotion.com" || $host == "www.dailymotion.com" ) {
			return bbp_5o1_video_panel::dailymotion( $atts, $content );
		}
		// Vimeo:
		if ( $host == "vimeo.com" || $host == "www.vimeo.com" ) {
			return bbp_5o1_video_panel::vimeo( $atts, $content );
		}
		// Metacafe:
		if ( $host == "metacafe.com" || $host == "www.metacafe.com" ) {
			return bbp_5o1_video_panel::metacafe( $atts, $content );
		}
		// Megavideo
		if ( $host == "megavideo.com" || $host == "www.megavideo.com" ) {
			return bbp_5o1_video_panel::megavideo( $atts, $content );
		}
		return $content;
	}
	
	function getDimensions($atts = null) {
		extract(shortcode_atts(array(
			'width' => 450,
			'height' => 286
		), $atts));
		return array( 'width' => $width, 'height' => $height );
	}
	
	function embed_iframe( $video_code, $atts = null ) {
		$dimensions = bbp_5o1_video_panel::getDimensions($atts);
		return '<iframe src="' . $video_code . '" style="margin:1.0em auto;" width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '" frameborder="0" allowfullscreen></iframe>';
	}
	
	function embed_flash ( $video_code, $flash_vars = null, $atts = null ) {
		$dimensions = bbp_5o1_video_panel::getDimensions($atts);
		return '<embed src="' . $video_code. '" width="' . $dimensions['width'] . '" height="' . $dimensions['height'] . '" flashVars="' . $flash_vars. '"  wmode="transparent" allowFullScreen="true" allowScriptAccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
	}
	
	// Video Provider Handlers Below:
	
	function youtube( $atts = null, $content = null ) {
		$url_query = explode('&', parse_url($content, PHP_URL_QUERY));
		foreach ($url_query as $query) {
			$q = explode('=', $query);
			$video_code[$q[0]] = $q[1];
		}
		return bbp_5o1_video_panel::embed_iframe( "http://www.youtube.com/embed/${video_code['v']}", $atts );
	}
	
	function dailymotion( $atts = null, $content = null ) {
		$video_code = explode( '_', parse_url( $content, PHP_URL_PATH ));
		return bbp_5o1_video_panel::embed_iframe( "http://www.dailymotion.com/embed${video_code[0]}", $atts );
	}
	
	function vimeo( $atts = null, $content = null ) {
		$video_code = parse_url( $content, PHP_URL_PATH );
		return bbp_5o1_video_panel::embed_iframe( "http://player.vimeo.com/video${video_code}?portrait=0", $atts );
	}
	
	function metacafe( $atts = null, $content = null ) {
		$video_code = parse_url( $content, PHP_URL_PATH );
		$video_code = explode( '/', $video_code );
		return bbp_5o1_video_panel::embed_flash( "http://www.metacafe.com/fplayer/${video_code[2]}/what_if.swf", "playerVars=showStats=yes|autoPlay=no", $atts );
	}
	
	function megavideo( $atts = null, $content = null ) {
		$url_query = explode('&', parse_url($content, PHP_URL_QUERY));
		foreach ($url_query as $query) {
			$q = explode('=', $query);
			$video_code[$q[0]] = $q[1];
		}
		return bbp_5o1_video_panel::embed_flash( "http://www.megavideo.com/v/${video_code['v']}", null, $atts );
	}

}

?>