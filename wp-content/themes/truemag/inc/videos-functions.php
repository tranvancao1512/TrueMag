<?php
/**
 * Plugin: JW Player for WordPress
 *
 * @link http://wordpress.org/plugins/jw-player-plugin-for-wordpress/
 * @since 1.0
 */ 
if(!function_exists('cactus_jwplayer7')) {
	function cactus_jwplayer7( $post_id = '' ){
        echo '<div class="jwplayer playlist-none cactus-jw7">';

        if($post_id)
            echo do_shortcode('[jw7-video p="' . $post_id . '"]');
        else
            echo do_shortcode('[jw7-video]');
		echo '</div>';
	}
}
if(class_exists('JWP6_Plugin')) {
	if (JWP6_USE_CUSTOM_SHORTCODE_FILTER)
		add_filter('tm_video_filter', array('JWP6_Shortcode', 'widget_text_filter'));
}
	
if(function_exists('jwplayer_tag_callback')) {
	add_filter('tm_video_filter', 'jwplayer_tag_callback');
}
/**
 * Determines if the specified post is a video post.
*/
require_once locate_template('/inc/classes/class.video-fetcher.php');
function tm_is_post_video($post_id = null){
	wp_reset_query();
	if($post_id){
		$post = get_post($post_id);
	}else{
		$post = get_post(get_the_ID());
	}
	
	if(!$post->ID){
		return false;
	}
	
	// Back compat, if the post has any video field, it also is a video. 
	$video_file = get_post_meta($post->ID, 'tm_video_file', true);
	$video_url = get_post_meta($post->ID, 'tm_video_url', true);
	$video_code = get_post_meta($post->ID, 'tm_video_code', true);
	// Post meta by Automatic Youtube Video Post plugin
	if(!empty($video_code) || !empty($video_url) || !empty($video_file))
		return $post->ID;
	
	return has_post_format('video', $post->ID);
}

if(!function_exists('tm_player')){
	function tm_player($player = '', $args = array()) {
        
		if(empty($player) || empty($args['files']))
			return;
		
		$defaults = array(
			'files' => array(),
			'poster' => '',
			'autoplay' => false
		);
		$args = wp_parse_args($args, $defaults);
		
		extract($args);
			
		/* JWPlayer */
		if($player == 'jwplayer') {
            cactus_jwplayer7( $args['post_id'] );
		}	
		/* FlowPlayer */
		elseif($player == 'flowplayer' && function_exists('flowplayer_content_handle')) {
			$atts = array(
				'splash' => $poster,
                'autoplay' => ($autoplay ? 'true' : 'false')
			);
			foreach($files as $key => $file) {
				$att = ($key == 0) ? 'src' : 'src'.$key;
				$atts[$att] = $file;
			}
			echo flowplayer_content_handle($atts, '', '');
			tm_flowplayer_script();
		}	
		elseif($player == 'videojs' && function_exists('videojs_html5_video_embed_handler')){
			$atts = array(
				'poster' => $poster,
                'autoplay' => $autoplay
			);
			foreach($files as $key => $file) {
				$att = ($key == 0) ? 'src' : 'src'.$key;
				if(strpos($file, '.mp4') !== false){$atts['mp4'] = $file;}
				if(strpos($file, '.webm') !== false){$atts['webm'] = $file;}
				if(strpos($file, '.ogg') !== false){$atts['ogg'] = $file;}
			}
			echo videojs_html5_video_embed_handler($atts);
		}
		/* WordPress Native Player: MediaElement */
		else {
            
			$atts = array();
			$atts['autoplay'] = $autoplay ? 'on' : 'off';
            // check if any of the files are self-hosted, then we assume that all files are self-hosted
            if(strpos($files[0], site_url()) !== false && count($files) == 1){
                $file = trim($files[0]);
                
                $type = wp_check_filetype($file, wp_get_mime_types());
                $atts[$type['ext']] = $file;
				
                echo wp_video_shortcode($atts);
            } else {
                ?>
                <video <?php echo $auto_play != '0' ? 'autoplay="autoplay"' : '';?> controls="controls">
                <?php
                foreach($files as $file) {
                    $file = trim($file);
                    
                    // check file type
                    $type = wp_check_filetype($file, wp_get_mime_types());
                    if($type['type'] == '') $type = 'video/mp4';
                    else $type = $type['type'];
                    ?>
                    <source src="<?php echo $file;?>" type='<?php echo $type;?>'/>
                    <?php
                }
                ?>
                </video>
                <?php
            }
		} 
	}
}
/**
 */
