<?php

/**
 * Fortmat page title
 *
 * @since Twenty Twelve 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string Filtered title.
 */
function cactusthemes_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'cactusthemes' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'cactusthemes_wp_title', 10, 2 );

/**
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 */
function cactusthemes_page_menu_args( $args ) {
	if ( ! isset( $args['show_home'] ) )
		$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'cactusthemes_page_menu_args' );

if ( ! function_exists( 'cactusthemes_content_nav' ) ) :
/**
 * Displays navigation to next/previous pages when applicable.
 *
 * @since Twenty Twelve 1.0
 */
function cactusthemes_content_nav( $html_id, $custom_query=false ) {
	global $wp_query;
	$current_query = $wp_query;
	if($custom_query){
		$current_query = $custom_query;
	}
	$html_id = esc_attr( $html_id );

	if ( $current_query->max_num_pages > 1 ) : ?>
		<nav id="<?php echo $html_id; ?>" class="paging-navigation" role="navigation">
			<div class="nav-previous alignleft"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'cactusthemes' ),$current_query->max_num_pages ); ?></div>
			<div class="nav-next alignright"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'cactusthemes' ) ); ?></div>
		</nav><!-- #<?php echo $html_id; ?> .navigation -->
	<?php endif;
}
endif;

add_filter('get_avatar','ct_filter_avatar', 2, 5);
if(!function_exists('ct_filter_avatar')){
	function ct_filter_avatar($avatar, $id_or_email, $size = 66, $default, $alt = false){
		global $_is_retina_;
		
		if ( ! get_option('show_avatars') )
			return false;

		if ( false === $alt)
			$safe_alt = '';
		else
			$safe_alt = esc_attr( $alt );

		if ( !is_numeric($size) )
			$size = '96';

		$email = '';
		if ( is_numeric($id_or_email) ) {
			$id = (int) $id_or_email;
			$user = get_userdata($id);
			if ( $user )
				$email = $user->user_email;
		} elseif ( is_object($id_or_email) ) {
			// No avatar for pingbacks or trackbacks
			$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
			if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
				return false;

			if ( !empty($id_or_email->user_id) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_userdata($id);
				if ( $user)
					$email = $user->user_email;
			} elseif ( !empty($id_or_email->comment_author_email) ) {
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}

		if ( empty($default) ) {
			$avatar_default = get_option('avatar_default');
			if ( empty($avatar_default) )
				$default = 'mystery';
			else
				$default = $avatar_default;
		}

		if ( !empty($email) )
			$email_hash = md5( strtolower( trim( $email ) ) );

		if ( is_ssl() ) {
			$host = 'https://secure.gravatar.com';
		} else {
			if ( !empty($email) )
				$host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash[0] ) % 2 ) );
			else
				$host = 'http://0.gravatar.com';
		}
		
		if(strpos($avatar,'avatar-default') > -1){
			if($_is_retina_){
				$default = get_template_directory_uri() . '/images/avatar-2x.png';// default avatar in theme
			} else {
				$default = get_template_directory_uri() . '/images/avatar.png';// default avatar in theme
			}
		}
		elseif ( 'mystery' == $default ){
			if($_is_retina_){
				$default = get_template_directory_uri() . '/images/avatar-2x.png';// default avatar in theme
			} else {
				$default = get_template_directory_uri() . '/images/avatar.png';// default avatar in theme
			}
		}
		elseif ( 'blank' == $default )
			$default = $email ? 'blank' : includes_url( 'images/blank.gif' );
		elseif ( !empty($email) && 'gravatar_default' == $default )
			$default = '';
		elseif ( 'gravatar_default' == $default )
			$default = "$host/avatar/?s={$size}";
		elseif ( empty($email) )
			$default = "$host/avatar/?d=$default&amp;s={$size}";
		elseif ( strpos($default, 'http://') === 0 )
			$default = esc_url_raw(add_query_arg( 's', $size, $default ));

		if ( !empty($email) ) {
			$out = "$host/avatar/";
			$out .= $email_hash;
			$out .= '?s='.$size;
			$out .= '&amp;d=' . urlencode( $default );

			$rating = get_option('avatar_rating');
			if ( !empty( $rating ) )
				$out .= "&amp;r={$rating}";

			$out = str_replace( '&#038;', '&amp;', esc_url( $out ) );
			
			$out = apply_filters( 'get_avatar_url', $out, $id_or_email, array(
																			'size'           => $size,
																			'default'        => $default) );
			
			$avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
		} else {
			$avatar = "<img alt='{$safe_alt}' src='{$default}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
		}
		
		return $avatar;
	}
}

