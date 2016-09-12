<?php

class videopro_channel{
	private static $instance;
	
	public $template_url;
	
	public static function getInstance(){
		if(null == static::$instance){
			static::$instance = new static();
		}
		
		return static::$instance;
	}
	
	protected function __construct(){
		$this->includes();
		add_action( 'init', array($this, 'init' ));
		add_action( 'admin_init', array( $this, 'add_social_account_meta' ) );
		add_action( 'wp_ajax_videopro_subscribe', array( $this, 'ajax_subscribe_channel') );
		add_action( 'wp_ajax_nopriv_videopro_subscribe', array( $this, 'ajax_subscribe_channel') );
	}
	function init(){
		$this->add_actions();
		
		if($this->get_option('enable_video_channels') != '0'){
			$this->register_post_type();
		}
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
	
	function add_actions(){
		add_action('cactus-video-subscribe-button', array($this, 'echo_subcribe_button'), 10, 1);
		add_filter( 'cmb_meta_boxes', array($this,'register_post_type_metadata') );
		
		if($this->get_option('enable_containing_channels') == 1){
			add_action('videopro-single-video-before-comment', array($this, 'add_content_before_comment_in_single_video'), 10, 0);
		}
	}
	
	function includes(){
		// Widget
		include ct_video_get_plugin_url().'widgets/top-channel.php';
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
		$channels = get_post_meta(get_the_ID(), 'channel_id', true);

		$args = array(	'post_type' => 'ct_channel',
						'posts_per_page' => 4,
						'post__in' => $channels,
						'orderby' => 'rand'
					);

		$the_query = new WP_Query($args);
		
		include $this->get_template('single-video-containing-channels.php');
		
		wp_reset_postdata();
	}
	
	function echo_subcribe_button($ID = ''){
		$subcribe_ID = $ID != '' ? $ID : get_the_ID();
		$j_subscribe = '';
		$action = $this->get_option('subscribe-button-action');
		$is_logged = is_user_logged_in();

		ob_start();
		if ( $is_logged ) {
			$button_id = "subscribe-" . $subcribe_ID;
			$user_id  = get_current_user_id();
			$subscribe_url = wp_nonce_url(home_url('/') . '?id='. $subcribe_ID. '&id_user=' . $user_id,'idn'.$subcribe_ID,'sub_wpnonce');
			
			$meta_user = get_user_meta($user_id, 'subscribe_channel_id',true);
			if(!is_array($meta_user) && $meta_user == $subcribe_ID){
				$j_subscribe = 'subscribed';
			}elseif(is_array($meta_user)&& in_array($subcribe_ID, $meta_user)){
				$j_subscribe = 'subscribed';
			}
			$l_href = 'javascript:;';
		} else {
			switch($action){
				case 'custom_url':
					$l_href = esc_url(add_query_arg(apply_filters('video-channels-subscribe-button-redirect_to_param','redirect_to'),urlencode(get_permalink()),$this->get_option('subscribe-button-url')));
					break;
				case 'popup':
					$popup = $this->get_option('subscribe-button-popup');
					$popup = apply_filters('the_content', $popup);
					$l_href = 'javascript:cactus_video.subscribe_login_popup(\'#login_require\');';
					break;
				case 'default':
				default:
					$l_href = esc_url(wp_login_url( get_permalink() ));
					break;
			}
		}
		$subscribe_counter = get_post_meta($subcribe_ID, 'subscribe_counter',true);
		if($subscribe_counter){
			$subscribe_counter = videopro_get_formatted_string_number($subscribe_counter);
		}else{$subscribe_counter = 0;}
		?>
		
		<?php if($action == 'popup'){?>        
            <div class="popup-classic" id="login_require">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title" id="myModalLabel"><?php echo esc_html__('Login Require', 'videopro');?></h4>
                  </div>
                  <div class="modal-body"><?php echo do_shortcode($popup);?></div>
                </div>
              </div>
            </div>        
		<?php }?>
        <div class="channel-button <?php echo esc_html($j_subscribe); ?>" id="<?php echo esc_attr($button_id);?>">
            <a href="<?php echo $l_href;?>" <?php if($is_logged) echo 'onclick="cactus_video.subscribe_channel(\'#' . esc_attr($button_id) . '\', \'' . esc_url($subscribe_url) . '\');"';?> class="btn btn-default <?php if($j_subscribe != ''){ echo esc_attr($j_subscribe);}else {echo 'subscribe';}?> font-size-1 metadata-font">
                <i class="fa fa-circle"></i><i class="fa fa-check"></i>
                <span class="first-title"><?php esc_html_e('SUBSCRIBE','videopro');?></span>
                <span class="last-title"><?php esc_html_e('SUBSCRIBED','videopro');?></span>
            </a>
            <input type="hidden"  name="url_ajax" value="<?php echo esc_url(admin_url( 'admin-ajax.php' )); ?>">
            <span class="font-size-1 metadata-font sub-count">
                <span class="subscribe-counter"><?php echo esc_html($subscribe_counter);?></span>               
            </span><span class="font-size-1 metadata-font sub-count meta-2">                
                <?php
				if(is_singular( 'ct_channel' )){
					$args = array(
						'post_type' => 'post',
						'post_status' => 'publish',
						'ignore_sticky_posts' => 1,
						'posts_per_page' => -1,
						'orderby' => 'latest',
						'meta_query' => array(
							array(
								'key' => 'channel_id',
								'value' => $subcribe_ID,
								'compare' => 'LIKE',
							),
						)
					);
					$video_query = new WP_Query( $args );
					$n_video = $video_query->post_count;
					
				?><span class="info-dot"></span><?php echo sprintf(esc_html__('%d videos', 'videopro'), $n_video);
				}?>
            </span>
        </div>
        <?php
		
		$button_html = ob_get_contents();
		ob_end_clean();
		
		echo apply_filters('video-channels-subscribe-button-filter', $button_html, $subcribe_ID);
	}

	/**
	 * ajax call to subscribe a channel
	 */
	function ajax_subscribe_channel(){
		$id 		= isset($_POST['id']) ? $_POST['id'] : '';
		$id_user 		= isset($_POST['id_user']) ? $_POST['id_user'] : '';
		if($id_user!='' && $id!=''){
			$meta =  get_user_meta($id_user, 'subscribe_channel_id',true);
			$subscribe_counter =  (int)get_post_meta($id, 'subscribe_counter',true);
			if($subscribe_counter == '' || $subscribe_counter==null) {$subscribe_counter = 0;};
			if($meta){
				if(!is_array($meta)){
					if($meta!= $id){
						$arr = array();
						array_push($arr, $meta);
						array_push($arr, $id);
						$meta = $arr;
						update_user_meta( $id_user, 'subscribe_channel_id', $meta);
						$subscribe_counter = $subscribe_counter +1; 
						update_post_meta( $id, 'subscribe_counter', $subscribe_counter);
						echo '1';
					}else{
						$meta = '';
						update_user_meta( $id_user, 'subscribe_channel_id', $meta);
						$subscribe_counter = $subscribe_counter -1; 
						update_post_meta( $id, 'subscribe_counter', $subscribe_counter);
						echo '0';
					}
				}else{
					if(in_array($id, $meta)){
						$key = array_search($id, $meta);
						unset($meta[$key]);
						update_user_meta( $id_user, 'subscribe_channel_id', $meta);
						$subscribe_counter = $subscribe_counter -1; 
						update_post_meta( $id, 'subscribe_counter', $subscribe_counter);
						echo 0;
					}else{
						array_push($meta, $id);
						update_user_meta( $id_user, 'subscribe_channel_id', $meta);
						$subscribe_counter = $subscribe_counter +1; 
						update_post_meta( $id, 'subscribe_counter', $subscribe_counter);
						echo 1;
					}
				}
			}else{
				$meta = $id;
				update_user_meta( $id_user, 'subscribe_channel_id', $meta);
				$subscribe_counter = $subscribe_counter +1; 
				update_post_meta( $id, 'subscribe_counter', $subscribe_counter);
				echo 1;
			}
		}
		exit;
	}
	
	/* Get main options of the plugin. If there are any sub options page, pass Options Page Id to the second args
	 *
	 *
	 */
	function get_option($option_name, $op_id = ''){
		return $GLOBALS[$op_id != '' ? $op_id : 'ct_channel_settings']->get($option_name);
	}
	
	/* Register ct_channel post type and its custom taxonomies */
	function register_post_type(){
		$labels = array(
			'name'               => esc_html__('Channel', 'videopro'),
			'singular_name'      => esc_html__('Channel', 'videopro'),
			'add_new'            => esc_html__('Add New Channel', 'videopro'),
			'add_new_item'       => esc_html__('Add New Channel', 'videopro'),
			'edit_item'          => esc_html__('Edit Channel', 'videopro'),
			'new_item'           => esc_html__('New Channel', 'videopro'),
			'all_items'          => esc_html__('All Channels', 'videopro'),
			'view_item'          => esc_html__('View Channel', 'videopro'),
			'search_items'       => esc_html__('Search Channel', 'videopro'),
			'not_found'          => esc_html__('No Channel found', 'videopro'),
			'not_found_in_trash' => esc_html__('No Channel found in Trash', 'videopro'),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__('Video Channel', 'videopro'),
		  );
		$slug_cn =  $this->get_option('channel-slug');
		if(is_numeric($slug_cn)){ 
			$slug_cn = get_post($slug_cn);
			$slug_cn = $slug_cn->post_name;
		}
		if($slug_cn==''){
			$slug_cn = 'channel';
		}
		if ( $slug_cn )
			$rewrite =  array( 'slug' => untrailingslashit( $slug_cn ), 'with_front' => false, 'feeds' => true );
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
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
		  );
		register_post_type( 'ct_channel', $args );
		
	}
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
	function register_post_type_metadata(array $meta_boxes){
		$channel_fields = array(	
				array( 'id' => 'channel_id', 'name' => esc_html__('Channel','videopro'), 'type' => 'post_select', 'use_ajax' => true, 'query' => array( 'post_type' => 'ct_channel' ), 'multiple' => true,  'desc' => esc_html__('Add this video to a channel', 'videopro'),  'repeatable' => false),
		);

		$meta_boxes[] = array(
			'title' => esc_html__('Video Channel','videopro'),
			'pages' => 'post',
			'fields' => $channel_fields,
			'priority' => 'high'
		);	
		
		$channel_fields = array(	
				array( 'id' => 'channel_sidebar', 'name' => esc_html__('Sidebar','videopro'), 'type' => 'select', 'options' => array('' => esc_html__('Default','videopro'),'both' => esc_html__('Left & Right','videopro'), 'left' => esc_html__('Left','videopro'), 'right' => esc_html__('Right','videopro'), 'full' => esc_html__('Hidden','videopro')),  'desc' => esc_html__('Choose sidebar for this channel','videopro'), 'repeatable' => false, 'multiple' => false));

		$meta_boxes[] = array(
			'title' => esc_html__('Channel Settings','videopro'),
			'pages' => 'ct_channel',
			'fields' => $channel_fields,
			'priority' => 'high'
		);
		
		return $meta_boxes;
	}
	
