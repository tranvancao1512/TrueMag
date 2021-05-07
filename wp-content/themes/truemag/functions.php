<?php

if(!defined('PARENT_THEME')){
	define('PARENT_THEME','truemag');
}
if ( ! isset( $content_width ) ) $content_width = 900;
global $_theme_required_plugins;

/* Define list of recommended and required plugins */
$_theme_required_plugins = array(
        array(
            'name'      => 'TrueMAG - Member',
            'slug'      => 'ct-member',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/ct-member.zip',
        ),
        array(
            'name'      => 'TrueMAG - Movie',
            'slug'      => 'truemag-movie',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/truemag-movie.zip',
			'version' => '3.4.5.3'
        ),
		array(
            'name'      => 'Truemag - Playlist',
            'slug'      => 'cactus-video',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/cactus-video.zip',
			'version' => '1.1.3.3'
        ),
        array(
            'name'      => 'TrueMAG Rating',
            'slug'      => 'truemag-rating',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/truemag-rating.zip',
			'version' => '3.3.2.6'
        ),
        array(
            'name'      => 'TrueMAG - Shortcodes',
            'slug'      => 'truemag-shortcodes',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/truemag-shortcodes.zip',
			'version' => '3.3.4.3'
        ),
        array(
            'name'    => 'WPBakery Page Builder',
            'slug'    => 'js_composer',
            'required'  => true,
            'source'  => get_template_directory() . '/inc/plugins/packages/js_composer.zip',
			'version' => '6.6.0'
        ),
        array(
            'name'    => 'Advance Search Form',
            'slug'    => 'advance-search-form',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/advance-search-form.zip',
            'version' => '1.4.9'
        ),
		array(
            'name'    => 'TrueMAG - Sample Data',
            'slug'    => 'cactus-unyson-backup-restore',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/truemag-sampledata.zip',
			'version' => '1.2'
        ),
        array(
            'name'      => 'Video Thumbnails',
            'slug'      => 'video-thumbnails',
            'required'  => false,
            'source'  => get_template_directory() . '/inc/plugins/packages/video-thumbnails.zip',
            'version' => '2.12.4'
        ),
        array(
            'name'      => 'WP Pagenavi',
            'slug'      => 'wp-pagenavi',
            'required'  => false
        ),
        array(
            'name'      => 'BAW Post Views Count',
            'slug'      => 'baw-post-views-count',
            'required'  => false
        ),
		array(
            'name'      => 'WTI Like Post',
            'slug'      => 'wti-like-post',
            'required'  => false
        ),
		array(
            'name'      => 'Categories Images',
            'slug'      => 'categories-images',
            'required'  => false
        ),
		array(
            'name'      => 'Black Studio TinyMCE Widget',
            'slug'      => 'black-studio-tinymce-widget',
            'required'  => false
        ),
		array(
            'name'      => 'Contact Form 7',
            'slug'      => 'contact-form-7',
            'required'  => false
        ),
		array(
            'name'      => 'Simple Twitter Tweets',
            'slug'      => 'simple-twitter-tweets',
            'required'  => false
        ),
    );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //for check plugin status

if ( ! function_exists( 'ct_predl' ) ) {
    function ct_predl( $reply, $package, $updater ) {
        if ( ! preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package) ) return $package;
        else return $reply;
    }
}

if ( ! function_exists('ct_ta_install_filter') ) {
    add_action( 'init','ct_ta_install_filter' );
    function ct_ta_install_filter() {
        global $pagenow;
        if ( $pagenow == 'themes.php' && isset( $_GET['page'] ) && $_GET['page'] && ( $_GET['page'] == 'install-required-plugins' ||  $_GET['page'] == 'tgmpa-install-plugins' ) ) {
            add_filter( 'upgrader_pre_download' , 'ct_predl', 9999, 4 );
        }
    }
}
/**
 * Load core framework
 */
require_once 'inc/core/skeleton-core.php';
require_once 'inc/videos-functions.php';
/**
 * Load Theme Options settings
 */
add_filter('option_tree_settings_args','filter_option_tree_args');
function filter_option_tree_args($custom_settings){
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if(!is_plugin_active('video-ads/video-ads-management.php')){
		for($i = 0; $i < count($custom_settings['sections']); $i++){
			$section = $custom_settings['sections'][$i];
			if($section['id'] == 'video-ads'){
				unset($custom_settings['sections'][$i]);
				break;
			}
		}
	}

	return $custom_settings;
}

require_once 'inc/theme-options.php';

/**
 * Load Theme Core Functions, Hooks & Filter
 */
require_once 'inc/core/theme-core.php';

require_once 'inc/videos-functions.php';

add_action( 'after_setup_theme', 'tm_megamenu_require' );
function tm_megamenu_require(){
	if(!class_exists('MashMenuWalkerCore')){
		require_once 'inc/megamenu/megamenu.php';
	}
}

/*//////////////////////////////////////////////True-Mag////////////////////////////////////////////////*/

/*Remove filter*/
function remove_like_view_widget() {
	unregister_widget('MostLikedPostsWidget');
	unregister_widget('WP_Widget_Most_Viewed_Posts');
}
add_action( 'widgets_init', 'remove_like_view_widget' );

remove_filter('the_content', 'PutWtiLikePost');

/* Add filter to modify markup */
add_filter( 'video_thumbnail_markup', 'tm_video_thumbnail_markup', 10, 2 );

add_filter('widget_text', 'do_shortcode');
if(!function_exists('tm_get_default_image')){
	function tm_get_default_image(){
		return get_template_directory_uri().'/images/nothumb.jpg';
	}
}
//add prev and next link rel on head
add_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