if ( ! function_exists( 'cactusthemes_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own cactusthemes_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function cactusthemes_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'cactusthemes' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'cactusthemes' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<div class="avatar-wrap">
			<?php
			if(isset($_is_retina_)&&$_is_retina_){
						echo get_avatar( $comment, 66, get_template_directory_uri() . '/images/avatar-2x.png');
					} else {
						echo get_avatar( $comment, 66, get_template_directory_uri() . '/images/avatar.png');
					}
			?>
			</div>
			<div class="comment-meta comment-author">
				<?php
					printf( '<cite class="fn">%1$s</cite> ', get_comment_author_link());
				?>
				<section class="comment-edit">
					<?php 
                    printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
                            esc_url( get_comment_link( $comment->comment_ID ) ),
                            get_comment_time( 'c' ),
                            /* translators: 1: date, 2: time */
                            sprintf( __( '%1$s  at %2$s ', 'cactusthemes' ), get_comment_date(), get_comment_time() )
                        );?>
                    <?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'cactusthemes' ), 'after' => ' <span></span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
                <!-- .reply -->    
                        <?php
                    edit_comment_link( __( 'Edit', 'cactusthemes' ), '<p class="edit-link">', '</p>' ); ?>
                </section>
				<div class="comment-content">					
					<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'cactusthemes' ); ?></p>
				<?php endif; ?>
					<?php comment_text(); ?>
				</div><!-- .comment-content -->
            </div><!-- .comment-meta -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;

if(!function_exists('alter_comment_form_fields')){
	function alter_comment_form_fields($fields){
		$commenter = wp_get_current_commenter();
		$user = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';
		
		$req = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
	
		$fields['author'] = '<div class="cm-form-info"><div class="row comment-author-field"><div class="col-md-4"><p class="comment-form-author"><input id="author" name="author" type="text" placeholder="'.($req ? '' : '').__('Your Name *','cactusthemes').'" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p></div>';
		$fields['email'] = '<div class="col-md-4"><p class="comment-form-email"><input id="email" placeholder="'.($req ? '' : '').__('Your Email *','cactusthemes').'" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p></div>';  //removes email field
		$fields['url'] = '<div class="col-md-4"><p class="comment-form-url"><input id="url" placeholder="'.__('Your Website','cactusthemes').'" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p></div></div></div>';
		
		return $fields;
	}

	add_filter('comment_form_default_fields','alter_comment_form_fields');
}