function tm_extend_video_html($html, $autoplay = false, $wmode = 'opaque') {
	$replace = false;
	if(function_exists('ot_get_option')){$color_bt = ot_get_option('main_color_1');}
	if($color_bt==''){$color_bt = 'f9c73d';}
	preg_match('/src=[\"|\']([^ ]*)[\"|\']/', $html, $matches);
	$color_bt = str_replace('#','',$color_bt);
	if(isset($matches[1])) {
		$url = $matches[1];
		
		// Vimeo
		if(strpos($url, 'vimeo.com')) {
			// Remove the title, byline, portrait on Vimeo video
			$url = add_query_arg(array('title'=>0,'byline'=>0,'portrait'=>0,'player_id'=>'player_1','color'=>$color_bt), $url);
			//
			// Set autoplay
			if($autoplay)
				$url = add_query_arg('autoplay', '1', $url);
			$replace = true;
		}
			
		// Youtube
		if(strpos($url, 'youtube.com')) {
			// Set autoplay
			if($autoplay)
				$url = add_query_arg('autoplay', '1', $url);
		
			// Add wmode
			if($wmode)
				$url = add_query_arg('wmode', $wmode, $url);
			
			// Disabled suggested videos on YouTube video when the video finishes
			$url = add_query_arg(array('rel'=>0), $url);
			// Remove top info bar
			$url = add_query_arg(array('showinfo'=>0), $url);
			$remove_annotations = ot_get_option('remove_annotations');
			if($remove_annotations!= '1'){
				$url = add_query_arg(array('iv_load_policy'=>3), $url);
			}
			// Remove YouTube Logo
			$url = add_query_arg(array('modestbranding'=>0), $url);
			// Remove YouTube video annotations
			// $url = add_query_arg('iv_load_policy', 3, $url);
			
			$replace = true;
		}
		
		if($replace) {
			$url = esc_attr($url);	
			$html = preg_replace('/src=[\"|\']([^ ]*)[\"|\']/', 'src="'.esc_url($url).'"', $html);
		}
	}
	
	return $html;
}



