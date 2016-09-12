<?php
$id = get_the_ID();
$post_data = videopro_get_post_viewlikeduration($id);
extract($post_data);

if((isset($atts_sc['show_like']) && $atts_sc['show_like'] =='0')){
	$like ='';
}
if((isset($atts_sc['show_duration']) && $atts_sc['show_duration'] =='0')){
	$time_video ='';
}
$img_id         = get_post_thumbnail_id(get_the_ID());
$image          = wp_get_attachment_image_src($img_id, 'full');

$link_post = apply_filters('videopro_loop_item_url', get_the_permalink(), $id);
?>

<!--item listing-->                                                
<article class="cactus-post-item hentry">

    <div class="entry-content" <?php if((isset($atts_sc['videoplayer_inline']) && $atts_sc['videoplayer_inline'] =='1' && $post_format == 'video')){ ?>data-id="<?php echo esc_attr($output_id.$id);?>" <?php }?>>                                        
        
        <!--picture (remove)-->
        <div class="picture">
            <div class="picture-content" style="background-image:url(<?php echo esc_url($image[0]);?>)">
                <div class="content-big-layout">
					<?php 
					$lightbox 			= isset($atts_sc['videoplayer_lightbox']) ? $atts_sc['videoplayer_lightbox'] : '';
					$post_format = get_post_format($id);
					echo apply_filters('videopro_loop_item_icon', $post_format == 'video' ? '<div class="ct-icon-video big-icon-a"></div>' : '', $id, $post_format, $lightbox, 'big-icon-a');?> 
					
                    <h2><?php the_title();?></h2>
                    <span class=""><?php echo videopro_show_cat(1,'font-size-3',1);?></span>
                    
                    <a href="<?php echo esc_url($link_post); ?>" target="<?php echo apply_filters('videopro_loop_item_url_target', '_self', $id);?>" title="<?php the_title_attribute(); ?>" class="big-link"></a>
                    
                    <?php if((isset($atts_sc['videoplayer_inline']) && $atts_sc['videoplayer_inline'] =='1' && $post_format == 'video')){
						videopro_video_inline($output_id);
					}?>
                </div>                                           
            </div>                              
        </div><!--picture-->
        
    </div>
    
</article><!--item listing-->