//change comment form
if(!function_exists('comment_form_tm')){
function comment_form_tm( $args = array(), $post_id = null ) {
	if ( null === $post_id )
		$post_id = get_the_ID();
	else
		$id = $post_id;

	$commenter = wp_get_current_commenter();
	$user = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';

	$args = wp_parse_args( $args );
	if ( ! isset( $args['format'] ) )
		$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';

	$req      = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$html5    = 'html5' === $args['format'];
	$fields   =  array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name','cactusthemes' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
		            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email','cactusthemes'  ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
		            '<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website','cactusthemes'  ) . '</label> ' .
		            '<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);

	$required_text = sprintf( ' ' . __('Required fields are marked %s','cactusthemes' ), '<span class="required">*</span>' );

	/**
	 * Filter the default comment form fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $fields The default comment fields.
	 */
	$fields = apply_filters( 'comment_form_default_fields', $fields );
	$defaults = array(
		'fields'               => $fields,
		'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . __( 'Comment', 'cactusthemes' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
		'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ,'cactusthemes' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>','cactusthemes'), get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.','cactusthemes' ) . ( $req ? $required_text : '' ) . '</p>',
		'comment_notes_after'  => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s','cactusthemes' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
		'id_form'              => 'commentform',
		'id_submit'            => 'submit',
		'title_reply'          => __( 'Leave a Reply','cactusthemes' ),
		'title_reply_to'       => __( 'Leave a Reply to %s','cactusthemes'  ),
		'cancel_reply_link'    => __( 'Cancel reply','cactusthemes'  ),
		'label_submit'         => __( 'Submit' ,'cactusthemes' ),
		'format'               => 'xhtml',
	);

	/**
	 * Filter the comment form default arguments.
	 *
	 * Use 'comment_form_default_fields' to filter the comment fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $defaults The default comment form arguments.
	 */
	$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

	?>
		<?php if ( comments_open( $post_id ) ) : ?>
			<?php
			/**
			 * Fires before the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_before' );
			?>            
            
			<div id="respond" class="comment-respond">
            
              <div class="author-current">
				<?php $user_ID = get_current_user_id();
					global $_is_retina_;
					if($_is_retina_){ 
						echo get_avatar( $user_ID, 122, get_template_directory_uri() . '/images/avatar-2x.png' );
					}else{
						echo get_avatar( $user_ID, 66, get_template_directory_uri() . '/images/avatar.png' );
					}
					//echo tm_author_avatar($user_ID,66);
				?>
              </div>

				<h3 id="reply-title" class="comment-reply-title"><?php comment_form_title( $args['title_reply'], $args['title_reply_to'] ); ?> <small><?php cancel_comment_reply_link( $args['cancel_reply_link'] ); ?></small></h3>
				<?php if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) : ?>
					<?php echo $args['must_log_in']; ?>
					<?php
					/**
					 * Fires after the HTML-formatted 'must log in after' message in the comment form.
					 *
					 * @since 3.0.0
					 */
					do_action( 'comment_form_must_log_in_after' );
					?>
				<?php else : ?>
					<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="comment-form"<?php echo $html5 ? ' novalidate' : ''; ?>>
						<?php
						/**
						 * Fires at the top of the comment form, inside the <form> tag.
						 *
						 * @since 3.0.0
						 */
						do_action( 'comment_form_top' );
						?>
						<?php if ( is_user_logged_in() ) : ?>
							<?php
							/**
							 * Filter the 'logged in' message for the comment form for display.
							 *
							 * @since 3.0.0
							 *
							 * @param string $args['logged_in_as'] The logged-in-as HTML-formatted message.
							 * @param array  $commenter            An array containing the comment author's username, email, and URL.
							 * @param string $user_identity        If the commenter is a registered user, the display name, blank otherwise.
							 */
							echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );
							?>
							<?php
							/**
							 * Fires after the is_user_logged_in() check in the comment form.
							 *
							 * @since 3.0.0
							 *
							 * @param array  $commenter     An array containing the comment author's username, email, and URL.
							 * @param string $user_identity If the commenter is a registered user, the display name, blank otherwise.
							 */
							do_action( 'comment_form_logged_in_after', $commenter, $user_identity );
							?>
						<?php else : ?>
							<?php echo $args['comment_notes_before']; ?>
							<?php
							/**
							 * Fires before the comment fields in the comment form.
							 *
							 * @since 3.0.0
							 */
							do_action( 'comment_form_before_fields' );
							/**
							 * Fires after the comment fields in the comment form.
							 *
							 * @since 3.0.0
							 */
							do_action( 'comment_form_after_fields' );
							?>
						<?php endif; ?>
						<?php
						/**
						 * Filter the content of the comment textarea field for display.
						 *
						 * @since 3.0.0
						 *
						 * @param string $args['comment_field'] The content of the comment textarea field.
						 */
						echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );
						?>
						<?php echo $args['comment_notes_after']; 
						if (!is_user_logged_in() ) :
						foreach ( (array) $args['fields'] as $name => $field ) {
							/**
							 * Filter a comment form field for display.
							 *
							 * The dynamic portion of the filter hook, $name, refers to the name
							 * of the comment form field. Such as 'author', 'email', or 'url'.
							 *
							 * @since 3.0.0
							 *
							 * @param string $field The HTML-formatted output of the comment form field.
							 */
							echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
						}
						endif;
						
						
						?>

						<p class="form-submit">
							<input name="submit" type="submit" id="<?php echo esc_attr( $args['id_submit'] ); ?>" value="<?php echo esc_attr( $args['label_submit'] ); ?>" />
							<?php comment_id_fields( $post_id ); ?>
						</p>
						<?php
						/**
						 * Fires at the bottom of the comment form, inside the closing </form> tag.
						 *
						 * @since 1.5.2
						 *
						 * @param int $post_id The post ID.
						 */
						do_action( 'comment_form', $post_id );
						?>
					</form>
				<?php endif; ?>
			</div><!-- #respond -->
			<?php
			/**
			 * Fires after the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_after' );
		else :
			/**
			 * Fires after the comment form if comments are closed.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_comments_closed' );
		endif;
}


}
//end

if ( ! function_exists( 'cactusthemes_entry_meta' ) ) :
/**
 * Prints HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own cactusthemes_entry_meta() to override in a child theme.
 */
