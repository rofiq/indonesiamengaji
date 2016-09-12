<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package cactus
 */

if ( ! function_exists( 'videopro_paging_nav' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @params $content_div & $template are passed to Ajax pagination
 */
function videopro_paging_nav($content_div = '#main', $template = 'html/loop/content', $text_bt = false, $the_query = null) {
	if(!isset($text_bt)){ $text_bt = '';}
	// Don't print empty markup if there's only one page.
    
    if(!$the_query){
        
        $the_query = $GLOBALS['wp_query'];
    }
    
	if ( $the_query->max_num_pages < 2 ) {
		return;
	}
	echo '<div class="page-navigation heading-font">';
	$nav_type = ot_get_option('pagination','def');

    /* don't remember why 
	if(is_search()){
		$nav_type = ot_get_option('search_pagination','def');
	}
    */
	
	$nav_type = apply_filters('videopro_nav_type', $nav_type);

	switch($nav_type){
		case 'ajax':
			videopro_paging_nav_ajax($content_div, $template, $text_bt, $the_query);
			break;
		case 'wp_pagenavi':
			if( ! function_exists( 'wp_pagenavi' ) ) {	
				// fall back to default navigation style
				videopro_paging_nav_default($the_query); 
			} else {
				wp_pagenavi(array('query'=>$the_query));
			}
			break;
		case 'def':
		default:
			videopro_paging_nav_default($the_query);
			break;
	}

	echo '</div>';
}
endif;

if ( ! function_exists( 'videopro_paging_nav_default' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable. Default WordPress style
 */
function videopro_paging_nav_default($the_query = null) {
    if($the_query){
        // we need to clone the main query, so next_posts_link and previous_posts_link work
        global $wp_query;
        $temp_query = clone $wp_query;
        $wp_query = clone $the_query;
    }
    
    // Don't print empty markup if there's only one page.
	if ( $the_query->max_num_pages < 2 ) {
		return;
	}
	
	?>
	<nav class="navigation paging-navigation">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'videopro' ); ?></h1>
		<div class="nav-links">

			<?php if ( get_next_posts_link() ) : ?>
			<div class="nav-previous"><?php next_posts_link( '<span class="meta-nav">&larr;</span>'.esc_html__( ' Previous', 'videopro' ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<div class="nav-next"><?php previous_posts_link( esc_html__( 'Next ', 'videopro' ).'<span class="meta-nav">&rarr;</span>' ); ?></div>
			<?php endif; ?>

		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
    
    if($the_query){
        $wp_query = clone $temp_query;
    }
}
endif;

if ( ! function_exists( 'videopro_paging_nav_ajax' ) ) :
/**
 * Display navigation to next/previous set of posts when applicable. Ajax loading
 *
 * @params $content_div (string) - ID of the DIV which contains items
 * @params $template (string) - name of the template file that hold HTML for a single item. It will look for specific post-format template files
			For example, if $template = 'content'
				it will look for content-$post_format.php first (i.e content-video.php, content-audio.php...)
				then it will look for content.php if no post-format template is found
*/
function videopro_paging_nav_ajax($content_div = '#main', $template = 'content', $text_bt = false, $the_query) {
	// Don't print empty markup if there's only one page.
    if(!$the_query){
        $the_query = $GLOBALS['wp_query'];
    }

	if ( $the_query->max_num_pages < 2 ) {
		return;
	}
	
	?>
	<nav class="navigation-ajax">
		<div class="wp-pagenavi">
			<a href="javascript:void(0)" data-target="<?php echo esc_attr($content_div);?>" data-template="<?php echo esc_attr($template); ?>" id="navigation-ajax" class="load-more btn btn-default font-1">
				<div class="load-title"><?php if($text_bt){ echo esc_html($text_bt); }else{ echo esc_html__('Load More','videopro');} ?></div>
				<i class="fa fa-refresh hide" id="load-spin"></i>
			</a>
		</div>
	</nav>
	
	<?php
}
endif;

if ( ! function_exists( 'videopro_post_nav' ) ) :
/**
 * Display navigation to next/previous post when applicable.
 */
function videopro_post_nav() {
	// Don't print empty markup if there's nowhere to navigate.
	$previous = apply_filters( 'videopro_previous_nav',( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true ));
	$next     = apply_filters( 'videopro_previous_nav', get_adjacent_post( false, '', false ));

	if ( ! $next && ! $previous ) {
		return;
	}
	$author_id_previous = isset($previous->post_author) ?  $previous->post_author:'';
	$author_id_next = isset($next->post_author) ? $next->post_author:'';
	?>
    <div class="cactus-navigation-post">
        <div class="cactus-navigation-post-content">
        	<?php if($previous){?>
                <div class="prev-post"> 
                    <a href="<?php echo get_permalink( $previous->ID ); ?>" rel="prev" title="<?php echo esc_attr(get_the_title( $previous->ID )); ?>"></a>
                    <div class="cactus-listing-wrap">                      
                      <div class="cactus-listing-config style-3">
                        <div class="cactus-sub-wrap"> 
                          
                          <!--item listing-->
                          <article class="cactus-post-item hentry">
                            <div class="entry-content"> 
                              <?php if(has_post_thumbnail($previous->ID)){?>
                              <!--picture (remove)-->
                              <div class="picture">
                                <div class="picture-content"> 
                                  <a href="<?php echo get_permalink( $previous->ID ); ?>" rel="prev" title="<?php echo esc_attr(get_the_title( $previous->ID )); ?>">
                                    <?php echo videopro_thumbnail(array(100,75), $previous->ID) ?>
                                  </a>
                                  <?php 
								  
								  $post_data = videopro_get_post_viewlikeduration($previous->ID);
								  extract($post_data);

								  if($time_video!='00:00' && $time_video!='00' && $time_video!='' ){
									  ?>
									  <div class="cactus-note ct-time font-size-1"><span><?php echo esc_html($time_video);?></span></div>
                                  <?php }?>
                                </div>
                              </div>
                              <!--picture-->
                              <?php }?>
                              <div class="content"> 
                                <div class="action-button heading-font"><span><?php esc_html_e('PREV','videopro');?></span></div>
                                <!--Title (no title remove)-->
                                <h3 class="cactus-post-title entry-title h6"><?php echo get_the_title( $previous->ID ); ?></h3>
                                <!--Title-->
                                
                                <div class="posted-on metadata-font"> 
                                    <?php
                                    $show_author = ot_get_option('show_author_single_post','on');
                                    if($show_author != 'off'){?>
                                  <a href="<?php echo get_author_posts_url($author_id_previous); ?>" rel="prev" class="author cactus-info font-size-1"><span><?php the_author_meta( 'display_name', $author_id_previous ); ?></span></a>
                                    <?php }?>
                                  <div class="date-time cactus-info font-size-1">
                                    <?php echo videopro_get_datetime($previous->ID); ?>
                                  </div>
                                </div>
                                
                              </div>
                            </div>
                          </article>
                          <!--item listing--> 
                          
                        </div>
                      </div>
                    </div>
                </div>
            <?php }
			if($next){?>
                <div class="next-post">   
                    <a href="<?php echo get_permalink( $next->ID ); ?>" rel="next" title="<?php echo esc_attr(get_the_title( $next->ID )); ?>"></a>                                          	
                    <div class="cactus-listing-wrap">                      
                      <div class="cactus-listing-config style-3">
                        <div class="cactus-sub-wrap"> 
                          
                          <!--item listing-->
                          <article class="cactus-post-item hentry">
                            <div class="entry-content"> 
                              <?php if(has_post_thumbnail($next->ID)){?>
                              <!--picture (remove)-->
                              <div class="picture">
                                <div class="picture-content"> 
                                  <a href="<?php echo get_permalink( $next->ID ); ?>" rel="next" title="<?php echo esc_attr(get_the_title( $next->ID )); ?>">
                                    <?php echo videopro_thumbnail(array(100,75), $next->ID) ?>
                                  </a>
                                  <?php 
								  
								  $post_data = videopro_get_post_viewlikeduration($next->ID);
								  extract($post_data);
								  
								  if($time_video != '00:00' && $time_video != '00' && $time_video != '' ){
									  ?>
                                  <div class="cactus-note ct-time font-size-1"><span><?php echo esc_html($time_video);?></span></div>
                                  <?php }?>
                                </div>
                              </div>
                              <!--picture-->
                              <?php }?>
                              <div class="content"> 
                                <div class="action-button heading-font"><span><?php esc_html_e('NEXT','videopro');?></span></div>
                                <!--Title (no title remove)-->
                                <h3 class="cactus-post-title entry-title h6"><?php echo get_the_title( $next->ID ); ?></h3>
                                <!--Title-->
                                
                                <div class="posted-on metadata-font"> 
                                <?php
                                    $show_author = ot_get_option('show_author_single_post','on');
                                    if($show_author != 'off'){?>
                                  <a href="<?php echo get_author_posts_url($author_id_next); ?>" rel="next" class="author cactus-info font-size-1"><span><?php the_author_meta( 'display_name', $author_id_next ); ?></span></a>
                                    <?php }?>
                                  <div class="date-time cactus-info font-size-1">
                                    <?php echo videopro_get_datetime($next->ID); ?>
                                  </div>
                                </div>
                                
                              </div>
                            </div>
                          </article>
                          <!--item listing--> 
                          
                        </div>
                      </div>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>
	<?php
}
endif;

if(!function_exists('videopro_paginate')){
	function videopro_paginate ($base_url, $query_str, $total_pages, $current_page, $paginate_limit)	{
		// Array to store page link list
		$page_array = array ();
		// Show dots flag - where to show dots?
		$dotshow = true;
		// walk through the list of pages
		for ( $i = 1; $i <= $total_pages; $i ++ ){
		   // If first or last page or the page number falls 
		   // within the pagination limit
		   // generate the links for these pages
		   if ($i == 1 || $i == $total_pages || 
				 ($i >= $current_page - $paginate_limit && 
				 $i <= $current_page + $paginate_limit) ) {
			  // reset the show dots flag
			  $dotshow = true;
			  // If it's the current page, leave out the link
			  // otherwise set a URL field also
			  if ($i != $current_page)
				  $page_array[$i]['url'] = add_query_arg($query_str, $i, $base_url);
              
			  $page_array[$i]['text'] = strval ($i);
		   }
		   // If ellipses dots are to be displayed
		   // (page navigation skipped)
		   else if ($dotshow == true) {
			   // set it to false, so that more than one 
			   // set of ellipses is not displayed
			   $dotshow = false;
			   $page_array[$i]['text'] = "...";
		   }
		}
		
		if(count($page_array) > 1){
		?>		
		
		<div class="pagination paging-navigation wp-pagenavi">
		
		<?php

		foreach ($page_array as $page) {

			// If page has a link
			if (isset ($page['url'])) { ?>
				<a href="<?php echo esc_url($page['url'])?>"> <?php echo esc_html($page['text']); ?> </a>
			<?php }

			// no link - just display the text
			 else 
				echo '<span class="current">' . $page['text'] . '</span>';
		}?>
		
		</div>
		
		<?php
		}
		
		
	}
}


if ( ! function_exists( 'videopro_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function videopro_posted_on() {
	$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string .= '<time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	printf( '<span class="posted-on">'.esc_html__( 'Posted on ', 'videopro' ).'%1$s</span><span class="byline"> '.esc_html__('by','videopro').' %2$s</span>',
		sprintf( '<a href="%1$s" rel="bookmark">%2$s</a>',
			esc_url( get_permalink() ),
			$time_string
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s">%2$s</a></span>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_html( get_the_author() )
		)
	);
}
endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function videopro_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'videopro_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'videopro_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so cactus_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so cactus_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in cactus_categorized_blog.
 */
function videopro_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'videopro_categories' );
}
add_action( 'edit_category', 'videopro_category_transient_flusher' );
add_action( 'save_post',     'videopro_category_transient_flusher' );
//Global function 
function videopro_global_page_title(){
	global $videopro_page_title;
	if(isset($videopro_page_title) && $videopro_page_title != ''){
		return $videopro_page_title;
	}
	if(is_search()){
		$videopro_page_title = sprintf(esc_html__('Search results for "%s"','videopro'), esc_html(isset($_GET['s'])) ? esc_html($_GET['s']) : '');
	}elseif(is_category()){
		$videopro_page_title = single_cat_title('',false);
	}elseif(is_tag()){
		$videopro_page_title = single_tag_title('',false);
	}elseif(is_tax()){
		$videopro_page_title = single_term_title('',false);
	}elseif(is_author()){
		$videopro_page_title = esc_html__("Author: ",'videopro') . get_the_author();
	}elseif(is_day()){
		$videopro_page_title = esc_html__("Archives for ",'videopro') . date_i18n(get_option('date_format') ,strtotime(get_the_date()));
	}elseif(is_month()){
		$videopro_page_title = esc_html__("Archives for ",'videopro') . get_the_date('F, Y');
	}elseif(is_year()){
		$videopro_page_title = esc_html__("Archives for ",'videopro') . get_the_date('Y');
	}elseif(is_home()){
		if(get_option('page_for_posts')){ $videopro_page_title = get_the_title(get_option('page_for_posts'));
		}else{
			$videopro_page_title = get_bloginfo('name');
		}
	}elseif(is_404()){
		$videopro_page_title = ot_get_option('page404_title',__('404 - Page Not Found','videopro'));
	}elseif(  function_exists ( "is_shop" ) && is_shop()){
		$videopro_page_title = woocommerce_page_title($echo = false);
    }else{
		global $post;
		if($post){$videopro_page_title = $post->post_title;}
	}
	return $videopro_page_title;
}

function videopro_global_layout(){
	global $videopro_layout;
	if(isset($videopro_layout) && $videopro_layout != ''){
		return $videopro_layout;
	}
	if(is_page_template('page-templates/front-page.php') || is_singular('post') || is_page_template('page-templates/demo-menu.php')){
		$videopro_layout = get_post_meta(get_the_ID(),'main_layout',true);
	}
	if(!isset($videopro_layout) || $videopro_layout == ''){
		$videopro_layout = ot_get_option('main_layout','fullwidth');
	}	
	return $videopro_layout;
}

function videopro_global_sidebar_style($style = false){
	global $videopro_sidebar_style;
	if(isset($style) && $style!=''){
		$videopro_sidebar_style =  $style;
	}
	if(isset($videopro_sidebar_style) && $videopro_sidebar_style != ''){
		return $videopro_sidebar_style;
	}
	return $videopro_sidebar_style;
}

function videopro_global_c_page(){
	global $videopro_cpage;
	return $videopro_cpage;
}
function videopro_global_post(){
	global $post;
	return $post;
}
function videopro_global_wpdb(){
	global $wpdb;
	return $wpdb;
}
function videopro_global_wp_query(){
	global $wp_query;
	return $wp_query;
}
function videopro_global_wp(){
	global $wp;
	return $wp;
}
function videopro_global_video_layout(){
	global $videopro_post_video_layout;
	if(isset($videopro_post_video_layout) && $videopro_post_video_layout != ''){
		return $videopro_post_video_layout;
	}
	$videopro_post_video_layout = get_post_meta(get_the_ID(),'post_video_layout',true);
	if(!$videopro_post_video_layout){
		$videopro_post_video_layout = ot_get_option('videopost_layout','2');
	}
	return $videopro_post_video_layout;
}

function videopro_global_post_layout(){
	global $videopro_post_layout;
	if(isset($videopro_post_layout) && $videopro_post_layout != ''){
		return $videopro_post_layout;
	}
	$videopro_post_layout = get_post_meta(get_the_ID(),'post_layout',true);
	if(!$videopro_post_layout){
		$videopro_post_layout = ot_get_option('post_layout','1');
	}
	return $videopro_post_layout;
}

function videopro_global_author(){
	global $author;
	return $author;
}

function videopro_global_id_cr_pos($id = false){
	global $videopro_id_cr_pos;
	if(isset($id) && $id != ''){
		$videopro_id_cr_pos =  $id;
	}
	if(isset($videopro_id_cr_pos) && $videopro_id_cr_pos != ''){
		return $videopro_id_cr_pos;
	}
	return $videopro_id_cr_pos;
}

function videopro_get_global_wl_cl_options(){
	global $videopro_wl_cl_options;
	return $videopro_wl_cl_options;
}

function videopro_get_global_wl_color_options(){
	global $videopro_wl_color_options;
	return $videopro_wl_color_options;
}

function videopro_get_global_wl_bgcolor_options(){
	global $videopro_wl_bgcolor_options;
	return $videopro_wl_bgcolor_options;
}

function videopro_get_global_wl_icon_options(){
	global $videopro_wl_icon_options;
	return $videopro_wl_icon_options;
}

function videopro_get_global_wl_sublabel_options(){
	global $videopro_wl_sublabel_options;
	return $videopro_wl_sublabel_options;
}

function videopro_get_global_wl_options_style(){
	global $videopro_wl_options_style;
	return $videopro_wl_options_style;
}

function videopro_global_blog_layout(){
	global $videopro_blog_layout;
	if(isset($videopro_blog_layout) && $videopro_blog_layout != ''){
		return $videopro_blog_layout;
	}
	$videopro_blog_layout = '';
	if(is_category()){
		$category                       = get_category(get_query_var('cat'));
		$videopro_blog_layout = get_option('cat_layout_' . $category->term_id);
	}
	if($videopro_blog_layout == ''){
		$videopro_blog_layout = ot_get_option('blog_layout', 'layout_1');
	}
	return $videopro_blog_layout;
}

function videopro_global_bloglist_sidebar(){
	global $videopro_blog_sidebar;
	if(isset($videopro_blog_sidebar) && $videopro_blog_sidebar != ''){
		return $videopro_blog_sidebar;
	}
	$videopro_blog_sidebar = '';
	if(is_category()){
		$category                       = get_category(get_query_var('cat'));
		$videopro_blog_sidebar                = get_option('cat_sidebar_' . $category->term_id);
	}
	if($videopro_blog_sidebar == ''){
		$videopro_blog_sidebar = ot_get_option('blog_sidebar','both');
	}

	return $videopro_blog_sidebar;
}

function videopro_global_video_sidebar(){
	global $videopro_post_sidebar;
	if(isset($videopro_post_sidebar) && $videopro_post_sidebar != ''){
		return $videopro_post_sidebar;
	}
	$videopro_post_sidebar = get_post_meta(get_the_ID(),'post_sidebar',true);
	if(!$videopro_post_sidebar){
		$videopro_post_sidebar = ot_get_option('post_sidebar','both');
	}

	return $videopro_post_sidebar;
}

/**
 * Display Social Share buttons for FaceBook, Twitter, LinkedIn, Google+, Thumblr, Pinterest, Email
 */
if(!function_exists('videopro_print_social_share')){
function videopro_print_social_share($class_css = false, $id = false){
	if(!$id){
		$id = get_the_ID();
	}
?>
		<ul class="social-listing list-inline <?php if(isset($class_css)){ echo esc_attr($class_css);} ?>">
	  		<?php if(ot_get_option('sharing_facebook')!='off'){ ?>
		  		<li class="facebook">
		  		 	<a class="trasition-all" title="<?php esc_html_e('Share on Facebook','videopro');?>" href="#" target="_blank" rel="nofollow" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+'<?php echo urlencode(get_permalink($id)); ?>','facebook-share-dialog','width=626,height=436');return false;"><i class="fa fa-facebook"></i>
		  		 	</a>
		  		</li>
	    	<?php }

			if(ot_get_option('sharing_twitter')!='off'){ ?>
		    	<li class="twitter">
			    	<a class="trasition-all" href="#" title="<?php esc_html_e('Share on Twitter','videopro');?>" rel="nofollow" target="_blank" onclick="window.open('http://twitter.com/share?text=<?php echo urlencode(html_entity_decode(get_the_title($id), ENT_COMPAT, 'UTF-8')); ?>&amp;url=<?php echo urlencode(get_permalink($id)); ?>','twitter-share-dialog','width=626,height=436');return false;"><i class="fa fa-twitter"></i>
			    	</a>
		    	</li>
	    	<?php }

			if(ot_get_option('sharing_linkedIn')!='off'){ ?>
				   	<li class="linkedin">
				   	 	<a class="trasition-all" href="#" title="<?php esc_html_e('Share on LinkedIn','videopro');?>" rel="nofollow" target="_blank" onclick="window.open('http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo urlencode(get_permalink($id)); ?>&amp;title=<?php echo urlencode(html_entity_decode(get_the_title($id), ENT_COMPAT, 'UTF-8')); ?>&amp;source=<?php echo urlencode(get_bloginfo('name')); ?>','linkedin-share-dialog','width=626,height=436');return false;"><i class="fa fa-linkedin"></i>
				   	 	</a>
				   	</li>
		   	<?php }

			if(ot_get_option('sharing_tumblr')!='off'){ ?>
			   	<li class="tumblr">
			   	   <a class="trasition-all" href="#" title="<?php esc_html_e('Share on Tumblr','videopro');?>" rel="nofollow" target="_blank" onclick="window.open('http://www.tumblr.com/share/link?url=<?php echo urlencode(get_permalink($id)); ?>&amp;name=<?php echo urlencode(html_entity_decode(get_the_title($id), ENT_COMPAT, 'UTF-8')); ?>','tumblr-share-dialog','width=626,height=436');return false;"><i class="fa fa-tumblr"></i>
			   	   </a>
			   	</li>
	    	<?php }

			if(ot_get_option('sharing_google')!='off'){ ?>
		    	 <li class="google-plus">
		    	 	<a class="trasition-all" href="#" title="<?php esc_html_e('Share on Google Plus','videopro');?>" rel="nofollow" target="_blank" onclick="window.open('https://plus.google.com/share?url=<?php echo urlencode(get_permalink($id)); ?>','googleplus-share-dialog','width=626,height=436');return false;"><i class="fa fa-google-plus"></i>
		    	 	</a>
		    	 </li>
	    	 <?php }

			 if(ot_get_option('sharing_pinterest')!='off'){ ?>
		    	 <li class="pinterest">
		    	 	<a class="trasition-all" href="#" title="<?php esc_html_e('Pin this','videopro');?>" rel="nofollow" target="_blank" onclick="window.open('//pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink($id)) ?>&amp;media=<?php echo urlencode(wp_get_attachment_url( get_post_thumbnail_id($id))); ?>&amp;description=<?php echo urlencode(html_entity_decode(get_the_title($id), ENT_COMPAT, 'UTF-8')); ?>','pin-share-dialog','width=626,height=436');return false;"><i class="fa fa-pinterest"></i>
		    	 	</a>
		    	 </li>
	    	 <?php }
			 
			 if(ot_get_option('sharing_vk')!='off'){ ?>
		    	 <li class="vk">
		    	 	<a class="trasition-all" href="#" title="<?php esc_html_e('Share on VK','videopro');?>" rel="nofollow" target="_blank" onclick="window.open('//vkontakte.ru/share.php?url=<?php echo urlencode(get_permalink(get_the_ID())); ?>','vk-share-dialog','width=626,height=436');return false;"><i class="fa fa-vk"></i>
		    	 	</a>
		    	 </li>
	    	 <?php }

			 if(ot_get_option('sharing_email')!='off'){ ?>
		    	<li class="email">
			    	<a class="trasition-all" href="mailto:?subject=<?php echo urlencode(html_entity_decode(get_the_title($id), ENT_COMPAT, 'UTF-8')); ?>&amp;body=<?php echo urlencode(get_permalink($id)) ?>" title="<?php esc_html_e('Email this','videopro');?>"><i class="fa fa-envelope"></i>
			    	</a>
			   	</li>
		   	<?php }?>
	    </ul>
        <?php
	}
}

/**
 * Print out channel social accounts link.
 */
if(!function_exists('videopro_print_channel_social_accounts')){
	function videopro_print_channel_social_accounts($class_css = false, $id = false){
		/* below are default supported social networks. To support more, add the name of theme option in the array */
		$accounts = array('facebook','youtube','twitter','linkedin','tumblr','google-plus','pinterest','flickr','envelope','rss');
		$target ='_blank';
		if(get_post_meta(get_the_ID(),'open_social_link_new_tab',true) == 'off'){ $target =  '_parent';}
		/* this HTML uses Font Awesome icons */
		?>
		<ul class='social-listing list-inline <?php if(isset($class_css)){ echo esc_attr($class_css);} ?>'>
		<?php
		foreach($accounts as $account){
			$url = '';
			$url = get_post_meta(get_the_ID(),$account,true);
			$label = videopro_setting_label_by_id($account);
			if($url){
				if($account == 'envelope'){
					// this is email account, so use mailto protocol
					$url = 'mailto:' . $url;
				}
			?>
				<li class="<?php echo esc_attr($account); ?>"><a <?php echo ($account == 'envelope' ? '' : "target='" . esc_attr($target) . "'");?> href="<?php echo esc_url($url);?>" title='<?php echo esc_attr($label);?>'><i class="fa fa-<?php echo esc_attr($account);?>"></i></a></li>
			<?php }?>
			<?php
		}
		?>
        <?php
			// Custom Social Account
			$custom_social_accounts = get_post_meta(get_the_ID(),'custom_social_account',true);
			if( $custom_social_accounts ):
				foreach ($custom_social_accounts as $custom_social_account):?>
					<li  class="<?php echo 'custom-'.esc_attr($custom_social_account['icon_custom_social_account']); ?>"><a href="<?php echo esc_url($custom_social_account["url_custom_social_account"]);?>" title='<?php echo esc_attr($custom_social_account["title"]);?>' <?php echo 'target="'. esc_attr($target) . '"'; ?>><i class="fa <?php echo esc_attr($custom_social_account["icon_custom_social_account"]);?>"></i></a></li>
				<?php endforeach;
			endif;
		?>
		</ul>
		<?php
	}
}

/**
 * Print out social accounts link.
 */
if(!function_exists('videopro_print_social_accounts')){
	function videopro_print_social_accounts(){
		/* below are default supported social networks. To support more, add the name of theme option in the array */
		$accounts = array('facebook','youtube','twitter','linkedin','tumblr','google-plus','pinterest','flickr','envelope','rss','twitch');
		$target = ot_get_option('open_social_link_new_tab', 'on') == 'on' ? '_blank' : '_parent';
		/* this HTML uses Font Awesome icons */
		?>
		<ul class='nav navbar-nav navbar-right social-listing list-inline social-accounts'>
		<?php
		foreach($accounts as $account){
			$url = ot_get_option($account,'');
			$label = videopro_setting_label_by_id($account);
			if($url){
				if($account == 'envelope'){
					// this is email account, so use mailto protocol
					$url = 'mailto:' . $url;
				}
			?>
				<li class="<?php echo esc_attr($account); ?>"><a <?php echo ($account == 'envelope' ? '' : "target='" . esc_attr($target) . "'");?> href="<?php echo esc_url($url);?>" title='<?php echo esc_attr($label);?>'><i class="fa fa-<?php echo esc_attr($account);?>"></i></a></li>
			<?php }?>
			<?php
		}
		?>
        <?php
			// Custom Social Account
			$custom_social_accounts = ot_get_option('custom_social_account','');
			if( $custom_social_accounts ):
				foreach ($custom_social_accounts as $custom_social_account):?>
					<li  class="<?php echo 'custom-'.esc_attr($custom_social_account['icon_custom_social_account']); ?>"><a href="<?php echo esc_url($custom_social_account["url_custom_social_account"]);?>" title='<?php echo esc_attr($custom_social_account["title"]);?>' <?php echo 'target="'. esc_attr($target) . '"'; ?>><i class="fa <?php echo esc_attr($custom_social_account["icon_custom_social_account"]);?>"></i></a></li>
				<?php endforeach;
			endif;
		?>
		</ul>
		<?php
	}
}

/* Breadcumb */
if(!function_exists('videopro_breadcrumbs')){
	function videopro_breadcrumbs($echo = true, $class = ''){

		$html = '';


		ob_start();

		$auto_next_html ='';
		$at_class ='';
		$auto_next_html = apply_filters( 'videopro_auto_next_video', $auto_next_html );
		if($auto_next_html != ''){
			$at_class = 'autoplay-item';
		}
		if(ot_get_option('enable_breadcrumbs','on') == 'off'){
			if($auto_next_html != ''){
				echo '<div class="cactus-breadcrumb ' . esc_attr($class) . ' navigation-font font-size-1 '. esc_attr($at_class) . '"><div class="breadcrumb-wrap"><span>&nbsp;</span>';
					echo $auto_next_html;
				echo '</div></div>';
			}
			
			$html = ob_get_clean();
			
			if($echo){
				echo $html;
				return;
			} else 
				return $html;
		}

		if(is_active_sidebar('breadcrumbs-sidebar')){
			echo '<div class="cactus-breadcrumb ' . esc_attr($class) . ' navigation-font font-size-1 '. esc_attr($at_class) .'"><div class="breadcrumb-wrap">';
				dynamic_sidebar( 'breadcrumbs-sidebar' );
				echo $auto_next_html;
			echo '</div></div>';
			
			$html = ob_get_clean();
			
			if($echo){
				echo $html;
				return;
			} else 
				return $html;
		}

		/* === OPTIONS === */
		$text['home']     = esc_html__('Home','videopro'); // text for the 'Home' link
		$text['category'] = '%s'; // text for a category page
		$text['search']   = esc_html__('Search Results for','videopro').' "%s"'; // text for a search results page
		$text['tag']      = esc_html__('Tag','videopro').' "%s"'; // text for a tag page
		$text['author']   = '%s'; // text for an author page
		$text['404']      = esc_html__('404','videopro'); // text for the 404 page

		$show_current   = 1; // 1 - show current post/page/category title in breadcrumbs, 0 - don't show
		$show_on_home   = 1; // 1 - show breadcrumbs on the homepage, 0 - don't show
		$show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
		$show_title     = 1; // 1 - show the title for the links, 0 - don't show
		$delimiter      = '<i class="fa fa-angle-right" aria-hidden="true"></i>'; // delimiter between crumbs
		
		$before         = '<span class="current">'; // tag before the current crumb
		$after          = '</span>'.$auto_next_html; // tag after the current crumb
		/* === END OF OPTIONS === */

		global $post;

		$home_link    = home_url('/');
		$link_before  = '<span typeof="v:Breadcrumb">';
		$link_after   = '</span>';
		$link_attr    = ' rel="v:url" property="v:title"';
		$link         = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
		$parent_id    = $parent_id_2 = ($post) ? $post->post_parent : 0;
		$frontpage_id = get_option('page_on_front');
		$event_layout = '';

		if(is_front_page()) {

			if ($show_on_home == 1) echo '<div class="cactus-breadcrumb ' . esc_attr($class) . ' navigation-font font-size-1 '. esc_attr($at_class) . '"><div class="breadcrumb-wrap"><a href="' . esc_url($home_link) . '">' . $text['home'] . '</a></div></div>';

		} elseif(is_home()) {
			
			$title = get_option('page_for_posts') ? get_the_title(get_option('page_for_posts')) : esc_html__('Blog','videopro');
			echo '<div class="cactus-breadcrumb ' . esc_attr($class) . ' navigation-font font-size-1 '. esc_attr($at_class) . '"><div class="breadcrumb-wrap"><a href="' . esc_url($home_link) . '">' . $text['home'] . '</a> \ '.$title.'</div></div>';
			
		} else {

			echo '<div class="cactus-breadcrumb ' . esc_attr($class) . ' navigation-font font-size-1 '. esc_attr($at_class) . '"><div class="breadcrumb-wrap">';
			if ($show_home_link == 1) {
				if(function_exists ( "is_shop" ) && is_shop()){

				}else{
					echo '<a href="' . esc_url($home_link) . '" rel="v:url" property="v:title">' . $text['home'] . '</a>';
					if ($frontpage_id == 0 || $parent_id != $frontpage_id) echo $delimiter;
				}
			}

			if ( is_category() ) {
				$this_cat = get_category(get_query_var('cat'), false);
				if ($this_cat->parent != 0) {
					$cats = get_category_parents($this_cat->parent, TRUE, $delimiter);
					if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
					$cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
					$cats = str_replace('</a>', '</a>' . $link_after, $cats);
					if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
					echo $cats;
				}
				if ($show_current == 1) echo $before . sprintf($text['category'], single_cat_title('', false)) . $after;

			} elseif ( is_search() ) {
				echo $before . sprintf($text['search'], get_search_query()) . $after;

			} elseif ( is_day() ) {
				echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
				echo sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . $delimiter;
				echo $before . get_the_time('d') . $after;

			} elseif ( is_month() ) {
				echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $delimiter;
				echo $before . get_the_time('F') . $after;

			} elseif ( is_year() ) {
				echo $before . get_the_time('Y') . $after;

			} elseif ( is_single() && !is_attachment() ) {
				if ( get_post_type() != 'post' ) {
					$post_type = get_post_type_object(get_post_type());
					$slug = $post_type->rewrite;
					printf($link, $home_link . $slug['slug'] . '/', $slug['slug']?$slug['slug']:$post_type->labels->singular_name);
					if ($show_current == 1) echo $delimiter . $before . get_the_title() . $after;
				} else {
					$cat = get_the_category(); $cat = $cat[0];
					$cats = get_category_parents($cat, TRUE, $delimiter);
					if( !is_wp_error( $cats ) ) {
						if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
						$cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
						$cats = str_replace('</a>', '</a>' . $link_after, $cats);
						if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
						echo $cats;
					}
					if ($show_current == 1) echo $before . get_the_title() . $after;
				}

			} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
				if(function_exists ( "is_shop" ) && is_shop()){
					do_action( 'woocommerce_before_main_content' );
					do_action( 'woocommerce_after_main_content' );
				}else{
					$post_type = get_post_type_object(get_post_type());
					$slug = $post_type->rewrite;
					echo $before . ($slug['slug'] ? $slug['slug'] : $post_type->labels->singular_name) . $after;
				}

			} elseif ( is_attachment() ) {
				$parent = get_post($parent_id);
				$cat = get_the_category($parent->ID); $cat = isset($cat[0])?$cat[0]:'';
				if($cat){
					$cats = get_category_parents($cat, TRUE, $delimiter);
					$cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
					$cats = str_replace('</a>', '</a>' . $link_after, $cats);
					if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
					echo $cats;
				}
				printf($link, get_permalink($parent), $parent->post_title);
				if ($show_current == 1) echo $delimiter . $before . get_the_title() . $after;

			} elseif ( is_page() && !$parent_id ) {
				if ($show_current == 1) echo $before . get_the_title() . $after;

			} elseif ( is_page() && $parent_id ) {
				if ($parent_id != $frontpage_id) {
					$breadcrumbs = array();
					while ($parent_id) {
						$page = get_page($parent_id);
						if ($parent_id != $frontpage_id) {
							$breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
						}
						$parent_id = $page->post_parent;
					}
					$breadcrumbs = array_reverse($breadcrumbs);
					for ($i = 0; $i < count($breadcrumbs); $i++) {
						echo $breadcrumbs[$i];
						if ($i != count($breadcrumbs)-1) echo $delimiter;
					}
				}
				if ($show_current == 1) {
					if ($show_home_link == 1 || ($parent_id_2 != 0 && $parent_id_2 != $frontpage_id)) echo $delimiter;
					echo $before . get_the_title() . $after;
				}

			} elseif ( is_tag() ) {
				echo $before . sprintf($text['tag'], single_tag_title('', false)) . $after;

			} elseif ( is_author() ) {
				global $author;
				$userdata = get_userdata($author);
				echo $before . sprintf($text['author'], $userdata->display_name) . $after;

			} elseif ( is_404() ) {
				echo $before . $text['404'] . $after;
			}

			echo '</div></div><!-- .breadcrumbs -->';

		}
		
		$html = ob_get_clean();
		
		if($echo){
			echo $html;
			return;
		} else 
			return $html;
	} // end _breadcrumbs()
}

if(!function_exists('videopro_echo_breadcrumbs')){
	function videopro_echo_breadcrumbs($post_id, $post_layout, $post_format){
		
		$html = videopro_breadcrumbs(false);

		echo apply_filters('video_breadcrumbs_filter', $html, $post_id, $post_layout, $post_format);
	}
}

if(!function_exists('videopro_display_ads')){
	function videopro_display_ads($section){
		$ad_top_1 = ot_get_option($section);
		$adsense_publisher_id = ot_get_option('adsense_id');
		$adsense_slot_top_1 = ot_get_option('adsense_slot_' . $section);
		if($adsense_publisher_id != '' && $adsense_slot_top_1 != ''){
	?>
			<div class='ad <?php echo esc_attr($section);?>'><?php videopro_echo_responsive_ad($adsense_publisher_id, $adsense_slot_top_1);?></div>
		<?php
		} elseif($ad_top_1 != ''){?>
			
			<div class='ad <?php echo esc_attr($section);?> <?php echo (preg_match('/cactus-ads/', $ad_top_1) ? "cactus-mutil-ads" : "");?>'><?php echo do_shortcode($ad_top_1);?></div>
		<?php
		}
	}
}

if(!function_exists('videopro_content_video_header')){
	function videopro_content_video_header($post_video_layout, $player_only = 0){
		$html = $video = '';
		
		// find any video tag <video>, <embed>, <object>, <iframe>
		$full_content = get_the_content();
		$tags_to_find = array("/<embed\s+(.+?)>/i", "/\<video(.*)\<\/video\>/is", "/\<object(.*)\<\/object\>/is", "/<iframe.*src=\"(.*)\".*><\/iframe>/isU");
		$found        = false;

		foreach ($tags_to_find as $tag) {
			if (preg_match($tag, $full_content, $matches)) {
				$found = true;

				// video HTML
				$video = $matches[0];

				// use $video somewhere else. For VideoPress, you will need to install Jetpack or Slim Jetpack plugin to turn the shortcode into a viewable video
			}
		}

		if (!$found) {
			// find first link
			if (preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $full_content, $matches)) {
				// video HTML
				$video = wp_oembed_get($matches[0][0]);

				// use $video somewhere else. For VideoPress, you will need to install Jetpack or Slim Jetpack plugin to turn the shortcode into a viewable video
			}
		}
		
		if($video != ''){
			$html .= '<div id="video_player_wrapper">' . $video . '</div>';
		}
		
		$html = apply_filters('videopro_content_video_header', $html, $post_video_layout, $player_only);
		
		echo $html;
	}
}

if(!function_exists('videopro_singlevideo_left_meta')){
	function videopro_singlevideo_left_meta($post_format){
		$id = get_the_ID();
		
		$post_data = videopro_get_post_viewlikeduration($id);
		extract($post_data);

		$isWTIinstalled = (ot_get_option('single_post_show_likes', 'off') == 'on' ? (function_exists('GetWtiLikeCount') ? 1 : 0) : 0);

		$is_comment_count_available = ot_get_option('show_cmcount_single_post','on') != 'off' && ! post_password_required() && ( comments_open() || '0' != get_comments_number() );

		$html = '';
		ob_start();
		?>
		<div class="left">
			<div class="posted-on metadata-font">
				<?php if(ot_get_option('single_post_date','on') != 'off'){?>
				<div class="date-time cactus-info font-size-1"><?php echo videopro_get_datetime(); ?></div>
				<?php }
				if(ot_get_option('show_cat_single_post','on') != 'off'){?>
				<div class="categories cactus-info">
					<?php echo videopro_show_cat();?>
				</div>
				<?php }
				
				if(ot_get_option('show_author_single_post','on') != 'off'){?>
				<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) );?>" class="author cactus-info font-size-1"><span><?php echo sprintf(esc_html__('By %s', 'videopro'), get_the_author());?></span></a>
				<?php }?>                                         
			</div>
		</div>
		<div class="right">
        	<div class="posted-on metadata-font right">
				<?php if($viewed != '') {?>
                <div class="view cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Views','videopro'), videopro_get_formatted_string_number($viewed));?></span></div>
                <?php }
                
                if($isWTIinstalled) {?>
                    <div class="cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Likes','videopro'), videopro_get_formatted_string_number($like)); ?></span></div>
                <?php }
                
                if ($is_comment_count_available) {?>                
                    <a href="<?php echo get_comments_link(); ?>" class="comment cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Comments','videopro'), number_format_i18n(get_comments_number())); ?></span></a>                
                <?php }?>
            </div>
		</div>
		<?php
		
		$html .= ob_get_clean();
		
		echo apply_filters('videopro_singlevideo_left_meta', $html, $post_format, $viewed);
	}
}

