<div class="cactus-listing-config style-3 style-widget-popular-post subscribe-header">
<div class="cactus-sub-wrap">
  <article class="cactus-post-item hentry">
    <div class="entry-content">
    	<?php  if(has_post_thumbnail()){ ?>
        <div class="picture">
          <div class="picture-content">
              <a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>">
              	<?php echo videopro_thumbnail(array(298,298));?>
              </a>
          </div>
        </div>
        <?php };?>
        <div class="content">
          	<h3 class="cactus-post-title entry-title h6 sub-lineheight"> <a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title(); ?></a> </h3>
			<?php do_action('cactus-video-subscribe-button', $channel_ID);?>
        </div>
    </div>
  </article>
</div>
</div>

<div class="cactus-listing-config style-2 style-channel-listing"> <!--addClass: style-1 + (style-2 -> style-n)-->

    <div class="cactus-sub-wrap">                                            
      <?php 
	  $subscreibed_item_page = '3';
	  $layout = videopro_global_layout();
	  if($layout =='fullwidth'){ $subscreibed_item_page = 4;}
	  $args = array(
		  'post_type' => 'post',
		  'post_status' => 'publish',
		  'ignore_sticky_posts' => 1,
		  'posts_per_page' => $subscreibed_item_page,
		  'orderby' => 'latest',
		  'meta_query' => array(
			  array(
				  'key' => 'channel_id',
				   'value' => get_the_ID(),
				   'compare' => 'LIKE',
			  ),
		  )
	  );
	  ?>      
      <?php
	  $list_query = new WP_Query( $args );
	  $it = $list_query->post_count;
	  if($list_query->have_posts()){
	  while($list_query->have_posts()){$list_query->the_post();
		get_template_part( 'html/loop/content', get_post_format()  );
	  }
	  wp_reset_postdata();
    }?>                                           
    </div>
</div>