function tm_video($post_id, $autoplay = false, $embed_code = '') {
	$file = get_post_meta($post_id, 'tm_video_file', true);
	$files = !empty($file) ? explode("\n", $file) : array();
	$url = trim(get_post_meta($post_id, 'tm_video_url', true));
	
	if($embed_code == ''){
		$code = trim(get_post_meta($post_id, 'tm_video_code', true));
	} else {
		$code = $embed_code;
	}

	$id_vid = trim(get_post_meta($post_id, 'tm_video_id', true));
	global $link_arr;
	if(isset($_GET['link']) && $_GET['link'] != ''){
		$url = $link_arr[$_GET['link']]['url'];
	}
	if(strpos($url, 'youku.com')) {
		$regExp = '/(?<=sid\/|\/id_)[^.\/]+/'; preg_match($regExp, $url, $match);
		if(!isset($match[0])){ $match[0] ='';}
		echo '<iframe width="100%" height="100%" src="http://player.youku.com/embed/'.$match[0].'" frameborder=0 allowfullscreen></iframe>';
	}else
	if(strpos($url, 'vid.me')) {
		$id = substr($url, strrpos($url,'/')+1);
		echo '<iframe src="https://vid.me/e/'.$id.'?stats=1&amp;tools=1" width="854" height="480" frameborder="0" allowfullscreen webkitallowfullscreen mozallowfullscreen scrolling="no"></iframe>';
	}else
	// Define RELATIVE_PATH for Flowplayer in Ajax Call
	//if (!defined('RELATIVE_PATH') && defined('DOING_AJAX') && DOING_AJAX)
		//define('RELATIVE_PATH', plugins_url().'/fv-wordpress-flowplayer');
	if(!empty($id_vid)) {
		if(is_plugin_active( 'contus-video-gallery/hdflvvideoshare.php' )){
			if(is_numeric($id_vid)){
				echo do_shortcode( '[hdvideo  id="'.$id_vid.'"]');
			}else{
				echo do_shortcode( '[hdvideo  '.$id_vid.']');
			}
		}elseif(is_plugin_active( 'all-in-one-video-pack/all_in_one_video_pack.php' )){
			echo do_shortcode( '[kaltura-widget  '.$id_vid.']');
		}
	}
	elseif(!empty($code)) {
		if(strpos($code, 'fvplayer') !== false){
			$video = do_shortcode($code);
			echo $video;
		}else{
			$video = do_shortcode($code);
			$video = apply_filters('tm_video_filter', $video);
			$video = tm_extend_video_html($video, $autoplay);
			
			if(has_shortcode($code, 'fvplayer') || has_shortcode($code, 'flowplayer')){
				tm_flowplayer_script();
			}
			//Support meipai on mobile
			$detect = new Mobile_Detect;
			global $_device_, $_device_name_, $_is_retina_;
			$_device_ = $detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'pc';
			if(strpos($code, 'meitudata.com') !== false && ($detect->isMobile() || $detect->isTablet()) ){
				$atts = array();
				$url_ar = explode("&",urldecode($code));
				$fl = str_replace('vcastr_file=','', $url_ar[1]);
				$type = wp_check_filetype($fl, wp_get_mime_types());
				$atts[$type['ext']] = $fl;
				echo wp_video_shortcode($atts);
			}else{
				echo $video;
			}
		}
	} 
	elseif(!empty($url)) {

		$url = trim($url);
		$video = '';
		$youtube_player = '';
		
		// Youtube List
		if(preg_match('/http:\/\/www.youtube.com\/embed\/(.*)?list=(.*)/', $url)) {
			$video = '<iframe width="560" height="315" src="'.$url.'" frameborder="0" allowfullscreen></iframe>';
		
		} 
		// Youtube Player
		elseif((strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) && !empty($youtube_player)) {
			$args = array(
				'files' => array($url),
				'poster' => $poster,
				'autoplay' => $autoplay,
                'post_id' => $post_id
			);
			tm_player($youtube_player, $args);
		} 
		// WordPress Embeds
		else {
			global $wp_embed;
			$orig_wp_embed = $wp_embed;

			$wp_embed->post_ID = $post_id;
			$video = $wp_embed->autoembed($url);
			
			if(trim($video) == $url) {
				$wp_embed->usecache = false;
				$video = $wp_embed->autoembed($url);
			}
			
			$wp_embed->usecache = $orig_wp_embed->usecache;
			$wp_embed->post_ID = $orig_wp_embed->post_ID;
		}
		
		$video = tm_extend_video_html($video, $autoplay);

		echo $video;
	} 
	elseif(!empty($files)) {
		if(has_post_thumbnail($post_id) && $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'custom-large')){
			$poster = $thumb[0];
        }
		$player = ot_get_option('single_player_video');
		if($player == 'jwplayer' && class_exists('JWP6_Plugin')){
            // make sure file is self-hosted
            if(strpos($files[0], site_url()) !== false){
                // so ok, we use JWPlayer 7 to play
                $player ='jwplayer';
            } else {
                $player = 'mediaelement'; // fall back to native HTML5 video
            }
        }
		else if($player == ''){
			$player = 'mediaelement';
		}
		$args = array(
			'files' => $files,
			'poster' => $poster,
			'autoplay' => $autoplay,
            'post_id' => $post_id
		);
		
		/* FlowPlayer HTML5 */
		if($player == 'flowplayer_html5' && shortcode_exists( 'flowplayer' )) {
			$sc_pr ='';
			foreach($files as $item){
				if(strpos($item, 'mp4') !== false){
					if (class_exists('FLOWPLAYER6_VIDEO_PLAYER')) {
						$sc_pr .=  ' src="'.$item.'"';
					}else{
						$sc_pr .=  ' mp4="'.$item.'"';
					}
				}elseif(strpos($item, 'webm') !== false){
					$sc_pr .=  ' webm="'.$item.'"';
				}elseif(strpos($item, 'ogg') !== false){
					$sc_pr .=  ' ogg="'.$item.'"';
				}elseif(strpos($item, 'flv') !== false){
					$sc_pr .=  ' flv="'.$item.'"';
				}
			}
			$flowplayer_shortcode = '[flowplayer '.$sc_pr.' splash="'.$poster.'" autoplay="'.$autoplay.'"]';
			echo do_shortcode($flowplayer_shortcode);
		} else {
		 	tm_player($player, $args);
		}
	}
}

/**
 * Get HTML of video player for $video_url
 */
