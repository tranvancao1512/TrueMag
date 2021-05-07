<article <?php post_class(is_single()&&get_post_format()=='video'?'video-item single-video-view':'');
if($review_point = get_post_meta(get_the_ID(),'taq_review_score',true)){
?> itemprop="review" itemscope itemtype="http://schema.org/Review" >
<div class="hidden">
	<span itemprop="itemReviewed"><?php the_title() ?></span>
    <span itemprop="author"><?php echo get_bloginfo('name') ?></span>
    <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
         Rating: <span itemprop="ratingValue"><?php echo round($review_point/10,1) ?></span> / <meta itemprop="bestRating" content="10"/>10
    </span>
</div>
<?php }else{ echo '>';} ?>
	

    <?php 
	if(is_front_page()||is_page_template('page-templates/front-page.php')){
		$show_hide_title = ot_get_option('show_hide_title','1');
		if($show_hide_title=='1'){?>
    		<h1 class="light-title entry-title"><?php the_title(); ?></h1>
	<?php }
	}else if(is_single() && get_post_format()=='video'){ ?>
    	<?php tm_display_ads('ad_single_content');?>
		<h1 class="light-title entry-title"><?php the_title(); ?></h1>
        <div class="item-info">
            <?php if(ot_get_option('single_show_meta_author',1)){?><span class="vcard author"><span class="fn"><?php the_author_posts_link();?></span></span> <?php } ?>
            <?php if(ot_get_option('single_show_meta_date',1)){ ?>
            <span class="item-date"><span class="post-date updated"><?php the_time(get_option('date_format')); ?> <?php the_time(get_option('time_format')); ?></span></span>
            <?php }?>
        </div>
	<?php } ?>       
    <div class="<?php echo is_single()?'item-content':'content-single'; echo ot_get_option('showmore_content',1)?'':' toggled' ?>">
        <?php the_content(); ?>
        <?php
		$pagiarg = array(
			'before'           => '<div class="single-post-pagi">'.__( 'Pages:','cactusthemes'),
			'after'            => '</div>',
			'link_before'      => '<span type="button" class="btn btn-default btn-sm">',
			'link_after'       => '</span>',
			'next_or_number'   => 'number',
			'separator'        => ' ',
			'nextpagelink'     => __( 'Next page','cactusthemes'),
			'previouspagelink' => __( 'Previous page','cactusthemes'),
			'pagelink'         => '%',
			'echo'             => 1
		);
		wp_link_pages($pagiarg);
		edit_post_link(esc_html__( 'Edit','cactusthemes'),'<i class="fa fa-pencil"></i>  ');
		?>
        <div class="clearfix"></div>
        <?php if(is_single()&&get_post_format()=='video'){ ?>
        <div class="item-tax-list">
        	<?php 
			  $onoff_tag = ot_get_option('onoff_tag');
			  $onoff_cat = ot_get_option('onoff_cat');
			  if($onoff_cat !='0'){
			?>
            <div><strong><?php _e('Category:', 'cactusthemes'); ?> </strong><?php the_category(', '); ?></div>
            <?php }
			if($onoff_tag !='0'){
			?>
            <div><?php the_tags('<strong>'.__('Tags:', 'cactusthemes').' </strong>', ', ', ''); ?></div>
            <?php } ?>
        </div>
        <?php
			global $video_toolbar_html;
			if(ot_get_option('video_toolbar_position','top')=='bottom'){
				echo $video_toolbar_html;
			}
		}elseif(is_single() && !(function_exists('is_bbpress') && is_bbpress())){ ?>
        <h1 class="light-title entry-title hidden"><?php the_title(); ?></h1>
        <div class="blog-meta">
        	<?php 
			 $onoff_tag = ot_get_option('onoff_tag');
			 $onoff_cat = ot_get_option('onoff_cat');
			 $single_show_meta_author = ot_get_option('single_show_meta_author');
			 $single_show_meta_comment = ot_get_option('single_show_meta_comment');
			if($onoff_tag !='0'){ ?>
            <div class="blog-tags"><?php the_tags('<i class="fa fa-tags"></i> ', ', ', ''); ?></div>
			<?php } ?>
            <div class="blog-meta-cat"><?php 
				if($single_show_meta_author !='0'){  ?><span class="vcard author"><span class="fn"><?php the_author_posts_link();?></span></span> | <?php } 
				if($onoff_cat !='0'){ the_category(', ');?> | <?php }?>
                <?php if(ot_get_option('single_show_meta_date',1)){ ?>
                    <span class="item-date"><span class="post-date updated"><?php the_time(get_option('date_format')); ?> <?php the_time(get_option('time_format')); ?></span></span>
                <?php }?>
            <?php
			 if($single_show_meta_comment !='0'){
					 if(comments_open()){ ?><a href="#comment"><?php comments_number(__('0 Comments','cactusthemes'),__('1 Comment','cactusthemes')); ?></a></span><?php } 
			}//check comment open?>
			<?php if(ot_get_option('show_hide_sharethis')){?>
                <div class="pull-right share-this">
                    <span class='st_sharethis' displayText=''></span>
                    <span class='st_facebook' displayText=''></span>
                    <span class='st_twitter' displayText=''></span>
                    <span class='st_googleplus' displayText=''></span>
                    <span class='st_pinterest' displayText=''></span>
                    <script type="text/javascript">var switchTo5x=false;</script>
                    <script type="text/javascript">var switchTo5x=false;</script>
                        <?php 
                          $_share_this_link = 'http://w.sharethis.com/button/buttons.js';
                          if ( is_ssl() ) {
                            $_share_this_link = 'https://ws.sharethis.com/button/buttons.js';
                          }
                        ?>
                    <script type="text/javascript" src="<?php echo esc_url( $_share_this_link );?>"></script>
                    <script type="text/javascript">stLight.options({publisher: "37243fc6-d06b-449d-bdd3-a60613856c42", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>
                </div>
            <?php }else{
				echo '<div class="pull-right social-links">';
				tm_social_share();
				echo '</div>';
			}?>
            </div>
        </div>
        <?php }?>
    </div><!--/item-content-->
    <?php if(is_single() && get_post_format()=='video' && ot_get_option('showmore_content',1)){ ?>
    <div class="item-content-toggle">
    	<div class="item-content-gradient"></div>
    	<i class="fa fa-angle-down maincolor2hover"></i>
    </div>
    <?php } ?>
</article>