function cactusthemes_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'cactusthemes' ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'cactusthemes' ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( date_i18n(get_option('date_format') ,get_the_time('U')) )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'cactusthemes' ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', 'cactusthemes' );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', 'cactusthemes' );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', 'cactusthemes' );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}
endif;

/**
 * Extends the default WordPress body class to denote:
 * 1. Using a full-width layout, when no active widgets in the sidebar
 *    or full-width template.
 * 2. Front Page template: thumbnail in use and number of sidebars for
 *    widget areas.
 * 3. White or empty background color to change the layout and spacing.
 * 4. Custom fonts enabled.
 * 5. Single or multiple authors.
 * @param array Existing class values.
 * @return array Filtered class values.
 */
function cactusthemes_body_class( $classes ) {
	$background_color = get_background_color();

	if ( ! is_active_sidebar( 'sidebar-1' ) || is_page_template( 'page-templates/full-width.php' ) )
		$classes[] = 'full-width';

	if ( is_page_template( 'page-templates/front-page.php' ) ) {
		$classes[] = 'template-front-page';
		if ( has_post_thumbnail() )
			$classes[] = 'has-post-thumbnail';
		if ( is_active_sidebar( 'sidebar-2' ) && is_active_sidebar( 'sidebar-3' ) )
			$classes[] = 'two-sidebars';
	}

	if ( empty( $background_color ) )
		$classes[] = 'custom-background-empty';
	elseif ( in_array( $background_color, array( 'fff', 'ffffff' ) ) )
		$classes[] = 'custom-background-white';

	// Enable custom font class only if the font CSS is queued to load.
	if ( wp_style_is( 'cactusthemes-fonts', 'queue' ) )
		$classes[] = 'custom-font-enabled';

	if ( ! is_multi_author() )
		$classes[] = 'single-author';

	return $classes;
}
add_filter( 'body_class', 'cactusthemes_body_class' );

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @since Twenty Twelve 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function cactusthemes_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
}
add_action( 'customize_register', 'cactusthemes_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since Twenty Twelve 1.0
 */
function cactusthemes_customize_preview_js() {
	wp_enqueue_script( 'cactusthemes-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array( 'customize-preview' ), '20120827', true );
}
add_action( 'customize_preview_init', 'cactusthemes_customize_preview_js' );

if(!function_exists('get_dynamic_sidebar')){
	function get_dynamic_sidebar($index = 1){
		$sidebar_contents = "";
		ob_start();
		dynamic_sidebar($index);
		$sidebar_contents = ob_get_clean();
		return $sidebar_contents;
	}
}

// Get custom options for widget
global $expand_wl_options;
if((!$expand_wl_options = get_option('cactusthemes')) || !is_array($expand_wl_options) ) $expand_wl_options = array();

/**
 * Add custom properties to every widget ===================
 *
 * Add: custom-variation textbox for adding CSS classes
 *
 **/
 
add_action( 'sidebar_admin_setup', 'cactusthemes_expand_control');
// adds in the admin control per widget, but also processes import/export
function cactusthemes_expand_control(){
	global $wp_registered_widgets, $wp_registered_widget_controls, $expand_wl_options;
	
	// ADD EXTRA CUSTOM FIELDS TO EACH WIDGET CONTROL
	// pop the widget id on the params array (as it's not in the main params so not provided to the callback)
	foreach ( $wp_registered_widgets as $id => $widget )
	{	// controll-less widgets need an empty function so the callback function is called.
		if (!$wp_registered_widget_controls[$id])
			wp_register_widget_control($id,$widget['name'], 'cactusthemes_empty_control');
		
		$wp_registered_widget_controls[$id]['callback_ct_redirect']=$wp_registered_widget_controls[$id]['callback'];
		$wp_registered_widget_controls[$id]['callback']='ct_widget_add_custom_fields';
		array_push($wp_registered_widget_controls[$id]['params'],$id);	
	}
	
	// UPDATE CUSTOM FIELDS OPTIONS (via accessibility mode?)
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) )
	{	foreach ( (array) $_POST['widget-id'] as $widget_number => $widget_id )
			if (isset($_POST[$widget_id.'-cactusthemes']))
				$expand_wl_options[$widget_id]=trim($_POST[$widget_id.'-cactusthemes']);
	}
	
	update_option('cactusthemes', $expand_wl_options);
}

