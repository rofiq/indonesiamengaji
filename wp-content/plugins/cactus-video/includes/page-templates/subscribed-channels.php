<?php
/**
 * Template Name: Subscribed Channels
 *
 * @package cactus
 */
if( !is_user_logged_in()){
	header('Location: ' . wp_login_url( get_permalink() ));
	exit();
}
get_header();

$sidebar = get_post_meta(get_the_ID(),'page_sidebar',true);
if(!$sidebar){
	$sidebar = ot_get_option('page_sidebar','both');
}
if($sidebar == 'hidden') $sidebar = 'full';
$layout = videopro_global_layout();
$sidebar_style = 'ct-small';
videopro_global_sidebar_style($sidebar_style);
?>
<!--body content-->
    <div id="cactus-body-container">
    
        <div class="cactus-sidebar-control <?php if($sidebar=='right' || $sidebar=='both'){?>sb-ct-medium <?php }?>  <?php if($sidebar!='full' && $sidebar!='right'){?>sb-ct-small <?php }?>"> <!--sb-ct-medium, sb-ct-small-->
        
            <div class="cactus-container <?php if($layout=='wide'){ echo 'ct-default';}?>">                        	
                <div class="cactus-row">
                    <?php if($layout=='boxed' && ($sidebar=='both')){?>
                        <div class="open-sidebar-small open-box-menu"><i class="fa fa-bars"></i></div>
                    <?php }?>
                    <?php if($sidebar=='left' || $sidebar=='both'){ get_sidebar('left'); } ?>
                    
                    <div class="main-content-col">
                        <div class="main-content-col-body">
							<?php videopro_breadcrumbs();?>                        
                            <h1 class="single-title entry-title"><?php the_title();?></h1>
                                <?php
                                if(is_active_sidebar('content-top-sidebar')){
                                    echo '<div class="content-top-sidebar-wrap">';
                                    dynamic_sidebar( 'content-top-sidebar' );
                                    echo '</div>';
                                } ?>
        
                                    <?php 
                                    $meta_user = get_user_meta(get_current_user_id(), 'subscribe_channel_id',true);
                                    if(!is_array($meta_user) && $meta_user!=''){
                                        $meta_user = explode(" ", $meta_user );
                                    }
									if(empty($meta_user)){$meta_user =array(-1);}
                                    $paged = get_query_var('paged')?get_query_var('paged'):(get_query_var('page')?get_query_var('page'):1);
                                    $query = new WP_Query( array( 'post_type' => 'ct_channel', 'post__in' => $meta_user , 'paged' => $paged ) );
                                    $it = $query->post_count;
                                    if($query->have_posts()){
                                        global $wp_query,$wp;
                                        $main_query = $wp_query;
                                        $wp_query = $query;
                                        ?>
                                        <div class="cactus-listing-wrap subscribe-listing">
											<script type="text/javascript">
                                             var cactus = {"ajaxurl":"<?php echo admin_url( 'admin-ajax.php' );?>","query_vars":<?php echo str_replace('\/', '/', json_encode(array( 'post_type' => 'ct_channel', 'post__in' => $meta_user ))) ?>,"current_url":"<?php echo home_url($wp->request);?>" }
                                            </script> 
                                            <?php	
											$_GET['sub_channel']= '1';
                                            while ( $query->have_posts() ) : $query->the_post(); 
                                                include ct_video_get_plugin_url() . 'templates/loop/content-feed.php';
                                            endwhile;
                                            wp_reset_postdata();
                                            ?>
                                        </div>
                                        <?php
										videopro_paging_nav('.cactus-listing-wrap.subscribe-listing', ct_video_get_plugin_url() . 'templates/loop/content-feed.php');
                                    }else{?>
                                        <div class="no-post">
                                            <h2 class="h4">You do not have any subscriptions.<br>Browse Channels to subscribe.</h2>
                                            <?php
											$query = new WP_Query( array('post_type'  => 'page', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'cactus-video/includes/page-templates/channel-listing.php' ) );
											if ( $query->have_posts() ){
												while ( $query->have_posts() ) : $query->the_post();?>
                                                <a href="<?php echo esc_url(get_permalink());?>" class="btn btn-default"><?php esc_html_e('Browse Channels','videopro');?></a>
                                                <?php 
											endwhile; 
                                            wp_reset_postdata();
											}else{?>
                                            	<a href="<?php echo get_post_type_archive_link('ct_channel');?>" class="btn btn-default"><?php esc_html_e('Browse Channels','videopro');?></a>
                                            <?php }?>
                                        </div>
                                    <?php }?>
                                <?php
                                if($it>0){ 
                                    $wp_query = $main_query;
                                }
                                ?>
                        </div>
                    </div>
                    
                    <?php 
					$sidebar_style = 'ct-medium';
					videopro_global_sidebar_style($sidebar_style);
					if($sidebar=='right' || $sidebar=='both'){ get_sidebar(); } ?>
                    
                </div>
            </div>
            
        </div>                
        
        
    </div><!--body content-->

<?php get_footer(); ?>