//add author social link meta
add_action( 'show_user_profile', 'tm_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'tm_show_extra_profile_fields' );
function tm_show_extra_profile_fields( $user ) { ?>
	<h3><?php _e('Social informations','cactusthemes') ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="twitter">Twitter</label></th>
			<td>
				<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter your Twitter profile url.','cactusthemes')?></span>
			</td>
		</tr>
        <tr>
			<th><label for="facebook">Facebook</label></th>
			<td>
				<input type="text" name="facebook" id="facebook" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter your Facebook profile url.','cactusthemes')?></span>
			</td>
		</tr>
        <tr>
			<th><label for="flickr">Flickr</label></th>
			<td>
				<input type="text" name="flickr" id="flickr" value="<?php echo esc_attr( get_the_author_meta( 'flickr', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter your Flickr profile url.','cactusthemes')?></span>
			</td>
		</tr>
        <tr>
			<th><label for="google-plus">Google+</label></th>
			<td>
				<input type="text" name="google" id="google" value="<?php echo esc_attr( get_the_author_meta( 'google', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter your Google+ profile url.','cactusthemes')?></span>
			</td>
		</tr>
	</table>
<?php }
add_action( 'personal_options_update', 'tm_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'tm_save_extra_profile_fields' );
function tm_save_extra_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_user_meta( $user_id, 'twitter', $_POST['twitter'] );
	update_user_meta( $user_id, 'facebook', $_POST['facebook'] );
	update_user_meta( $user_id, 'flickr', $_POST['flickr'] );
	update_user_meta( $user_id, 'google', $_POST['google'] );
}

/**
 * get views count number of a post
 *
 * @return view count in string format of number (ex. 1,000,000)
 */
function truemag_get_view_count($post_id = 0){
    $post_id = $post_id ? $post_id : get_the_ID();

    $view_count = '';
    $synchronize_views_count = function_exists('ot_get_option') ? ot_get_option('synchronize_views_count', '') : '';

    if(function_exists('bawpvc_main') || $synchronize_views_count != ''){
        $view_str = str_replace(',','', get_post_meta($post_id, '_count-views_all', true));
        if($view_str != ''){
            $view_count = tm_number($view_str);
        }
    } elseif(function_exists('get_tptn_post_count_only')) {
        $view_count = get_tptn_post_count_only($post_id);
    }

    return $view_count;
}

//get video meta count
if(!function_exists('tm_html_video_meta')){
	function tm_html_video_meta($single = false, $label = false, $break = false, $listing_page = false, $post_id = false){
		global $post;
		$post_id = $post_id ? $post_id : get_the_ID();
		ob_start();

        $view_count = truemag_get_view_count($post_id);

		if($single == 'view'){
			echo '<span class="pp-icon"><i class="fa fa-eye"></i> ' . ($view_count ? $view_count : 0).'</span>';
		} elseif($single == 'like'){
			if(function_exists('GetWtiLikeCount')){
			echo '<span class="pp-icon iclike"><i class="fa fa-thumbs-up"></i> '.str_replace('+','',GetWtiLikeCount($post_id)).'</span>';
			}
		}elseif($single == 'comment'){
			echo '<span class="pp-icon"><i class="fa fa-comment"></i> '.get_comments_number($post_id).'</span>';
		}elseif($listing_page){
			if(ot_get_option('blog_show_meta_view',1)){?>
        	<span><i class="fa fa-eye"></i> <?php echo ($view_count?$view_count:0).($label?__('  Views'):'') ?></span><?php echo $break?'<br>':'' ?>
            <?php }
			if(ot_get_option('blog_show_meta_comment',1)){?>
            <span><i class="fa fa-comment"></i> <?php echo get_comments_number($post_id).($label?__('  Comments'):''); ?></span><?php echo $break?'<br>':'' ?>
            <?php }
			if(ot_get_option('blog_show_meta_like',1)&&function_exists('GetWtiLikeCount')){?>
            <span><i class="fa fa-thumbs-up"></i> <?php echo str_replace('+','',GetWtiLikeCount($post_id)).($label?__('  Likes'):''); ?></span>
		<?php
			}
		}else{?>
            <span><i class="fa fa-eye"></i> <?php echo ($view_count?$view_count:0).($label?__('  Views'):'') ?></span>
            <?php echo $break?'<br>':'' ?>
            <span><i class="fa fa-comment"></i> <?php echo get_comments_number($post_id).($label?__('  Comments'):''); ?></span>
            <?php echo $break?'<br>':'' ?>
            <?php if(function_exists('GetWtiLikeCount')){?>
            <span><i class="fa fa-thumbs-up"></i> <?php echo str_replace('+','',GetWtiLikeCount($post_id)).($label?__('  Likes'):''); ?></span>
            <?php }
		}
		$html = ob_get_clean();
		return $html;
	}
}
//quick view
if(!function_exists('quick_view_tm')){
	function quick_view_tm(){
        $html = '';

        $title = get_the_title();
        $title = strip_tags($title);

        $video_type = '';
        $video_url = get_post_meta(get_the_id(), 'tm_video_url', true);
        $video_file = '';
        $embed_code = '';
        $link = ''; // if video is not youtube or vimeo, load video URL in an iframe
        $video_id = '';

        if($video_url != ''){
            $video_type = Video_Fetcher::extractChanneldFromURL($video_url);

            if($video_type == 'vimeo'){
                $video_id = Video_Fetcher::extractIDFromURL($video_url);
                $video_url = str_replace('http://vimeo.com/', 'http://player.vimeo.com/video/', $video_url);
            } elseif($video_type == 'youtube'){
                $video_id = Video_Fetcher::extractIDFromURL($video_url);
                $video_url = '//www.youtube.com/embed/' . $video_id . '?rel=0&amp;wmode=transparent';
            } else {
                $link = $video_url;
            }
        } else {
            // check if embed code is used
            $embed_code = get_post_meta(get_the_id(), 'tm_video_code', true);

            $video_type = 'embed';

            if($embed_code == ''){
                $video_type = 'file';

                // check if video file is used
                $file = get_post_meta(get_the_id(), 'tm_video_file', true);
                $files = !empty($file) ? explode("\n", $file) : array();
                $video_file = isset($files[0]) ? $files[0] : '';

                if($video_file == ''){
                    $video_type = '';
                }
            }
        }

        if($video_type != '') {
            if($link == ''){
                $link = $video_url;

                if($video_type == 'file'){
                    $link = $video_file;
                }
            }

            $html .= '<div><a href=\'' . esc_url($link) . '\' data-post-id=\'' . get_the_ID() . '\'data-video-id=\'' . $video_id . '\' data-video-type=\'' . $video_type . '\' class=\'quickview\'  title=\''.esc_attr($title).'\' data-embed=\''. esc_attr($embed_code) .'\' data-url=\''.esc_url(get_permalink()).'\' id=\'light_box\'>'.__('Quick View','cactusthemes').'</a></div>';
        }

        return $html;
	}
}
if(!function_exists('tm_post_rating')){
	function tm_post_rating($post_id,$get=false){
		$rating = round((int)get_post_meta($post_id, 'taq_review_score', true)/10,1);
		if($rating){
			$rating = number_format($rating,1,'.','');
		}
		if($get){
			return $rating;
		}elseif($rating){
			$html='<span class="rating-bar bgcolor2">'.$rating.'</span>';
			return $html;
		}
	}
}

/**
 * Sets up theme defaults and registers the various WordPress features that
 * theme supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails, automatic feed links,
 * 	custom background, and post formats.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 */
function cactusthemes_setup() {
	/*
	 * Makes theme available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 */
	load_theme_textdomain( 'cactusthemes', get_template_directory() . '/languages' );

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );
	// This theme supports a variety of post formats.

	add_theme_support( 'post-formats', array( 'gallery', 'video', 'audio' ) );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu( 'main-navigation', __( 'Main Navigation', 'cactusthemes' ) );
	register_nav_menu( 'footer-navigation', __( 'Footer Navigation', 'cactusthemes' ) );

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 624, 9999 ); // Unlimited height, soft crop

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-lightbox' );
}
add_action( 'after_setup_theme', 'cactusthemes_setup', 10 );

/**
 * Enqueues scripts and styles for front-end.
 */
