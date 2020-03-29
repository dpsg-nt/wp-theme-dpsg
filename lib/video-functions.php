<?php
/**
 * Filters/resizes video embed codes.
 * @param  string  embed code provided by video embedding service
 * @param  boolean $wmode 
 * @param  mixed $width
 * @param  mixed $height
 * @return string
 */
function filter_video($html, $wmode = false, $width = false, $height = false) {
	$final_html = $html;
	if ($wmode) {
		if (strpos($final_html, 'iframe') !== FALSE && strpos($final_html, 'youtube') !== FALSE) {
			preg_match('~src="([^"]*)"~', $final_html, $src);
			preg_replace('~src="[^"]*"~', 'src="' . add_query_arg('wmode', 'transparent', $src[1]) . '"', $final_html);
		} else if (strpos($final_html, '<embed') !== FALSE ) {
			$final_html = str_replace('<embed', '<param name="wmode" value="transparent"></param><embed wmode="transparent" ', $final_html);
		}
	}
	if (is_numeric($width)) {
		$final_html = preg_replace('~width="[\d]+"~', 'width="'.$width.'"', $final_html);
	}
	if (is_numeric($height)) {
		$final_html = preg_replace('~height="[\d]+"~', 'height="'.$height.'"', $final_html);
	}
	
	return $final_html;
}

/**
 * Return the thumbnail src for Youtube and Vimeo videos
 * @param  string $embed_code  the full video embed code ( or YouTube video url )
 * @return string URL to the thumbnail
 */
function get_video_thumb($embed_code) {
	$return = '';
	if (preg_match('~youtube~', $embed_code)) {
		if (preg_match('~iframe~', $embed_code)) {
			preg_match('~src="[^"]*(embed/)([^\?&"]*)~', $embed_code, $video_id);
		} else {
			preg_match('~(v/|v=)(.*?)(\?|&|\z)~', $embed_code, $video_id);
		}
		$return = "http://img.youtube.com/vi/".$video_id[2]."/0.jpg";
	} elseif (preg_match('~vimeo~', $embed_code)) {
		if (preg_match('~iframe~', $embed_code)) {
			preg_match('~src="[^"]*video/([^?&]*)[^"]*"~', $embed_code, $video_id);
		} else {
			preg_match('~clip_id=(.*?)&~', $embed_code, $video_id);
		}
		$thumb = get_vimeo_thumb($video_id[1]);
		$return = $thumb[0]['thumbnail_medium'];
	}

	return $return;
}


/**
 * Return the thumbnail src for Vimeo videos
 * @param  mixed Vimeo video id
 * @return string URL to the thumbnail
 */
function get_vimeo_thumb($videoid) {
	$url = "http://vimeo.com/api/v2/video/".$videoid.".php";
	$cache_id = 'vimeocache::' . md5($url);
	$cache_lifetime = 300;
	
	$cached = get_option($cache_id, false);
	$has_cache = (bool)$cached;
	
	$is_expired = isset($cached['expires']) && time() > $cached['expires'];
	
	if (!$has_cache || $is_expired) {
		$data = wp_remote_get($url);
		if (!is_wp_error($data)) {
			$data = $data['body'];
			
			$video_cache = array(
				'data'=>$data,
				'expires'=>time() + $cache_lifetime,
			);
			update_option($cache_id, $video_cache);
		} else {
			$data = false;
		}
	} else {
		$data = $cached['data'];
	}
	
	$finaldata = unserialize($data);
	
	return $finaldata;
}

/**
 * Return a URL to an embedabble YouTube Video (the actual video file URL)
 * @param  string $video_url
 * @return string
 */
function get_youtube_video($video_url) {
	return preg_replace('~http:\/\/www\.youtube\.com\/watch(\?v=)?(.*&v=)(.*)~', 'http://www.youtube.com/v/$3', $video_url);
}

/**
 * Builds embed code from a video URL
 * @param  string  $video_url
 * @param  integer $width
 * @param  integer $height
 * @param  boolean $old_embed_code Whether to produce a mobile devices compitable, iframe-based, code(new code) or flash based embed code(old code)
 * @param  boolean $autoplay
 * @return [type]
 */
function create_embedcode($video_url, $width = 440, $height = 350, $old_embed_code = false, $autoplay = false) {
	$embed_code = '';
	
	if (preg_match('~youtube~i', $video_url)) {
		$embed_code =  create_youtube_embedcode($video_url, $width, $height, $old_embed_code, $autoplay);
	} else if (preg_match('~vimeo~i', $video_url)) {
		$embed_code = create_vimeo_embedcode($video_url, $width, $height, $autoplay);
	}
	
	return $embed_code;
}

/**
 * Generates an embedcode of a YouTube Video.
 * @param  string  $video_url URL of the playable YouTube Video (for example: http://www.youtube.com/watch?v=emMDmRtdP7w0)
 * @param  integer $width width of embedded video (optional)
 * @param  integer $height height of embedded video (optional)
 * @param  boolean $old_embed_code whether to use the old embedcode (optional). Uses @get_youtube_video to grab embeddable video URL
 * @param  boolean $autoplay
 * @return [type]
 */
function create_youtube_embedcode($video_url, $width = 440, $height = 350, $old_embed_code = false, $autoplay = false) {
	$width = intval($width);
	$height = intval($height);
	
	if (!$width) $width = 440;
	if (!$height) $height = 350;

	if ( $old_embed_code ) {
		$actual_file = get_youtube_video($video_url);
		return '<object width="' . $width . '" height="' . $height . '"><param name="movie" value="' . $actual_file . '"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><param name="wmode" value="transparent"><embed src="' . $actual_file . '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $width . '" height="' . $height . '" wmode="transparent"></embed></object>';
	} else {
		$autoplay = $autoplay ? 1 : 0;
		return '<iframe title="YouTube video player" width="' . $width . '" height="' . $height . '" src="' . add_query_arg(array('wmode'=>'transparent', 'autoplay'=>$autoplay), preg_replace('~watch?([^"]*)v=([^&]*).*~', 'embed/$2', $video_url)) . '" frameborder="0" allowfullscreen></iframe>';
	}
}


/**
 * Return an embedcode of a Vimeo Video.
 * @param  string  $video_url URL of the playable Vimeo Video (for example: http://vimeo.com/29081264)
 * @param  integer $width width of embedded video (optional)
 * @param  integer $height height of embedded video (optional)
 * @param  boolean $autoplay
 * @return [type]
 */
function create_vimeo_embedcode($video_url, $width = 440, $height = 350, $autoplay = false) {
	$width = intval($width);
	$height = intval($height);
	
	if (!$width) $width = 440;
	if (!$height) $height = 350;
	
	preg_match('~vimeo.com/([\d]+)~', $video_url, $video_id);
	
	return '<iframe src="http://player.vimeo.com/video/' . $video_id[1] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff' . ($autoplay ? '&amp;autoplay=autoplay' : '') . '" width="' . $width . '" height="' . $height . '" frameborder="0" webkitAllowFullScreen allowFullScreen></iframe>';
}