if(!function_exists('videopro_singlevideo_right_meta')){
	function videopro_singlevideo_right_meta($post_format){
		$html = '';
		echo apply_filters('videopro_singlevideo_right_meta', $html, $post_format);
	}
}

if(!function_exists('videopro_loop_item_thumbnail')){
	/**
	 * $id - int - Post ID
	 * $format - string - Post Format
	 * $img_size - array - Thumbnail Size
	 * $video_data - array - containt video metadata
	 * $context - string - used to determine where this function is called. Used 'related' if it is called in Related Posts loop
	 */
	function videopro_loop_item_thumbnail($id, $format, $img_size, $video_data, $context = ''){
		$html = '';

		ob_start();
		$link_post = get_the_permalink($id);
		if(is_tax('video-series')){
			$queried_object = get_queried_object();
			$term_slug = $queried_object->slug;
			$link_post =  add_query_arg( array('series' => $term_slug), $link_post );
		}
        
        $link_post = apply_filters('videopro_loop_item_url', $link_post, $id);

		?>
		<div class="picture-content">
			<a href="<?php echo esc_url($link_post); ?>" target="<?php echo apply_filters('videopro_loop_item_url_target', '_self', $id);?>" title="<?php the_title_attribute(); ?>">
				<?php 
				
				echo videopro_thumbnail($img_size, $id);
				
				// we want to display lightbox in Related Post section, as users should visit single post
				$enable_lightbox_in_context = apply_filters('videopro_enable_lightbox_in_context', $context == 'related' ? 0 : 1, $context );

				echo apply_filters('videopro_loop_item_icon', $format == 'video' ? '<div class="ct-icon-video"></div>' : '', $id, $format, $enable_lightbox_in_context,'' );
				
				?>                                                               
			</a>
			
			<?php if(videopro_post_rating($id) != ''){
				echo videopro_post_rating($id);
			}
			
			extract($video_data);
			
			if(ot_get_option('single_post_show_likes', 'on') == 'on'){
			?>
				<div class="cactus-note font-size-1"><i class="fa fa-thumbs-up"></i><span><?php echo videopro_get_formatted_string_number($like);?></span></div>
			<?php 
			}

			if($time_video != '00:00' && $time_video != '00' && $time_video != '' ){?>
				<div class="cactus-note ct-time font-size-1"><span><?php echo esc_html($time_video);?></span></div>
			<?php }?>                                                       
		</div>    
		<?php
		$html = ob_get_clean();

		echo apply_filters('videopro_loop_item_thumbnail', $html, $id, $img_size, $format, $video_data, $context);
	}
}