function cactusthemes_scripts_styles() {
	global $wp_styles;

	/*
	 * Loads our main javascript.
	 */

	wp_enqueue_script( 'jquery');
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array(), '', true );
	wp_enqueue_script( 'caroufredsel', get_template_directory_uri() . '/js/jquery.caroufredsel-6.2.1.min.js', array('jquery'), '', true );
	if(ot_get_option( 'nice-scroll', 'off')=='on'){
		wp_enqueue_script( 'smooth-scroll', get_template_directory_uri() . '/js/SmoothScroll.js', array('jquery'), '', true);
	}
	wp_enqueue_script( 'touchswipe', get_template_directory_uri() . '/js/helper-plugins/jquery.touchSwipe.min.js', array('caroufredsel'), '', true );
	wp_enqueue_script( 'hammer', get_template_directory_uri() . '/js/jquery.hammer.js', array('jquery'), '', true );

	wp_enqueue_script( 'template', get_template_directory_uri() . '/js/template.js', array('jquery'), '', true );

    $js_params = array( 'lang' => array(), 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
    $js_params['lang']['please_choose_category'] = esc_html__('Please choose a category', 'cactusthemes');
    wp_localize_script( 'template', 'truemag', $js_params  );

	wp_enqueue_script( 'colorbox', get_template_directory_uri() . '/js/colorbox/jquery.colorbox-min.js', array('jquery'), '', true );
	wp_register_script( 'js-scrollbox', get_template_directory_uri() . '/js/jquery.scrollbox.js', array(), '', true );

	wp_enqueue_script( 'tooltipster', get_template_directory_uri() . '/js/jquery.tooltipster.js', array(), '', true );
	wp_enqueue_script( 'malihu-scroll', get_template_directory_uri() . '/js/malihu-scroll/jquery.mCustomScrollbar.concat.min.js', array(), '', true );
	//wp_enqueue_script( 'waypoints' );
	/*
	 * videojs.
	 */
	wp_register_script( 'videojs-cactus', get_template_directory_uri() . '/js/videojs/video.js' , array('jquery'), '', false );
	wp_enqueue_script( 'videojs-cactus' );
	wp_register_style( 'videojs-cactus', get_template_directory_uri() . '/js/videojs/video-js.min.css');
	wp_enqueue_style( 'videojs-cactus' );
	/*
	 * Loads our main stylesheet.
	 */
	$tm_all_font = array();
	$rm_sp = ot_get_option('text_font', 'Open Sans');
	if(ctype_space($rm_sp) == false){
		if($rm_sp != 'Custom Font'){
			$tm_all_font[] = $rm_sp;
		}
		$all_font = implode('|',$tm_all_font);
		wp_enqueue_style( 'google-font', '//fonts.googleapis.com/css?family='.$all_font );
	}

	wp_enqueue_style( 'colorbox', get_template_directory_uri() . '/js/colorbox/colorbox.css');
	wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css');
	wp_enqueue_style( 'tooltipster', get_template_directory_uri() . '/css/tooltipster.css');

	wp_enqueue_style( 'fontastic-entypo', get_template_directory_uri().'/fonts/fontastic-entypo.css' );
	wp_enqueue_style( 'google-font-Oswald', '//fonts.googleapis.com/css?family=Oswald:300' );
	wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/style.css');
	if (class_exists('Woocommerce')) {
		wp_enqueue_style( 'tmwoo-style', get_template_directory_uri() . '/css/tm-woo.css');
	}
	if(ot_get_option( 'flat-style')){
		wp_enqueue_style( 'flat-style', get_template_directory_uri() . '/css/flat-style.css');
	}
	// wp_deregister_style( 'font-awesome' );
	wp_enqueue_style( 'font-awesome-2', get_template_directory_uri() .'/css/font-awesome/css/font-awesome.min.css');
	wp_enqueue_style( 'custom-css', get_template_directory_uri() . '/css/custom.css.php');
	if(ot_get_option( 'right_to_left', 0)){
		wp_enqueue_style( 'rtl', get_template_directory_uri() . '/rtl.css');
	}
	if(ot_get_option( 'responsive', 1)!=1){
		wp_enqueue_style( 'no-responsive', get_template_directory_uri() . '/css/no-responsive.css');
	}
	if(is_singular() ) wp_enqueue_script( 'comment-reply' );
	if(is_plugin_active( 'buddypress/bp-loader.php' )){
		wp_enqueue_style( 'truemag-bp', get_template_directory_uri() . '/css/tm-buddypress.css');
	}
	if(is_plugin_active( 'bbpress/bbpress.php' )){
		wp_enqueue_style( 'truemag-bb', get_template_directory_uri() . '/css/tm-bbpress.css');
	}
	wp_enqueue_style( 'truemag-icon-blg', get_template_directory_uri() . '/css/justVectorFont/stylesheets/justVector.css');
	wp_enqueue_style( 'malihu-scroll-css', get_template_directory_uri() . '/js/malihu-scroll/jquery.mCustomScrollbar.min.css');

}
add_action( 'wp_enqueue_scripts', 'cactusthemes_scripts_styles' );

/*
 * enqueue admin scripts
 */
function cactusthemes_admin_scripts_styles() {
    wp_enqueue_style( 'font-awesome-2', get_template_directory_uri() .'/css/font-awesome/css/font-awesome.min.css');
}
add_action( 'admin_enqueue_scripts', 'cactusthemes_admin_scripts_styles' );

add_action( 'wp_ajax_get_video_player', 'truemag_ajax_load_video_player' );
add_action( 'wp_ajax_nopriv_get_video_player', 'truemag_ajax_load_video_player' );

/**
 * get video player HTML
 */
function truemag_ajax_load_video_player(){
    $video_id = $_POST['video_id'];
    $video_type = $_POST['video_type'];
    $post_id = $_POST['post_id'];

    $html = tm_get_video_player($video_id, $video_type, $post_id);

    echo $html;

    wp_die();
}

add_action('wp_head','cactus_wp_head',100);
if(!function_exists('cactus_wp_head')){
	function cactus_wp_head(){
		echo '<!-- custom css -->
				<style type="text/css">';

		require get_template_directory() . '/css/custom.css.php';

		echo '</style>
			<!-- end custom css -->';
	}
}

/**
 * Registers our main widget area and the front page widget areas.
 *
 * @since Twenty Twelve 1.0
 */
