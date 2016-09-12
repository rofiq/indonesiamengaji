<?php


add_filter( 'cbc_youtube_theme_support', 'videopro_add_cbc_youtube_theme_support', 10, 1);
function videopro_add_cbc_youtube_theme_support($themes){
	$themes = array_merge($themes, array(
				'videopro' => array(
								'post_type' 	=> 'post',
								'taxonomy' 		=> false,
								'post_meta' 	=> array(
									'url' => 'tm_video_url'
								),
								'post_format' 	=> 'video',
								'theme_name' 	=> 'VideoPro',
								'url'			=> '#',
								'extra_meta' 	=> array(
									'time_video' => array('type' 	=> 'video_data', 'value' => 'human_duration'),
									'_video_network_views' => array('type' => 'video_data', 'value' => 'views'),
									'_video_network_likes' => array('type' => 'video_data', 'value' => 'likes'),
									'_video_network_dislikes' => array('type' => 'video_data', 'value' => 'dislikes'),
									'_video_network_comments' => array('type' => 'video_data', 'value' => 'comments')
								),
								)
							)
						);
			
	return $themes;
}

/**
 * Modify main search query
 */
if(!function_exists('videopro_modify_search')){
	function videopro_modify_search($query){
		$s = get_search_query();
		if($s != '' || isset($_GET['orderby']) && $_GET['orderby']!=''){
			if($query->is_main_query()){
				if($s != ''){// search only
					$tax_query = $query->get('tax_query');
					if(!isset($tax_query) || $tax_query == '') $tax_query = array();
	
					if(ot_get_option('search_video_only', 'off') == 'on'){
						// filter to search on Video Post Format
						
						array_push($tax_query, array(
							'taxonomy' => 'post_format',
							'field' => 'slug',
							'terms' => array( 'post-format-video'),
							'operator' => 'IN',
						));
						
						
					}
					
					$meta_query = $query->get('meta_query');
					if(!isset($meta_query) || $meta_query == '') $meta_query = array();
					
					if(isset($_GET['length'])){
						$length = intval($_GET['length']);
						
						// make sure to only filter length by one of 3 values
						if($length <= 4){
							$length = 4;
						} elseif($length <= 20){
							$length = 20;
						} else{
							$length = 1000;
						}
						
						array_push($meta_query, array(
							'key' => 'time_video',
							'value' => $length * 60,
							'compare' => ($length == 1000 ? '>' : '<='),
							'type' => 'numeric'
						));
					}
				}
				
				$posts_per_page = $query->get('posts_per_page');
				$paged = $query->get('paged');
				$offset = $paged * $posts_per_page;
				// search, archives filter

				if(isset($_GET['orderby'])){
					$order = $_GET['orderby'];
					if($order == 'title'){
						$query->set('orderby', 'title');
						$query->set('order', 'ASC');
					}elseif($order == 'comments'){
						$query->set('orderby', 'comment_count');
					}elseif($order == 'ratings'){
						$query->set('meta_key', 'taq_review_score');
						$query->set('orderby', 'meta_value_num');
					}elseif($order == 'view'){
						if(function_exists('videopro_get_tptn_pop_posts')){
							$args = array(
								'daily' => 0,
								'post_types' =>'post'
							);
							$ids = videopro_get_tptn_pop_posts($args);
							$query->set('post__in', $ids );
							$query->set('orderby', 'post__in');
						}
					}elseif($order == 'like'){
						$ids = videopro_get_most_like();
						if(!empty($ids)){
							$query->set('post__in', $ids );
							$query->set('orderby', 'post__in');
						}
					}
				}
				if($s != ''){// search only
					$query->set( 'tax_query', $tax_query );
					$query->set( 'meta_query', $meta_query );
					
					$order = '';
					if(isset($_GET['order'])){
						if($_GET['order'] == 'DESC'){
							$query->set('order', 'DESC');
						} elseif($_GET['order'] == 'ASC') {
							$query->set('order', 'ASC');
						}
					}
				}
			}
		} else {
			if(isset($_GET['s']) && empty($_GET['s'])){
				// return home page if search for empty string
				wp_redirect(home_url('/'));
				exit;
			}
		}
		
		return $query;
	}
}

if( ! is_admin() )
{
   add_filter( 'pre_get_posts', 'videopro_modify_search' );
}

add_filter('easy-tab-number-of-tabs','videopro_filter_easytab_count', 10, 1);

function videopro_filter_easytab_count($default_count){
	$count = ot_get_option('easy-tab-count', 2);
	return $count;
}

add_action('videopro_before_search_results','videopro_default_hook_before_search_results', 10, 1);

function videopro_default_hook_before_search_results($search_query){
	ob_start();
?>
	<div class="search-form-listing">                                    	 
                            <form action="<?php echo esc_url(home_url('/'));?>" method="get">
                                <input type="text" placeholder="<?php echo esc_html_e('Search...','videopro');?>" name="s" value="<?php echo esc_attr($search_query);?>">
                                <input type="submit" value="<?php echo esc_html_e('SEARCH','videopro');?>" class="padding-small">
                            </form>
                        </div>
<?php
	$html = ob_get_clean();

	echo apply_filters('videopro_default_search_form', $html, $search_query);
}

add_filter('the_content', 'videopro_filter_content', 10, 1);
if(!function_exists('videopro_filter_content')){
	function videopro_filter_content($the_content){
		// find any video tag <video>, <embed>, <object>, <iframe>
		$post_format = get_post_format();
		$content = $the_content;

		if($post_format == 'video' || $post_format == 'audio'){
			$full_content = $the_content;

			$tags_to_find = array("/<embed\s+(.+?)>/i", "/\<video(.*)\<\/video\>/is", "/\<object(.*)\<\/object\>/is", "@<iframe[^>]*?>.*?</iframe>@siu");
			$found        = false;

			foreach ($tags_to_find as $tag) {
				if (preg_match($tag, $full_content, $matches)) {
					$found = true;

					// remove it
					$content = preg_replace($tag, '', $full_content, 1);

					// use $video somewhere else. For VideoPress, you will need to install Jetpack or Slim Jetpack plugin to turn the shortcode into a viewable video
				}
			}

			if (!$found) {
				// find first link
				if (preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $full_content, $matches)) {
					// remove it
					$content = preg_replace('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', '', $full_content, 1);
				}
			}
		}
		
		return apply_filters('videopro_filter_content_after', $content, $the_content);
	}
}