<?php

/*
Plugin Name: Cactus Video
Description: Video Features for CactusThemes's themes
Author: CactusThemes
Version: 1.5.1
Author URI: http://cactusthemes.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!function_exists('ct_video_get_plugin_url')){
	function ct_video_get_plugin_url(){
		return plugin_dir_path(__FILE__);
	}
}
include ct_video_get_plugin_url().'shortcode/cactus-player.php';

if(!class_exists('Cactus_video')){	
$text_translate_video_st = esc_html__('General','videopro').esc_html__('Player for Video File','videopro').esc_html__('JWPlayer','videopro').esc_html__('FlowPlayer','videopro').esc_html__('VideoJS - HTML5 Video Player','videopro').esc_html__('WordPress Native Player: MediaElement','videopro').esc_html__('Force Using VideoJS for external videos','videopro').esc_html__('Yes','videopro').esc_html__('No','videopro').esc_html__('Auto Play Video','videopro').esc_html__('Yes','videopro').esc_html__('No','videopro').esc_html__('Auto Load Next Video','videopro').esc_html__('Yes','videopro').esc_html__('No','videopro').esc_html__('Auto Load Next Video after','videopro').esc_html__('Enter number in seconds. Ex: 5 (seconds)','videopro').esc_html__('Choose Next Video for Auto Load','videopro').esc_html__('Newer Video','videopro').esc_html__('Older Video','videopro').esc_html__('Youtube Settings','videopro').esc_html__('Force Using JWPlayer','videopro').esc_html__('No','videopro').esc_html__('Yes','videopro').esc_html__('Related videos','videopro').esc_html__('Display related videos at the end of the video','videopro').esc_html__('Hide','videopro').esc_html__('Show','videopro').esc_html__('Use HTML5 player','videopro').esc_html__('Use HTML5 player to play YouTube videos','videopro').esc_html__('No','videopro').esc_html__('Yes','videopro').esc_html__('Show Video Info on player','videopro').esc_html__('Show','videopro').esc_html__('Hide','videopro').esc_html__('Remove annotations on video','videopro').esc_html__('Yes','videopro').esc_html__('No','videopro').esc_html__('Force using Embed Code','videopro').esc_html__('No','videopro').esc_html__('Yes','videopro').esc_html__('Allow Full Screen','videopro').esc_html__('Yes','videopro').esc_html__('No','videopro').esc_html__('Allow Networking','videopro').esc_html__('Allow Interactive Videos','videopro').esc_html__('Disable','videopro').esc_html__('Enable','videopro').esc_html__('Playlist settings','videopro').esc_html__('Playlist slug','videopro').esc_html__('Change single playlist slug. Remember to save the permalink settings again in Settings > Permalinks','videopro').esc_html__('Playlists Listing page','videopro').esc_html__('Assign Playlists Listing page to a page. Remember to save the permalink settings again in Settings > Permalinks','videopro');
class Cactus_video{
	/* custom template relative url in theme, default is "ct_video" */
	public $template_url;
    
	/* Plugin path */
	public $plugin_path;
	
	/* Main query */
	public $query;
	
	public function __construct() {
		// constructor
		$this->includes();
		$this->register_configuration();
		
		add_action( 'init', array($this,'init'), 0);
		add_action( 'after_setup_theme', array($this,'includes_after'), 0 );
		add_action( 'template_redirect', array($this,'ct_plstop_redirect'), 0);
		add_action( 'plugins_loaded', array( $this, 'register_page_templates' ) );
		add_action( 'pre_get_posts', array($this, 'alter_main_query'));
		add_action( 'save_post', array($this, 'videopro_post_updated'), 5, 3);
	}
	
	function alter_main_query($query){
		global $wp_query;
		
		if(!$query->is_main_query())
			return;
		
		if($query->is_tax('video-series')){
			// list all posts in a series
			$query->set('posts_per_page', -1);
		}
	}
	
	function register_page_templates(){
		if(!class_exists('PageTemplater')){
			require 'includes/page-templater.php';
		}
		$enable_video_playlist = unserialize(get_option('ct_playlist_settings'));
		$enable_video_playlist = isset($enable_video_playlist['enable_video_playlist']) ? $enable_video_playlist['enable_video_playlist'] : '';
		
		$enable_video_channels = unserialize(get_option('ct_channel_settings'));
		$enable_video_channels = isset($enable_video_channels['enable_video_channels']) ? $enable_video_channels['enable_video_channels'] : '';
		
        $page_templ = array('cactus-video/includes/page-templates/watch-later.php' => esc_html__('Watch Later', 'videopro'));

		if($enable_video_playlist != false && $enable_video_channels != false){
			$page_templater ='';
			$page_templ = array_merge($page_templ, array(
				'cactus-video/includes/page-templates/video-series.php' => esc_html__('Video Series Listing Page', 'videopro'),
				'cactus-video/includes/page-templates/subscribed-channels.php' => esc_html__('Subscribed Channels Page', 'videopro'),
				'cactus-video/includes/page-templates/channel-listing.php' => esc_html__('Channel Listing Page', 'videopro'),
				'cactus-video/includes/page-templates/playlist-listing.php' => esc_html__('Playlist Listing Page', 'videopro')
			));				
		}elseif($enable_video_channels != false){
			$page_templ = array_merge($page_templ, array(
				'cactus-video/includes/page-templates/video-series.php' => esc_html__('Video Series Listing Page', 'videopro'),
				'cactus-video/includes/page-templates/subscribed-channels.php' => esc_html__('Subscribed Channels Page', 'videopro'),
				'cactus-video/includes/page-templates/channel-listing.php' => esc_html__('Channel Listing Page', 'videopro'),
			));
		}elseif($enable_video_playlist!=false){
			$page_templ = array_merge($page_templ, array(
				'cactus-video/includes/page-templates/video-series.php' => esc_html__('Video Series Listing Page', 'videopro'),
				'cactus-video/includes/page-templates/playlist-listing.php' => esc_html__('Playlist Listing Page', 'videopro')
			));
		}else{
			$page_templ = array_merge($page_templ, array(
				'cactus-video/includes/page-templates/video-series.php' => esc_html__('Video Series Listing Page', 'videopro'),
			));
		}
		
		
		$page_templater = PageTemplater::get_instance($page_templ);
	}

	function ct_plstop_redirect(){
		if ( is_singular('ct_playlist') ) {
			global $wp_query;
			$page = (int) $wp_query->get('page');
			if ( $page > 1 ) {
		 		 // convert 'page' to 'paged'
		  		$query->set( 'page', 1 );
		  		$query->set( 'paged', $page );
			}
		// prevent redirect
		remove_action( 'template_redirect', 'redirect_canonical' );
	  }

	  if(is_front_page()){
		  global $wp_query;
		  $page = (int) $wp_query->get('page');
		  if ( $page > 1 ) {
		  	remove_action( 'template_redirect', 'redirect_canonical' );
		  }
	  }
      
      // redirect a post to its External Link if the post does not have Single Page
      if(is_single()){
          global $post;
          $post_id = $post->ID;
          $has_single_page = get_post_meta($post_id, 'has_single_page', true);
            if($has_single_page == 'no'){
                $external_url = get_post_meta($post_id, 'external_url', true);
                if($external_url != ''){
                    wp_redirect($external_url);
                }
            }
      }
	}
	function ct_video_scripts_styles() {
		wp_enqueue_script( 'cactus-video-js',plugins_url('/js/custom.js', __FILE__) , array(), '', true );
		wp_enqueue_script( 'videopro-lightbox-js',plugins_url('/js/lightbox/lightbox.js', __FILE__) , array(), '20161405', true );
		wp_enqueue_script( 'videopro-lazysizes-js',plugins_url('/js/lazysizes.min.js', __FILE__) , array(), '20161405', true );
	}
	
	function enqueue_styles(){
		wp_enqueue_style('cactus-video-css',plugins_url('/css/main.css', __FILE__));
		wp_enqueue_style('videopro-lightbox-style', plugins_url('/js/lightbox/lightbox.css', __FILE__));
	}
	
	function admin_video_scripts_styles() {
		wp_enqueue_style('admin-video-css',plugins_url('/css/admin-css.css', __FILE__));
	}
	function includes_after(){
		include_once ct_video_get_plugin_url().'video-functions.php';

		if($this->get_option('enable_video_seri') != '0'){
			include_once ct_video_get_plugin_url().'video-series.php';
		}
	}
	function includes(){
		// custom meta boxes
		if(!function_exists('cmb_init')){
			if(!class_exists('CMB_Meta_Box')){
				include_once ct_video_get_plugin_url().'includes/Custom-Meta-Boxes-master/custom-meta-boxes.php';
			}
		}
		if(!class_exists('Options_Page')){
			include_once ct_video_get_plugin_url().'includes/options-page/options-page.php';
		}
		include_once ct_video_get_plugin_url().'video-hook-functions.php';
		include_once ct_video_get_plugin_url().'video-data-functions.php';
		include_once ct_video_get_plugin_url().'class.video-fetcher.php';
		$enable_video_channels = unserialize(get_option('ct_channel_settings'));
		$enable_video_channels = isset($enable_video_channels['enable_video_channels']) ? $enable_video_channels['enable_video_channels'] : '';
		if($enable_video_channels!=false){
			include_once ct_video_get_plugin_url().'video-channels.php';
		}
		$enable_video_playlist = unserialize(get_option('ct_playlist_settings'));
		$enable_video_playlist = isset($enable_video_playlist['enable_video_playlist']) ? $enable_video_playlist['enable_video_playlist'] : '';
		if($enable_video_playlist!=false){
			include_once ct_video_get_plugin_url().'video-playlists.php';
		}

		
		include ct_video_get_plugin_url().'shortcode/user-frontend-submit-button.php';
		include ct_video_get_plugin_url().'shortcode/theme-my-login.php';
		include ct_video_get_plugin_url().'widgets/widget-recommended-series.php';
		include ct_video_get_plugin_url().'shortcode/series-listing.php';
		include ct_video_get_plugin_url().'shortcode/channel-listing.php';
	}
	
	/* This is called as soon as possible to set up options page for the plugin
	 * after that, $this->get_option($name) can be called to get options.
	 *
	 */
	function register_configuration(){
		global $ct_video_settings;
		$ct_video_settings = new Options_Page('ct_video_settings', array('option_file'=>dirname(__FILE__) . '/options.xml','menu_title' => esc_html__('Video Extensions','videopro'),'menu_position'=>null), array('page_title'=>esc_html__('Video Extensions - Settings','videopro'),'submit_text'=>esc_html__('Save','videopro')));

		global $ct_channel_settings;
		$ct_channel_settings = new Options_Page('ct_channel_settings', array('option_file' => dirname(__FILE__) . '/options-channel.xml','menu_title'=>esc_html__('Video Channels','videopro'),'menu_position'=>null,'parent_menu' => 'ct_video_settings'), array('page_title' => esc_html__('Video Channels','videopro'),'submit_text'=>esc_html__('Save','videopro')));
		
		global $ct_playlist_settings;
		$ct_playlist_settings = new Options_Page('ct_playlist_settings', array('option_file' => dirname(__FILE__) . '/options-playlist.xml','menu_title'=>esc_html__('Video Playlist','videopro'),'menu_position'=>null,'parent_menu' => 'ct_video_settings'), array('page_title' => esc_html__('Video Playlist','videopro'),'submit_text'=>esc_html__('Save','videopro')));
	}
	
	/* Get main options of the plugin. If there are any sub options page, pass Options Page Id to the second args
	 *
	 *
	 */
	function get_option($option_name, $op_id = ''){
		$op = $op_id != '' ? $op_id : 'ct_video_settings';
        if(isset($GLOBALS[$op])) return $GLOBALS[$op]->get($option_name);
        return null;
	}
	
	function init(){
		// Variables
		$this->register_taxonomies();
		$this->template_url			= apply_filters( 'ct_video_template_url', 'cactus-video/' );
		add_filter( 'cmb_meta_boxes', array($this,'register_post_type_metadata') );
		add_filter( 'template_include', array( $this, 'template_loader' ) );

		add_action( 'wp_enqueue_scripts', array($this, 'ct_video_scripts_styles') );
		add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
		add_action( 'admin_enqueue_scripts', array($this, 'admin_video_scripts_styles') );
		
		add_action( 'admin_bar_menu', array($this, 'add_toolbar_link'), 999 );
        
        add_action( 'wp_ajax_get_video_player', array($this, 'get_video_player') );
        add_action( 'wp_ajax_nopriv_get_video_player', array($this, 'get_video_player') );
        
        add_action( 'wp_ajax_add_watch_later', array($this, 'add_watch_later') );
        add_action( 'wp_ajax_nopriv_add_watch_later', array($this, 'add_watch_later') );
        
        add_filter('option_tree_settings_args', array($this, 'filter_option_tree_settings'));
	}
    
    function filter_option_tree_settings($settings){
        $settings['settings'][] = array(
            'id'          => 'adsense_slot_ads_single_3',
            'label'       => 'Video Player Side-Ads Left - AdSense Ads Slot ID',
            'desc'        => esc_html__('This Ads appears on the Left Side of Video Player (full-width layout only). Enter Google AdSense Ad Slot ID here. If left empty, "Video Player Side-Ads Left - Custom Code" will be used.', 'videopro' ),
            'std'         => '',
            'type'        => 'text',
            'section'     => 'advertising'
          );
        
        $settings['settings'][] = array(
            'id'          => 'ads_single_3',
            'label'       => 'Video Player Side-Ads Left - Custom Code',
            'desc'        => esc_html__('Custom Code for Video Player Side-Ads Left', 'videopro' ),
            'std'         => '',
            'type'        => 'textarea-simple',
            'section'     => 'advertising'
          );
          
        $settings['settings'][] = array(
            'id'          => 'adsense_slot_ads_single_4',
            'label'       => 'Video Player Side-Ads Right - AdSense Ads Slot ID',
            'desc'        => esc_html__('This Ads appears on the Right Side of Video Player (full-width layout only). If left empty, "Single Post Ads 2 - Custom Code" will be used.', 'videopro' ),
            'std'         => '',
            'type'        => 'text',
            'section'     => 'advertising'
          );
        
        $settings['settings'][] = array(
            'id'          => 'ads_single_4',
            'label'       => 'Video Player Side-Ads Right - Custom Code',
            'desc'        => esc_html__('Custom Code for Video Player Side-Ads Right', 'videopro' ),
            'std'         => '',
            'type'        => 'textarea-simple',
            'section'     => 'advertising'
          );
          
        return $settings;
    }
    
    function add_watch_later(){
        $post_id = isset($_POST['id']) ? $_POST['id'] : 0;
        $current_url = isset($_POST['url']) ? $_POST['url'] : '';
        $todo = isset($_POST['do']) ? $_POST['do'] : '';
        
        $result = array();
        
        $user_id = get_current_user_id();
        if($post_id && $user_id){
            // only logged-in user can add to Watch Later
            $posts_list = get_user_meta($user_id, 'watch_later', true);
            
            if(!isset($posts_list) || !is_array($posts_list)) $posts_list = array();
            
            if(!in_array($post_id, $posts_list)){
                array_push($posts_list, $post_id);
                
                $result['status'] = 1;
                $result['message'] = esc_html('Added to Watch Later', 'videopro');
                
                update_user_meta($user_id, 'watch_later', $posts_list);
            } else {
                $result['status'] = 1;
                $result['message'] = esc_html('Added to Watch Later', 'videopro');
                
                // do nothing
                if($todo == 'remove'){
                    if(($key = array_search($post_id, $posts_list)) !== false) {
                        unset($posts_list[$key]);
                        
                        update_user_meta($user_id, 'watch_later', $posts_list);
                    }
                    
                    $result['status'] = -1;
                    $result['message'] = esc_html('Removed from Watch Later', 'videopro');
                }
            }
        } else {
            $result['status'] = 0;
            $sign_in_url = wp_login_url($current_url);
            $result['message'] = sprintf(wp_kses(__('<a href="%s">Sign in</a> to add this to Watch Later', 'videopro'), array('a' => array('href' => array()))), $sign_in_url);
        }
        
        echo json_encode($result);
        
        die();
    }
    
    function get_video_player(){
        $post_id = $_POST['id'];
        
        $html = '';
        ob_start();
        ?>
        
        <div class="cactus-post-format-video<?php if(osp_get('ct_video_settings','video_floating')=='on'){echo ' floating-video '.osp_get('ct_video_settings','video_floating_position');}?>">
            <div class="cactus-video-content-api cactus-video-content"> 
                <span class="close-video-floating"><i class="fa fa-times" aria-hidden="true"></i></span>
                <?php echo do_shortcode('[cactus_player id="'.$post_id.'"]');?>
            </div>
        </div>
        
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        
        echo $html;
        
        die();
    }
	
	function videopro_post_updated( $post_id, $post, $update ) {
		if('post' != get_post_type($post_id))
			return;
		
		if ( wp_is_post_revision( $post_id ) )
			return;

		if( isset($_POST['tm_video_url']['cmb-field-0']) )
		{
			$url = $_POST['tm_video_url']['cmb-field-0'];
		}

		if(!isset($url) || $url == ''){
			$url = get_post_meta($post_id, 'tm_video_url', true);
		}

		if(!isset($url) || $url == ''){ 
			return;
		}
		
		$post_data = array('ID' => $post_id);

		$data =  Video_Fetcher::fetchData($url, $fields = array(), $post_id);
		
		$auto_get_info = get_post_meta($post_id,'fetch_info',true);
		
		if(($url != '' && (strpos($url, 'youtube.com') !== false)) || ($url !='' && (strpos($url, 'vimeo.com') !== false)) || ($url !='' && (strpos($url, 'dailymotion.com') !== false))){
			if(empty($auto_get_info) || $auto_get_info['0'] != '1'){
				if(function_exists('osp_get')){
					$get_info = osp_get('ct_video_settings','auto_get_info');
				}
				if(empty($get_info)){
					return;
				}
				if(in_array('1', $get_info)){
					$post_data['post_title'] =  $data['title'] ;
					$post_data['post_name'] =  $data['title'] ;
				}
				if(in_array('2', $get_info)){
					$post_data['post_content'] = $data['description'];
				}
				
				if(in_array('3',$get_info)){
					wp_set_post_tags( $post_id, $data['tags'], true );

				}
				if(in_array('4',$get_info)){
					update_post_meta($post_id, '_video_network_views', $data['viewCount']);
					update_post_meta($post_id, '_video_network_likes', $data['likeCount']);
					update_post_meta($post_id, '_video_network_dislikes', $data['dislikeCount']);
					update_post_meta($post_id, '_video_network_comments', $data['commentCount']);

					if($_POST['video_duration']['cmb-field-0'] == ''){
						update_post_meta($post_id, 'time_video', $data['duration']);
					
						// auto fill in video_duration (human-read time). We do not need to save this as custom_meta_box already did
						$seconds = $data['duration'];
						$hours = floor($seconds / 3600);
						$mins = floor($seconds / 60 % 60);
						$secs = floor($seconds % 60);

						$_POST['video_duration'] = ($hours > 0 ? ($hours . ':') : '') . $mins . ':' . $secs;
					} else {
						// if user specifies video_duration, then update our time_video value
						$human_time = $_POST['video_duration']['cmb-field-0'];
						$values = explode(':', $human_time);
						$hours = 0; $mins = 0; $secs = 0;
						if(count($values) == 3) { $hours = $values[0]; $mins = $values[1]; $secs = $values[0];}
						if(count($values) == 2) { $mins = $values[0]; $secs = $values[0];}
						
						update_post_meta($post_id, 'time_video', $hours * 3600 + $mins * 60 + $secs);
					}
				}
				
				// update the post, removing the action to prevent an infinite loop
				remove_action( 'save_post', array($this, 'videopro_post_updated' ), 5, 3);
				wp_update_post($post_data);
				add_action('save_post', array($this, 'videopro_post_updated' ), 5, 3);
			}
		}
	}
	
	function add_toolbar_link( $wp_admin_bar ) {
		$args = array(
			'id'    => 'cactus_video_extension',
			'title' => esc_html__('Video Extension','videopro'),
			'href'  => admin_url('admin.php?page=ct_video_settings'),
			'meta'  => array( 'class' => 'cactus-video-extension' ),
			'parent' => 'appearance'
		);
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Get the plugin path.
	 *
	 * @access public
	 * @return string
	 */
	public function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
	/**
	 *
	 * Load custom page template for specific pages 
	 *
	 * @return string
	 */
	function template_loader($template){
		$find = array('cactus-video.php');
		$file = '';
		
		if(is_post_type_archive( 'ct_playlist' )){
			$slug_pl =  osp_get('ct_playlist_settings','playlist-slug') !='' ? osp_get('ct_playlist_settings','playlist-slug') :'playlist';
			$query = new WP_Query( array('post_type'  => 'page', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'cactus-video/includes/page-templates/playlist-listing.php' ) );
           	if ( $query->have_posts() ){
				while ( $query->have_posts() ) : $query->the_post();
					$slug = basename( get_permalink() );
					if($slug == $slug_pl){
						return ct_video_get_plugin_url().'includes/page-templates/playlist-listing.php';
					}else{
						wp_redirect( get_permalink() ); exit;
					}
				endwhile;
				wp_reset_postdata();
			}else{
				return ct_video_get_plugin_url().'includes/page-templates/playlist-listing.php';
			}
		}
		elseif(is_singular('ct_playlist')){
			$file = 'single-playlist.php';
		} elseif(is_tax('video-series')){
			$file = 'video-series.php';
		}
		
		if(is_post_type_archive( 'ct_channel' )){
			$slug_channel =  osp_get('ct_channel_settings','channel-slug')!='' ? osp_get('ct_channel_settings','channel-slug') : 'channel';
			$query = new WP_Query( array('post_type'  => 'page', 'posts_per_page' => 1, 'meta_key' => '_wp_page_template', 'meta_value' => 'cactus-video/includes/page-templates/channel-listing.php' ) );
           	if ( $query->have_posts() ){
				while ( $query->have_posts() ) : $query->the_post();
					$slug = basename( get_permalink() );
					if($slug == $slug_channel){
						return ct_video_get_plugin_url().'includes/page-templates/channel-listing.php';
					}else{
						wp_redirect( get_permalink() ); exit;
					}
				endwhile;
				wp_reset_postdata();
			}else{
				return ct_video_get_plugin_url().'includes/page-templates/channel-listing.php';
			}
		}
		elseif(is_singular('ct_channel')){
			$file = 'single-channel.php';
		}
		
		$find[] = $file;
		$find[] = $this->template_url . $file;
		
		if ( $file ) {
			$template = locate_template( $find );
			
			if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
		}
		return $template;		
	}
	
	/**
	 * Handle redirects before content is output - hooked into template_redirect so is_page works.
	 *
	 * @access public
	 * @return void
	 */
	function template_redirect(){
		global $ct_video, $wp_query;

		// When default permalinks are enabled, redirect stores page to post type archive url
		if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == "" && $_GET['page_id'] ==  'video') {
			wp_safe_redirect( get_post_type_archive_link('ct_video') );
			exit;
		}
		
		$slug_cn =  osp_get('ct_channel_settings','channel-slug');
		if(is_numeric($slug_cn)){ 
			$slug_cn = get_post($slug_cn);
			$slug_cn = $slug_cn->post_name;
		}
		if($slug_cn==''){
			$slug_cn = 'channel';
		}
		// When default permalinks are enabled, redirect stores page to post type archive url
		if ( ! empty( $_GET['page_id'] ) && get_option( 'permalink_structure' ) == "" && $_GET['page_id'] ==  $slug_cn) {
			wp_safe_redirect( get_post_type_archive_link('ct_channel') );
			exit;
		}
	}

	function register_taxonomies(){
		
	}
			
	/* Register meta box for Store Type 
	 * Wordpress 3.8
	 */
	function ct_video_type_meta_box_cb($post, $box){
		$defaults = array('taxonomy' => 'post_tag');
		if ( !isset($box['args']) || !is_array($box['args']) )
			$args = array();
		else
			$args = $box['args'];
		extract( wp_parse_args($args, $defaults), EXTR_SKIP );
		$tax_name = esc_attr($taxonomy);
		$taxonomy = get_taxonomy($taxonomy);
		$user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
		$comma = _x( ',', 'tag delimiter' );
		?>
		<div class="tagsdiv" id="<?php echo $tax_name; ?>">
			<div class="jaxtag">
			<div class="nojs-tags hide-if-js">
			<p><?php echo $taxonomy->labels->add_or_remove_items; ?></p>
			<textarea name="<?php echo "tax_input[$tax_name]"; ?>" rows="3" cols="20" class="the-tags" id="tax-input-<?php echo $tax_name; ?>" <?php disabled( ! $user_can_assign_terms ); ?>><?php echo str_replace( ',', $comma . ' ', get_terms_to_edit( $post->ID, $tax_name ) ); // textarea_escaped by esc_attr() ?></textarea></div>
			<?php if ( $user_can_assign_terms ) : ?>
			<div class="ajaxtag hide-if-no-js">
				<label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $box['title']; ?></label>
				<div class="taghint"><?php echo $taxonomy->labels->add_new_item; ?></div>
				<p><input type="text" id="new-tag-<?php echo $tax_name; ?>" name="newtag[<?php echo $tax_name; ?>]" class="newtag form-input-tip" size="16" autocomplete="off" value="" />
				<input type="button" class="button tagadd" value="<?php esc_attr_e('Add'); ?>" /></p>
			</div>
			<p class="howto"><?php echo $taxonomy->labels->separate_items_with_commas; ?></p>
			<?php endif; ?>
			</div>
			<div class="tagchecklist"></div>
		</div>
		<?php if ( $user_can_assign_terms ) : ?>
		<p class="hide-if-no-js"><a href="#titlediv" class="tagcloud-link" id="link-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->choose_from_most_used; ?></a></p>
		<?php endif; ?>
		<?php
	}
	
	/**
	 * Display post categories form fields.
	 *
	 * @since 2.6.0
	 *
	 * @param object $post
	 */
	function ct_video_categories_meta_box_cb( $post, $box ) {
	$defaults = array('taxonomy' => 'category');
	if ( !isset($box['args']) || !is_array($box['args']) )
		$args = array();
	else
		$args = $box['args'];
	extract( wp_parse_args($args, $defaults), EXTR_SKIP );
	$tax = get_taxonomy($taxonomy);

	?>
	<div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">
		<ul id="<?php echo $taxonomy; ?>-tabs" class="category-tabs">
			<li class="tabs"><a href="#<?php echo $taxonomy; ?>-all"><?php echo $tax->labels->all_items; ?></a></li>
			<li class="hide-if-no-js"><a href="#<?php echo $taxonomy; ?>-pop"><?php _e( 'Most Used' ); ?></a></li>
		</ul>

		<div id="<?php echo $taxonomy; ?>-pop" class="tabs-panel" style="display: none;">
			<ul id="<?php echo $taxonomy; ?>checklist-pop" class="categorychecklist form-no-clear" >
				<?php $popular_ids = wp_popular_terms_checklist($taxonomy); ?>
			</ul>
		</div>

		<div id="<?php echo $taxonomy; ?>-all" class="tabs-panel">
			<?php
            $name = ( $taxonomy == 'category' ) ? 'post_category' : 'tax_input[' . $taxonomy . ']';
            echo "<input type='hidden' name='{$name}[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
            ?>
			<ul id="<?php echo $taxonomy; ?>checklist" data-wp-lists="list:<?php echo $taxonomy?>" class="categorychecklist form-no-clear">
				<?php wp_terms_checklist($post->ID, array( 'taxonomy' => $taxonomy, 'popular_cats' => $popular_ids ) ) ?>
			</ul>
		</div>
	<?php if ( current_user_can($tax->cap->edit_terms) ) : ?>
			<div id="<?php echo $taxonomy; ?>-adder" class="wp-hidden-children">
				<h4>
					<a id="<?php echo $taxonomy; ?>-add-toggle" href="#<?php echo $taxonomy; ?>-add" class="hide-if-no-js">
						<?php
							/* translators: %s: add new taxonomy label */
							printf( __( '+ %s' ), $tax->labels->add_new_item );
						?>
					</a>
				</h4>
				<p id="<?php echo $taxonomy; ?>-add" class="category-add wp-hidden-child">
					<label class="screen-reader-text" for="new<?php echo $taxonomy; ?>"><?php echo $tax->labels->add_new_item; ?></label>
					<input type="text" name="new<?php echo $taxonomy; ?>" id="new<?php echo $taxonomy; ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $tax->labels->new_item_name ); ?>" aria-required="true"/>
					<label class="screen-reader-text" for="new<?php echo $taxonomy; ?>_parent">
						<?php echo $tax->labels->parent_item_colon; ?>
					</label>
					<?php wp_dropdown_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => 0, 'name' => 'new'.$taxonomy.'_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => '&mdash; ' . $tax->labels->parent_item . ' &mdash;' ) ); ?>
					<input type="button" id="<?php echo $taxonomy; ?>-add-submit" data-wp-lists="add:<?php echo $taxonomy ?>checklist:<?php echo $taxonomy ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $tax->labels->add_new_item ); ?>" />
					<?php wp_nonce_field( 'add-'.$taxonomy, '_ajax_nonce-add-'.$taxonomy, false ); ?>
					<span id="<?php echo $taxonomy; ?>-ajax-response"></span>
				</p>
			</div>
		<?php endif; ?>
	</div>
	<?php

}
	
	function register_post_type_metadata(array $meta_boxes){
		// register aff store metadata
		$video_fields = array(	
				array( 'id' => 'tm_video_url', 'name' => esc_html__( 'Video URL','videopro'), 'type' => 'text','desc' => wp_kses(__( 'Paste the url from popular video sites like YouTube or Vimeo. For example: <br/><code>http://www.youtube.com/watch?v=nTDNLUzjkpg</code><br/>or<br/><code>http://vimeo.com/23079092</code>','videopro'),array('br'=>array()),array('code'=>array())),  'repeatable' => false, 'multiple' => false ),	
				array( 'id' => 'tm_video_file', 'name' => esc_html__('Video File','videopro'), 'type' => 'textarea', 'desc' => wp_kses(__( 'Paste your video file url to here. Supported Video Formats: mp4, m4v, webmv, webm, ogv and flv.<br/><b>About Cross-platform and Cross-browser Support</b><br/>If you want your video works in all platforms and browsers(HTML5 and Flash), you should provide various video formats for same video, if the video files are ready, enter one url per line.<br/> For Example:<br/> <code>http://yousite.com/sample-video.m4v</code><br/><code>http://yousite.com/sample-video.ogv</code><br/> <b>Recommended Format Solution:</b> webmv + m4v + ogv. ','videopro'),array('br'=>array()),array('b'=>array()),array('code'=>array())),  'repeatable' => false, 'multiple' => false ),	
				array( 'id' => 'tm_video_code', 'name' => esc_html__('Video Embeded Code'), 'type' => 'textarea', 'desc' => wp_kses(__( 'Paste the raw video code to here, such as <code>&lt;</code><code>object</code><code>&gt;</code>,<code>&lt;</code><code>embed</code><code>&gt;</code> or <code>&lt;</code><code>iframe</code><code>&gt;</code> code.','videopro'),array('br'=>array()),array('b'=>array()),array('code'=>array())),  'repeatable' => false, 'multiple' => false ),	
				array( 'id' => 'video_duration', 'name' => esc_html__('Duration'), 'desc' => esc_html__('Human-read time value, ex. mm:ss. Leave empty to fetch data again or enter your own value here', 'videopro'), 'type' => 'text',  'repeatable' => false, 'multiple' => false ),
				array( 'id' => 'video_download_url', 'name' => esc_html__('Download Link'), 'desc' => esc_html__('If this video can be downloaded, provide the download link here. In fact, you can add any link here', 'videopro'), 'type' => 'text' ),
				array( 'id' => 'video_download_button', 'name' => esc_html__('Download Button Text'), 'desc' => esc_html__('Change the Download Button text', 'videopro'), 'type' => 'text' ),
                array( 'id' => 'external_url', 'name' => esc_html__('External Link'), 'desc' => esc_html__('If this video has external link then instead of playing video, users will be redirected to this URL when they click on thumbnail image', 'videopro'), 'type' => 'text' ),
                array( 
                        'id' => 'has_single_page', 
                        'name' => esc_html__('Has Single Page'), 
                        'desc' => esc_html__('Choose to disable single page for this video. It means that users will be redirected to External Link when clicking on thumbnail image of this video in archives page', 'videopro'), 
                        'type' => 'select', 
                        'options' => array('' => esc_html__('Yes', 'videopro'),
                                            'no' => esc_html__('No (use External Link)', 'videopro'))
                    ),
                array( 
                        'id' => 'video_player', 
                        'name' => esc_html__('Video Header'), 
                        'desc' => esc_html__('Choose to display Thumbnail Image in Single Post, or display Video Player', 'videopro'), 
                        'type' => 'select', 
                        'options' => array('' => esc_html__('Default (use setting in Video Extension', 'videopro'),
                                            '2' => esc_html__('Thumbnail Image', 'videopro'),
                                            '1'    => esc_html__('Video Player', 'videopro'))
                    )
			);

		$meta_boxes[] = array(
			'title' => esc_html__('Video settings','videopro'),
			'pages' => 'post',
			'fields' => $video_fields,
			'priority' => 'high'
		);	
		$playlogic_fields = array(	
				array( 'id' => 'player_logic', 'name' => esc_html__( 'Player logic','videopro'), 'type' => 'text','desc' => wp_kses(__( 'Enter shortcode (ex: [my_shortcode][player][/my_shortcode], <strong>[player]</strong> is required)<br>or condition function (ex: <b>is_user_logged_in() && is_sticky()</b> ) to control video player visiblitily','videopro'),array('br'=>array()),array('strong'=>array()),array('code'=>array())) ,  'repeatable' => false, 'multiple' => false ),	
				array( 'id' => 'player_logic_alt', 'name' => esc_html__('Alternative Content','videopro'), 'type' => 'text', 'desc' => esc_html__( 'Content to display when Condition is false (Not work with Shortcodes)','videopro') ,  'repeatable' => false, 'multiple' => false ),
			);
		$meta_boxes[] = array(
			'title' => esc_html__('Player Logic','videopro'),
			'pages' => 'post',
			'fields' => $playlogic_fields,
			'priority' => 'high'
		);
		
		// Auto Fetch
		$auto_fetch_data = array(	
				array( 'id' => 'fetch_info',  'name' => esc_html__('Do Not Fetch','videopro'), 'type' => 'checkbox','multiple' => false,  'desc' => esc_html__('Check bellow checkbox if you do not want to auto-fetch video data after save/edit. To chose which fields to fetch, go to Video Setting > ','videopro') , 'repeatable' => false ),
		);
		$meta_boxes[] = array(
			'title' => esc_html__('Videos Auto Fetch Data','videopro'),
			'pages' => 'post',
			'fields' => $auto_fetch_data,
			'context' => 'side',
			'priority' => 'high'
		);
		
		return $meta_boxes;
	}
}


} // class_exists check