if(!function_exists('videopro_post_toolbar')){
	/**
	 * $post_id - int - Post ID
	 * $post_format - string - Post Format
	 */
	function videopro_post_toolbar($post_id, $post_format){
		$html = '';
		
		echo apply_filters('videopro_post_toolbar', $html, $post_id, $post_format); return; // currently we do nothing here. Will be updated in next versions
	}
}

if(!function_exists('videopro_post_rating')){
	function videopro_post_rating($post_id,$class=false,$is_single=false){
		return;
		$rating = round(get_post_meta($post_id, 'taq_review_score', true)/10,1);
		$rating_options = get_option('tmr_options_group');
		if($rating_options['tmr_rate_type'] == 'star')
		{
			$rating = round($rating) / 2;
			$rating = number_format($rating,1,'.','');
		}

		if($rating && $rating_options['tmr_rate_type'] == 'point'){
			$rating = number_format($rating,1,'.','');

		}
		if($rating != 0)
		{
				return '<span class="cactus-point font-size-3 heading-font '.$class.'">'.$rating.'</span>';
		}
	}
}

if(!function_exists('videopro_get_default_image')){
	function videopro_get_default_image(){
		return get_template_directory_uri().'/images/default_image.jpg';
	}
}

if(!function_exists('videopro_get_category')){
	function videopro_get_category($category,$class=false,$unlink=false)
	{
		$ct_class = 'font-size-1';
		if(isset($class) && $class !=''){
			$ct_class = $class;
		}
		if(is_array($category) && isset($category[0])){
			$category = $category[0];
		}
		if(isset($unlink) && $unlink=='1'){
			return $category->name;
		}else{
			return '<a class="'.$ct_class.'" href="' . esc_url(get_category_link( $category->term_id )) . '" title="' . esc_html__('View all posts in ','videopro') . $category->name . '">' . $category->name . '</a>';
		}
		
	}
}
if(!function_exists('videopro_get_tags')){
	function videopro_get_tags()
	{
		$posttags = get_the_tags();
		if ($posttags) {
			foreach($posttags as $tag) {
				echo '<a href="'.get_tag_link($tag->term_id).'" class="font-size-1">'.$tag->name.'</a>'; 
			}
		}
	}
}

if(!function_exists('videopro_get_datetime')){
	function videopro_get_datetime($post_ID = '', $permalink = ''){
		if($post_ID == ''){
			global $post;
		 	if($post) {
		 		$post_ID = $post->ID;
		 	}
		}
		$post_datetime_setting  = ot_get_option('enable_link_on_datetime', 'on');
		if($post_datetime_setting == 'on'){
            if($permalink == ''){
                $permalink = get_the_permalink($post_ID);
            }
            
			return '<a href="' . esc_url($permalink) . '" target="' . apply_filters('videopro_loop_item_url_target', '_self', $post_ID) . '" class="cactus-info" rel="bookmark"><time datetime="' . get_the_date( 'c', $post_ID ) . '" class="entry-date updated">' . date_i18n(get_option('date_format') ,get_the_time('U', $post_ID)) . '</time></a>';
        } else
			return '<div class="cactus-info" rel="bookmark"><time datetime="' . get_the_date( 'c', $post_ID ) . '" class="entry-date updated">' . date_i18n(get_option('date_format') ,get_the_time('U', $post_ID)) . '</time></div>';
	}
}