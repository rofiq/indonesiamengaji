<?php
/*
Template Name: Authors Listing
*/

// Get all users order by amount of posts
$paged = 1;

if(isset($_GET['paged'])){
    $paged = intval($_GET['paged']);
}

$posts_per_page = get_post_meta(get_the_ID(), 'authors_per_page', true);

if(!isset($posts_per_page) || $posts_per_page <= 0){
    $posts_per_page = 0;
}

$args = array(
                    'orderby' => 'display_name',
                    'order' => 'ASC',
                    'paged' => $paged,
                    'role'  => 'author');
                    
if($posts_per_page != 0){
    $args['number'] = $posts_per_page;
}

$allUsers = get_users($args);

// count total users
$user_query = new WP_User_Query(array('role'=>'author','count_total'=>true));
$totalCount = $user_query->get_total();

$layout = get_post_meta(get_the_ID(),'sidebar',true);
if(!$layout){
	$layout = $global_page_layout ? $global_page_layout : ot_get_option('page_layout','right');
}

?>
<?php
get_header();

$videopro_sidebar = get_post_meta(get_the_ID(),'page_sidebar',true);
if(!$videopro_sidebar){
	$videopro_sidebar = ot_get_option('page_sidebar','both');
}
if($videopro_sidebar == 'hidden') $videopro_sidebar = 'full';
$videopro_page_title = videopro_global_page_title();
$videopro_layout = videopro_global_layout();
$videopro_sidebar_style = 'ct-small';
videopro_global_sidebar_style($videopro_sidebar_style);
?>
    <!--body content-->
    <div id="cactus-body-container">
    
        <div class="cactus-sidebar-control <?php if($videopro_sidebar=='right' || $videopro_sidebar=='both'){?>sb-ct-medium <?php }?>  <?php if($videopro_sidebar!='full' && $videopro_sidebar!='right'){?>sb-ct-small <?php }?>"> <!--sb-ct-medium, sb-ct-small-->
        
            <div class="cactus-container <?php if($videopro_layout=='wide'){ echo 'ct-default';}?>">                        	
                <div class="cactus-row">
                    <?php if($videopro_layout == 'boxed' && ($videopro_sidebar == 'both')){?>
                        <div class="open-sidebar-small open-box-menu"><i class="fa fa-bars"></i></div>
                    <?php }?>
                    <?php if($videopro_sidebar == 'left' || $videopro_sidebar == 'both'){ get_sidebar('left'); } ?>
                    
                    <div class="main-content-col">
                        <div class="main-content-col-body">
                        	<div class="single-page-content">
                                <article class="cactus-single-content">                                	
									<?php 	
									if(!is_page_template('page-templates/front-page.php')){								
										videopro_breadcrumbs();
										?>                        
										<h1 class="single-title entry-title"><?php echo esc_html($videopro_page_title);?></h1>
										<?php 
									}else{
										echo '<h2 class="hidden-title">'.esc_html($videopro_page_title).'</h2>';
									}?>
                                    <?php
									if(is_active_sidebar('content-top-sidebar')){
                                        echo '<div class="content-top-sidebar-wrap">';
                                        dynamic_sidebar( 'content-top-sidebar' );
                                        echo '</div>';
                                    } 
                                    
                                    $column_width = apply_filters('videopro-author-listing-columns', 4);
                                    if(in_array($column_width, array(1,2,3,4,6,12))){
                                        
                                    } else {
                                        $column_width = 4; // default 
                                    }
                                    
                                    $columns = 12 / $column_width;
                                    ?>
                
                                    <section class="authors-listing" id="authors-list">
                                        <div class="authors-listing-content">
                                            <div class="vc_row wpb_row vc_row-fluid">
                                            <?php
                                            $i = 0;
                                            foreach($allUsers as $user)
                                            {
                                                $name = $user->display_name;
                                                if($name == ''){
                                                    $name = $user->user_nicename;
                                                }
                                                if($name != ''){
                                                    $i++;
                                                    
                                                    $count = count_user_posts($user->ID);
                                                ?>
                                                <div class="wpb_column vc_column_container vc_col-sm-<?php echo esc_attr($column_width);?>">
                                                    <div class="vc_column-inner "><div class="wpb_wrapper">
                                                        <div class="user with-name">
                                                            <div class="user-data">
                                                                <a href="<?php echo get_author_posts_url( $user->ID ); ?>" class="thumbnail" title="<?php echo esc_attr($name); ?>">
                                                                    <span class="avatar" title="<?php echo esc_html($name); ?>"><?php echo get_avatar( $user->user_email, '60' ); ?></span>
                                                                </a>
                                                                <h3 class="author-name name data"><a href="<?php echo get_author_posts_url( $user->ID ); ?>" class="" title="<?php echo esc_attr($name); ?>"><?php echo esc_html($name); ?></a></h3>
                                                                <span class="posts_count data"><?php echo $count < 2 ? sprintf(__('%d post','videopro'), $count) : sprintf(__('%d posts','videopro'), $count);?></span>
                                                                <span class="description data"><?php echo get_user_meta($user->ID, 'description', true); ?><br><br></span>
                                                                <ul class="social-listing data list-inline">
                                                              
                                                                  <?php
                                                                  if($email = get_the_author_meta('author_email',$user->ID) && ot_get_option('author_page_email_contact','on') == 'on'){ ?>
                                                                      <li class="email"><a rel="nofollow" href="mailto:<?php echo esc_attr($email); ?>" title="<?php esc_html_e('Email', 'videopro');?>"><i class="fa fa-envelope-o"></i></a></li>
                                                                  <?php }
                                                                  
                                                                  if(ot_get_option('author_page_social_accounts','on') == 'on'){
                                                                      if($facebook = get_the_author_meta('facebook',$user->ID)){ ?>
                                                                          <li class="facebook"><a rel="nofollow" href="<?php echo esc_url($facebook); ?>" title="<?php esc_html_e('Facebook', 'videopro');?>"><i class="fa fa-facebook"></i></a></li>
                                                                      <?php }
                                                                      if($twitter = get_the_author_meta('twitter',$user->ID)){ ?>
                                                                          <li class="twitter"><a rel="nofollow" href="<?php echo esc_url($twitter); ?>" title="<?php esc_html_e('Twitter', 'videopro');?>"><i class="fa fa-twitter"></i></a></li>
                                                                      <?php }
                                                                      if($linkedin = get_the_author_meta('linkedin',$user->ID)){ ?>
                                                                          <li class="linkedin"><a rel="nofollow" href="<?php echo esc_url($linkedin); ?>" title="<?php esc_html_e('Linkedin', 'videopro');?>"><i class="fa fa-linkedin"></i></a></li>
                                                                      <?php }
                                                                      if($tumblr = get_the_author_meta('tumblr',$user->ID)){ ?>
                                                                          <li class="tumblr"><a rel="nofollow" href="<?php echo esc_url($tumblr); ?>" title="<?php esc_html_e('Tumblr', 'videopro');?>"><i class="fa fa-tumblr"></i></a></li>
                                                                      <?php }
                                                                      if($google = get_the_author_meta('google',$user->ID)){ ?>
                                                                         <li class="google-plus"> <a rel="nofollow" href="<?php echo esc_url($google); ?>" title="<?php esc_html_e('Google Plus', 'videopro');?>"><i class="fa fa-google-plus"></i></a></li>
                                                                      <?php }
                                                                      if($pinterest = get_the_author_meta('pinterest',$user->ID)){ ?>
                                                                         <li class="pinterest"> <a rel="nofollow" href="<?php echo esc_url($pinterest); ?>" title="<?php esc_html_e('Pinterest', 'videopro');?>"><i class="fa fa-pinterest"></i></a></li>
                                                                      <?php }
                                                                      
                                                                      if($custom_acc = get_the_author_meta('cactus_account',$user->ID)){
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
                                                                  <?php if($user->user_url != ''){?>
                                                                  <span class="web data"><a href="<?php echo $user->user_url; ?>" target="_blank"><?php echo $user->user_url; ?></a></span>
                                                                  <?php }?>
                                                              </div>
                                                              <div class="clearer"><!-- --></div>
                                                        </div>
                                                    </div></div>
                                                </div>
                                                    <?php
                                                    if($i % $columns == 0){
                                                        echo '<div class="clearer"><!-- --></div>';
                                                    }
                                                }
                                            }
                                            ?>
                                            </div>
                                        </div><!--/video-listing-content(blog-listing-content)-->
                                    <?php
                                    
                                    
                                    
                                    if($posts_per_page > 0){
                                        $pages = ceil($totalCount / $posts_per_page);
                                        if($pages > 1){
                                            $baseurl = videopro_get_current_url();
                                            
                                            $baseurl = remove_query_arg('pagename', $baseurl);

                                            videopro_paginate($baseurl,'paged', $pages, $paged, 5);
                                        }
                                    }
                                    
                                    ?>
                                    </section>
                                    
                                    <?php
									
									if(is_active_sidebar('content-bottom-sidebar')){
                                        echo '<div class="content-bottom-sidebar-wrap">';
                                        dynamic_sidebar( 'content-bottom-sidebar' );
                                        echo '</div>';
                                    } ?>
                                </article>
                            </div>
                        </div>
                    </div>
                    
                    <?php 
					$videopro_sidebar_style = 'ct-medium';
					videopro_global_sidebar_style($videopro_sidebar_style);
					if($videopro_sidebar=='right' || $videopro_sidebar=='both'){ get_sidebar(); } ?>
                    
                </div>
            </div>
            
        </div>                
        
        
    </div><!--body content-->

<?php get_footer();