function tm_get_video_player( $video_id, $video_type, $post_id = '' ){
    $auto_play = ot_get_option('auto_play_video');
    
    /**
     * settings for YouTube
     */
    $youtube_quality = ot_get_option('youtube_quality', 'default');
    $onoff_related_yt = ot_get_option('onoff_related_yt');
    $onoff_html5_yt = ot_get_option('onoff_html5_yt');
    $using_yt_param = ot_get_option('using_yout_param');
    $onoff_info_yt = ot_get_option('onoff_info_yt');
    $allow_full_screen = ot_get_option('allow_full_screen');
    $allow_networking = ot_get_option('allow_networking');
    $remove_annotations = ot_get_option('remove_annotations');
    $interactive_videos = ot_get_option('interactive_videos');
    if ( $post_id != '' ) {
    	$youtube_start = get_post_meta($post_id, 'youtube_start', true);
    	$youtube_start = cactus_utube_conver_time( $youtube_start );
    	$youtube_end = get_post_meta($post_id, 'youtube_end', true);
    	$youtube_end = cactus_utube_conver_time( $youtube_end );
    } else {
    	$youtube_start = '';
    	$youtube_end = '';
    }
    
    ob_start();

    if($video_type == 'youtube'){
        if(ot_get_option('using_jwplayer_param') == 1){
            cactus_jwplayer7( $post_id );
        } else {
        ?>
        <div id="player-embed-<?php echo $post_id;?>"><!-- --></div>
        <script src="//www.youtube.com/player_api"></script>
        <script>
            
            
            var player;
            /**
             * This function is called only once when youtube_api is loaded
             */
            function onYouTubePlayerAPIReady() {
                player = new YT.Player('player-embed-<?php echo $post_id;?>', {
                  height: '506',
                  width: '900',
                  videoId: '<?php echo $video_id; ?>',
                  <?php if($onoff_related_yt != '0' || $onoff_html5_yt == '1' || $remove_annotations != '1' || $onoff_info_yt == '1'){ ?>
                  playerVars : {
                     <?php if($remove_annotations!= '1'){?>
                      iv_load_policy : 3,
                      <?php }
                      if($onoff_related_yt == '1'){?>
                      rel : 0,
                      <?php }
                      if($onoff_html5_yt == '1'){
                      ?>
                      html5 : 1,
                      <?php }
                      if($onoff_info_yt == '1'){
                      ?>
                      showinfo: 0,
                      <?php }?>
                      autohide: 1,
                      <?php 
                      if($youtube_start != ''){?>
                      start: <?php echo esc_attr($youtube_start);?>,
                      <?php }
                      if($youtube_end != ''){
                      ?>
                      end: <?php echo esc_attr($youtube_end);?>,
                      <?php }?>
                      <?php if(isset($match_pll[1]) && $match_pll != ''){?>
                      listType:'playlist',
                      list: '<?php echo $match_pll[1];?>',
                      <?php }?>
                  },
                  <?php }?>
                  events: {
                    'onReady': onPlayerReady
                  }
                });
            }
            
            /* make sure player is initiated. This is only a fix for ajax-called YouTube player */
            if(typeof YT !== 'undefined'){
                onYouTubePlayerAPIReady();            
            }

            // autoplay video
            function onPlayerReady(event) {
                event.target.setPlaybackQuality('<?php echo $youtube_quality;?>')
                <?php
                if($auto_play == '1'){?>
                    event.target.playVideo();
                <?php } ?>
            }
        </script>
        
        <?php
        }
    } elseif($video_type == 'vimeo'){
        ?>
        <div id="player-embed">
            <?php 
            
            global $wp_embed;
            $orig_wp_embed = $wp_embed;

            $wp_embed->post_ID = $post_id;
            
            $url = 'https://vimeo.com/' . $video_id;
            $video = $wp_embed->autoembed($url);
            
            if(trim($video) == $url) {
                $wp_embed->usecache = false;
                $video = $wp_embed->autoembed($url);
            }
            
            $wp_embed->usecache = $orig_wp_embed->usecache;
            $wp_embed->post_ID = $orig_wp_embed->post_ID;

            $video = tm_extend_video_html($video, $auto_play);
            
            echo $video;
            
            ?>
        </div>
        <script src="<?php echo get_template_directory_uri().'/';?>js/froogaloop2.min.js"></script>
		<script>
			jQuery(document).ready(function() {	
				jQuery('iframe').attr('id', 'player_1');
	
				var iframe = jQuery('#player_1')[0],
					player = $f(iframe),
					status = jQuery('.status_videos');
				
				player.addEvent('ready', function() {
					status.text('ready');
				});
				
				// Call the API when a button is pressed
				jQuery(window).load(function() {
					player.api(jQuery(this).text().toLowerCase());
				});
                
			});	
		</script>
        <?php
    } else {
        tm_video($post_id, $auto_play);
    }
    
    $html = ob_get_contents();
    ob_end_clean();
    
    return $html;
}

