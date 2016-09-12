<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package cactus
 */

get_header();

$sidebar = ot_get_option('page_sidebar','right');
$sidebar_style = 'ct-small';
videopro_global_sidebar_style($sidebar_style);
$videopro_layout = videopro_global_layout();
?>

    <div id="cactus-body-container"> <!--Add class cactus-body-container for single page-->
        <div class="cactus-sidebar-control sb-ct-medium sb-<?php echo esc_attr($sidebar_style);?>">
        <div class="cactus-container <?php if($videopro_layout == 'wide'){ echo 'ct-default';}?>">                        	
            <div class="cactus-row">
            	<?php if($videopro_layout == 'boxed'){?>
                    <div class="open-sidebar-small open-box-menu"><i class="fa fa-bars"></i></div>
                <?php }?>
                <?php if($sidebar!='full'){ get_sidebar('left'); } ?>
                <div class="main-content-col">
                    <div class="main-content-col-body">
						<?php if(function_exists('videopro_breadcrumbs')){
                             videopro_breadcrumbs();
                        }?>  
                        <?php if(is_active_sidebar('content-top-sidebar')){
							echo '<div class="content-top-sidebar-wrap">';
							dynamic_sidebar( 'content-top-sidebar' );
							echo '</div>';
						} ?>
                        <?php
							$videopro_author = videopro_global_author();
							$userdata               = get_userdata($videopro_author);
							$videopro_author_description     = (is_object($userdata) && get_the_author_meta('description',$userdata->ID)) != '' ? get_the_author_meta('description',$userdata->ID) : '';
						?>
                        <div class="cactus-author-post single-actor">
                          <div class="cactus-author-pic">
                            <div class="img-content">
                            	<?php
								
								echo get_avatar( get_the_author_meta('email', $userdata->ID), 400, esc_url(get_template_directory_uri() . '/images/avatar-2x-retina.jpg') );
                                
                                ?>
                            </div>
                          </div>
                          <div class="cactus-author-content">
                            <div class="author-content"> <h3 class="author-name h1"><?php echo esc_html($userdata->display_name);?></h3> 
							  <?php if($videopro_author_description){?>
                                  <span class="author-body"><?php echo esc_html($videopro_author_description);?></span>
                              <?php }?>                                
                              <ul class="social-listing list-inline">
							  
                                  <?php
								  if($email = get_the_author_meta('email',$userdata->ID) && ot_get_option('author_page_email_contact','on') == 'on'){ ?>
									  <li class="email"><a rel="nofollow" href="mailto:<?php echo esc_attr($email); ?>" title="<?php esc_html_e('Email', 'videopro');?>"><i class="fa fa-envelope-o"></i></a></li>
								  <?php }
								  
								  if(ot_get_option('author_page_social_accounts','on') == 'on'){
									  if($facebook = get_the_author_meta('facebook',$userdata->ID)){ ?>
										  <li class="facebook"><a rel="nofollow" href="<?php echo esc_url($facebook); ?>" title="<?php esc_html_e('Facebook', 'videopro');?>"><i class="fa fa-facebook"></i></a></li>
									  <?php }
									  if($twitter = get_the_author_meta('twitter',$userdata->ID)){ ?>
										  <li class="twitter"><a rel="nofollow" href="<?php echo esc_url($twitter); ?>" title="<?php esc_html_e('Twitter', 'videopro');?>"><i class="fa fa-twitter"></i></a></li>
									  <?php }
									  if($linkedin = get_the_author_meta('linkedin',$userdata->ID)){ ?>
										  <li class="linkedin"><a rel="nofollow" href="<?php echo esc_url($linkedin); ?>" title="<?php esc_html_e('Linkedin', 'videopro');?>"><i class="fa fa-linkedin"></i></a></li>
									  <?php }
									  if($tumblr = get_the_author_meta('tumblr',$userdata->ID)){ ?>
										  <li class="tumblr"><a rel="nofollow" href="<?php echo esc_url($tumblr); ?>" title="<?php esc_html_e('Tumblr', 'videopro');?>"><i class="fa fa-tumblr"></i></a></li>
									  <?php }
									  if($google = get_the_author_meta('google',$userdata->ID)){ ?>
										 <li class="google-plus"> <a rel="nofollow" href="<?php echo esc_url($google); ?>" title="<?php esc_html_e('Google Plus', 'videopro');?>"><i class="fa fa-google-plus"></i></a></li>
									  <?php }
									  if($pinterest = get_the_author_meta('pinterest',$userdata->ID)){ ?>
										 <li class="pinterest"> <a rel="nofollow" href="<?php echo esc_url($pinterest); ?>" title="<?php esc_html_e('Pinterest', 'videopro');?>"><i class="fa fa-pinterest"></i></a></li>
									  <?php }
									  
									  if($custom_acc = get_the_author_meta('cactus_account',$userdata->ID)){
										  foreach($custom_acc as $acc){
											  if($acc['icon'] || $acc['url']){
										  ?>
										  <li class="cactus_account custom-account-<?php echo sanitize_title(@$acc['title']);?>"><a rel="nofollow" href="<?php echo esc_attr(@$acc['url']); ?>" title="<?php echo esc_attr(@$acc['title']);?>"><i class="fa <?php echo esc_attr(@$acc['icon']);?>"></i></a></li>
									  <?php 	}
										  }
									  }
								  }
								  ?>
                              </ul>
                            </div>
                          </div>
                        </div>
                        
                        <div class="single-divider"></div>                      
                        <h1 class="h4 category-title entry-title single-actor"><?php esc_html_e('Videos','videopro');?></h1>
                        <div class="cactus-listing-wrap">
                            <div class="cactus-listing-config style-2"> <!--addClass: style-1 + (style-2 -> style-n)-->
                                <div class="cactus-sub-wrap">
                                
                                    <?php if ( have_posts() ) : ?>
										<?php while ( have_posts() ) : the_post(); ?>
                                        <!--item listing-->                                                
                                            <?php get_template_part( 'html/loop/content', get_post_format() ); ?>
                                        <?php endwhile; ?>
                                    <?php else : ?>
            
                                        <?php get_template_part( 'html/loop/content', 'none' ); ?>
            
                                    <?php endif; ?>
                                    <!--item listing-->
                                                                                    
                                </div>
                                
                                <?php videopro_paging_nav('.cactus-listing-config .cactus-sub-wrap','html/loop/content'); ?>
                                <?php if(is_active_sidebar('content-bottom-sidebar')){
									echo '<div class="content-bottom-sidebar-wrap">';
									dynamic_sidebar( 'content-bottom-sidebar' );
									echo '</div>';
								} ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
				<?php 
                $sidebar_style = 'ct-medium';
				videopro_global_sidebar_style($sidebar_style);
                if($sidebar!='full'){ get_sidebar(); } ?>
        
            </div>
        </div>
        
    </div>                
    
    
</div>
<?php get_footer(); ?>