/**
 * Init Cactus_video
 */
$GLOBALS['cactus_video'] = new Cactus_video();

/**
 * Modify main search query, override theme's function
 */
if(!function_exists('videopro_modify_search')){
	function videopro_modify_search($query){
		$s = get_search_query();

		if($s != '' || isset($_GET['orderby']) && $_GET['orderby']!=''){
			if($query->is_main_query()){
				if($s != ''){
					// search only
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
						if($length != 0){
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
				}

				$use_network_data = osp_get('ct_video_settings', 'use_video_network_data');
				$use_network_data = ($use_network_data == 'on') ? 1 : 0;
				
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
						if($use_network_data){
							$query->set('order', 'DESC');
							$query->set('orderby', 'meta_value_num');
							$query->set('meta_key', '_video_network_comments');
						} else {
							$query->set('orderby', 'comment_count');
						}
					}elseif($order == 'ratings'){
						$query->set('meta_key', 'taq_review_score');
						$query->set('orderby', 'meta_value_num');
					}elseif($order == 'view'){
						if($use_network_data){

							$query->set('order', 'DESC');
							$query->set('orderby', 'meta_value_num');
							$query->set('meta_key', '_video_network_views');
						} else {
							if(function_exists('videopro_get_tptn_pop_posts')){
								$args = array(
									'daily' => 0,
									'post_types' =>'post'
								);
								$ids = videopro_get_tptn_pop_posts($args);
								$query->set('post__in', $ids );
								$query->set('orderby', 'post__in');
							}
						}
					}elseif($order == 'like'){
						if($use_network_data){
							$query->set('order', 'DESC');
							$query->set('orderby', 'meta_value_num');
							$query->set('meta_key', '_video_network_likes');
						} else {
							$ids = videopro_get_most_like();
							
							if(!empty($ids)){
								$query->set('post__in', $ids );
								$query->set('orderby', 'post__in');
							}
						}
					}
				}
				if($s != ''){ // search only
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

function videopro_custom_order_join($clauses)
{
    global $wp_query;

    // check for order by custom_order
    if (($wp_query->get('meta_key') == '_video_network_comments' || $wp_query->get('meta_key') == '_video_network_likes' || $wp_query->get('meta_key') == '_video_network_views' || $wp_query->get('meta_key') == 'taq_review_score') && $wp_query->get('orderby') == 'meta_value_num')
    {
        // change the inner join to a left join, 
        // and change the where so it is applied to the join, not the results of the query
        $clauses['join'] = str_replace('INNER JOIN', 'LEFT JOIN', $clauses['join']).$clauses['where'];
        $clauses['where'] = '';
    }
    return $clauses;
}
add_filter('get_meta_sql', 'videopro_custom_order_join', 10, 1);
?>