/*
 * Output Flowplayer script
 * 
 */
function tm_flowplayer_script(){
	if(!defined('DOING_AJAX') || !DOING_AJAX)
		return;

	echo '
	<script type="text/javascript">
		(function ($) {
			$(function(){typeof $.fn.flowplayer=="function"&&$("video").parent(".flowplayer").flowplayer()});
		}(jQuery));
	</script>
	';
	
	flowplayer_display_scripts();
}
/*
 * Output videojs script
 * 
 */
function tm_add_videojs_swf(){
		echo '
		<script type="text/javascript">
			videojs.options.flash.swf = "'. get_template_directory_uri().( '/js/videojs/video-js.swf') .'";
		</script>
		';
}
/*
Auto fetch data
*/
global $post_id;
global $post;
add_action( 'save_post', 'tm_save_postdata',999 );
function tm_save_postdata($post_id ){
	
	if('post' != get_post_type($post_id))
	return;
	$url = get_post_meta($post_id,'tm_video_url',true);
	$time = get_post_meta($post_id,'time_video',true);
	$data =  Video_Fetcher::fetchData($url,$fields = array(),$post_id);

	if($time == '' || $time == '00:00'){
		update_post_meta( $post_id, 'time_video', tm_secondsToTime($data['duration']) );
	}
}

add_filter( 'the_content', 'truemag_synchronize_views_count' );
// synchronize video views count if configured to do so
function truemag_synchronize_views_count($content){
    global $post;
    
    if ( is_single() && $post->post_status != 'draft' ) {
        $need_synchronize = false;

        $synchronize_views_count = function_exists('ot_get_option') ? ot_get_option('synchronize_views_count', '') : '';
        if($synchronize_views_count == 'auto'){
            $last_time_synced = get_post_meta($post->ID, '_last_time_synced', true);
            if(!$last_time_synced){
                $last_time_synced = 0;
            }
            
            if(time() - $last_time_synced > apply_filters('truemag_sync_views_count_internal', 30 * 60)){
                // only sync if last time sync was 30 minutes ago
                $need_synchronize = true;
                
                update_post_meta($post->ID, '_last_time_synced', time());
            }
        }

        if($need_synchronize){
            truemag_fetch_video_data($post->ID, true);
        }
    }
    
    return $content;
}

// fetch video data on call
function truemag_fetch_video_data($post_id, $views_count_only = false) {
    $url = get_post_meta($post_id, 'tm_video_url', true);
	$data =  Video_Fetcher::fetchData($url, $fields = array(), $post_id);

	$auto_get_info = get_post_meta($post_id, 'fetch_info', true);

	if(function_exists('ot_get_option')){
		$google_api_key = ot_get_option('google_api_key');
	}
    
	$channel = Video_Fetcher::extractChanneldFromURL($url);
    
	if(in_array($channel, array('vimeo', 'youtube', 'dailymotion'))){
		if($channel == 'youtube' && $google_api_key == ''){
            // if it is youtube URL, Google API key is needed
		} else {
			if(empty($auto_get_info) || $auto_get_info['0']!='1'){
				if(function_exists('ot_get_option')){
                    $get_info = ot_get_option('auto_get_info');
                }
                
                if($views_count_only){
                    if($get_info['3'] == '4'){
                        update_post_meta( $post_id, '_count-views_all', $data['viewCount']); // support BAW
                        
                        if(function_exists('tptn_default_options')){
                            // support Top 10;
                            $wpdb->update("{$wpdb->prefix}top_ten",array('cntaccess' => $view_all),
                                                                            array('postnumber' => $post->ID,
                                                                            'blog_id' => get_current_blog_id()),
                                                                    array('%d'), array('%d', '%d'));
                        }
                    }
                } else {
                    if($get_info['0'] == '1'){
                        $post['post_title'] =  $data['title'] ;
                        $post['post_name'] =  $data['title'] ;
                    }

                    if($get_info['1'] == '2'){
                        $post['post_content'] = $data['description'];
                    }
                    
                    // update the post, removing the action to prevent an infinite loop
                    remove_action( 'save_post', 'truemag_post_updated' );
                    wp_update_post($post);
                    add_action( 'save_post', 'truemag_post_updated' );
                    
                    if($get_info['2'] == '3'){
                        wp_set_post_tags( $post_id, $data['tags'], true );
                    }
                }
			}
		}
	}
}

