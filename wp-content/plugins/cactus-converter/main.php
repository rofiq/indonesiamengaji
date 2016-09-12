<?php

/*
Plugin Name: Convert To VideoPro
Description: Convert data from True Mag, NewsTube to VideoPro
Version: 1.0.2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

register_shutdown_function( "cactus_converter_fatal_handler" );


    function cactus_converter_fatal_handler() {
      $error = error_get_last();

      if( $error !== NULL && $error['type'] == E_ERROR) {
        $result = array(
                                'message' => $error,
                                'error' => 1);
                                
                                echo json_encode($result);
      }
    }

if(!class_exists('cactus_converter')){
	class cactus_converter{
		function __construct(){
			add_action( 'init', array($this,'init'), 0);
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array($this, 'admin_video_scripts_styles') );
			
			add_action( 'wp_ajax_videopro_import', array($this, 'do_import' ));
			add_action( 'wp_ajax_nopriv_videopro_import', array($this, 'do_import' ));
		}
		
		public function init(){
			
		}
		
		public function admin_menu(){
			add_options_page(
				esc_html__('VideoPro Converter','cactus'),
				esc_html__('VideoPro Converter','cactus'),
				'manage_options',
				'videopro_converter',
				array(
					$this,
					'settings_page'
				)
			);
		}
		
		function admin_video_scripts_styles() {
			wp_enqueue_style('videopro-converter-css',plugins_url('/css/admin.css', __FILE__));
			wp_enqueue_script( 'videopro-converter-js',plugins_url('/js/admin.js', __FILE__) , array(), '20161405', true );
		}
		
		function do_import(){
			$theme = $_POST['theme'];
			$index = $_POST['index'];
			
			$total_posts = wp_count_posts('post');
			$total_channels = wp_count_posts('ct_channel');
			$total_playlists = wp_count_posts('ct_playlist');
			$total_series = wp_count_terms('video-series', array('hide_empty' => false));
			
			$total = $total_posts->publish + $total_channels->publish + $total_playlists->publish + $total_series;
			
			$progress = $index / $total * 100;
			
            $message = '';
            
			if($index < $total_posts->publish){
				$message = $this->convert_posts($index, $theme);
			} elseif($index < $total_posts->publish + $total_channels->publish){
				
			} elseif($index < $total_posts->publish + $total_channels->publish + $total_playlists->publish){
				
			} elseif($index < $total){
				$message = $this->convert_series($index - ($total_posts->publish + $total_channels->publish + $total_playlists->publish), $total_series, $theme);
			}
			
			$result = array(
							'progress' => $progress,
							'total' => $total_posts->publish,
                            'message' => $message);

			echo json_encode($result);
			die();
		}
		
		private function convert_series($index, $total, $theme){
			if($index < $total && post_type_exists('vseries_post')){
				$series = get_terms('video-series', array('hide_empty' => false));
				$i = 0;
				foreach($series as $seri){
					if($i == $index){
						// get related post type
						$posts = get_posts(array('post_type' => 'vseries_post', 'meta_key' => 'video_series_id', 'meta_value' => $seri->term_id));
						
						if(count($posts) > 0){
							// we do not need to do anything as vseries_post has been created
						} else {
							// create a post type of vseries_post, to save additional information for video series taxonomy
							$new_id = wp_insert_post(array(
												'post_type' => 'vseries_post',
												'post_title' => $seri->name,
												'post_status' => 'publish'
											));
											
							update_post_meta($new_id, 'video_series_id', $seri->term_id);
							update_post_meta($new_id, 'video_series_slug', $seri->slug);
						}

						wp_reset_postdata();
                        
                        return 'Converting ' . $seri->name;
                        
						break;
					}
					$i++;
				}
			} else {
                return 'No Video Series found';
            }
		}
		
		private function convert_posts($index, $theme){
			/**
			 * DO THE CONVERT HERE
			 */
			 $wp_query = new WP_Query(array(
								'post_type' => 'post',
								'offset' => $index,
								'posts_per_page' => 1,
								'post_status' => 'publish'
							));
							if($wp_query->have_posts()){
								$posts = $wp_query->posts;
							}

							
			if($posts && count($posts) > 0){
				$post = $posts[0];
				
				global $wpdb;
				
				
				/** CONVERT BAW VIEWS COUNT TO TOP-10 VIEWS **/
				
				$view_all = get_post_meta($post->ID, '_count-views_all', true);	

				$sql = "SELECT * FROM {$wpdb->prefix}top_ten WHERE postnumber = %d AND blog_id = %d";
				$found = $wpdb->get_results($wpdb->prepare($sql, $post->ID, get_current_blog_id()));
				
				if(!$found || count($found) == 0){

					// insert into table
					$done = $wpdb->insert("{$wpdb->prefix}top_ten",array('postnumber' => $post->ID,
																		'cntaccess' => $view_all,
																		'blog_id' => get_current_blog_id()),
																array('%d','%d', '%d'));
																

				}
				
				// daily views count
				$sql = "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key LIKE %s";
				$results = $wpdb->get_results($wpdb->prepare($sql, $post->ID, '_count-views_day-%'));
				
				if($results){
					foreach($results as $result){
						$day = substr($result->meta_key, 17); // cut meta_key string to get the date. format: YYYYMMDD
						$date = date_create_from_format('Ymd', $day);
						$day = $date->format('Y-m-d') . ' 00:00:00';
						
						// now move data to Top10 table
						$sql = "SELECT * FROM {$wpdb->prefix}top_ten_daily WHERE postnumber = %d AND dp_date = %s";
						$found = $wpdb->get_results($wpdb->prepare($sql, $post->ID, $day));
						
						if(!$found || count($found) == 0){

							// insert into table
							$done = $wpdb->insert("{$wpdb->prefix}top_ten_daily",array('postnumber' => $post->ID,
																				'cntaccess' => $result->meta_value, 
																				'dp_date' => $day,
																				'blog_id' => get_current_blog_id()),
																		array('%d','%d', '%s', '%d'));
																		

						}
					}
				}
				
				/** end VIEWS converting **/
				
				/** convert Video Duration **/
				$human_time = get_post_meta($post->ID, 'time_video', true);
				
				$values = explode(':', $human_time);
				
				$hours = 0; $mins = 0; $secs = 0;
				if(count($values) > 1){
					if(count($values) == 3) { $hours = $values[0]; $mins = $values[1]; $secs = $values[0];}
					if(count($values) == 2) { $mins = $values[0]; $secs = $values[0];}
					
							
					update_post_meta($post->ID, 'time_video', $hours * 3600 + $mins * 60 + $secs);
					update_post_meta($post->ID, 'video_duration', $human_time);
				}
				
				/** convert Video Ads metadata **/
				$ad_meta = get_post_meta($post->ID, 'video_ads_id', true);
				update_post_meta($post->ID, 'ads_id', $ad_meta);
                
                update_post_meta($post->ID, 'order_series', 0); //set default order in series
				
				/** SCB compatible **/
                return 'Converting ' . $post->post_title;
				
			} else {
                return 'No more posts!';
            }
		}
		
		public function settings_page(){
			?>
			<div class="wrap cactus-converter">
				<h2>VideoPro Converter Tool</h2>
                <div class="info">
                    <p>This tool is used to convert data from True Mag, NewsTube to VideoPro theme. Choose your previously used theme in the select box and click the button</p>
                    <p clas="note">Note: This tool does not auto-configure your site. It only converts Views, Video Metadata values to make them compatible with VideoPro. After converted, please re-configure Widgets, Menus and Shortcodes</p>
                </div>
				<p><label>Your previous theme: <br/>
					<select id="converter-theme-select">
						<option value="">-- Select --</option>
						<option value="truemag">True Mag (from CactusThemes)</option>
						<option value="newstube">NewsTube (from CactusThemes)</option>
					</select>
				</label></p>
				<div id="converter-button-wraper"><a href="javascript:void(0)" id="converter-button">Start Converting</a> <div class="progress-bar animate" id="import-progress-bar"><span class="inner" style="width:0%"><span><!-- --></span></span></div></div>
				<p id="converter-message"><!-- --></p>
			</div>
			<?php
		}
	}
}

$converter = new cactus_converter();