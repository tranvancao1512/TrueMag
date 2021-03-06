<?php

// Register custom post type "Page Section"
add_action('init', 'member'); 
function member()  {
	$member_slug = function_exists('ot_get_option') ? ot_get_option('member_slug','member') : 'member';
    $labels = array(  
    'name' => __('Members', 'cactusthemes'),  
    'singular_name' => __('Member', 'cactusthemes'),  
    'add_new' => __('Add New Member', 'cactusthemes'),  
    'add_new_item' => __('Add New Member', 'cactusthemes'),  
    'edit_item' => __('Edit Member', 'cactusthemes'),  
    'new_item' => __('New Member', 'cactusthemes'),  
    'view_item' => __('View Member', 'cactusthemes'),  
    'search_items' => __('Search Member', 'cactusthemes'),  
    'not_found' =>  __('No Member found', 'cactusthemes'),  
    'not_found_in_trash' => __('No Member found in Trash', 'cactusthemes'),  
    'parent_item_colon' => '' 
  );  
  
  $args = array(  
    'labels' => $labels,  
    'menu_position' => 8, 
    'supports' => array('title','editor','thumbnail',),
	'public' => false,
	'show_ui' => true,
	/*'menu_icon' =>  get_stylesheet_directory_uri(). '/images/member.png',*/
	'publicly_queryable' => true,
	'has_archive' => true,
	'hierarchical' => false,
	'rewrite' => array('slug' => $member_slug),
  );  
  register_post_type('member',$args);  
} 


//add_action('admin_init', 'remove_permalink');

//function remove_permalink() {
//
//	if($_GET['post']) {
//
//		$post_type = get_post_type($_GET['post']);
//
//		if($post_type == 'member' && $_GET['action'] == 'edit') {
//			echo '<style>#edit-slug-box{display:none;}</style>';
//		}
//	}
//}

// Add meta data
add_action( 'admin_init', 'member_meta_boxes' );


function member_meta_boxes() {
	/**
	 * Supported TYPE:
	 * background
	 * ...
	 * ...
	 * ...
	 */
  $my_meta_box = array(
    'id'        => 'my_meta_box',
    'title'     => 'Info',
    'desc'      => '',
    'pages'     => array( 'member' ),
    'context'   => 'normal',
    'priority'  => 'high',
    'fields'    => array(
      array(
        'id'          => 'position',
        'label'       => 'Position',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'mb-hover',
        'label'       => 'Set Image when hover',
        'desc'        => 'Upload image',
        'std'         => '',
        'type'        => 'upload',
        'class'       => '',
		'choices'     => array()
      ),	  
    )
  );  

	if (function_exists('ot_register_meta_box')) {
	  ot_register_meta_box( $my_meta_box );
	}
}

add_filter('manage_member_posts_columns', 'mb_posts_columns_id', 1);
function mb_posts_columns_id($defaults){
    $defaults['wps_post_id'] = __('ID');
    return $defaults;
}
add_action('manage_member_posts_custom_column', 'tm_posts_custom_id_columns', 1, 2);
function tm_posts_custom_id_columns($column_name, $id){
        if($column_name === 'wps_post_id'){
                echo $id;
    }
}



add_action( 'admin_init', 'member_meta_boxes1' );

function member_meta_boxes1() {
	/**
	 * Supported TYPE:
	 * background
	 * ...
	 * ...
	 * ...
	 */
  $my_meta_box2 = array(
    'id'        => 'my_meta_box2',
    'title'     => 'Social',
    'desc'      => '',
    'pages'     => array( 'member' ),
    'context'   => 'normal',
    'priority'  => 'high',
    'fields'    => array(
      array(
        'id'          => 'dribbble',
        'label'       => 'Dribble',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),

      array(
        'id'          => 'envelope-o',
        'label'       => 'Email',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'facebook',
        'label'       => 'Facebook',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'flickr',
        'label'       => 'Flickr',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'google+',
        'label'       => 'Google+',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'instagram',
        'label'       => 'Instagram',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'linkedIn',
        'label'       => 'LinkedIn',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'pinterest',
        'label'       => 'Pinterest',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'rss',
        'label'       => 'RSS',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'stumbleupon',
        'label'       => 'StumbleUpon',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'twitter',
        'label'       => 'Twitter',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'vimeo',
        'label'       => 'Vimeo',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
      array(
        'id'          => 'youtube',
        'label'       => 'YouTube',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'class'       => '',
        'choices'     => array()
      ),
    )
	
  );  

	if (function_exists('ot_register_meta_box')) {
  		ot_register_meta_box( $my_meta_box2 );
	}
}


