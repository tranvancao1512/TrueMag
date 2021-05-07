<?php 

$condition = get_post_meta(get_the_ID(),'header_home_condition',true) ? get_post_meta(get_the_ID(),'header_home_condition', true) : ot_get_option('header_home_condition','lastest');
$ids = get_post_meta(get_the_ID(),'header_home_postids',true) ? get_post_meta(get_the_ID(), 'header_home_postids', true) : ot_get_option('header_home_postids', '');
$categories = get_post_meta(get_the_ID(),'header_home_cat',true) ? get_post_meta(get_the_ID(), 'header_home_cat',true):ot_get_option('header_home_cat', '');
$tags = get_post_meta(get_the_ID(),'header_home_tag',true) ? get_post_meta(get_the_ID(), 'header_home_tag',true) : ot_get_option('header_home_tag', '');
$sort_by = get_post_meta(get_the_ID(),'header_home_order',true) ? get_post_meta(get_the_ID(), 'header_home_order', true) : ot_get_option('header_home_order', 'DESC');
$count = get_post_meta(get_the_ID(),'header_home_number',true) ? get_post_meta(get_the_ID(), 'header_home_number', true) : ot_get_option('header_home_number', 12);

$postformats = $timerange = $paged = '';
if($condition == 'most_viewed' || $condition == 'most_comments' || $condition == 'likes'){
	$timerange = get_post_meta(get_the_ID(), 'header_time_range', true) ? get_post_meta(get_the_ID(), 'header_time_range', true) : ot_get_option('header_time_range', '');
}

$auto = ot_get_option('header_home_auto', '');
$auto_timeout = ot_get_option('header_home_auto_timeout', '');
$auto_duration = ot_get_option('header_home_auto_duration', '');
$player = ot_get_option('header_home_use_player', '');

$header_bg = get_post_meta(get_the_ID(),'background', true);
	
if(!$header_bg) {
	$header_bg = ot_get_option('header_home_bg'); 
}
?>
<style type="text/css">
#classy-carousel{
<?php if(isset($header_bg['background-color']) && $header_bg['background-color'] != ''){ echo 'background-color:'.$header_bg['background-color'].';';} ?>
<?php if(isset($header_bg['background-attachment']) && $header_bg['background-attachment'] != ''){ echo 'background-attachment:'.$header_bg['background-attachment'].';';} ?>
<?php if(isset($header_bg['background-repeat'])&&$header_bg['background-repeat'] != ''){
	echo 'background-repeat:'.$header_bg['background-repeat'].';';
	echo 'background-size: initial;';
} ?>
<?php if(isset($header_bg['background-position'])&&$header_bg['background-position']!= ''){ echo 'background-position:'.$header_bg['background-position'].';';} ?>
<?php if(isset($header_bg['background-image'])&&$header_bg['background-image']!=''){ echo 'background-image:url('.$header_bg['background-image'].');';} ?>
}
<?php if(isset($header_bg['background-attachment']) && $header_bg['background-attachment'] == 'fixed'){ ?>
	/*@media(min-width:768px){
		#body-wrap{
			position:fixed;
			top:0;
			bottom:0;
			left:0;
			right:0;
		}
		.admin-bar #body-wrap{
			top:32px;
		}
	}
	@media(min-width:768px) and (max-width:782px){
		.admin-bar #body-wrap{
			top:46px;
		}
		.admin-bar #off-canvas{
			top:46px;
		}
	}
	.bg-ad {
		right: 14px;
	}*/
	<?php if(ot_get_option('theme_layout') != 1){ ?>
		/*#body-wrap{
			position:fixed;
			top:0;
			bottom:0;
			left:0;
			right:0;
		}
		.admin-bar #body-wrap{
			top:32px;
		}
		@media(max-width:782px){
			.admin-bar #body-wrap{
				top:46px;
			}
			.admin-bar #off-canvas{
				top:46px;
			}
		}*/
<?php } 
}?>
</style>
<?php

echo do_shortcode('[classy condition="'.$condition.'" ids="'.$ids.'" categories="'.$categories.'" tags="'.$tags.'" sort_by="'.$sort_by.'" count="'.$count.'" postformats="'.$postformats.'" timerange="'.$timerange.'" paged="'.$paged.'" auto="'.$auto.'" auto_timeout="'.$auto_timeout.'" auto_duration="'.$auto_duration.'" player="'.$player.'"]');