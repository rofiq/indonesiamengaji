<?php

class videopro_playlist{
	private static $instance;
	
	public $template_url;
	
	public static function getInstance(){
		if(null == static::$instance){
			static::$instance = new static();
		}
		
		return static::$instance;
	}
	
	protected function __construct(){
		add_action( 'init', array($this, 'init' ));
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
	
	function init(){
		$this->template_url			= apply_filters( 'ct_video_template_url', 'cactus-video/' );
		 
		$this->register_post_type();
		add_filter( 'cmb_meta_boxes', array($this,'register_metadata') );
		
		if($this->get_option('enable_containing_playlists') == 1){
			add_action('videopro-single-video-before-comment', array($this, 'add_content_before_comment_in_single_video'), 10, 0);
		}
	}
	
	/* Get main options of the plugin. If there are any sub options page, pass Options Page Id to the second args
	 *
	 *
	 */
	function get_option($option_name, $op_id = ''){
		return $GLOBALS[$op_id != '' ? $op_id : 'ct_playlist_settings']->get($option_name);
	}
	
	function get_template($file){
		$find = array();
		$find[] = $file;
		$find[] = $this->template_url . $file;
		
		$template = locate_template( $find );
			
		if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
		
		return $template;
	}
	
	function add_content_before_comment_in_single_video(){
		// to be implemented later
		$playlists = get_post_meta(get_the_ID(), 'playlist_id', true);

		$args = array(	'post_type' => 'ct_playlist',
						'posts_per_page' => 4,
						'post__in' => $playlists,
						'orderby' => 'rand'
					);

		$the_query = new WP_Query($args);
		
		include $this->get_template('single-video-containing-playlists.php');
		
		wp_reset_postdata();
	}
	
	function register_post_type(){
		$labels = array(
			'name'               => esc_html__('Playlist', 'videopro'),
			'singular_name'      => esc_html__('Playlist', 'videopro'),
			'add_new'            => esc_html__('Add New Playlist', 'videopro'),
			'add_new_item'       => esc_html__('Add New Playlist', 'videopro'),
			'edit_item'          => esc_html__('Edit Playlist', 'videopro'),
			'new_item'           => esc_html__('New Playlist', 'videopro'),
			'all_items'          => esc_html__('All Playlists', 'videopro'),
			'view_item'          => esc_html__('View Playlist', 'videopro'),
			'search_items'       => esc_html__('Search Playlist', 'videopro'),
			'not_found'          => esc_html__('No Playlist found', 'videopro'),
			'not_found_in_trash' => esc_html__('No Playlist found in Trash', 'videopro'),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__('Video Playlist', 'videopro'),
		  );
		$slug_pl =  osp_get('ct_playlist_settings','playlist-slug');
		if(is_numeric($slug_pl)){ 
			$slug_pl = get_post($slug_pl);
			$slug_pl = $slug_pl->post_name;
		}
		if($slug_pl == ''){
			$slug_pl = 'playlist';
		}
		if ( $slug_pl )
			$rewrite =  array( 'slug' => untrailingslashit( $slug_pl ), 'with_front' => false, 'feeds' => true );
		else
			$rewrite = false;

		  $args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => $rewrite,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt')
		  );
		register_post_type( 'ct_playlist', $args );
	}
	
	function register_metadata(array $meta_boxes){
		
		// Plays list meta
		$playlist_channel = array(	
				array( 'id' => 'playlist_channel_id', 'name' => esc_html__('Channel','videopro'), 'type' => 'post_select', 'use_ajax' => true, 'query' => array( 'post_type' => 'ct_channel' ), 'multiple' => true,  'desc' => esc_html__('Add this playlist to a channel') , 'repeatable' => false ),
		);
		$meta_boxes[] = array(
			'title' => esc_html__('Video Channel','videopro'),
			'pages' => 'ct_playlist',
			'fields' => $playlist_channel,
			'priority' => 'high'
		);
		
		$playlist_id = array(	
				array( 'id' => 'playlist_id', 'name' => esc_html__('Playlist','videopro'), 'type' => 'post_select', 'use_ajax' => true, 'query' => array( 'post_type' => 'ct_playlist' ), 'multiple' => true,  'desc' => esc_html__('Add this video to a playlist', 'videopro'),  'repeatable' => false),
		);

		$meta_boxes[] = array(
			'title' => esc_html__('Video PlayList','videopro'),
			'pages' => 'post',
			'fields' => $playlist_id,
			'priority' => 'high'
		);
		
		return $meta_boxes;
	}
}

$videopro_playlist = videopro_playlist::getInstance();