add_action( 'save_post', 'truemag_post_updated');
// fetch video data on post update
function truemag_post_updated( $post_id ) {
	if('post' != get_post_type($post_id))
	return;

	$url = '';
	
	if( isset($_POST['tm_video_url']) )
    {
        $url = $_POST['tm_video_url'];
    }

	if($url == ''){$url = get_post_meta($post_id,'tm_video_url',true);}
	$data =  Video_Fetcher::fetchData($url,$fields = array(),$post_id);
	
    $post['ID'] = $post_id;

	$auto_get_info = get_post_meta($post_id, 'fetch_info', true);

	if(function_exists('ot_get_option')){
		$google_api_key = ot_get_option('google_api_key');
	}
    
    $channel = Video_Fetcher::extractChanneldFromURL($url);
    
	if(in_array($channel, array('vimeo', 'youtube', 'dailymotion'))){
		if($channel == 'youtube' && $google_api_key == ''){
            // if it is youtube URL, Google API key is needed
		} else {
			if(empty($auto_get_info) || $auto_get_info['0'] != '1'){
				if(function_exists('ot_get_option')){$get_info = ot_get_option('auto_get_info');}
                
				if(is_array($data)){
					if($get_info['0'] == '1' && $data['title'] != ''){
						$post['post_title'] =  $data['title'] ;
						$post['post_name'] =  $data['title'] ;
					}
					if($get_info['1'] == '2' && $data['description'] != ''){
						$post['post_content'] = $data['description'];
					}
					if($get_info['2'] == '3' && $data['tags'] != ''){
						wp_set_post_tags( $post_id, $data['tags'], true );
					}
					if($get_info['3'] == '4' && $data['viewCount'] != ''){
						update_post_meta( $post_id, '_count-views_all', $data['viewCount']);
                        $synchronize_views_count = function_exists('ot_get_option') ? ot_get_option('synchronize_views_count', '') : '';
						if(function_exists('tptn_default_options') && $synchronize_views_count != ''){
							// support Top 10;
							$_POST['total_count'] = $data['viewCount'];
						}
					}
				}
		
				// update the post, removing the action to prevent an infinite loop
				remove_action( 'save_post', 'truemag_post_updated' );
				wp_update_post($post);
				add_action( 'save_post', 'truemag_post_updated' );
			}
		}
	}
}
// End Fetch

// add default value = 0 for post in top-10 table when first create a post
if (is_plugin_active('top-10/top-10.php')) {
    function tm_add_default_viewcount_for_top10($post_id) {
        global $wpdb;
        $table_name = $wpdb->base_prefix . 'top_ten';
        $blog_id = get_current_blog_id();
        $resultscount = $wpdb->get_row($wpdb->prepare("SELECT postnumber, cntaccess FROM {$table_name} WHERE postnumber = %d AND blog_id = %d ", $post_id, $blog_id));
        if (!$resultscount) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES('%d', '%d', '%d') ON DUPLICATE KEY UPDATE cntaccess= %d ", $post_id, 0, $blog_id, 0));
        }
    }

    add_action('save_post', 'tm_add_default_viewcount_for_top10');
}

function auto_update_likes($post_id){
	if('post' != get_post_type())
	return;
	global $wpdb;
	$time = get_post_meta($post_id,'time_video',true);
	if($time == ''){
		$wpdb->insert(
			 "{$wpdb->prefix}wti_like_post", 
			 array(
				'post_id' => $post_id ,
				'value' => '0',
				'date_time' => date('Y-m-d H:i:s'),
			)
		);
	}
}
add_action( 'save_post', 'auto_update_likes' );

///already - vote
function TmAlreadyVoted($post_id, $ip = null) {
	global $wpdb;
	
	if (null == $ip) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	
	$tm_has_voted = $wpdb->get_var("SELECT value FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND ip = '$ip'");
	
	return $tm_has_voted;
}