function cactusthemes_widgets_init() {
	$rtl = function_exists('ot_get_option') ? ot_get_option( 'righttoleft', 0) : 0;

	register_sidebar( array(
		'name' => __( 'Main Sidebar', 'cactusthemes' ),
		'id' => 'main_sidebar',
		'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));

	register_sidebar( array(
		'name' => __( 'Home Sidebar', 'cactusthemes' ),
		'id' => 'home_sidebar',
		'description' => __('Sidebar in home page. If empty, main sidebar will be used', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));

	register_sidebar( array(
		'name' => __( 'Main Top Sidebar', 'cactusthemes' ),
		'id' => 'maintop_sidebar',
		'description' => __( 'Sidebar in top of site, be used if there are no slider ', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="widget-title maincolor1">',
		'after_title' => '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Headline Sidebar', 'cactusthemes' ),
		'id' => 'headline_sidebar',
		'description' => __( '', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="headline-widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	));
	register_sidebar( array(
		'name' => __( 'Pathway Sidebar', 'cactusthemes' ),
		'id' => 'pathway_sidebar',
		'description' => __( 'Replace Pathway (
		crumbs) with your widgets', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="pathway-widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	));
	register_sidebar( array(
		'name' => __( 'Search Box Sidebar', 'cactusthemes' ),
		'id' => 'search_sidebar',
		'description' => __( '', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="heading-search-widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	));
	register_sidebar( array(
		'name' => __( 'User Submit Video Sidebar', 'cactusthemes' ),
		'id' => 'user_submit_sidebar',
		'description' => __( 'Sidebar in popup User submit video', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="widget-title maincolor2">',
		'after_title' => '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Footer Sidebar', 'cactusthemes' ),
		'id' => 'footer_sidebar',
		'description' => __( '', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget col-md-3 col-sm-6 %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="widget-title maincolor1">',
		'after_title' => '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Footer 404 page Sidebar', 'cactusthemes' ),
		'id' => 'footer_404_sidebar',
		'description' => __( '', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget col-md-3 col-sm-6 %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="widget-title maincolor1">',
		'after_title' => '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Blog Sidebar', 'cactusthemes' ),
		'id' => 'blog_sidebar',
		'description' => __( 'Sidebar in blog, category (blog) page. If there is no widgets, Main Sidebar will be used ', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Video listing Sidebar', 'cactusthemes' ),
		'id' => 'video_listing_sidebar',
		'description' => __( 'Sidebar in blog, category (video) page. If there is no widgets, Main Sidebar will be used ', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Single Blog Sidebar', 'cactusthemes' ),
		'id' => 'single_blog_sidebar',
		'description' => __( 'Sidebar in single post page. If there is no widgets, Main Sidebar will be used ', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Single Video Sidebar', 'cactusthemes' ),
		'id' => 'single_video_sidebar',
		'description' => __( 'Sidebar in single Video post page. If there is no widgets, Main Sidebar will be used ', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Search page Sidebar', 'cactusthemes' ),
		'id' => 'search_page_sidebar',
		'description' => __( 'Appears on Search result page', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'Single Page Sidebar', 'cactusthemes' ),
		'id' => 'single_page_sidebar',
		'description' => __( 'Sidebar in single page. If there is no widgets, Main Sidebar will be used ', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	if(function_exists('is_woocommerce')){
		register_sidebar( array(
			'name' => __( 'Woocommerce Single Product Sidebar', 'cactusthemes' ),
			'id' => 'single_woo_sidebar',
			'description' => __( 'Sidebar in single product. If there is no widgets, Main Sidebar will be used ', 'cactusthemes' ),
			'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
			'after_widget' => '</div>',
			'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
			'after_title' => $rtl ? '</h2>' : '</h2>',
		));
		register_sidebar( array(
			'name' => __( 'Woocommerce Shop Page Sidebar', 'cactusthemes' ),
			'id' => 'shop_sidebar',
			'description' => __( 'Sidebar in shop page. If there is no widgets, Main Sidebar will be used ', 'cactusthemes' ),
			'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
			'after_widget' => '</div>',
			'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
			'after_title' => $rtl ? '</h2>' : '</h2>',
		));
	}
if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
	//buddyPress
	register_sidebar( array(
		'name' => __( 'BuddyPress Sidebar', 'cactusthemes' ),
		'id' => 'bp_sidebar',
		'description' => __( 'Sidebar in BuddyPress Page.', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'BuddyPress Sitewide Activity Sidebar', 'cactusthemes' ),
		'id' => 'bp_activity_sidebar',
		'description' => __( 'Sidebar in BuddyPress Sitewide Activity Page. If there is no widgets, BuddyPress Sidebar will be used', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'BuddyPress Sitewide Members Sidebar', 'cactusthemes' ),
		'id' => 'bp_member_sidebar',
		'description' => __( 'Sidebar in BuddyPress Sitewide Member Page. If there is no widgets, BuddyPress Sidebar will be used', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'BuddyPress Sitewide Groups Sidebar', 'cactusthemes' ),
		'id' => 'bp_group_sidebar',
		'description' => __( 'Sidebar in BuddyPress Sitewide Groups Page. If there is no widgets, BuddyPress Sidebar will be used', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'BuddyPress Single Members Sidebar', 'cactusthemes' ),
		'id' => 'bp_single_member_sidebar',
		'description' => __( 'Sidebar in BuddyPress Single Member Page. If there is no widgets, BuddyPress Sidebar will be used', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'BuddyPress Single Groups Sidebar', 'cactusthemes' ),
		'id' => 'bp_single_group_sidebar',
		'description' => __( 'Sidebar in BuddyPress Single Groups Page. If there is no widgets, BuddyPress Sidebar will be used', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
	register_sidebar( array(
		'name' => __( 'BuddyPress Register Sidebar', 'cactusthemes' ),
		'id' => 'bp_register_sidebar',
		'description' => __( 'Sidebar in BuddyPress Register Page. If there is no widgets, BuddyPress Sidebar will be used', 'cactusthemes' ),
		'before_widget' => '<div id="%1$s" class="widget widget-border %2$s">',
		'after_widget' => '</div>',
		'before_title' => $rtl ? '<h2 class="widget-title maincolor2">' : '<h2 class="widget-title maincolor2">',
		'after_title' => $rtl ? '</h2>' : '</h2>',
	));
}
}
add_action( 'widgets_init', 'cactusthemes_widgets_init' );

add_image_size('thumb_139x89',139,89, true); //widget
add_image_size('thumb_365x235',365,235, true); //blog
add_image_size('thumb_196x126',196,126, true); //cat carousel, related
add_image_size('thumb_520x293',520,293, true); //big carousel 16:9
add_image_size('thumb_260x146',260,146, true); //metro carousel 16:9
add_image_size('thumb_356x200',356,200, true); //metro carousel 16:9 bigger
add_image_size('thumb_370x208',370,208, true); //scb grid 16:9
add_image_size('thumb_180x101',180,101, true); //scb small
add_image_size('thumb_130x73',130,73, true); //mobile
add_image_size('thumb_748x421',748,421, true); //classy big
add_image_size('thumb_72x72',72,72, true); //classy thumb

add_image_size('thumb_358x242',358,242, true); //shop

// Hook widget 'SEARCH'
add_filter('get_search_form', 'cactus_search_form');
function cactus_search_form($text) {
	$text = str_replace('value=""', 'placeholder="'.__("SEARCH",'cactusthemes').'"', $text);
    return $text;
}

/* Display Facebook and Google Plus button */
function gp_social_share($post_ID){
if(ot_get_option('social_like',1)){
?>
<div id="social-share">
    &nbsp;
    <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post_ID)) ?>&amp;width=450&amp;height=21&amp;colorscheme=light&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;send=false&amp;appId=498927376861973" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:85px; height:21px;" allowTransparency="true"></iframe>
    &nbsp;
    <div class="g-plusone" data-size="medium"></div>
    <script type="text/javascript">
      window.___gcfg = {lang: 'en-GB'};
      (function() {
        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
        po.src = 'https://apis.google.com/js/plusone.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
      })();
    </script>
</div>
<?php }
}

/* Display Icon Links to some social networks */
function tm_social_share(){ ?>
<div class="tm-social-share">
	<?php if(ot_get_option('share_facebook')){ ?>
	<a class="social-icon s-fb" title="<?php _e('Share on Facebook','cactusthemes'); ?>" href="#" target="_blank" rel="nofollow" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href),'facebook-share-dialog','width=626,height=436');return false;"><i class="fab fa-facebook"></i></a>
    <?php } ?>
    <?php if(ot_get_option('share_twitter')){ ?>
    <a class="social-icon s-tw" href="#" title="<?php _e('Share on Twitter','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('http://twitter.com/share?text=<?php echo urlencode(html_entity_decode(get_the_title(get_the_ID()), ENT_COMPAT, 'UTF-8')); ?>&amp;url=<?php echo urlencode(get_permalink(get_the_ID())); ?>','twitter-share-dialog','width=626,height=436');return false;"><i class="fab fa-twitter"></i></a>
    <?php } ?>
    <?php if(ot_get_option('share_linkedin')){ ?>
    <a class="social-icon s-lk" href="#" title="<?php _e('Share on LinkedIn','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo urlencode(get_permalink(get_the_ID())); ?>&amp;title=<?php echo urlencode(html_entity_decode(get_the_title(get_the_ID()), ENT_COMPAT, 'UTF-8')); ?>&amp;source=<?php echo urlencode(get_bloginfo('name')); ?>','linkedin-share-dialog','width=626,height=436');return false;"><i class="fab  fa-linkedin"></i></a>
    <?php } ?>
    <?php if(ot_get_option('share_tumblr')){ ?>
    <a class="social-icon s-tb" href="#" title="<?php _e('Share on Tumblr','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('http://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink(get_the_ID())); ?>&amp;name=<?php echo urlencode(html_entity_decode(get_the_title(get_the_ID()), ENT_COMPAT, 'UTF-8')); ?>','tumblr-share-dialog','width=626,height=436');return false;"><i class="fab fa-tumblr"></i></a>
    <?php } ?>
    <?php if(ot_get_option('share_google_plus')){ ?>
    <a class="social-icon s-gg" href="#" title="<?php _e('Share on Google Plus','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('https://plus.google.com/share?url=<?php echo urlencode(get_permalink(get_the_ID())); ?>','googleplus-share-dialog','width=626,height=436');return false;"><i class="fab fa-google-plus"></i></a>
    <?php } ?>

    <?php if(ot_get_option('share_blogger')){ ?>
    <a class="social-icon s-bl" href="#" title="<?php _e('Share on Blogger','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('https://www.blogger.com/blog-this.g?u=<?php echo urlencode(get_permalink(get_the_ID())); ?>&amp;n=<?php echo urlencode(html_entity_decode(get_the_title(get_the_ID()), ENT_COMPAT, 'UTF-8')); ?>&amp;t=<?php echo urlencode(get_the_excerpt()); ?>','blogger-share-dialog','width=626,height=436');return false;"><i id="jv-blogger" class="jv-blogger"></i></a>
    <?php } ?>
    <?php if(ot_get_option('share_reddit')){ ?>
    <a class="social-icon s-rd" href="#" title="<?php _e('Share on Reddit','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('//www.reddit.com/submit?url=<?php echo urlencode(get_permalink(get_the_ID())); ?>','reddit-share-dialog','width=626,height=436');return false;"><i class="fab fa-reddit"></i></a>
    <?php } ?>

    <?php if(ot_get_option('share_vk')){ ?>
    <a class="social-icon s-vk" href="#" title="<?php _e('Share on Vk','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('http://vkontakte.ru/share.php?url=<?php echo urlencode(get_permalink(get_the_ID())); ?>','vk-share-dialog','width=626,height=436');return false;"><i class="fab fa-vk"></i></a>
    <?php } ?>


    <?php if(ot_get_option('share_pinterest')){ ?>
    <a class="social-icon s-pin" href="#" title="<?php _e('Pin this','cactusthemes'); ?>" rel="nofollow" target="_blank" onclick="window.open('//pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink(get_the_ID())) ?>&amp;media=<?php echo urlencode(wp_get_attachment_url( get_post_thumbnail_id(get_the_ID()))); ?>&amp;description=<?php echo urlencode(html_entity_decode(get_the_title(get_the_ID()), ENT_COMPAT, 'UTF-8')); ?>','pin-share-dialog','width=626,height=436');return false;"><i class="fab fa-pinterest"></i></a>
    <?php } ?>
    <?php if(ot_get_option('share_email')){ ?>
    <a class="social-icon s-em" href="mailto:?subject=<?php echo urlencode(html_entity_decode(get_the_title(get_the_ID()), ENT_COMPAT, 'UTF-8')); ?>&amp;body=<?php echo urlencode(get_permalink(get_the_ID())) ?>" title="<?php _e('Email this','cactusthemes'); ?>"><i class="fas fa-envelope"></i></a>
    <?php } ?>
</div>
<?php }

require_once 'inc/category-metadata.php';
require_once 'inc/google-adsense-responsive.php';

/*facebook comment*/
if(!function_exists('tm_update_fb_comment')){
	function tm_update_fb_comment(){
		if(is_plugin_active('facebook/facebook.php')&&get_option('facebook_comments_enabled')&&is_single()){
			global $post;
			//$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			if(class_exists('Facebook_Comments')){
				//$comment_count = Facebook_Comments::get_comments_number_filter(0,$post->ID);
				$comment_count = get_comments_number($post->ID);
			}else{
				$actual_link = get_permalink($post->ID);
				$fql  = "SELECT url, normalized_url, like_count, comment_count, ";
				$fql .= "total_count, commentsbox_count, comments_fbid FROM ";
				$fql .= "link_stat WHERE url = '".$actual_link."'";
				$apifql = "https://api.facebook.com/method/fql.query?format=json&query=".urlencode($fql);
				$json = file_get_contents($apifql);
				//print_r( json_decode($json));
				$link_fb_stat = json_decode($json);
				$comment_count = $link_fb_stat[0]->commentsbox_count?$link_fb_stat[0]->commentsbox_count:0;
			}
			update_post_meta($post->ID, 'custom_comment_count', $comment_count);
		}elseif(is_plugin_active('disqus-comment-system/disqus.php')&&is_single()){
			global $post;
			echo '<a href="'.get_permalink($post->ID).'#disqus_thread" id="disqus_count" class="hidden">comment_count</a>';
			?>
            <script type="text/javascript">
			/* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
			var disqus_shortname = '<?php echo get_option('disqus_forum_url','testtruemag') ?>'; // required: replace example with your forum shortname
			/* * * DON'T EDIT BELOW THIS LINE * * */
			(function () {
			var s = document.createElement('script'); s.async = true;
			s.type = 'text/javascript';
			s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
			(document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
			}());
			//get comments number
			jQuery(window).load(function(e) {
                var str = jQuery('#disqus_count').html();
				var pattern = /[0-9]+/;
				var matches = str.match(pattern);
				matches = (matches)?matches[0]:0;
				if(!isNaN(parseFloat(matches)) && isFinite(matches)){ //is numberic
					var param = {
						action: 'tm_disqus_update',
						post_id:<?php echo $post->ID ?>,
						count:matches,
					};
					jQuery.ajax({
						type: "GET",
						url: "<?php echo home_url('/'); ?>wp-admin/admin-ajax.php",
						dataType: 'html',
						data: (param),
						success: function(data){
							//
						}
					});
				}//if numberic
			});
			</script>
            <?php
		}
	}
}

add_action('wp_footer', 'tm_update_fb_comment', 100);
//ajax update disqus count
if(!function_exists('tm_disqus_update')){
	function tm_disqus_update(){
		if(isset($_GET['post_id'])){
			update_post_meta($_GET['post_id'], 'custom_comment_count', $_GET['count']?$_GET['count']:0);
		}
	}
}
add_action("wp_ajax_tm_disqus_update", "tm_disqus_update");
add_action("wp_ajax_nopriv_tm_disqus_update", "tm_disqus_update");

//hook for get disqus count
if(!function_exists('tm_get_disqus_count')){
	function tm_get_disqus_count($count, $post_id){
		if(is_plugin_active('disqus-comment-system/disqus.php')){
			$return = get_post_meta($post_id,'custom_comment_count',true);
			return $return?$return:0;
		}else{
			return $count;
		}
	}
}
add_filter( 'get_comments_number', 'tm_get_disqus_count', 10, 2 );

if(!function_exists('tm_breadcrumbs')){
	function tm_breadcrumbs(){
		/* === OPTIONS === */
		$text['home']     = __('Home','cactusthemes'); // text for the 'Home' link
		$text['category'] = '%s'; // text for a category page
		$text['search']   = __('Search Results for','cactusthemes').' "%s"'; // text for a search results page
		$text['tag']      = __('Tag','cactusthemes').' "%s"'; // text for a tag page
		$text['author']   = __('Author','cactusthemes').' %s'; // text for an author page
		$text['404']      = __('404','cactusthemes'); // text for the 404 page

		$show_current   = 1; // 1 - show current post/page/category title in breadcrumbs, 0 - don't show
		$show_on_home   = 1; // 1 - show breadcrumbs on the homepage, 0 - don't show
		$show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
		$show_title     = 1; // 1 - show the title for the links, 0 - don't show
        $delimiter      = ' \\ ';
        $position       = 1;
        $meta           = '<meta itemprop="position" content="%u" />';
        $before         = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem" class="current"><span itemprop="name">'; // tag before the current  crumb
		// $after          = '</a></li>'; // tag after the current crumb
		/* === END OF OPTIONS === */

		global $post;
		$home_link    = home_url('/');
		$link_before  = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
		$link_after   = '</li>';
		$link_attr    = ' itemprop="item"';
		$link         = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
		$parent_id    = $parent_id_2 = ($post) ? $post->post_parent : 0;
		$frontpage_id = get_option('page_on_front');

		if (is_front_page()) {

            if ($show_on_home == 1) echo '<ol class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="' . $home_link . '"><span itemprop="name">' . $text['home']. '</span></a>' . sprintf($meta, $position) . '</li></ol>';

		}elseif(is_home()){
			$title = get_option('page_for_posts')?get_the_title(get_option('page_for_posts')):__('Blog','cactusthemes');
            echo '<ol class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList"><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="' . $home_link . '"><span itemprop="name">' . $text['home'] . '</span></a>' . sprintf($meta, $position) . ' \ '.$title.'</li></ol>';
		} else {

            echo '<ol class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">';
			if ($show_home_link == 1) {
                echo '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemprop="item" href="' . $home_link . '"><span itemprop="name">' . $text['home'] .'</span></a>' . sprintf($meta, $position)  .'</li>';
				if ($frontpage_id == 0 || $parent_id != $frontpage_id) echo $delimiter;
			}
			if(is_tax()){
				single_term_title('',true);
			}else if ( is_category() ) {
				$this_cat = get_category(get_query_var('cat'), false);
				if ($this_cat->parent != 0) {
                    $parentArr = explode("*",get_category_parents($this_cat, false,"*"));
                    for ($i = 0; $i < count($parentArr)-2; $i ++ ){
                        $cats = '<a href="' . get_category_link(get_cat_ID($parentArr[$i])) . '"><span itemprop="name">' . $parentArr[$i] . '</span></a> '. $delimiter; 
    					if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
    					$cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
    					$cats = str_replace('</a>', '</a>' . sprintf($meta, ++$position) . $link_after, $cats);
                    }
					if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
					echo $cats;
				}
				if ($show_current == 1) echo $before . sprintf($text['category'], single_cat_title('', false)) . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_search() ) {
				echo $before . sprintf($text['search'], get_search_query()) . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_day() ) {
				echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
				echo sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . $delimiter;
				echo $before . get_the_time('d') . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_month() ) {
				echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
				echo $before . get_the_time('F') . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_year() ) {
				echo $before . get_the_time('Y') . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_single() && !is_attachment() ) {
				if ( get_post_type() != 'post' ) {
					$post_type = get_post_type_object(get_post_type());
					$slug = $post_type->rewrite;
                    $link = $link_before . '<a' . $link_attr . ' href="%1$s"><span itemprop="name">%2$s</span></a>' . sprintf($meta, ++$position) . $link_after;
					printf($link, $home_link . '/' . $slug['slug'] . '/', $post_type->labels->singular_name);
					if ($show_current == 1) echo $delimiter . $before . get_the_title() . '</span>' . sprintf($meta, ++$position) . '</li>';
				} else {
					$cat = get_the_category();
                    if(count($cat) > 0){
                        $cat = $cat[0];
                    } else {
                        $cat = 1; // uncategorized
                    }
                    $parentArr = explode("*",get_category_parents($cat, false,"*"));
                    for ($i = 0; $i < count($parentArr)-1; $i ++ ){
                        $cats = '<a href="' . get_category_link(get_cat_ID($parentArr[$i])) . '"><span itemprop="name">' . $parentArr[$i] . '</span></a> '. $delimiter;
                        if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
                        $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
                        $cats = str_replace('</a>', '</a>' . sprintf($meta, ++$position) . $link_after, $cats);
                        if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                        echo $cats;
                    }
                    if ($show_current == 1) echo $before . get_the_title() . '</span>' . sprintf($meta, ++$position) . '</li>';
				}

			} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
				$post_type = get_post_type_object(get_post_type());
				echo $before . $post_type->labels->singular_name . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_attachment() ) {
				$parent = get_post($parent_id);
				printf($link, get_permalink($parent), $parent->post_title);
				if ($show_current == 1) echo $delimiter . $before . get_the_title() . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_page() && !$parent_id ) {

				if ($show_current == 1) echo $before . get_the_title() . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_page() && $parent_id ) {
				if ($parent_id != $frontpage_id) {
					$breadcrumbs = array();
					while ($parent_id) {
						$page = get_page($parent_id);
						if ($parent_id != $frontpage_id) {
                            $link = $link_before . '<a' . $link_attr . ' href="%1$s"><span itemprop="name">%2$s</span></a>' . sprintf($meta, ++$position) . $link_after;
							$breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));					
                        }
						$parent_id = $page->post_parent;
					}
					$breadcrumbs = array_reverse($breadcrumbs);
					for ($i = 0; $i < count($breadcrumbs); $i++) {
						echo $breadcrumbs[$i];
						if ($i != count($breadcrumbs)-1) echo $delimiter;
					}
				}
				if ($show_current == 1) {
					if ($show_home_link == 1 || ($parent_id_2 != 0 && $parent_id_2 != $frontpage_id)) echo $delimiter;
					echo $before . get_the_title() . '</span>' . sprintf($meta, ++$position) . '</li>';
				}

			} elseif ( is_tag() ) {
				echo $before . sprintf($text['tag'], single_tag_title('', false)) . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_author() ) {
				global $author;
				$userdata = get_userdata($author);
				echo $before . sprintf($text['author'], $userdata->display_name) . '</span>' . sprintf($meta, ++$position) . '</li>';

			} elseif ( is_404() ) {
				echo $before . $text['404'] . '</span>' . sprintf($meta, ++$position) . '</li>';
			}

			if ( get_query_var('paged') ) {
				if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() || is_home() || is_page_template()) echo ' (';
				echo __('Page','cactusthemes') . ' ' . get_query_var('paged');
				if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() || is_home() || is_page_template()) echo ')';
			}

			echo '</ol><!-- .breadcrumbs -->';

		}
	} // end tm_breadcrumbs()
}

//custom login fail
add_action( 'wp_login_failed', 'tm_login_fail' );  // hook failed login
function tm_login_fail( $username ) {
	if($login_page = ot_get_option('login_page',false)){
		$referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
		// if there's a valid referrer, and it's not the default log-in screen
		if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
			wp_redirect(get_permalink($login_page).'?login=failed');  // let's append some information (login=failed) to the URL for the theme to use
			exit;
		}
	}
}
//redirect default login
add_action('init','tm_login_redirect');
function tm_login_redirect(){
	if($login_page = function_exists('ot_get_option') ? ot_get_option('login_page', false) : false){
	 global $pagenow;
	  if( 'wp-login.php' == $pagenow ) {
		if ( (isset($_POST['wp-submit']) && $_POST['log']!='' && $_POST['pwd']!='') ||   // in case of LOGIN
		  ( isset($_GET['action']) && $_GET['action']=='logout') ||   // in case of LOGOUT
		  ( isset($_GET['checkemail']) && $_GET['checkemail']=='confirm') ||   // in case of LOST PASSWORD
		  ( isset($_GET['action']) && $_GET['action']=='lostpassword') ||
		  ( isset($_GET['action']) && $_GET['action']=='rp') ||
		  ( isset($_GET['checkemail']) && $_GET['checkemail']=='registered') || // in case of REGISTER
		  isset($_GET['loginFacebook']) || isset($_GET['imadmin'])) return true;
		elseif(isset($_POST['wp-submit'])&&($_POST['log']=='' || $_POST['pwd']=='')){ wp_redirect(get_permalink($login_page) . '?login=failed' ); }
		else wp_redirect( get_permalink($login_page) ); // or wp_redirect(home_url('/login'));
		exit();
	  }
	}
}
//replace login page template
add_filter( 'page_template', 'tm_login_page_template' );
function tm_login_page_template( $page_template )
{
	if($login_page = function_exists('ot_get_option') ? ot_get_option('login_page',false) : false){
		if ( is_page( $login_page ) ) {
			$page_template = dirname( __FILE__ ) . '/page-templates/tpl-login.php';
		}
	}
    return $page_template;
}
function tm_author_avatar($ID = false, $size = 60){
	$user_avatar = false;
	$email='';
	if($ID == false){
		global $post;
		$ID = get_the_author_meta('ID');
		$email = get_the_author_meta('email');
	}
	if($user_avatar==false){
		global $_is_retina_;
		if($_is_retina_ && $size>120){
			$user_avatar = get_avatar( $email, $size, get_template_directory_uri() . '/images/avatar-3x.png' );
		}elseif($_is_retina_ || $size>120){
			$user_avatar = get_avatar( $email, $size, get_template_directory_uri() . '/images/avatar-2x.png' );
		}else{
			$user_avatar = get_avatar( $email, $size, get_template_directory_uri() . '/images/avatar.png' );
		}
	}
	return $user_avatar;
}

//add report post type
add_action( 'init', 'reg_report_post_type' );
function reg_report_post_type() {
	$args = array(
		'labels' => array(
			'name' => __( 'Reports' ),
			'singular_name' => __( 'Report' )
		),
		'menu_icon' 		=> 'dashicons-flag',
		'public'             => true,
		'publicly_queryable' => true,
		'exclude_from_search'=> true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'editor', 'custom-fields' )
	);
	if(function_exists('ot_get_option') && ot_get_option('video_report','on') != 'off'){
		register_post_type( 'tm_report', $args );
	}
}

//redirect report post type
add_action( 'template_redirect', 'redirect_report_post_type' );
function redirect_report_post_type() {
	global $post;
	if(is_singular('tm_report')){
		if($url = get_post_meta(get_the_ID(),'tm_report_url',true)){
			wp_redirect($url);
		}
	}
}

//social locker
function tm_get_social_locker($string){
	preg_match('~\[sociallocker\s+id\s*=\s*("|\')(?<id>.*?)\1\s*\]~i', $string, $match);
	$locker_id = isset($match['id']) ? $match['id']:''; //get id
	$id_text = $locker_id?'id="'.$locker_id.'"':''; //build id string
	return $id_text;
}

//YouTube WordPress plugin - video import - views
add_action( 'cbc_post_insert', 'cbc_tm_save_data', 10, 4 );
function cbc_tm_save_data( $post_id, $video, $theme_import, $post_type ){
	$data = get_post_meta($post_id, '__cbc_video_data', false);
	if( isset( $data['stats']['views'] ) ){
		update_post_meta( $post_id, '_count-views_all', $data['stats']['views']);
	}
}

add_theme_support( 'custom-header' );
add_theme_support( 'custom-background' );

include 'inc/user-submission-support.php';

function woo_related_tm() {
  global $product;

	$args['posts_per_page'] = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'woo_related_tm' );
/* Functions, Hooks, Filters and Registers in Admin */
require_once 'inc/functions-admin.php';
if(!function_exists('cactus_get_datetime')){
	function cactus_get_datetime()
	{
		$post_datetime_setting  = 'off';
		if($post_datetime_setting == 'on')
			return '<a href="' . esc_url(get_the_permalink()) . '" class="cactus-info" rel="bookmark"><time datetime="' . get_the_date( 'c' ) . '" class="entry-date updated">' . date_i18n(get_option('date_format') ,get_the_time('U')) . '</time></a>';
		else
			return '<div class="cactus-info" rel="bookmark"><time datetime="' . get_the_date( 'c' ) . '" class="entry-date updated">' . date_i18n(get_option('date_format') ,get_the_time('U')) . '</time></div>';
	}
}

if(!function_exists('cactus_hook_get_meta')){
	function cactus_hook_get_meta($metadata, $object_id, $meta_key, $single) {
		$jw_video_url_key = "_jwppp-video-url-1";
        $jw_mobile_url_key = "_jwppp-video-mobile-url-1";

        $jw_video_url = '';
        $jw_video_mobile_url = '';

		if((($meta_key == $jw_video_url_key) || ($meta_key == $jw_mobile_url_key)) && isset($meta_key)) {
			//use $wpdb to get the value
			global $wpdb;
			$value = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $object_id AND  meta_key = 'tm_video_url'" );

			if($value == ''){
				$value = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $object_id AND  meta_key = 'tm_video_file'" );
                $urls = explode(PHP_EOL, $value);

                if(count($urls) >= 2){
                    $jw_video_url = $urls[0];
                    $jw_video_mobile_url = $urls[1];
                } else {
                    $jw_video_url = $urls[0];
                }
			} else {
                $jw_video_url = $value;
            }
			//do whatever with $value

            if($meta_key == $jw_video_url_key)
                return $jw_video_url;

            if($meta_key == $jw_mobile_url_key)
                return $jw_video_mobile_url;
		}
	}
}

add_filter('get_post_metadata', 'cactus_hook_get_meta', 10, 4);

function filter_excerpt_baw( $excerpt ) {
	global $post;
	if( function_exists('bawpvc_get_options') && ($bawpvc_options = bawpvc_get_options()) && 'on' == $bawpvc_options['in_content'] && in_array( $post->post_type, $bawpvc_options['post_types'] ) ) {
		$excerpt = preg_replace('/\([\s\S]+?\)/', '', $excerpt);
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'filter_excerpt_baw' );

/**
 * dummy shortcode to prevent missing cactus badges plugin
 */
if(!shortcode_exists('cactus_badges')){
    add_shortcode('cactus_badges', 'cactus_badges_dummy');
    function cactus_badges_dummy($atts, $content){
        return '';
    }
}

if(!function_exists('tm_get_current_url')){
	/* Get current page URL */
	function tm_get_current_url() {
		global $wp;
		$pageURL = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
        $pageURL = remove_query_arg('pagename', $pageURL);
        $pageURL = remove_query_arg('name', $pageURL);
		return $pageURL;
	}
}


if(!function_exists('cactus_print_social_share')){
function cactus_print_social_share($class_css = false, $id = false){
	if(!$id){
		$id = get_the_ID();
	}
?>
	<ul class="social-listing list-inline <?php if(isset($class_css)){ echo $class_css;} ?>">
  		<?php if(ot_get_option('sharing_facebook')!='off'){ ?>
	  		<li class="facebook">
	  		 	<a class="trasition-all" title="<?php _e('Share on Facebook','cactusthemes');?>" href="#" target="_blank" rel="nofollow" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+'<?php echo urlencode(get_permalink($id)); ?>','facebook-share-dialog','width=626,height=436');return false;"><i class="fab fa-facebook"></i>
	  		 	</a>
	  		</li>
    	<?php }

		if(ot_get_option('sharing_twitter')!='off'){ ?>
	    	<li class="twitter">
		    	<a class="trasition-all" href="#" title="<?php _e('Share on Twitter','cactusthemes');?>" rel="nofollow" target="_blank" onclick="window.open('http://twitter.com/share?text=<?php echo urlencode(get_the_title($id)); ?>&url=<?php echo urlencode(get_permalink($id)); ?>','twitter-share-dialog','width=626,height=436');return false;"><i class="fab fa-twitter"></i>
		    	</a>
	    	</li>
    	<?php }

		if(ot_get_option('sharing_linkedIn')!='off'){ ?>
			   	<li class="linkedin">
			   	 	<a class="trasition-all" href="#" title="<?php _e('Share on LinkedIn','cactusthemes');?>" rel="nofollow" target="_blank" onclick="window.open('http://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(get_permalink($id)); ?>&title=<?php echo urlencode(get_the_title($id)); ?>&source=<?php echo urlencode(get_bloginfo('name')); ?>','linkedin-share-dialog','width=626,height=436');return false;"><i class="fab fa-linkedin"></i>
			   	 	</a>
			   	</li>
	   	<?php }

		if(ot_get_option('sharing_tumblr')!='off'){ ?>
		   	<li class="tumblr">
		   	   <a class="trasition-all" href="#" title="<?php _e('Share on Tumblr','cactusthemes');?>" rel="nofollow" target="_blank" onclick="window.open('http://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink($id)); ?>&name=<?php echo urlencode(get_the_title($id)); ?>','tumblr-share-dialog','width=626,height=436');return false;"><i class="fab fa-tumblr"></i>
		   	   </a>
		   	</li>
    	<?php }

		if(ot_get_option('sharing_google')!='off'){ ?>
	    	 <li class="google-plus">
	    	 	<a class="trasition-all" href="#" title="<?php _e('Share on Google Plus','cactusthemes');?>" rel="nofollow" target="_blank" onclick="window.open('https://plus.google.com/share?url=<?php echo urlencode(get_permalink($id)); ?>','googleplus-share-dialog','width=626,height=436');return false;"><i class="fab fa-google-plus"></i>
	    	 	</a>
	    	 </li>
    	 <?php }

		 if(ot_get_option('sharing_pinterest')!='off'){ ?>
	    	 <li class="pinterest">
	    	 	<a class="trasition-all" href="#" title="<?php _e('Pin this','cactusthemes');?>" rel="nofollow" target="_blank" onclick="window.open('//pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($id)) ?>&media=<?php echo urlencode(wp_get_attachment_url( get_post_thumbnail_id($id))); ?>&description=<?php echo urlencode(get_the_title($id)) ?>','pin-share-dialog','width=626,height=436');return false;"><i class="fab fa-pinterest"></i>
	    	 	</a>
	    	 </li>
    	 <?php }

		 if(ot_get_option('sharing_email')!='off'){ ?>
	    	<li class="email">
		    	<a class="trasition-all" href="mailto:?subject=<?php echo get_the_title($id) ?>&body=<?php echo urlencode(get_permalink($id)) ?>" title="<?php _e('Email this','cactusthemes');?>"><i class="fa fa-envelope"></i>
		    	</a>
		   	</li>
	   	<?php }?>
    </ul>
        <?php
	}
}

/**
 *
 *
 */
add_action( 'wp_head', 'ct_hook_baw_pvc_main' );
function ct_hook_baw_pvc_main()
{
	global $post, $bawpvc_options;
	$bots = array( 	'wordpress', 'googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati',
					'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'pubsub', 'syndic8', 'userland', 'gigabot', 'become.com' );
	if( 	!( ( $bawpvc_options['no_members']=='on' && is_user_logged_in() ) || ( $bawpvc_options['no_admins']=='on' && current_user_can( 'administrator' ) ) ) &&
			!empty( $_SERVER['HTTP_USER_AGENT'] ) &&
			is_singular( $bawpvc_options['post_types'] ) &&
			!preg_match( '/' . implode( '|', $bots ) . '/i', $_SERVER['HTTP_USER_AGENT'] )
		)
	{
		global $timings;
		$IP = substr( md5( getenv( 'HTTP_X_FORWARDED_FOR' ) ? getenv( 'HTTP_X_FORWARDED_FOR' ) : getenv( 'REMOTE_ADDR' ) ), 0, 16 );
		$time_to_go = $bawpvc_options['time']; // Default: no time between count
		if( (int)$time_to_go == 0 || !get_transient( 'baw_count_views-' . $IP . $post->ID ) ) {
				$channel = get_post_meta( $post->ID, 'channel_id', true );
				if(!is_array($channel)){
					$count_channel = (int)get_post_meta( $channel, 'view_channel', true );
					$count_channel++;
					update_post_meta( $channel, 'view_channel', $count_channel );
				}else{
					foreach($channel as $channel_item){
						$count_channel = (int)get_post_meta( $channel_item, 'view_channel', true );
						$count_channel++;
						update_post_meta( $channel_item, 'view_channel', $count_channel );
					}
				}
				$playlist_v = get_post_meta( $post->ID, 'playlist_id', true );
				if(!is_array($playlist_v)){
					$count_playlist = (int)get_post_meta( $playlist_v, 'view_playlist', true );
					$count_playlist++;
					update_post_meta( $playlist_v, 'view_playlist', $count_playlist );
				}else{
					foreach($playlist_v as $playlist_item){
						$count_playlist = (int)get_post_meta( $playlist_item, 'view_playlist', true );
						$count_playlist++;
						update_post_meta( $playlist_item, 'view_playlist', $count_playlist );
					}
				}
			if( (int)$time_to_go > 0 )
				set_transient( 'baw_count_views-' . $IP . $post->ID, $IP, $time_to_go );
		}
	}
}

function get_sticky_posts_count() {
	 global $wpdb;
	 $sticky_posts = array_map( 'absint', (array) get_option('sticky_posts') );
	 return count($sticky_posts) > 0 ? $wpdb->get_var( "SELECT COUNT( 1 ) FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND ID IN (".implode(',', $sticky_posts).")" ) : 0;
}
// when the request action is 'load_more', the cactus_ajax_load_next_page() will be called
add_action( 'wp_ajax_load_more', 'cactus_ajax_load_next_page' );
add_action( 'wp_ajax_nopriv_load_more', 'cactus_ajax_load_next_page' );

function cactus_ajax_load_next_page() {
	//get blog listing style
	global $blog_layout;

	$test_layout = isset($_POST['blog_layout']) ? $_POST['blog_layout'] : '';

	if(isset($test_layout) && $test_layout != '' && ($test_layout == 'layout_1' || $test_layout == 'layout_2' || $test_layout == 'layout_3' || $test_layout == 'layout_4' || $test_layout == 'layout_5' || $test_layout == 'layout_6' || $test_layout == 'layout_7'))
	    $blog_layout = $test_layout;
	else
	    $blog_layout = ot_get_option('blog_layout', 'layout_1');


    // Get current page
	$page = $_POST['page'];

	// number of published sticky posts
	$sticky_posts = get_sticky_posts_count();

	// current query vars
	$vars = $_POST['vars'];


	// convert string value into corresponding data types
	foreach($vars as $key=>$value){
		if(is_numeric($value)) $vars[$key] = intval($value);
		if($value == 'false') $vars[$key] = false;
		if($value == 'true') $vars[$key] = true;
	}

	// item template file
	$template = $_POST['template'];

	// Return next page
	$page = intval($page) + 1;

	$posts_per_page = isset($_POST['post_per_page']) ? $_POST['post_per_page'] : get_option('posts_per_page');

	if($page == 0) $page = 1;
	$offset = ($page - 1) * $posts_per_page;
	/*
	 * This is confusing. Just leave it here to later reference
	 *

	if(!$vars['ignore_sticky_posts']){
		$offset += $sticky_posts;
	}
	 *
	 */


	// get more posts per page than necessary to detect if there are more posts
	$args = array('post_status'=>'publish','posts_per_page' => $posts_per_page + 1,'offset' => $offset);
	$args = array_merge($vars,$args);

	// remove unnecessary variables
	unset($args['paged']);
	unset($args['p']);
	unset($args['page']);
	unset($args['pagename']); // this is neccessary in case Posts Page is set to a static page

	$query = new WP_Query($args);




	$idx = 0;
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$idx = $idx + 1;
			if($idx < $posts_per_page + 1)
				get_template_part( $template, get_post_format() );
		}

		if($query->post_count <= $posts_per_page){
			// there are no more posts
			// print a flag to detect
			echo '<div class="invi no-posts"><!-- --></div>';
		}
	} else {
		// no posts found
	}

	/* Restore original Post Data */
	wp_reset_postdata();

	die('');
}

if ( ! function_exists( 'cactus_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @params $content_div & $template are passed to Ajax pagination
 */
function cactus_paging_nav($content_div = '#main', $template = 'html/loop/content', $text_bt = false) {
	if(!isset($text_bt)){ $text_bt ='';}
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	$nav_type = ot_get_option('pagination_style','page_def');
	switch($nav_type){
		case 'page_def':
			cacus_paging_nav_default();
			break;
		case 'page_ajax':
			cacus_paging_nav_ajax($content_div, $template, $text_bt);
			break;
		case 'page_navi':
			if( ! function_exists( 'wp_pagenavi' ) ) {
				// fall back to default navigation style
				cacus_paging_nav_default();
			} else {
				wp_pagenavi();
			}
			break;
	}
}
endif;

if ( ! function_exists( 'cacus_paging_nav_default' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable. Default WordPress style
 */
function cacus_paging_nav_default() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	?>
	<nav id="paging" class="paging-navigation channel" role="navigation">
		<div class="nav-links">

			<?php if ( get_next_posts_link() ) : ?>
			<div class="nav-previous alignleft"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'cactusthemes' ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<div class="nav-next alignright"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'cactusthemes' ) ); ?></div>
			<?php endif; ?>

		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if ( ! function_exists( 'cacus_paging_nav_ajax' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable. Ajax loading
 *
 * @params $content_div (string) - ID of the DIV which contains items
 * @params $template (string) - name of the template file that hold HTML for a single item. It will look for specific post-format template files
			For example, if $template = 'content'
				it will look for content-$post_format.php first (i.e content-video.php, content-audio.php...)
				then it will look for content.php if no post-format template is found
*/
function cacus_paging_nav_ajax($content_div = '#main', $template = 'content', $text_bt = false) {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	?>
	<nav class="navigation-ajax" role="navigation">
		<div class="wp-pagenavi">
			<a href="javascript:void(0)" data-target="<?php echo $content_div;?>" data-template="<?php echo $template; ?>" id="navigation-ajax" class="load-more btn btn-default font-1">
				<div class="load-title"><?php if($text_bt){ echo __($text_bt); }else{ echo __('LOAD MORE VIDEOS','cactusthemes');} ?></div>
				<i class="fa fa-refresh hide" id="load-spin"></i>
			</a>
		</div>
	</nav>

	<?php
}
endif;

if ( ! function_exists( 'cactus_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 */
function cactus_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous ) {
		return;
	}
	?>
	<nav class="navigation post-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'cactusthemes' ); ?></h1>
		<div class="nav-links">
			<?php
				previous_post_link( '<div class="nav-previous">%link</div>', _x( '<span class="meta-nav">&larr;</span> %title', 'Previous post link', 'cactusthemes' ) );
				next_post_link(     '<div class="nav-next">%link</div>',     _x( '%title <span class="meta-nav">&rarr;</span>', 'Next post link',     'cactusthemes' ) );
			?>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if ( ! function_exists( 'cactus_utube_conver_time' ) ) {
    function cactus_utube_conver_time( $time ) {
        if ( $time == '' || ! $time ) return '';
        $time = preg_replace( '/[^0-9:]/', '', $time );
        if ( preg_match( '/:/', $time ) ) {
            $time = explode( ':', $time );
            switch ( count( $time ) ) {
                case 2:
                    $time = $time[0] * 60 + $time[1];
                    break;
                case 3:
                    $time = $time[0] * 60 * 60 + $time[1] * 60 + $time[2];
                    break;
                default:
                    $time = 0;
                    break;
            }
        }
        return $time ? $time : 0;
    }
}