/* Empty function for callback - DO NOT DELETE!!! */
function cactusthemes_empty_control() {}

function ct_widget_add_custom_fields() {
	global $wp_registered_widget_controls, $expand_wl_options;

	$params=func_get_args();
	
	$id=array_pop($params);
	// go to the original control function
	$callback=$wp_registered_widget_controls[$id]['callback_ct_redirect'];
	if (is_callable($callback))
		call_user_func_array($callback, $params);	
	$value = !empty( $expand_wl_options[$id ] ) ? htmlspecialchars( stripslashes( $expand_wl_options[$id ] ),ENT_QUOTES ) : '';
	//var_dump(get_option('cactusthemes'));
	
	// dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
	$number=$params[0]['number'];
	if ($number==-1) {$number="__i__"; $value="";}
	$id_disp=$id;
	if (isset($number)) $id_disp=$wp_registered_widget_controls[$id]['id_base'].'-'.$number;
	
	// output our extra widget logic field
	echo "<p><label for='".$id_disp."-cactusthemes'>".__('Custom Variation', 'cactusthemes').": <input class='widefat' type='text' name='".$id_disp."-cactusthemes' id='".$id_disp."-cactusthemes' value='".$value."' /></label></p>";
}

/*Add custom fields 2*/
// Get custom options for widget
global $wl_options_style;
if((!$wl_options_style = get_option('cactusthemes2')) || !is_array($wl_options_style) ) $wl_options_style = array();

if ( is_admin() )
{
    add_action( 'sidebar_admin_setup', 'widget_style_expand_control' );
}

// CALLED VIA 'sidebar_admin_setup' ACTION
// adds in the admin control per widget, but also processes import/export
function widget_style_expand_control()
{   
    global $wp_registered_widgets, $wp_registered_widget_controls, $wl_options_style;

    // ADD EXTRA WIDGET LOGIC FIELD TO EACH WIDGET CONTROL
    // pop the widget id on the params array (as it's not in the main params so not provided to the callback)
    foreach ( $wp_registered_widgets as $id => $widget )
    {   // controll-less widgets need an empty function so the callback function is called.
        if (!$wp_registered_widget_controls[$id])
            wp_register_widget_control($id,$widget['name'], 'widget_style_empty_control');
        $wp_registered_widget_controls[$id]['callback_style_redirect'] = $wp_registered_widget_controls[$id]['callback'];
        $wp_registered_widget_controls[$id]['callback'] = 'widget_style_extra_control';
        array_push( $wp_registered_widget_controls[$id]['params'], $id );   
    }
	// UPDATE CUSTOM FIELDS OPTIONS (via accessibility mode?)
	if ( 'post' == strtolower($_SERVER['REQUEST_METHOD']) )
	{	foreach ( (array) $_POST['widget-id'] as $widget_number => $widget_id )
			if (isset($_POST[$widget_id.'-cactusthemes2']))
				$wl_options_style[$widget_id]=trim($_POST[$widget_id.'-cactusthemes2']);
	}
	
	update_option('cactusthemes2', $wl_options_style);
}