add_filter('pre_post_title', 'wpse28021_mask_empty');
add_filter('pre_post_content', 'wpse28021_mask_empty');
function wpse28021_mask_empty($value)
{
    if ( empty($value) ) {
        return ' ';
    }
    return $value;
}

add_filter('wp_insert_post_data', 'wpse28021_unmask_empty');
function wpse28021_unmask_empty($data)
{
    if ( ' ' == $data['post_title'] ) {
        $data['post_title'] = '';
    }
    if ( ' ' == $data['post_content'] ) {
        $data['post_content'] = '';
    }
    return $data;
}


// * Convert seconds to timecode
// * http://stackoverflow.com/q/8273804
// 
function tm_secondsToTime($inputSeconds) 
{

    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // extract days
    $days = floor($inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // DAYS
    if( (int)$days == 0 )
        $days = '';
    elseif( (int)$days < 10 )
        $days = '0' . (int)$days . ':';
    else
        $days = (int)$days . ':';

    // HOURS
    if( (int)$hours == 0 )
        $hours = '';
    elseif( (int)$hours < 10 )
        $hours = '0' . (int)$hours . ':';
    else 
        $hours = (int)$hours . ':';

    // MINUTES
    if( (int)$minutes == 0 )
        $minutes = '00:';
    elseif( (int)$minutes < 10 )
        $minutes = '0' . (int)$minutes . ':';
    else 
        $minutes = (int)$minutes . ':';

    // SECONDS
    if( (int)$seconds == 0 )
        $seconds = '00';
    elseif( (int)$seconds < 10 )
        $seconds = '0' . (int)$seconds;

    return $days . $hours . $minutes . $seconds;
}

function tm_video_thumbnail_markup( $markup, $post_id ) {
	$markup .= ' ' . get_post_meta($post_id, 'tm_video_code', true);
	$markup .= ' ' . get_post_meta($post_id, 'tm_video_url', true);

	return $markup;
}
/**
 * Convert array to attributes string
 */
function arr2atts($array = array(), $include_empty_att = false) {
	if(empty($array))
		return;
	
	$atts = array();
	foreach($array as $key => $att) {
		if(!$include_empty_att && empty($att))
			continue;
		
		$atts[] = $key.'="'.$att.'"';
	}
	
	return ' '.implode(' ', $atts);
}
/**
 * Shorten long numbers
 */

if(!function_exists('tm_short_number')) {
function tm_short_number($n, $precision = 3) {
	$n = $n*1;
    if ($n < 1000000) {
        // Anything less than a million
        $n_format = number_format($n);
    } else if ($n < 1000000000) {
        // Anything less than a billion
        $n_format = number_format($n / 1000000, $precision) . 'M';
    } else {
        // At least a billion
        $n_format = number_format($n / 1000000000, $precision) . 'B';
    }

    return $n_format;
}
}

if(!function_exists('tm_build_multi_link')) {
function tm_build_multi_link($arr, $echo=false) {
	if($arr){
		ob_start(); ?>
        <div class="tm-multilink">
        <div class="multilink-table-wrap">
        <table class="table table-bordered"><tbody>
		<?php
		$link_arr = array();
		$link_count = 0;
		foreach($arr as $group){ ?>
			<tr>
                <td width="140" class="multilink-title"><?php echo isset($group['title'])?$group['title']:'' ?></td>
                <td>
                <?php 
				$multi_link = explode("\n",$group['links']); //raw array
				$temp_title = '';
				$link_number = 0;
				foreach($multi_link as $link){
					if(strpos($link, 'http') !== false){ //is a url
						$link_arr[]=array(
							'title' => $temp_title,
							'url' => $link
						);
						?>
                        <a class="multilink-btn btn btn-sm btn-default bordercolor2hover bgcolor2hover <?php if(isset($_GET['link']) && $_GET['link']==$link_count){ echo 'current-link'; } ?>" href="<?php echo esc_url(add_query_arg( 'link', $link_count, get_permalink(get_the_ID()) )); ?>"><i class="fa fa-play"></i> <?php echo $temp_title?$temp_title:__('Link ').($link_number+1); ?> </a>
                        <?php
						$temp_title = '';
						$link_count++;
						$link_number++;
					}else{
						$temp_title = $link;
					}
				}
				?>
                </td>
			</tr>
		<?php } ?>
        </tbody></table>
        </div>
        </div>
		<?php
		$html = ob_get_clean();
	}//if arr
	if($echo){
		echo $html;
	}else{
		return $link_arr;
	}
}
}