	function add_social_account_meta(){
		//option tree
		  $meta_box_review = array(
			'id'        => 'social_acount_box',
			'title'     => esc_html__('Social Account Settings', 'videopro'),
			'desc'      => esc_html__('', 'videopro'),
			'pages'     => array( 'ct_channel' ),
			'context'   => 'normal',
			'priority'  => 'high',
			'fields'    => array(
				array(
					  'id'          => 'facebook',
					  'label'       => esc_html__('Facebook', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel Facebook page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'twitter',
					  'label'       => esc_html__('Twitter', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel Twitter page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'youtube',
					  'label'       => esc_html__('YouTube', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel YouTube page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'linkedin',
					  'label'       => esc_html__('LinkedIn', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel LinkedIn page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'tumblr',
					  'label'       => esc_html__('Tumblr', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel Tumblr page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'google-plus',
					  'label'       => esc_html__('Google Plus', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel Google Plus page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'pinterest',
					  'label'       => esc_html__('Pinterest', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel Pinterest page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'flickr',
					  'label'       => esc_html__('Flickr', 'videopro'),
					  'desc'        => esc_html__('Enter link to channel Flickr page', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'envelope',
					  'label'       => esc_html__('Email', 'videopro'),
					  'desc'        => esc_html__('Enter channel email contact', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  ),
				  array(
					  'id'          => 'rss',
					  'label'       => esc_html__('RSS', 'videopro'),
					  'desc'        => esc_html__('Enter channel site\'s RSS URL', 'videopro' ),
					  'std'         => '',
					  'type'        => 'text',
					  'class'       => '',
					  'choices'     => array()
				  )
		  	)
		  );
		  $meta_box_review['fields'][] = array(
				'label'       => esc_html__('Custom Social Account', 'videopro'),
				'id'          => 'custom_social_account',
				'type'        => 'list-item',
				'class'       => '',
				'desc'        => esc_html__('Add more social accounts using Font Awesome Icons', 'videopro'),
				'choices'     => array(),
				'settings'    => array(
					 array(
						'label'       => esc_html__( 'Font Awesome Icons', 'videopro' ),
						'id'          => 'icon_custom_social_account',
						'type'        => 'text',
						'desc'        => esc_html__( 'Enter Font Awesome class (ex: fa-instagram)', 'videopro' ),
						'std'         => '',
						'rows'        => '',
						'post_type'   => '',
						'taxonomy'    => ''
					 ),
					 array(
						'label'       => esc_html__( 'URL', 'videopro' ),
						'id'          => 'url_custom_social_account',
						'type'        => 'text',
						'desc'        => esc_html__( 'Enter full link to channel social account (including http)', 'videopro' ),
						'std'         => '',
						'rows'        => '',
						'post_type'   => '',
						'taxonomy'    => ''
					 ),
				)
		  );
		  $meta_box_review['fields'][] = array(
					  'id'          => 'open_social_link_new_tab',
					  'label'       => esc_html__( 'Open Social Link in new tab', 'videopro' ),
					  'desc'        => esc_html__( 'Open link in new tab?', 'videopro' ),
					  'std'         => 'on',
					  'type'        => 'on-off',
					  'class'       => '',
					  'choices'     => array()
				  );
		  if (function_exists('ot_register_meta_box')) {
			ot_register_meta_box( $meta_box_review );
		  }
	}
}

$videopro_channel = videopro_channel::getInstance();
