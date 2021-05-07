<?php

/**
 * Validate Video URL. Make sure URL is one of allowed video networks
 */
function tm_is_valid_video_url($url){
    // make sure it is a URL. Right now we accept all URL
    if(!filter_var($url, FILTER_VALIDATE_URL) === false){
        return true;
    } else {
        return apply_filters('tm_is_valid_video_url', false, $url );
    }
}

//contact form 7 hook
function tm_do_post_submission($posted_data, $contact_form_7 = null) {
    if(ot_get_option('user_submit',1)){
        if((isset($posted_data['video-url']) && tm_is_valid_video_url($posted_data['video-url'])) || isset($posted_data['video-file']) || isset($posted_data['video-code'])){
            $video_url = $posted_data['video-url'];
            $video_title = isset($posted_data['video-title']) ? $posted_data['video-title'] : '';
            $video_description = isset($posted_data['video-description']) ? $posted_data['video-description'] : '';
            $video_excerpt = isset($posted_data['video-excerpt'])?$posted_data['video-excerpt'] : '';
            $video_user = isset($posted_data['your-email']) ? $posted_data['your-email'] : '';
            $video_cat = isset($posted_data['cat']) ? $posted_data['cat'] : '';
            $video_tag = isset($posted_data['tag']) ? $posted_data['tag'] : '';
            $video_status = ot_get_option('user_submit_status','pending');
            $video_format = ot_get_option('user_submit_format','');
			
			/**
			 * fix contact form 7 missing current user ID
			 */
			$current_user_id = get_current_user_id();
			if(!$current_user_id){
				$current_user_id = apply_filters( 'determine_current_user', false );
				wp_set_current_user( $current_user_id );
			}
            
            $video_post = array(
              'post_content'   => $video_description,
              'post_excerpt'   => $video_excerpt,
              'post_name' 	   => sanitize_title($video_title), //slug
              'post_title'     => $video_title,
              'post_status'    => $video_status,
              'post_category'  => $video_cat,
              'tags_input'	   => $video_tag,
              'post_type'      => 'post',
			  'post_author' 	=> $current_user_id
            );

            if($new_ID = wp_insert_post( $video_post, $wp_error )){
                add_post_meta( $new_ID, 'tm_user_submit', $video_user );

                set_post_format( $new_ID, $video_format );
                
                if(isset($video_url)){
                    add_post_meta( $new_ID, 'tm_video_url', $video_url );
                }
                
                // video code
                if(isset($posted_data['video-code'])){
                    add_post_meta( $new_ID, 'tm_video_code', $posted_data['video-code'] );
                }
                
                // upload video file
                if(isset($posted_data['video-file']) && $posted_data['video-file'] != ''){
                    $video_location = '';
                    if(!$contact_form_7){
                        // gravity form uploaded, $posted_data['video-file'] already contains uploaded url 
                        add_post_meta( $new_ID, 'tm_video_file', $posted_data['video-file']);
                    } else {
                        $uploaded_files = $contact_form_7->uploaded_files();
                        $video_location = $uploaded_files["video-file"];
                    
                        $wud = wp_upload_dir();
                        $video_data = file_get_contents($video_location);
                        $filename = basename($video_location);

                        if(wp_mkdir_p($wud['path'])){
                            $file = $wud['path'].'/'.$filename;
                        } else {
                            $file = $wud['basedir'].'/'.$filename;
                        }
                        file_put_contents($file, $video_data);
                        require_once(ABSPATH . 'wp-admin/includes/admin.php');
                        $wp_filetype = wp_check_filetype(basename($filename), null );
                          $attachment = array(
                           'post_mime_type' => $wp_filetype['type'],
                           'post_title' => sanitize_file_name($filename),
                           'post_content' => '',
                           'post_status' => 'inherit'
                        );
                        $attach_id = wp_insert_attachment( $attachment, $file, $new_ID);
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                        $res = wp_update_attachment_metadata( $attach_id, $attach_data );
                        add_post_meta( $new_ID, 'tm_video_file', wp_get_attachment_url($attach_id));
                    }
                }
                
                // upload video thumbnail
                if(isset($posted_data['video-thumbnail']) && $posted_data['video-thumbnail'] != ''){
                    $file_location = '';
                    if(!$contact_form_7){
                        $file_location = $posted_data['video-thumbnail'];
                    } else {
                        $file_name = $posted_data["video-thumbnail"];
                        $uploaded_files = $contact_form_7->uploaded_files();
                        $file_location = $uploaded_files["video-thumbnail"];
                    }
                    
                    $upload_dir = wp_upload_dir();
                    $image_data = file_get_contents($file_location);
                    $filename = basename($file_location);
                    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
                    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
                    file_put_contents($file, $image_data);

                    $wp_filetype = wp_check_filetype($filename, null );
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => sanitize_file_name($filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    $attach_id = wp_insert_attachment( $attachment, $file, $new_ID );
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                    $res1 = wp_update_attachment_metadata( $attach_id, $attach_data );
                    $res2 = set_post_thumbnail( $new_ID, $attach_id );
                }                
                
                if( ot_get_option('user_submit_fetch','0') == '1' ){
                    do_action( 'save_post', $new_ID, get_post($new_ID), true );
                } else {
                    // mark do not fetch
                    add_post_meta( $new_ID, 'fetch_info', 1);
                }
            }
        }//if video_url || video-file || video-code
    }
    
    //catch report form
    if(isset($posted_data['report-url'])){
        $post_url = $posted_data['report-url'];
        $post_user = isset($posted_data['your-email']) ? $posted_data['your-email'] : '';
        $post_message = isset($posted_data['your-message']) ? $posted_data['your-message'] : '';
        
        $post_title = sprintf(__("%s reported a video",'cactusthemes'), $post_user);
        $post_content = sprintf(__("%s reported a video has inappropriate content with message:<blockquote>%s</blockquote><br><br>You could review it here <a href='%s'>%s</a>",'cactusthemes'), $post_user, $post_message, $post_url, $post_url);
        
        $report_post = array(
          'post_content'   => $post_content,
          'post_name' 	   => sanitize_title($video_title), //slug
          'post_title'     => $post_title,
          'post_status'    => 'publish',
          'post_type'      => 'tm_report'
        );
        if($new_report = wp_insert_post( $report_post, $wp_error )){
            add_post_meta( $new_report, 'tm_report_url', $post_url );
            add_post_meta( $new_report, 'tm_user_submit', $post_user );
        }
    }//if report_url
}


/**
 * contact form 7 hook for user submit video
 */
function tm_contactform7_hook($WPCF7_ContactForm) {
	if(ot_get_option('user_submit',0) == 1){
		$submission = WPCF7_Submission::get_instance();
		if($submission) {
			$posted_data = $submission->get_posted_data();
            
			tm_do_post_submission($posted_data, $submission);
		}
	}
}
add_action("wpcf7_before_send_mail", "tm_contactform7_hook");

function tm_wpcf7_add_shortcode(){
    $action = '';
	if(function_exists('wpcf7_add_form_tag')){
        $action = 'wpcf7_add_form_tag';
    } elseif(function_exists('wpcf7_add_shortcode')){
        // support Contact Form 7 prior to 4.6
        $action = 'wpcf7_add_shortcode';
    }
    
    if($action != ''){
        $action(array('category','category*'), 'tm_catdropdown', true);
		$action(array('report_url','report_url*'), 'tm_report_input', true);
    }
}
function tm_catdropdown($tag){
	$class = '';
	$is_required = 0;
	
	if(class_exists('WPCF7_FormTag')){
		// CF7 4.6+
		$tag = new WPCF7_FormTag( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	} elseif(class_exists('WPCF7_Shortcode')) {
		$tag = new WPCF7_Shortcode( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	}
	
	$cargs = array(
		'hide_empty'    => false, 
		'exclude'       => explode(",",ot_get_option('user_submit_cat_exclude',''))
	); 
	$cats = get_terms( 'category', $cargs );
	if($cats){
		$output = '<div class="wpcf7-form-control-wrap cat"><div class="row wpcf7-form-control wpcf7-checkbox wpcf7-validates-as-required'.$class.'">';
		if(ot_get_option('user_submit_cat_radio','off')=='on'){
			foreach ($cats as $acat){
				$output .= '<label class="col-md-4 wpcf7-list-item"><input type="radio" name="cat[]" value="'.$acat->term_id.'" /> '.$acat->name.'</label>';
			}
		}else{
			foreach ($cats as $acat){
				$output .= '<label class="col-md-4 wpcf7-list-item"><input type="checkbox" name="cat[]" value="'.$acat->term_id.'" /> '.$acat->name.'</label>';
			}
		}
		$output .= '</div></div>';
	}
	
	return $output;
}
function tm_report_input($tag){
	$class = '';
	$is_required = 0;
	if(class_exists('WPCF7_FormTag')){
		// CF7 4.6+
		$tag = new WPCF7_FormTag( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	} elseif(class_exists('WPCF7_Shortcode')) {
		$tag = new WPCF7_Shortcode( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	}
	
	$output = '<div class="hidden wpcf7-form-control-wrap report_url"><div class="wpcf7-form-control wpcf7-validates-as-required'.$class.'">';
	$output .= '<input name="report-url" class="hidden wpcf7-form-control wpcf7-text wpcf7-validates-as-required" type="hidden" value="'.esc_attr(curPageURL()).'" />';
	$output .= '</div></div>';
	return $output;
}


add_action( 'init', 'tm_wpcf7_add_shortcode' );

if(class_exists('GFForms')){
    include get_template_directory() .'/inc/gravityforms/classes/class-gf-field-categories.php';
    include get_template_directory() .'/inc/gravityforms/classes/class-gf-field-tags.php';
    include get_template_directory() .'/inc/gravityforms/classes/class-gf-field-report.php';
}

/**
 * Add custom fields to gravity forms
 */
add_filter('gform_add_field_buttons', 'tm_gform_custom_fields');
function tm_gform_custom_fields($g_fields){
    $new_group = array( 'name' => 'tm_submission_fields', 'label' => esc_html__( 'TrueMag Post Submission', 'cactusthemes' ), 'tooltip_class' => 'tooltip_bottomleft' );
    $new_group['fields'] = array(
                            array(
                                'class'     => 'button',
                                'data-type' => 'vs_categories',
                                'value'     => esc_html__( 'Categories', 'cactusthemes' )
                            ),
                            array(
                                'class'     => 'button',
                                'data-type' => 'vs_tags',
                                'value'     => esc_html__( 'Tags', 'cactusthemes' )
                            )                            
                            );
    
    $g_fields[] = $new_group;
    
    $new_group = array( 'name' => 'tm_report_fields', 'label' => esc_html__( 'TrueMag Post Report', 'cactusthemes' ), 'tooltip_class' => 'tooltip_bottomleft' );
    $new_group['fields'] = array(
                            array(
                                'class'     => 'button',
                                'data-type' => 'vs_report',
                                'value'     => esc_html__( 'Report URL', 'cactusthemes' )
                            ));
                            
    $g_fields[] = $new_group;

    return $g_fields;
}

 /** 
 * GravityForm hook for user submit video
 */
function tm_gravity_user_submit($lead, $form){
    $posted_data = array();
    
    $data = array(
    'video-url',
    'video-file',
    'video-code',
    'video-thumbnail',
    'video-title',
    'video-description',
    'video-excerpt',
    'your-email',
    'cat',
    'tag',
    'report-url',
    'your-email',
    'your-message'
    );
    
    
    foreach($form['fields'] as $field){
        if(isset($field['adminLabel'])){
            $key = $field['adminLabel'];
            
            if(in_array($key, $data)){
                if($key == 'video-file' || $key == 'video-thumbnail'){
                    $file = GFFormsModel::get_fileupload_value($form['id'], 'input_' . $field['id']);
                    
                    $files = json_decode($file);
                    if(is_array($files)){
                        $posted_data[$key] = $files[0];
                    } else {
                        $posted_data[$key] = $file;
                    }
                } else {
                    $posted_data[$key] = GFFormsModel::get_field_value($field);
                }
            }
        }
    }

    tm_do_post_submission($posted_data);    
}
add_action('gform_after_submission', 'tm_gravity_user_submit', 10, 2);

// notify users if submitted posts are published
add_action( 'save_post', 'tm_notify_user_submit');
function tm_notify_user_submit( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || !ot_get_option('user_submit_notify',1) )
		return;
	$notified = get_post_meta($post_id,'notified',true);
	$email = get_post_meta($post_id,'tm_user_submit',true);
	if(!$notified && $email && get_post_status($post_id)=='publish'){
		$subject = __('Your video submission has been approved','cactusthemes');
		$message = __('Your video ','cactusthemes').get_post_meta($post_id,'tm_video_url',true).' '.__('has been approved. You can see it here','cactusthemes').' '.get_permalink($post_id);
		wp_mail( $email, $subject, $message );
		update_post_meta( $post_id, 'notified', 1);
	}
}