// added to widget functionality in 'widget_style_expand_control' (above)
function widget_style_empty_control() {}

// added to widget functionality in 'widget_style_expand_control' (above)
function widget_style_extra_control()
{   
    global $wp_registered_widget_controls, $wl_options_style;

    $params = func_get_args();
    $id = array_pop($params);

    // go to the original control function
    $callback = $wp_registered_widget_controls[$id]['callback_style_redirect'];
    if ( is_callable($callback) )
        call_user_func_array($callback, $params);       

    $value = !empty( $wl_options_style[$id] ) ? htmlspecialchars( stripslashes( $wl_options_style[$id ] ),ENT_QUOTES ) : '';

    // dealing with multiple widgets - get the number. if -1 this is the 'template' for the admin interface
	if(isset($params[0]['number']))
		$number = $params[0]['number'];
    if ($number == -1) {
        $number = "%i%"; 
        $value = "";
    }
    $id_disp=$id;
    if ( isset($number) ) 
        $id_disp = $wp_registered_widget_controls[$id]['id_base'].'-'.$number;

    // output our extra widget logic field
    echo "
	<p><label for='".$id_disp."-cactusthemes2'>".__('Widget Style', 'cactusthemes2').": 
	<select name='".$id_disp."-cactusthemes2' id='".$id_disp."-cactusthemes2'>
	  <option value='1' ".($value=='1'?'selected="selected"':'').">Default</option>
	  <option value='no-border' ".($value=='no-border'?'selected="selected"':'').">Fullwidth</option>
	  <option value='solid-noborder' ".($value=='solid-noborder'?'selected="selected"':'').">Solid Header & No Border</option>
	</select>
	</label></p>";
}
/**
 * =================== End Add custom properties to every widget  <<<
 */
/**
 * Hook before widget 
 */
if(!is_admin()){
	add_filter('dynamic_sidebar_params', 'cactusthemes_hook_before_style_widget'); 	
	function cactusthemes_hook_before_style_widget($params){
		/* Add custom variation classs to widgets */
		global $wl_options_style;
		$id=$params[0]['widget_id'];
		$classe_to_add = !empty( $wl_options_style[$id ] ) ? htmlspecialchars( stripslashes( $wl_options_style[$id ] ),ENT_QUOTES ) : '';
		
		if(preg_match('/icon-\w+\s*/',$classe_to_add,$matches)){
			if(ot_get_option( 'righttoleft', 0)){
				$params[0]['after_title'] = '<i class="'.$matches[0].'"></i>' . $params[0]['after_title'];
			} else {
				$params[0]['before_title'] .= '<i class="'.$matches[0].'"></i>';
			}
			$classe_to_add = str_replace('icon-','wicon-',$classe_to_add); // replace "icon-xxx" class to not add Awesome Icon before div.widget
		};
		
		if ($params[0]['before_widget'] != ""){  
			$classe_to_add = 'class="'.$classe_to_add.' ';
			$params[0]['before_widget'] = str_replace('class="',$classe_to_add,$params[0]['before_widget']);
		}else{
			$classe_to_add = $classe_to_add;
			$params[0]['before_widget'] = '<div class="'.$classe_to_add.'">';
			$params[0]['after_widget'] = '</div>';
		}
		
		return $params;
	}
}


/**
 * Hook before widget 
 */
