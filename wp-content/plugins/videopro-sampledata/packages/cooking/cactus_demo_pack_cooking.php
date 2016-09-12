<?php
/*
 * step = 0  - Import categories & terms
 * step = 10 - Import pages
 * step = 25 - Import posts
 * step = 55 - Import menu items
 * step = 85 - Import theme options, setup mega menu
 * step = 90 - Import widget settings, widget logics, setup homepage * blog pages, do other things
 *
 * item_index is the index of item to start import in each step
 *
 *
 *
 * 
 */
class cactus_demo_pack_cooking extends cactus_demo_content{

	function __construct($base_uri, $base_dir){
		parent::__construct($base_uri, $base_dir);
		
		$this->name = 'cooking';
		$this->home_page = 'Home Cooking'; // title of the page which is set to Home Page
		$this->heading = esc_html__('Cooking', 'cactus');
        $this->description = esc_html__('To have light schema in footer, please use "VideoPro Child Theme - Light Footer" (included in the package)', 'cactus');
        $this->url = 'http://videopro.cactusthemes.com/cooking/';
	}
	
	public function do_import($step = 0, $index = 0, $option_only = 0){
		$progress = parent::do_import($step, $index, $option_only);
		
		return $progress;
	}
	
	// override
	// return -1 if nothing needs to be done
	function do_others($step, $index){
		if($step == 10 || $step == 90){
			// we need to import plugin settings in advance (when $step = 10), before import posts (because there are custom post types)
			// then we make sure that we import plugin settings also at $step = 90 because users may choose to import options only
            if(file_exists(dirname(__FILE__) . '/data/video-settings.txt')){
                $video_settings = file_get_contents(dirname(__FILE__) . '/data/video-settings.txt');
                update_option('ct_video_settings', $video_settings);
            }
			
            if(file_exists(dirname(__FILE__) . '/data/channel-settings.txt')){
                $channel_settings = file_get_contents(dirname(__FILE__) . '/data/channel-settings.txt');
                update_option('ct_channel_settings', $channel_settings);
            }
            
            if(file_exists(dirname(__FILE__) . '/data/playlist-settings.txt')){
                $playlist_settings = file_get_contents(dirname(__FILE__) . '/data/playlist-settings.txt');
                update_option('ct_playlist_settings', $playlist_settings);
            }
            
            if(file_exists(dirname(__FILE__) . '/data/actor-settings.txt')){
                $settings = file_get_contents(dirname(__FILE__) . '/data/actor-settings.txt');
                update_option('ct_actor_settings', $settings);
            }
            
            update_option('tmr_options_group', unserialize('a:6:{s:12:"tmr_criteria";s:24:"Time, Quality, Aesthetic";s:12:"tmr_position";s:6:"bottom";s:9:"tmr_float";s:5:"block";s:9:"tmr_title";s:6:"Review";s:13:"tmr_user_rate";s:3:"all";s:13:"tmr_rate_type";s:5:"point";}'));
		} elseif($step == 0){
			// import metadata
            if(file_exists(dirname(__FILE__) . '/data/category-meta.txt')){
                $cat_meta = file_get_contents(dirname(__FILE__) . '/data/category-meta.txt');
                $metas = unserialize($cat_meta);
                foreach($metas as $cat_slug => $data){
                    $cat = get_term_by('slug', $cat_slug, 'category');
                    if($cat){
                        foreach($data as $key => $val){
                            update_option($key . $cat->term_id, $val);
                        }
                    }
                }
            }
			
			if(file_exists(dirname(__FILE__) . '/data/video-series-meta.txt')){
                $cat_meta = file_get_contents(dirname(__FILE__) . '/data/video-series-meta.txt');
                $metas = unserialize($cat_meta);
                foreach($metas as $cat_slug => $data){
                    $cat = get_term_by('slug', $cat_slug, 'video-series');
                    if($cat){
                        foreach($data as $key => $val){
                            update_option($key . $cat->term_id, $val);
                        }
                    }
                }
            }
		}
		
		return -1;
	}
	
	// override
	// return total of items in step 
	public function count_other_steps($step){
		return 0;
	}
	
	/**
	 * Process post metadata
	 *
	 */
	public function import_post_meta($post_id, $key, $value, $post_type = ''){
		$serialize_field = false;
		
		switch($key){
			case 'actor_id':
				// assign Posts to Actors, Channels and Playlists
				$serialize_field = true;
				$arr = array('actor_id' => 'ct_actor');
				
				break;
			case 'channel_id':
				// assign Posts to Actors, Channels and Playlists
				$serialize_field = true;
				$arr = array('playlist_id' => 'ct_playlist');
				
				break;
			case 'playlist_id':
				// assign Posts to Actors, Channels and Playlists
				$arr = array('channel_id' => 'ct_channel');
				$serialize_field = true;
				
				break;
			case 'playlist_channel_id':
				// assign Playlists to Channels
				$arr = array('playlist_channel_id' => 'ct_channel');
				$serialize_field = true;
				
				break;
			case 'tm_multi_link':
			case 'custom_review':
			case '_vc_post_settings':
				update_post_meta($post_id, $key, unserialize($value));
				break;
			case 'video_series_id':
				break;
			case 'video_series_slug':
				if($post_type == 'vseries_post'){
					// get random series
					$s = get_term_by('slug', $value, 'video-series');
					if($s){
						$id = $s->term_id;
						update_post_meta($post_id, 'video_series_id', $id);
						update_post_meta($post_id, 'video_series_slug', $value);
					}
					
					break;
				}
			default:
				update_post_meta( $post_id, $key, $value );
				break;
		}
		
		if($serialize_field){
			foreach($arr as $k => $post_type){
					$count = 5;
					$the_query = new WP_Query(array('post_type' => $post_type, 'posts_per_page' => $count, 'orderby' => 'rand'));
					if($the_query->have_posts()){
						$posts = $the_query->posts;
						$ids = array();
						foreach($posts as $post){
							array_push($ids, $post->ID);
						}
						
						update_post_meta($post_id, $k, $ids);
					}
				}
		}
		
		return true;
	}
	
	/**
	 * Override parent class
	 */
	public function configure_widget_options($widget_settings, $widget_index_mapping){
		cactus_import_widget_settings::set_widget_options('left-sidebar', 1, 'cactusthemes_style', 'style-4');
		cactus_import_widget_settings::set_widget_options('left-sidebar', 1, 'cactusthemes', 'small-padding');
		
		cactus_import_widget_settings::set_widget_options('right-sidebar', 4, 'cactusthemes_style', 'style-3');
		cactus_import_widget_settings::set_widget_options('right-sidebar', 4, 'cactusthemes', 'dark-div');
	}
}