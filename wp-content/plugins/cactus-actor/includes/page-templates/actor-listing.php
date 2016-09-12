<?php
/**
 * Template Name: Actor Listing
 *
 * @package videopro
 */

get_header();
$sidebar = ot_get_option('blog_sidebar','right');
$sidebar = ot_get_option('blog_sidebar','right') == 'hidden' ? 'full' : $sidebar;
$layout = videopro_global_layout();
$sidebar_style = 'ct-small';
videopro_global_sidebar_style($sidebar_style);
?>
<div id="cactus-body-container">
    <div class="cactus-sidebar-control <?php if($sidebar!='full' && $sidebar!='left'){?>sb-ct-medium<?php }if($sidebar!='full' && $sidebar!='right'){?> sb-ct-small<?php }?>"> <!--sb-ct-medium, sb-ct-small-->
        <div class="cactus-container <?php if($layout=='wide'){ echo 'ct-default';}?>">                        	
            <div class="cactus-row">
				<?php if($layout=='boxed'&& $sidebar=='both'){?>
                    <div class="open-sidebar-small open-box-menu"><i class="fa fa-bars"></i></div>
                <?php }?>
                <?php if($sidebar!='full' && $sidebar!='right'){ get_sidebar('left'); } ?>
                <?php if(is_active_sidebar('content-top-sidebar')){
                    echo '<div class="content-top-sidebar-wrap">';
                    dynamic_sidebar( 'content-top-sidebar' );
                    echo '</div>';
                } ?>
                <div class="main-content-col">
                    <div class="main-content-col-body">
                    
                        <?php if(function_exists('videopro_breadcrumbs')){
							 videopro_breadcrumbs();
						}?>
                		<h1 class="castings-title category-title entry-title"><?php if(is_page()){the_title();} else{ esc_html_e('Actors','videopro');}?></h1>
                        <?php 
						if(get_option('permalink_structure') != ''){
							$curent_url = home_url( $wp->request );
							if(function_exists('qtrans_getLanguage') && qtrans_getLanguage()!=''){
								$curent_url = '//'.$_SERVER["HTTP_HOST"].$_SERVER['REDIRECT_URL'];
							}
						}else{
							$query_string = $wp->query_string;
							if(isset($_GET['lang'])){
								$query_string = $wp->query_string.'&lang='.$_GET['lang'];
							}
							$curent_url = add_query_arg( $query_string, '', home_url( $wp->request ) );
						}?>
                        <div class="alphabet-filter">
                            <a class="font-size-1 ct-gradient metadata-font <?php echo isset($_GET['orderby'])?'':'active' ?>" href="<?php echo esc_url(add_query_arg( array('' =>''), $curent_url )); ?>"><?php esc_html_e('All','videopro'); ?></a>
                                <?php 
								if(is_tax()){
									//$curent_url =home_url().'/'.$slug_mb.'/';
								}
								$startCapital = 65;
                                for($i = 0;$i<26;$i++){
                                ?>
                            	<a class="font-size-1 ct-gradient metadata-font <?php echo (isset($_GET['orderby']) && chr($startCapital + $i)==$_GET['orderby'])?'active':'' ?>" href="<?php echo esc_url(add_query_arg( array('orderby' => chr($startCapital + $i)), $curent_url )); ?>"><?php echo chr($startCapital + $i); ?></a>
                              	<?php }?>
                        </div>
                        <?php 
						$paged = get_query_var('paged')?get_query_var('paged'):(get_query_var('page')?get_query_var('page'):1);
						$args = array(
							'post_type' => 'ct_actor',
							'posts_per_page' => get_option('posts_per_page'),
							'post_status' => 'publish',
							'ignore_sticky_posts' => 1,
							'paged' => $paged,
						);
						if(is_tax('actor_cat')){
							$args['tax_query'] = array(
								array(
								'taxonomy' => 'actor_cat',
								'field' => 'id',
								'terms' => get_queried_object()->term_id,
								 )
							  );
						}
						if(isset($_GET['orderby'])){
							$wpdb = videopro_global_wpdb(); 
							$request = $_GET['orderby']; // could be any letter you want
							$results = $wpdb->get_results($wpdb->prepare(
								  "
								  SELECT ID FROM $wpdb->posts
								  WHERE post_title LIKE %s
								  AND post_type = 'ct_actor'
								  AND post_status = 'publish'; 
								  ",$request.'%')
							); 
							if(!empty($results)){
								$ar_id = array();
								foreach ($results as $item){
									$ar_id[]= $item->ID;
								}
								$args['post__in'] = $ar_id;
							}else{
								$args['post__in'] = array(-1);
							}
						}
						$list_query = new WP_Query( $args );
						$it = $list_query->post_count;
						if($list_query->have_posts()){?>
						<?php
						$wp_query = videopro_global_wp_query();
						$wp = videopro_global_wp();
						$main_query = $wp_query;
						$wp_query = $list_query;
						?>
						
						<script type="text/javascript">
						 var cactus = {"ajaxurl":"<?php echo admin_url( 'admin-ajax.php' );?>","query_vars":<?php echo str_replace('\/', '/', json_encode($args)) ?>,"current_url":"<?php echo home_url($wp->request);?>" }
						</script> 
                            <div class="cactus-listing-wrap actor-listing">
                                <div class="cactus-listing-config style-2 style-castings"> <!--addClass: style-1 + (style-2 -> style-n)-->
                                    <div class="cactus-sub-wrap">
                                    <?php
										if ( have_posts() ) :
											while ( have_posts() ) : the_post();
												include actor_get_plugin_url() . 'templates/loop/content-actor.php';
											endwhile;
										endif;
									?>                                          
                                    </div>
                                </div>
                            </div>
                            <?php videopro_paging_nav('.cactus-listing-wrap.actor-listing .cactus-sub-wrap', actor_get_plugin_url() . 'templates/loop/content-actor.php'); ?>
                        <?php }else{
							get_template_part( 'html/loop/content', 'none' );
						}
						wp_reset_postdata();
                        if($it>0){
                            $wp_query = $main_query;
                        }
                        ?>
                    </div>
                </div>
                 <?php 
                $sidebar_style = 'ct-medium';
				videopro_global_sidebar_style($sidebar_style);
                if($sidebar!='full'&& $sidebar!='left'){ get_sidebar(); } ?>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