if(!is_admin()){
	add_filter('dynamic_sidebar_params', 'cactusthemes_hook_before_widget'); 	
	function cactusthemes_hook_before_widget($params){
		/* Add custom variation classs to widgets */
		global $expand_wl_options;
		$id=$params[0]['widget_id'];
		$classe_to_add = !empty( $expand_wl_options[$id ] ) ? htmlspecialchars( stripslashes( $expand_wl_options[$id ] ),ENT_QUOTES ) : '';
		
		if(preg_match('/icon-\w+\s*/',$classe_to_add,$matches)){
			if(ot_get_option( 'righttoleft', 0)){
				$params[0]['after_title'] = '<i class="'.$matches[0].'"></i>' . $params[0]['after_title'];
			} else {
				$params[0]['before_title'] .= '<i class="'.$matches[0].'"></i>';
			}
			$classe_to_add = str_replace('icon-','wicon-',$classe_to_add); // replace "icon-xxx" class to not add Awesome Icon before div.widget
		};
		
		if ($params[0]['before_widget'] != ""){  
			$classe_to_add = 'class="'.$classe_to_add.' ';
			$params[0]['before_widget'] = str_replace('class="',$classe_to_add,$params[0]['before_widget']);
		}else{
			$classe_to_add = $classe_to_add;
			$params[0]['before_widget'] = '<div class="'.$classe_to_add.'">';
			$params[0]['after_widget'] = '</div>';
		}
		
		return $params;
	}
}


/* Echo meta data tags */
function ct_meta_tags(){
	$description = get_bloginfo('description');
	if(is_author()){
		$description = get_the_author_meta( 'description' );
		if($description==''){$description = get_bloginfo('description');}
	}
	if(is_single()){
		global $post;
			
		$description = $post->post_excerpt;
		$url_video = get_post_meta($post->ID,'tm_video_url',true);
		if($url_video==''){$url_video = get_permalink($post->ID);}
		$play_on_facebook = ot_get_option('play_on_facebook');
		if($play_on_facebook==1){?>
            <meta property="og:title" content="<?php echo get_the_title($post->ID);?>"/>
            <meta property="og:type" content="website" />
            <meta property="og:image" content="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); ?>"/>
            <meta property="og:url" content="<?php echo get_permalink($post->ID);?>"/>
            <meta property="og:site_name" content="<?php echo get_bloginfo('name');?>"/>        
            <meta property="og:description" content="<?php echo strip_tags($description);?>"/>
            <?php if(ot_get_option('facebook_app_id')){ ?>
            	<meta property="fb:app_id" content="<?php echo ot_get_option('facebook_app_id');?>" />
            <?php }?>
		<?php }else{ ?>
            <meta property="og:title" content="<?php echo get_the_title($post->ID);?>"/>
            <meta property="og:type" content="<?php echo (get_post_format($post->ID) == 'video'? 'video.other':'');?>"/>
            <meta property="og:image" content="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)); ?>"/>
            <meta property="og:url" content="<?php echo $url_video;?>"/>
            <meta property="og:site_name" content="<?php echo get_bloginfo('name');?>"/>
            <meta property="og:description" content="<?php echo strip_tags($description);?>"/>
            <?php if(ot_get_option('facebook_app_id')){ ?>
                <meta property="fb:app_id" content="<?php echo ot_get_option('facebook_app_id');?>" />
            <?php }?>
        <?php 
		}
		if(get_post_format($post->ID) == 'video'){
			$video_file = get_post_meta($post->ID,'tm_video_file',true);
			if($video_file != ''){
				$urls = explode(PHP_EOL,$video_file);
				?>
				<meta property="og:video" content="<?php echo $urls[0];?>"/>
				<?php
				if(ot_get_option('video_secure_url','on')!='off' && $play_on_facebook!=1){
					?>
                    <meta property="og:video:secure_url" content="<?php echo $urls[0];?>"/>
                    <?php
				}
			}elseif($play_on_facebook!=1){
				if(ot_get_option('video_secure_url','on')!='off'){
					?>
                    <meta property="og:video" content="<?php echo $url_video;?>"/>
                    <meta property="og:video:secure_url" content="<?php echo $url_video;?>"/>
                    <?php
				}
			}
		
		?>
		<?php }?>
	<?php
	}else{
		?>
		<meta property="description" content="<?php echo strip_tags($description);?>"/>
		<?php
	}
}

/** 
 * Get Thumbnail Image
 *
 */
if(!function_exists('cactus_thumbnail')){
	function cactus_thumbnail($size = array(), $post_id = -1, $source_sizes = ''){
		if($post_id == -1){ //if there is no ID
			$post_id = get_the_ID();
		}
		
		$size = apply_filters('cactus_thumbnail_size_filter', $size, $post_id);
        
		//get attachment id
		if(get_post_type($post_id) == 'attachment'){
			$attachment_id = $post_id;
		}else{
			$attachment_id = get_post_thumbnail_id($post_id);
		}
		
		//return
		if(function_exists('wp_get_attachment_image_srcset')){
			$lazyload = 'off';
			$lazyClass = '';

			$img_src = wp_get_attachment_image_url( $attachment_id, $size);
			$img_srcset = wp_get_attachment_image_srcset( $attachment_id, $size);
			$img_sizes = wp_get_attachment_image_sizes( $attachment_id, $size);
            
            if($source_sizes != ''){
                $img_sizes = $source_sizes;
            }
            
			$html_img_src = $img_src!=''?( ($lazyload=='on')?' data-src="'.$img_src.'"':' src="'.$img_src.'"' ):'';
			$html_img_responsive = ($img_srcset != '' && $img_sizes != '')?( ($lazyload == 'on')?' data-srcset="'.$img_srcset.'" data-sizes="'.$img_sizes.'"':' srcset="'.$img_srcset.'" sizes="'.$img_sizes.'"' ):'';
			
            $image_attributes = wp_get_attachment_image_src( $attachment_id, $size);
            
			if($lazyload == 'on'){
				$ratio = '';
				if(!empty($image_attributes)){
					$ratio = 'style="padding-top:'.($image_attributes[2]/$image_attributes[1]*100).'%;"';
				}
				
				$lazyload_dfimg = apply_filters('cactus_image_placeholder_url', get_template_directory_uri().'/images/dflazy.jpg', $size);

				$lazyClass = ' class="lazyload effect-fade" src="' . $lazyload_dfimg . '" ' . $ratio;
			}
						
			$html = $html_img_src != '' ? '<img width="'.$image_attributes[1].'" height="'.$image_attributes[2].'" '.$html_img_src.$html_img_responsive.$lazyClass.' alt="'.esc_attr(get_the_title($attachment_id)).'"/>' : '';
            	
			return $html;
			
		} else {
			return wp_get_attachment_image($attachment_id, $size);
		}
	}
}


if(!function_exists('cactus_paginate')){
	function cactus_paginate ($base_url, $query_str, $total_pages, $current_page, $paginate_limit)	{
		// Array to store page link list
		$page_array = array ();
		// Show dots flag - where to show dots?
		$dotshow = true;
		// walk through the list of pages
		for ( $i = 1; $i <= $total_pages; $i ++ ){
		   // If first or last page or the page number falls 
		   // within the pagination limit
		   // generate the links for these pages
		   if ($i == 1 || $i == $total_pages || 
				 ($i >= $current_page - $paginate_limit && 
				 $i <= $current_page + $paginate_limit) ) {
			  // reset the show dots flag
			  $dotshow = true;
			  // If it's the current page, leave out the link
			  // otherwise set a URL field also
			  if ($i != $current_page)
				  $page_array[$i]['url'] = add_query_arg($query_str, $i, $base_url);
              
			  $page_array[$i]['text'] = strval ($i);
		   }
		   // If ellipses dots are to be displayed
		   // (page navigation skipped)
		   else if ($dotshow == true) {
			   // set it to false, so that more than one 
			   // set of ellipses is not displayed
			   $dotshow = false;
			   $page_array[$i]['text'] = "...";
		   }
		}
		
		if(count($page_array) > 1){
		?>		
		
		<div class="pagination paging-navigation wp-pagenavi">
		
		<?php

		foreach ($page_array as $page) {

			// If page has a link
			if (isset ($page['url'])) { ?>
				<a href="<?php echo esc_url($page['url'])?>"> <?php echo esc_html($page['text']); ?> </a>
			<?php }

			// no link - just display the text
			 else 
				echo '<span class="current">' . $page['text'] . '</span>';
		}?>
		
		</div>
		
		<?php
		}
		
		
	}
}

function cactus_global_post(){
    global $post;
    return $post;
}
function cactus_global_wpdb(){
    global $wpdb;
    return $wpdb;
}
function cactus_global_wp_query(){
    global $wp_query;
    return $wp_query;
}
function cactus_global_wp(){
    global $wp;
    return $wp;
}