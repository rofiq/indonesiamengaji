<?php
//Report
//add report post type
add_action( 'init', 'videopro_report_post_type' );
function videopro_report_post_type() {
	$args = array(
		'labels' => array(
			'name' => esc_html__( 'Reports', 'videopro' ),
			'singular_name' => esc_html__( 'Report', 'videopro' )
		),
		'menu_icon' 		=> 'dashicons-flag',
		'public'             => true,
		'publicly_queryable' => true,
		'exclude_from_search'=> true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'editor', 'custom-fields' )
	);
	if(ot_get_option('video_report','on')!='off'){
		register_post_type( 'tm_report', $args );
	}
}
//redirect report post type
add_action( 'template_redirect', 'videopro_redirect_report_post_type' );
function videopro_redirect_report_post_type() {
	global $post;
	if(is_singular('tm_report')){
		if($url = get_post_meta(get_the_ID(),'tm_report_url',true)){
			wp_redirect($url);
		}
	}
}

function videopro_report_input($tag){
	$class = '';
	$is_required = 0;
	if(class_exists('WPCF7_Shortcode')){
		$tag = new WPCF7_Shortcode( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	}
	$output = '<div class="hidden wpcf7-form-control-wrap report_url"><div class="wpcf7-form-control wpcf7-validates-as-required'.$class.'">';
	$output .= '<input name="report-url" class="hidden wpcf7-form-control wpcf7-text wpcf7-validates-as-required" type="hidden" value="'.esc_attr(videopro_get_current_url()).'" />';
	$output .= '</div></div>';
	return $output;
}
//contact form 7 hook
function videopro_contactform7_hook($WPCF7_ContactForm) {
	if(osp_get('ct_video_settings','user_submit')){
		$submission = WPCF7_Submission::get_instance();
		if($submission) {
			$posted_data = $submission->get_posted_data();
            
           
			if(isset($posted_data['video-url']) || isset($posted_data['video-code']) || isset($posted_data['video-file'])){
                
				$post_title = isset($posted_data['post-title']) ? $posted_data['post-title'] : esc_html('User Submitted Post Title', 'videopro');
				$post_description = isset($posted_data['post-description']) ? $posted_data['post-description'] : esc_html('User Submitted Post Content', 'videopro');
				$post_excerpt = isset($posted_data['post-excerpt']) ? $posted_data['post-excerpt'] : '';
				$post_user = isset($posted_data['your-email']) ? $posted_data['your-email'] : '';
				$post_cat = isset($posted_data['cat']) ? $posted_data['cat'] : '';
				$post_tag = isset($posted_data['tag'])? $posted_data['tag'] : '';
				$post_status = osp_get('ct_video_settings','user_submit_status') ? osp_get('ct_video_settings','user_submit_status') : 'pending';
                
                
				$post_args = array(
				  'post_content'   => $post_description,
				  'post_excerpt'   => $post_excerpt,
				  'post_name' 	   => sanitize_title($post_title), //slug
				  'post_title'     => $post_title,
				  'post_status'    => $post_status,
				  'post_category'  => $post_cat,
				  'tags_input'	   => $post_tag,
				  'post_type'      => 'post'
				);
                
                $post_args = apply_filters('videopro_before_video_submission', $post_args, $posted_data);
                
				if($new_ID = wp_insert_post( $post_args, false )){
                    
					// upload 
					if(isset($posted_data["video-file"]) && $posted_data["video-file"] != ''){ 
						$video_name = $posted_data["video-file"];
						$video_location = $submission->uploaded_files();
						$video_location = $video_location["video-file"];
						$content = file_get_contents($video_location);
						$wud = wp_upload_dir(); 
						$upload = wp_upload_bits( $video_name, '', $content);
						$chemin_final = $upload['url'];
						$filename= $upload['file'];
						require_once(ABSPATH . 'wp-admin/includes/admin.php');
						$wp_filetype = wp_check_filetype(basename($filename), null );
						  $attachment = array(
						   'post_mime_type' => $wp_filetype['type'],
						   'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
						   'post_content' => '',
						   'post_status' => 'inherit'
						);
						$attach_id = wp_insert_attachment( $attachment, $filename, $new_post_id);
						add_post_meta( $new_ID, 'tm_video_file', wp_get_attachment_url($attach_id));
					}
                    
                    // video code
					if(isset($posted_data['video-code'])){
                        add_post_meta( $new_ID, 'tm_video_code', $posted_data['video-code'] );
                    }
                    
                    // video URL
                    if(isset($posted_data['video-url'])){
                        add_post_meta( $new_ID, 'tm_video_url', $posted_data['video-url'] );
                    }
					
					add_post_meta( $new_ID, 'tm_user_submit', $post_user );
                    
                    if(isset($posted_data['channel'])){
                        $channels = $posted_data['channel'];
                        
                        add_post_meta( $new_ID, 'channel_id', $channels);
                        
                    }
                    
                    if(isset($posted_data['playlist'])){
                        $playlists = $posted_data['playlist'];
                        
                        add_post_meta( $new_ID, 'playlist_id', $playlists);
                    }
                    
                    $post_format = osp_get('ct_video_settings','user_submit_format');
					set_post_format( $new_ID, $post_format );
                    
                    do_action('videopro_after_post_submission', $new_ID, $posted_data);
				}
			} elseif(isset($posted_data['report-url'])){
                $post_url = $posted_data['report-url'];
                $post_user = isset($posted_data['your-email'])?$posted_data['your-email']:'';
                $post_message = isset($posted_data['your-message'])?$posted_data['your-message']:'';
                
                $post_title = $post_user.(esc_html__(' reported a post','videopro'));
                $post_content = $post_user.esc_html__(' reported a post has inappropriate content with message:','videopro').
                    '<blockquote>'.$post_message.'</blockquote><br><br>'.
                    esc_html__('You could review it here','videopro').' <a href="'.esc_url($post_url).'">'.esc_url($post_url).'</a>';
                
                $report_post = array(
                  'post_content'   => $post_content,
                  'post_title'     => $post_title,
                  'post_status'    => 'publish',
                  'post_type'      => 'tm_report'
                );

                if($new_ID = wp_insert_post( $report_post, false )){
                    add_post_meta( $new_ID, 'tm_report_url', $post_url );
                    add_post_meta( $new_ID, 'tm_user_submit', $post_user );
                    
                    do_action('videopro_after_post_report', $new_ID, $posted_data);
                }
            } elseif(isset($posted_data['post-description']) && isset($posted_data['post-title'])){
                // standard post submission
                
                $post_title = $posted_data['post-title'];
				$post_description = $posted_data['post-description'];
                $post_excerpt = isset($posted_data['post-excerpt'])?$posted_data['post-excerpt'] : '';
				$post_user = isset($posted_data['your-email']) ? $posted_data['your-email'] : '';
				$post_cat = isset($posted_data['cat']) ? $posted_data['cat'] : '';
				$post_tag = isset($posted_data['tag'])? $posted_data['tag'] : '';
                
                $post_args = array(
				  'post_content'   => $post_description,
				  'post_excerpt'   => $post_excerpt,
				  'post_name' 	   => sanitize_title($post_title), //slug
				  'post_title'     => $post_title,
				  'post_status'    => $post_status,
				  'post_category'  => $post_cat,
				  'tags_input'	   => $post_tag,
				  'post_type'      => 'post'
				);
                
                $post_args = apply_filters('videopro_before_post_submission', $post_args, $posted_data);
                
				if($new_ID = wp_insert_post( $post_args, false )){
                    $post_format = osp_get('ct_video_settings','user_submit_format');
					set_post_format( $new_ID, $post_format );
                    
                    do_action('videopro_after_post_submission', $new_ID, $posted_data);
                }
            }
		}//if submission
	}
}
add_action("wpcf7_before_send_mail", "videopro_contactform7_hook");

function videopro_wpcf7_cactus_shortcode(){
	if(function_exists('wpcf7_add_shortcode')){
		wpcf7_add_shortcode(array('category','category*'), 'videopro_catdropdown', true);
        wpcf7_add_shortcode(array('channel','channel*'), 'videopro_channel_dropdown', true);
        wpcf7_add_shortcode(array('playlist','playlist*'), 'videopro_playlist_dropdown', true);
		wpcf7_add_shortcode(array('report_url','report_url*'), 'videopro_report_input', true);
	}
}
add_action( 'init', 'videopro_wpcf7_cactus_shortcode' );
//mail after publish
add_action( 'save_post', 'videopro_notify_user_submit');
function videopro_notify_user_submit( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || !osp_get('ct_video_settings','user_submit_notify') )
		return;
	$notified = get_post_meta($post_id,'notified',true);
	$email = get_post_meta($post_id,'tm_user_submit',true);
	if(!$notified && $email && is_string($email) && $email != '' && is_email($email) && get_post_status($post_id) == 'publish'){
		$subject = esc_html__('Your post submission has been approved','videopro');
        
        $subject = apply_filters('videopro_post_submission_user_notification_subject', $subject);

        $video_permalink = get_permalink($post_id);

		$message = sprintf(esc_html__('Congratulation! Your submission has been approved. You can see it here: %s','videopro'), $video_permalink);
        
        $message = apply_filters('videopro_post_submission_user_notification_message', $message, $video_permalink);
        
        $headers = apply_filters('videopro_post_submission_user_notification_headers', array('Content-type: text/html; charset=UTF-8'));
        
		wp_mail( $email, $subject, $message, $headers );
		update_post_meta( $post_id, 'notified', 1);
	}
}

function videopro_channel_dropdown($tag){
    $class = '';
	$is_required = 0;
	if(class_exists('WPCF7_Shortcode')){
		$tag = new WPCF7_Shortcode( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	}
    
    $args = array(
        'post_type' => 'ct_channel',
		'post__not_in'       => explode(",",osp_get('ct_video_settings','user_submit_channel_exclude'))
	); 
    
    $html = '';

	$query = new WP_Query($args);
    if($query->have_posts()){
        $html .= '<span class="wpcf7-form-control-wrap channel"><span class="row wpcf7-form-control wpcf7-checkbox wpcf7-validates-as-required'.$class.'">';
		if(osp_get('ct_video_settings','user_submit_channel_radio') == 'on'){
            while($query->have_posts()){
                $query->the_post();
                $html .= '<label class="col-md-4 wpcf7-list-item"><input type="radio" name="channel[]" value="'.get_the_ID().'" /> '.get_the_title().'</label>';
            }
		}else{
            while($query->have_posts()){
                $query->the_post();
                $html .= '<label class="col-md-4 wpcf7-list-item"><input type="checkbox" name="channel[]" value="'.get_the_ID().'" /> '.get_the_title().'</label>';
            }
		}
		$html .= '</span></span>';
        
        wp_reset_postdata();
    }
    
    $js_string = '';

    ob_start();
	if($is_required){
	?>
    <script>
	jQuery(document).ready(function(e) {
		jQuery("form.wpcf7-form").submit(function (e) {
			var checked = 0;
			jQuery.each(jQuery("input[name='channel[]']:checked"), function() {
				checked = jQuery(this).val();
			});
			if(checked == 0){
				if(jQuery('.channel-alert').length==0){
					jQuery('.wpcf7-form-control-wrap.channel').append('<span role="alert" class="wpcf7-not-valid-tip channel-alert"><?php esc_html_e('Please choose a channel','videopro') ?>.</span>');
				}
				return false;
			}else{
				return true;
			}
		});
	});
	</script>
	<?php
	}
	$js_string = ob_get_contents();
	ob_end_clean();
    
    return $html.$js_string;
}

function videopro_playlist_dropdown($tag){
    $class = '';
	$is_required = 0;
	if(class_exists('WPCF7_Shortcode')){
		$tag = new WPCF7_Shortcode( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	}
    
    $args = array(
        'post_type' => 'ct_playlist',
		'post__not_in'       => explode(",",osp_get('ct_video_settings','user_submit_playlist_exclude'))
	); 
    
    $html = '';

	$query = new WP_Query($args);
    if($query->have_posts()){
        $html .= '<span class="wpcf7-form-control-wrap playlist"><span class="row wpcf7-form-control wpcf7-checkbox wpcf7-validates-as-required'.$class.'">';
		if(osp_get('ct_video_settings','user_submit_playlist_radio') == 'on'){
            while($query->have_posts()){
                $query->the_post();
                $html .= '<label class="col-md-4 wpcf7-list-item"><input type="radio" name="playlist[]" value="'.get_the_ID().'" /> '.get_the_title().'</label>';
            }
		}else{
            while($query->have_posts()){
                $query->the_post();
                $html .= '<label class="col-md-4 wpcf7-list-item"><input type="checkbox" name="playlist[]" value="'.get_the_ID().'" /> '.get_the_title().'</label>';
            }
		}
		$html .= '</span></span>';
        
        wp_reset_postdata();
    }
    
    $js_string = '';

    ob_start();
	if($is_required){
	?>
    <script>
	jQuery(document).ready(function(e) {
		jQuery("form.wpcf7-form").submit(function (e) {
			var checked = 0;
			jQuery.each(jQuery("input[name='playlist[]']:checked"), function() {
				checked = jQuery(this).val();
			});
			if(checked == 0){
				if(jQuery('.playlist-alert').length==0){
					jQuery('.wpcf7-form-control-wrap.playlist').append('<span role="alert" class="wpcf7-not-valid-tip playlist-alert"><?php esc_html_e('Please choose a playlist','videopro') ?>.</span>');
				}
				return false;
			}else{
				return true;
			}
		});
	});
	</script>
	<?php
	}
	$js_string = ob_get_contents();
	ob_end_clean();
    
    return $html.$js_string;
}

function videopro_catdropdown($tag){
	$class = '';
	$is_required = 0;
	if(class_exists('WPCF7_Shortcode')){
		$tag = new WPCF7_Shortcode( $tag );
		if ( $tag->is_required() ){
			$is_required = 1;
			$class .= ' required-cat';
		}
	}
	$cargs = array(
		'hide_empty'    => false, 
		'exclude'       => explode(",",osp_get('ct_video_settings','user_submit_cat_exclude'))
	); 
	$cats = get_terms( 'category', $cargs );
	if($cats){
		$output = '<span class="wpcf7-form-control-wrap cat"><span class="row wpcf7-form-control wpcf7-checkbox wpcf7-validates-as-required'.$class.'">';
		if(osp_get('ct_video_settings','user_submit_cat_radio')=='on'){
			foreach ($cats as $acat){
				$output .= '<label class="col-md-4 wpcf7-list-item"><input type="radio" name="cat[]" value="'.$acat->term_id.'" /> '.$acat->name.'</label>';
			}
		}else{
			foreach ($cats as $acat){
				$output .= '<label class="col-md-4 wpcf7-list-item"><input type="checkbox" name="cat[]" value="'.$acat->term_id.'" /> '.$acat->name.'</label>';
			}
		}
		$output .= '</span></span>';
	}
	ob_start();
	if($is_required){
	?>
    <script>
	jQuery(document).ready(function(e) {
		jQuery("form.wpcf7-form").submit(function (e) {
			var checked = 0;
			jQuery.each(jQuery("input[name='cat[]']:checked"), function() {
				checked = jQuery(this).val();
			});
			if(checked == 0){
				if(jQuery('.cat-alert').length==0){
					jQuery('.wpcf7-form-control-wrap.cat').append('<span role="alert" class="wpcf7-not-valid-tip cat-alert"><?php esc_html_e('Please choose a category','videopro') ?>.</span>');
				}
				return false;
			}else{
				return true;
			}
		});
	});
	</script>
	<?php
	}
	$js_string = ob_get_contents();
	ob_end_clean();
	return $output.$js_string;
}

if(!function_exists('videopro_user_submit_video_form_html')) { 
	function videopro_user_submit_video_form_html() {
		if(osp_get('ct_video_settings','user_submit')=='1') {?>
        <div class="submitModal modal fade" id="videopro_submit_form">         
          <div class="modal-dialog">        	
            <div class="modal-content">              
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title" id="videopro_frontend_submit_heading"><?php esc_html_e('Submit Video','videopro'); ?></h4>
              </div>
              <div class="modal-body">
                <?php 
				if(is_active_sidebar('user_submit_sidebar')){
					dynamic_sidebar( 'user_submit_sidebar' );
				} else {
					echo esc_html__('Please go to Appearance > Sidebars and drag a widget into User Submit Sidebar. A Contact Form 7 is recommended!','videopro');
				}
				?>
              </div>
            </div>
          </div>
        </div>
    <?php } 
	}
}
add_action('videopro_before_end_body' , 'videopro_user_submit_video_form_html', 10);

if(!function_exists('videopro_user_mark_spam_form_html')){
	function videopro_user_mark_spam_form_html() {
		if(osp_get('ct_video_settings','spam_flag') != 'off') { ?>
        <div class="submitModal modal fade" id="submitReport">         
          <div class="modal-dialog">        	
            <div class="modal-content">              
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <h4 class="modal-title h4" id="myModalLabel"><?php esc_html_e('Report This','videopro'); ?></h4>
              </div>
              <div class="modal-body">
					<?php 
					$form_id = osp_get('ct_video_settings','spam_flag_contactform');
					if($form_id != ''){
						echo do_shortcode('[contact-form-7 id="' . $form_id . '"]');
					} else {
						$form_id = osp_get('ct_video_settings','spam_flag_gravityform');
						if($form_id != ''){
							echo do_shortcode('[gravityform id="' . $form_id . '"]');
						}  else {
							echo esc_html__('Please specify an ID for the Contact Form in Video Settings > Video Post > Spam Flag-Contact Form 7 ID or Spam Flag-Gravity Form ID','videopro');
						}
					}
					?>
              </div>
            </div>
          </div>
        </div>
		
    <?php }
	}
}
add_action('videopro_before_end_body' , 'videopro_user_mark_spam_form_html', 10);

if(!function_exists('videopro_user_submit_video_button_html')) { 
	function videopro_user_submit_video_button_html() {
		if(osp_get('ct_video_settings','user_submit')==1) {
			$text_bt_submit = osp_get('ct_video_settings','text_bt_submit');
			$bg_bt_submit = osp_get('ct_video_settings','bg_bt_submit');
			$color_bt_submit = osp_get('ct_video_settings','color_bt_submit');
			$bg_hover_bt_submit = osp_get('ct_video_settings','bg_hover_bt_submit');
			$color_hover_bt_submit = osp_get('ct_video_settings','color_hover_bt_submit');
			$limit_tags = osp_get('ct_video_settings','user_submit_limit_tag');
			
			if($text_bt_submit == '') { 
				$text_bt_submit = esc_html__('Submit Video','videopro');
			}

			if( osp_get('ct_video_settings','only_user_submit') == '1'){
				if(is_user_logged_in()){ 
					echo do_shortcode("[v_submit_button bg='".$bg_bt_submit."' color='".$color_bt_submit."' bg_hover='".$bg_hover_bt_submit."' color_hover='".$color_hover_bt_submit."' tags='".$limit_tags."']".$text_bt_submit."[/v_submit_button]");
				}
			} else {
				echo do_shortcode("[v_submit_button bg='".$bg_bt_submit."' color='".$color_bt_submit."' bg_hover='".$bg_hover_bt_submit."' color_hover='".$color_hover_bt_submit."' tags='".$limit_tags."']".$text_bt_submit."[/v_submit_button]");
			}
		}
	}
}
add_action('videopro_button_user_submit_video' , 'videopro_user_submit_video_button_html');

if(!function_exists('videopro_query_morevideo')){
	function videopro_query_morevideo($id_curr, $query_by, $post_format, $number, $video_load=false, $date= false){
		$args = array(
		  'posts_per_page' => $number,
		  'post_type' => 'post',
		  'ignore_sticky_posts' => 1,
		  'post_status' => 'publish',
		  'orderby' => 'date',
		  'order' => 'ASC',
		  'post__not_in' => array($id_curr),
		);
		$taxo = array();
		if($query_by=='cat'){
		   $categories = get_the_category();
		   if($categories=='' || (is_array($categories) && empty($categories))){ return;}
		   $category_id = $categories[0]->cat_ID;
		   foreach($categories as $cat_item){
			   $cats[] = $cat_item->cat_ID;
		   }
		   $args['category__in'] = $cats;
		}else if($query_by=='tag'){
		    $cr_tags = get_the_tags();
		    if($cr_tags=='' || (is_array($cr_tags) && empty($cr_tags))){ return;}
			if ($cr_tags) {
				foreach($cr_tags as $tag) {
					$tag_item .= ',' . $tag->slug;
				}
			}
			$tag_item = substr($tag_item, 1);
			$args['tag'] = $tag_item;
		}else if($query_by=='tax'){
			$morevideo_tax ='';
			if(function_exists('osp_get')){
				$morevideo_tax = osp_get('ct_video_settings','morevideo_tax');
			}
			if($morevideo_tax!=''){
				$post_tax = get_the_terms( $id_curr, $morevideo_tax);
				$tax = array();
				if ($post_tax) {
					foreach($post_tax as $cat) {
						$cats = $cat->slug; 
						array_push($tax,$cats);
					}
				}
				$taxo['relation'] =  'OR';
				if(count($tax)>1){
					$tax_tag = array(
						'relation' => 'OR',
					);
					foreach($tax as $iterm) {
						$tax_tag[] = 
							array(
								'taxonomy' => $morevideo_tax,
								'field' => 'slug',
								'terms' => $iterm,
							);
					}
					$taxo = array($tax_tag);
				}else{
					$taxo = array(
						array(
								'taxonomy' => $morevideo_tax,
								'field' => 'slug',
								'terms' => $tax,
							)
					);
				}
			}
		}else if($query_by=='series'){
			$post_tax = get_the_terms( $id_curr, 'video-series');
			$tax = array();
			if ($post_tax) {
				foreach($post_tax as $cat) {
					$cats = $cat->slug; 
					array_push($tax,$cats);
				}
			}
			$taxo['relation'] =  'OR';
			if(count($tax)>1){
				$tax_tag = array(
					'relation' => 'OR',
				);
				foreach($tax as $iterm) {
					$tax_tag[] = 
						array(
							'taxonomy' => 'video-series',
							'field' => 'slug',
							'terms' => $iterm,
						);
				}
				$taxo = array($tax_tag);
			}else{
				$taxo = array(
					array(
							'taxonomy' => 'video-series',
							'field' => 'slug',
							'terms' => $tax,
						)
				);
			}
		}elseif($query_by=='current-series'){
			$taxo =  array(
			array(
					'taxonomy' => 'video-series',
					'field'    => 'slug',
					'terms'    => $_GET['series'],
				),
			);
		}else{
			$id_pl = get_post_meta($id_curr,'playlist_id',true);
			if($id_pl=='' || (is_array($id_pl) && empty($id_pl))){ return;}
			if(is_array($id_pl) && !empty($id_pl)){
				$id_pl = $id_pl[0];
			}
			$args['meta_query'] = array(
				array(
				'key' => 'playlist_id',
				'value' => $id_pl,
				'compare' => 'LIKE',
			   )
			);
		}
		if($post_format!='off'){
			if(empty($taxo)){
				$taxo[] = array(
			        'taxonomy' => 'post_format',
			        'field'    => 'slug',
			        'terms'    => array( 'post-format-video' ),
                );
			}else{
				$taxo['relation'] =  'AND';
				$taxo[] = array(
					'taxonomy' => 'post_format',
			        'field'    => 'slug',
			        'terms'    => array( 'post-format-video' ),
				);
			}
		}
		$args['tax_query'] = $taxo;
		if(isset($video_load) && isset($date)){
			if($video_load=='prev'){
				$args['date_query']['after'] = $date;
			}elseif($video_load=='next'){
				$args['order']= 'DESC';
				$args['date_query']['after'] = '';
				$args['date_query']['before'] = $date;
			}else{
				$args['order']= 'ASC';
			}
		}
		if($query_by=='series'){
			$args['meta_key']= 'order_series';
			$args['orderby']= 'meta_value_num';
			$args['order']= 'ASC';
		}
		if($query_by=='current-series'){
			$args['date_query'] ='';
			if($video_load=='prev'){
				$args['meta_query']= array(
					array(
						'key'     => 'order_series',
						'value'   => get_post_meta($id_curr,'order_series',true),
						'type'    => 'numeric',
						'compare' => '>',
					),
				);
				$args['orderby']= 'meta_value_num';
				$args['order']= 'ASC';
			}else{
				$args['meta_query']= array(
					array(
						'key'     => 'order_series',
						'value'   => get_post_meta($id_curr,'order_series',true),
						'type'    => 'numeric',
						'compare' => '<',
					),
				);
				$args['meta_key'] = 'order_series';
				
				$args['orderby']= 'meta_value_num';
				$args['order']= 'DESC';
			}
			if($video_load=='first'){
				$args['meta_key'] = 'order_series';
				$args['orderby']= 'meta_value_num';
				$args['order']= 'ASC';
			}
		}
		$ct_query_more = get_posts($args);
		return $ct_query_more;
	}
}

if(!function_exists('videopro_toolbar_html')){
	function videopro_toolbar_html($html, $post_id, $post_format){
		if($post_format != 'video') return $html;
		
		$id_curr = $post_id;
		
		$show_more = 'on';
		$show_like = 'off';
		$show_dislike = 'off';
		$show_sharing = 'on';
		$show_facebook = 'on';
		$show_google = 'on';
		$show_flag = 'on';

		if(function_exists('osp_get')){
			$show_more = osp_get('ct_video_settings','show_morevideo');
			$show_like = osp_get('ct_video_settings','videotoolbar_show_like_button');
			$show_sharing = osp_get('ct_video_settings','videotoolbar_show_sharing_button');
			$show_facebook = osp_get('ct_video_settings','videotoolbar_show_fblike_button');
			$show_google = osp_get('ct_video_settings','videotoolbar_show_google_button');
			$show_flag = osp_get('ct_video_settings','spam_flag');
		}
		
		ob_start();

		$show_share_button_social = ot_get_option('show_share_button_social');?>
        <div class="video-toolbar dark-div dark-bg-color-1">
            <div class="video-toolbar-content">
                <div class="toolbar-left">
                    <?php
				if($show_like == 'on') {
					if(function_exists('GetWtiLikePost')){
						$like = GetWtiLikeCount(get_the_ID());
						$unlike = GetWtiUnlikeCount(get_the_ID());
						$like = $re_like = str_replace('+','',$like);
						$unlike = $re_unlike = str_replace('-','',$unlike);
						$sum = $re_like + $re_unlike;
						$class_li = '';
						$is_logged_in = is_user_logged_in();
						$login_required = get_option('wti_like_post_login_required');
						if ($login_required && !$is_logged_in) {
							$class_li = 'login-to-vote';
						}
						?>
						<div class="share-tool-block like-button _check-like-id-<?php the_ID();?> <?php echo esc_attr($class_li);?>" data-like="<?php esc_html_e('like','videopro');?>" data-unlike="<?php esc_html_e('dislike','videopro');?>">
							<?php GetWtiLikePost();
							if($class_li!=''){
								$login_message = get_option('wti_like_post_login_message');
								?>
                                <div class="login-msg">
                                	<div class="login-content">
                                        <span class="login-info">
                                            <?php if($login_message!=''){ 
                                                echo esc_attr($login_message);
                                            }else{ 
                                                esc_html_e('Please Signin','videopro');
                                            }?>
                                        </span>
                                        <a href="<?php echo wp_login_url(get_permalink());?>" class="login-link button btn btn-default bt-style-1"><?php esc_html_e('Sign In','videopro');?></a></span>
                                	</div>
                                </div>
                                <?php 
							}
							?>
						</div>
						<?php
						if($sum != 0 && $sum != ''){
							$fill_cl = (($re_like/$sum)*100);
						} else
						if($sum == 0){
							$fill_cl = 50;
						}
						
						$msg = GetWtiVotedMessage(get_the_ID());
						
						$ip = WtiGetRealIpAddress();
						
						$tm_vote = videopro_AlreadyVoted(get_the_ID(), $ip);
						
						// get setting data
						$color_active = (ot_get_option('main_color', '#d9251d')!='#d9251d')?ot_get_option('main_color', '#d9251d'):'#d9251d';										
						$mes= '<style scoped>.action-like a span{ background-color: '.$color_active.' !important; color:#FFFFFF !important;}</style>';
						$mes_un= '<style scoped>.action-unlike a span{ background-color: '.$color_active.' !important; color:#FFFFFF !important;}</style>';
						if ($login_required && !$is_logged_in) {
							//echo $mes;
							//echo $mes_un;
						} else {
							$has_already_voted = HasWtiAlreadyVoted(get_the_ID(), $ip);
							$voting_period = get_option('wti_like_post_voting_period');
							$datetime_now = date('Y-m-d H:i:s');
							if ("once" == $voting_period && $has_already_voted) {
								// user can vote only once and has already voted.
								if($tm_vote>0){echo $mes;}
								else if ($tm_vote<0){echo $mes_un;}
							} elseif (0 == $voting_period) {
								if($tm_vote>0){echo $mes;}
								else if ($tm_vote<0){echo $mes_un;}
							} else {
								if (!$has_already_voted) {
									// never voted befor so can vote
								} else {
									// get the last date when the user had voted
									$last_voted_date = GetWtiLastVotedDate(get_the_ID(), $ip);
									// get the bext voted date when user can vote
									$next_vote_date = GetWtiNextVoteDate($last_voted_date, $voting_period);
									if ($next_vote_date > $datetime_now) {
										$revote_duration = (strtotime($next_vote_date) - strtotime($datetime_now)) / (3600 * 24);
			
										if($tm_vote>0){echo $mes;}
										else if ($tm_vote<0){echo $mes_un;}
									}
								}
							}
						}
					}
				}
					?>

                <?php if($show_sharing!='off'){?>
                    <a href="#" class="btn btn-default video-tb icon-only font-size-1 open-share-toolbar"><i class="fa fa-share-alt"></i></a>
                <?php }?>
                
                <?php if(osp_get('ct_video_settings', 'videotoolbar_show_watch_later_button') == 'on'){?>
                <a href="#" title="<?php echo esc_html('Watch Later', 'videopro');?>" class="btn btn-default video-tb icon-only font-size-1 btn-watch-later" data-id="<?php echo $post_id;?>"><i class="fa fa-clock-o"></i></a>
                <?php }?>
				
				<?php if($show_facebook != 'off' || $show_google != 'off'){?>
                    <div class="like-group">
						<?php if($show_facebook != 'off'){?>
                        <div class="facebook-group">
                            <iframe src="//www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post_id)); ?>&amp;width=450&amp;height=21&amp;colorscheme=light&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;send=false&amp;appId=498927376861973" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:85px; height:21px;" allowTransparency="true"></iframe>
                        </div>
						<?php }?>
						<?php if($show_google != 'off'){?>
                        <div class="google-group">
                            <div class="g-plusone" data-size="medium"></div>
							<script type="text/javascript">
                              window.___gcfg = {lang: 'en-GB'};
                              (function() {
                                var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                                po.src = 'https://apis.google.com/js/plusone.js';
                                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                              })();
                            </script>
                        </div>
						<?php }?>
                    </div>
				<?php }?>
                <?php if($show_flag != 'off') {
                        if($show_flag == 'on2'){
                            if(is_user_logged_in()){?>
                            <a href="javascript:;" class="btn btn-default video-tb icon-only font-size-1" id="open-report"><i class="fa fa-flag"></i></a>
                            <?php
                            }
                        } else {
                        ?>
                    <a href="javascript:;" class="btn btn-default video-tb icon-only font-size-1" id="open-report"><i class="fa fa-flag"></i></a>
                <?php }
                    }
                    ?>

                </div>
                <div class="toolbar-right">
                	<?php 
					$auto_load = osp_get('ct_video_settings','auto_load_next_video');
					$next_previous_same = osp_get('ct_video_settings','next_prev_same');
					$next_video_only = osp_get('ct_video_settings','next_video_only');
					if($next_previous_same==''){
						$next_previous_same ='cat';
					}
					if(isset($_GET['series']) && $_GET['series']!=''){$next_previous_same ='current-series';}
					$date_st = get_the_time('m/d/Y '.get_option('time_format'),get_the_ID());
					$p_query = videopro_query_morevideo($id_curr, $next_previous_same, $next_video_only, 1,'prev', $date_st);
					$p =array();
					if(!empty($p_query)){
						foreach ( $p_query as $key => $post ) : setup_postdata( $post );
						   $p = $post;break;
						endforeach;
						wp_reset_postdata();
					}
					$n_query = videopro_query_morevideo($id_curr, $next_previous_same, $next_video_only, 1,'next', $date_st);
					$n =array();
					if(!empty($n_query)){
						foreach ( $n_query as $key => $post ) : setup_postdata( $post );
						   $n = $post;break;
						endforeach;
						wp_reset_postdata();
					}
					if(empty($p) && $auto_load=='2'){						
						$f_query = videopro_query_morevideo($id_curr, $next_previous_same, $next_video_only, 1,'first');
						if(!empty($f_query)){
							foreach ( $f_query as $key => $post ) : setup_postdata( $post );
							   if($post->ID == $n->ID){
								   $n =array();
							   }
							   $p = $post;
							   break;
							endforeach;
							wp_reset_postdata();
						}
					}
					if(!empty($p)){
                        $pv_link = get_permalink($p->ID);
                        
						if(isset($_GET['series']) && $_GET['series']!=''){
							$pv_link = add_query_arg( array('series' => $_GET['series']), $pv_link);
						}
						?>
						<a href="<?php  echo esc_url($pv_link);?>" class="btn btn-default video-tb font-size-1 cactus-new"><i class="fa fa-chevron-left"></i><span><?php echo esc_html__( 'PREV VIDEO', 'videopro' )?></span></a>
					<?php 
					}
					if(!empty($n)){
						$nv_link = get_permalink($n->ID); 
						if(isset($_GET['series']) && $_GET['series']!=''){
							$nv_link = add_query_arg( array('series' => $_GET['series']), $nv_link);
						}
						?>
                    	<a href="<?php echo esc_url($nv_link); ?>" class="btn btn-default video-tb font-size-1 cactus-old"><span><?php echo esc_html__( 'NEXT VIDEO', 'videopro' )?></span><i class="fa fa-chevron-right"></i></a>
					<?php }
					$number_of_more = 10;
					$sort_of_more ='';
					if(function_exists('osp_get')){
						$sort_of_more = osp_get('ct_video_settings','morevideo_by');
					}
					$ct_query_more = videopro_query_morevideo($id_curr, $sort_of_more, 'video', $number_of_more);
					if($show_more != 'off' && !empty($ct_query_more)){?>
                    	<a href="#" class="btn btn-default video-tb font-size-1 open-carousel-post-list"><span><?php esc_html_e('MORE VIDEOS','videopro');?></span><i class="fa fa-caret-down"></i></a>
                    <?php }?>
                </div>
            </div>
            <?php if($show_sharing != 'off'){?>
            <!--Social Share-->
            <div class="social-share-tool-bar-group dark-bg-color-1 dark-div">
                <div class="group-social-content">
                    <?php videopro_print_social_share();?>
                </div>

            </div><!--Social Share-->
            <?php }?>
			<?php
            if($show_more!='off' && !empty($ct_query_more)){
				$post_video_layout = videopro_global_video_layout();
				$layout = videopro_global_layout();
				$sidebar = videopro_global_video_sidebar();
				if($layout=='' || $layout=='fullwidth'){
					if($post_video_layout=='1'){
						$img_size = array(270,152);
					}else{
						$img_size = array(251,141);
					}
				}elseif($sidebar=='full'){
					if($post_video_layout=='1'){
						$img_size = array(270,152);
					}else{
						$img_size = array(320,180);
					}
				}else{
					if($layout!='wide'){
						$img_size = array(270,152);
					}else{
						$img_size = array(205,115);
					}
				}

                ?>            
                <div class="slider-toolbar-group dark-bg-color-1 dark-div">
                    <div class="slider-toolbar">
                        <!---->
                        
                        <div class="prev-slide"><i class="fa fa-angle-left"></i></div> 
                        <div class="next-slide"><i class="fa fa-angle-right"></i></div>    
                        
                        <div class="slider-toolbar-carousel">
                            <div class="cactus-listing-wrap">
                                <div class="cactus-listing-config style-2"> <!--addClass: style-1 + (style-2 -> style-n)-->
                                    <div class="cactus-sub-wrap">                        
                                        
                                        <!--item listing-->                                                
                                        <article class="cactus-post-item hentry active">
                                        
                                            <div class="entry-content">                                        
                                                
                                                <!--picture (remove)-->
                                                <div class="picture">
                                                    <div class="picture-content">
                                                        <a href="<?php echo esc_url(get_permalink($id_curr)); ?>" title="">
                                                            <?php if(has_post_thumbnail($id_curr)){
                                                                echo videopro_thumbnail($img_size,$id_curr);
                                                            }?>
                                                            <h3 class="cactus-post-title entry-title h5"> 
                                                                <?php echo esc_attr(get_the_title($id_curr)); ?> 
                                                            </h3>
                                                            <?php if(get_post_format($id_curr)=='video'){?>
                                                            <div class="ct-icon-video"></div>                   
                                                            <?php }?>                                       
                                                        </a>                                               
                                                    </div>                              
                                                </div><!--picture-->
                                            </div>
                                            
                                        </article><!--item listing-->
                                        <?php
                                        foreach ( $ct_query_more as $key_more => $post ) :
                                        ?>
                                        <!--item listing-->                                                
                                        <article class="cactus-post-item hentry">
                                        
                                            <div class="entry-content">                                        
                                                
                                                <!--picture (remove)-->
                                                <div class="picture">
                                                    <div class="picture-content">
                                                        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" title="">
                                                            <?php if(has_post_thumbnail($post->ID)){
																 echo videopro_thumbnail($img_size,$post->ID);
                                                            }?>  
                                                            <h3 class="cactus-post-title entry-title h5"> 
                                                                <?php echo esc_attr(get_the_title($post->ID)); ?> 
                                                            </h3>                                                                                             
                                                            <?php if(get_post_format($id_curr)=='video'){?>
                                                            <div class="ct-icon-video"></div>                   
                                                            <?php }?>                      
                                                        </a>
                                                    </div>                              
                                                </div><!--picture-->
                                            </div>
                                            
                                        </article><!--item listing-->
                                        <?php endforeach;?>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <!---->
                    </div>                                                        
                </div>
            <?php
            wp_reset_postdata();
            }?>
            </div>
            <?php
		
		$html = ob_get_clean();
		return $html;
	}
}
add_filter('videopro_post_toolbar' , 'videopro_toolbar_html', 10, 3);

if(!function_exists('videopro_author_video_html')) { 
	function videopro_author_video_html() {
		$id = get_the_ID();
		$post_data = videopro_get_post_viewlikeduration($id);
		extract($post_data);
		
		$use_network_data = osp_get('ct_video_settings', 'use_video_network_data') == 'on' ? 1 : 0;
		
		$isWTIinstalled = $use_network_data ? 1 : (function_exists('GetWtiLikeCount') ? 1 : 0);
		$isTop10PluginInstalled = $use_network_data ? 1 : (is_plugin_active('top-10/top-10.php') ? 1 : 0);

		$video_sub_author = null;// ver 1.5 osp_get('ct_video_settings','video_sub_author');
        $show_author = ot_get_option('show_author_single_post','on');
        
        if($show_author != 'off' || ($isWTIinstalled || $isTop10PluginInstalled)){
		?>
        <div class="post-metadata sp-style <?php if($video_sub_author == null || $video_sub_author == 'off'){ echo 'style-2';}?>">
            <div class="left">
                <?php
                
                if($show_author != 'off'){?>
                <div class="channel-subscribe">
                    <div class="channel-picture">
                        <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) );?>">
                            <?php echo get_avatar( get_the_author_meta('email'), 110 ); ?>
                        </a>
                    </div>
                    <div class="channel-content">
                        <h4 class="channel-title h6">
                            <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) );?>"><?php echo esc_html( get_the_author() );?></a>
                        </h4>
                        <?php 
                        if($video_sub_author == null || $video_sub_author == 'off'){
                            echo videopro_numbervideo_byauthor();
                        } elseif($video_sub_author == 'on') {
                            echo videopro_addto_subscribe();
                        }?>
                    </div>
                </div>
                <?php }?>
            </div>
            
			<?php if($isWTIinstalled || $isTop10PluginInstalled){?>
            <div class="right">
				<?php if($isWTIinstalled) {?>
                <div class="like-information">
                    <i class="fa fa-thumbs-up"></i>
                    <span class="heading-font">
                        <?php if($like + $unlike == 0){ echo 0;} else { echo round($like/($like + $unlike) * 100,1);}?>%
                    </span>
                </div>
				<?php }?>
                <div class="posted-on metadata-font"> 
					<?php if($isTop10PluginInstalled) {?>
                    <div class="view cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Views','videopro'), videopro_get_formatted_string_number($viewed));?></span></div>
					<?php }?>
					<?php if($isWTIinstalled) {?>
                    <div class="cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Likes','videopro'), videopro_get_formatted_string_number($like)); ?></span></div>
					<?php }?>
                </div>
            </div>
            <?php }?>

        </div>

    <?php 
        }
	}
}
add_action('videopro_author_video' , 'videopro_author_video_html',10, 3);

add_filter('videopro_singlevideo_left_meta' , 'videopro_singlevideo_left_meta_html', 10, 3);
if(!function_exists('videopro_singlevideo_left_meta_html')){
	function videopro_singlevideo_left_meta_html($html, $post_format, $viewed){
		if($post_format != 'video') return $html;
		
		$html = '';
		
		$video_screenshots = osp_get('ct_video_settings','video_screenshots');

		if($video_screenshots != ''){
			
			global $post;
			
			$thumbnail_id = get_post_thumbnail_id($post->ID);
			$images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => 999, 'exclude' => $thumbnail_id ) );

			if(count($images) == 0){
				$show_right_meta = true;
			}
		}
		
		$download_link = get_post_meta($post->ID, 'video_download_url', true);
		if($download_link){
			$show_right_meta = false;
		}
		
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
				<?php }if(ot_get_option('show_author_single_post','on')!='off'){?>
				<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) );?>" class="author cactus-info font-size-1"><span><?php echo sprintf(esc_html__('By %s', 'videopro'), get_the_author());?></span></a>
				<?php }?>                                         
			</div>
			<?php if (!$show_right_meta){?>
			<div class="posted-on metadata-font right">
				<a href="<?php echo get_comments_link($post->ID); ?>" class="comment cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Comments','videopro'), number_format_i18n(get_comments_number($post->ID))); ?></span></a>
			</div>
			<?php }?>
		</div>
		<?php
		if($show_right_meta){
		?>
		<div class="right">
			<div class="posted-on metadata-font right">
				<a href="<?php echo get_comments_link($post->ID); ?>" class="comment cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Comments','videopro'), number_format_i18n(get_comments_number($post->ID))); ?></span></a>
			</div>
		</div>
			<?php
		}
		
		$html .= ob_get_clean();
		
		return $html;
	}
}

add_filter('videopro_singlevideo_right_meta' , 'videopro_singlevideo_right_meta_html', 10, 2);
if(!function_exists('videopro_singlevideo_right_meta_html')) { 
	function videopro_singlevideo_right_meta_html($html, $post_format) {
		if($post_format != 'video') return $html;

		$html = '';

		ob_start();
		
		?>
		<div class="right">
			<?php 
			
			$download_link = get_post_meta(get_the_ID(), 'video_download_url', true);
			$download_button = get_post_meta(get_the_ID(), 'video_download_button', true);
			if($download_link){?>
				<a href="<?php echo $download_link;?>" target="_blank" class="btn btn-default ct-gradient bt-action metadata-font font-size-1"><span><?php echo $download_button ? $download_button : wp_kses(__('<i class="fa fa-cloud-download"></i> Download Video', 'videopro'),array('i'=>array('class'=>array())));?></span></a>
			<?php
			}
			
			$video_screenshots = osp_get('ct_video_settings','video_screenshots');  // '' to disable, 'simple' for a Simple List layout, 'lightbox' for Lightbox Gallery layout
			
			$screenListing = array(); 
			$screenSimple = ''; 
			
			if($video_screenshots != ''){
				
				global $post;
				
				$thumbnail_id = get_post_thumbnail_id($post->ID);
				$images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => 999, 'exclude' => $thumbnail_id, 'order' => 'ASC', 'orderby' => 'ID') );
				
				if(count($images) > 0){
				?>
					<a href="javascript:void(0)" id="video-screenshots-button" class="btn btn-default ct-gradient bt-action metadata-font font-size-1">
						<i class="fa fa-file-image-o"></i><span><?php echo esc_html__('Screenshots','videopro');?></span>
					</a>
				<?php 
					
					foreach((array)$images as $attachment_id => $attachment){
						$defaultIMGsrc = wp_get_attachment_image_url( $attachment_id, array(277, 156));
						$imgSimpleGrid = 	'<img 
												src="'.$defaultIMGsrc.'"
												srcset="'.wp_get_attachment_image_srcset( $attachment_id, array(277, 156) ).'"
												sizes="'.wp_get_attachment_image_sizes( $attachment_id, array(277, 156) ).'"
												alt="'.esc_attr(get_the_title($attachment_id)).'"												
											 />';
						$imgLightBox = array($defaultIMGsrc, wp_get_attachment_image_url($attachment_id, 'full'));					 
						array_push(
							$screenListing, 
							$imgLightBox
						);//json data
						$screenSimple.='<div class="screenshot">'.$imgSimpleGrid.'</div>'; //html data						  
					}
				}
			}
			?>
		</div>
		<?php if($video_screenshots == 'simple'){?>
			<div class="clearer"><!-- --></div>
			<div id="video-screenshots" style="display:none">
				<?php echo $screenSimple;?>
			</div>
		<?php } else{ ?>
			<script>
				<?php echo 'var json_listing_img='.json_encode($screenListing);?>
			</script>
		<?php }
		
		$html .= ob_get_clean();
		

		
		return $html;
	}
}

function videopro_show_about_the_author_hook($status){
	$status = osp_get('ct_video_settings','video_hide_about_author') ? osp_get('ct_video_settings','video_hide_about_author') :'off';
	return $status;
}
add_filter('videopro_show_about_the_author' , 'videopro_show_about_the_author_hook',11,1);

add_filter('videopro_auto_next_video' , 'videopro_auto_next_video_html',11,1);
function videopro_auto_next_video_html($auto_next_html){
	$url = trim(get_post_meta(get_the_ID(), 'tm_video_url', true));
	$auto_next_html ='';
	$user_control_next_video = osp_get('ct_video_settings','user_control_next_video');
	$auto_load_next_video = osp_get('ct_video_settings','auto_load_next_video');
	$at_class ='';
    $format = get_post_format(get_the_ID());
    $single_video = false;
    if(is_single()){
        if($format == 'video'){
            $single_video = true;
        }
    }
	if($single_video && $user_control_next_video=='1' && ((strpos($url, 'youtube.com') !== false) || (strpos($url, 'vimeo.com') !== false ) || (strpos($url, 'dailymotion.com') !== false))){
		$class_at = '';
		if($auto_load_next_video!='4'){ 
			$class_at='active';
			$auto_next_html = '
			<div class="autoplay-group">
				<div class="auto-text">'.esc_html__('AUTO NEXT','videopro').'</div>
				<div class="autoplay-elms '.$class_at.'">
					<div class="oval-button"></div>
				</div>
			</div>';
		}
	}
	return $auto_next_html;
}
//add_filter('videopro_get_related_posts' , 'videopro_get_related_posts_video',11,1);
function videopro_get_related_posts_video($arr){
	return $arr;
}

add_filter('videopro_loop_item_icon', 'videopro_video_lightbox_html', 10, 5);

if(!function_exists('videopro_video_lightbox_html')){
	/**
	 * $html - string - HTML to filter
	 * $id - int - Post ID
	 * $format - string - Post Format
	 * $class - string - extra CSS class
	 */
	function videopro_video_lightbox_html($html, $id, $format, $lightbox, $class) {
		if($format != 'video') return $html;
		
		ob_start();

		if(!isset($lightbox) || $lightbox == '1'){
			$enable_archives_lightbox = osp_get('ct_video_settings','enable_archives_lightbox');
		} else {
			$enable_archives_lightbox = $lightbox;
		}
		
		if($enable_archives_lightbox == '1'){?>
			<div class="ct-icon-video lightbox_item<?php if(isset($class) && $class!=''){ echo ' '.esc_attr($class);}?>" data-source="" data-type="iframe-video" data-caption="<?php the_title_attribute(); ?>" data-id="<?php echo esc_attr($id);?>">
				<?php
					$strIframeVideo='';
					ob_start();
						echo tm_video($id, true);
						$strIframeVideo = ob_get_contents();
					ob_end_clean();					
					
					$jsonIframeVideo = array($strIframeVideo);
					echo '<script>if(typeof(video_iframe_params) == "undefined") video_iframe_params = []; video_iframe_params['.$id.'] = ' . json_encode($jsonIframeVideo) . ';</script>';
				?>
			</div>
			<?php
		} else {
			echo $html; // return what it was used
		}


		$html = ob_get_clean();
		return $html;
	}
}

add_action('videopro_before_video_content' , 'videopro_build_multi_link_html');
if(!function_exists('videopro_build_multi_link_html')){
	function videopro_build_multi_link_html() {
		$multi_link = get_post_meta(get_the_ID(), 'tm_multi_link', true);
		if(!empty($multi_link)&& function_exists('videopro_build_multi_link')){
			videopro_build_multi_link($multi_link, true);
		}
	}
}
add_action('videopro_video_series' , 'videopro_build_series_html',99);
if(!function_exists('videopro_build_series_html')){
	function videopro_build_series_html() {
		$series = wp_get_post_terms(get_the_ID(), 'video-series', array("fields" => "all"));
		if(!empty($series)&& class_exists('videopro_series')){
			$sidebar = videopro_global_video_sidebar();
			$layout = videopro_global_layout();
            $video_series = videopro_series::getInstance();
            ?>
            <div class="style-post">
                <div class="cactus-post-format-video-wrapper <?php if(($layout=='boxed' || $layout=='wide') && ($sidebar !='right' || $sidebar !='both')){ echo 'style-small';}?>">
					<?php $video_series->get_post_series(); ?>
                </div>
            </div>
            <?php
		}
	}
}

function videopro_print_header_thumbnail_image($video_id){
    $external_link = get_post_meta($video_id, 'external_url', true);
                        
    if($external_link == '') $external_link = '#';
    ?>
    <div id="video_thumbnail_image">
        <?php echo videopro_thumbnail('full', $video_id); ?>
        <a href="<?php echo $external_link == '#' ? '#' : esc_url($external_link);?>" class="link" data-id="<?php if($external_link == '#') echo $video_id;?>" <?php if($external_link != '#') {?> target="<?php echo apply_filters('videopro_external_link_target', '_blank');?>" <?php }?>>
            <div class="ct-icon-video"><!-- --></div>
        </a>
        
        <div class="overlay"><!-- --></div>
        
        <div class="post-meta">
            <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) );?>" class="author"><?php echo esc_html( get_the_author() );?></a>
            <div class="heading h1"><?php the_title();?></div>
            <div class="meta-1 categories cactus-info font-size-1">
                <?php if(ot_get_option('show_cat_single_post','on') != 'off'){?>
                    <?php echo videopro_show_cat();?>
                <?php }?>
                <?php if(ot_get_option('single_post_date','on') != 'off'){?>
                    <?php echo videopro_get_datetime(); ?>
                <?php }?>
            </div>
            <div class="meta-2">
                <?php
                    $post_data = videopro_get_post_viewlikeduration($video_id);
                    extract($post_data);
                    
                    $use_network_data = osp_get('ct_video_settings', 'use_video_network_data') == 'on' ? 1 : 0;
                    
                    $isWTIinstalled = $use_network_data ? 1 : (function_exists('GetWtiLikeCount') ? 1 : 0);
                    $isTop10PluginInstalled = $use_network_data ? 1 : (is_plugin_active('top-10/top-10.php') ? 1 : 0);
                ?>
                <div class="posted-on metadata-font"> 
                    <?php if($isTop10PluginInstalled) {?>
                    <div class="view cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Views','videopro'), videopro_get_formatted_string_number($viewed));?></span></div>
                    <?php }?>
                    <?php if($isWTIinstalled) {?>
                    <div class="cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Likes','videopro'), videopro_get_formatted_string_number($like)); ?></span></div>
                    <?php }?>
                    <a href="<?php echo get_comments_link($video_id); ?>" class="comment cactus-info font-size-1"><span><?php echo sprintf(esc_html__('%s Comments','videopro'), number_format_i18n(get_comments_number($video_id))); ?></span></a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

add_filter('videopro_content_video_header', 'videopro_content_video_header_filter',10, 3);
if(!function_exists('videopro_content_video_header_filter')){
    /**
     * $player_only == 1 - Video Player/Header is in body
     */
	function videopro_content_video_header_filter($html, $post_video_layout, $player_only){
		ob_start();
		$video_id = get_the_ID();
        
        $video_header = get_post_meta($video_id, 'video_player', true);
                    
        if($video_header == ''){
            $video_header = osp_get('ct_video_settings', 'video_header');
        }
                    
		if($player_only){
            if($video_header == 2){
                ?>
                <div class="dark-div">
                <?php
                    videopro_print_header_thumbnail_image($video_id);
                ?>
                </div>
                <?php
            } else {
?>
			<div class="cactus-post-format-video<?php if(osp_get('ct_video_settings','video_floating')=='on'){echo ' floating-video '.osp_get('ct_video_settings','video_floating_position');}?>">
				<div class="cactus-video-content-api cactus-video-content">
                	<span class="close-video-floating"><i class="fa fa-times" aria-hidden="true"></i></span>
					<?php echo do_shortcode('[cactus_player]');?>
				</div>
			</div>
<?php
            }
		} else {
			$video_appearance_bg = get_post_meta($video_id,'video_appearance_bg',true);
			if(!is_array($video_appearance_bg)){ $video_appearance_bg = array();}
			$video_bg_op = osp_get('ct_video_settings','video_appearance_bg');
			if((!isset($video_appearance_bg['background-image'])  || $video_appearance_bg['background-image'] == '')){
				if((isset($video_bg_op['background-url'])  && $video_bg_op['background-url'] != '')){
					$video_appearance_bg['background-image'] = $video_bg_op['background-url'];
				}
			}
			if((!isset($video_appearance_bg['background-color'])  || $video_appearance_bg['background-color'] == '')){
				if((isset($video_bg_op['background-color'])  && $video_bg_op['background-color'] != '')){
					$video_appearance_bg['background-color'] = $video_bg_op['background-color'];
				}
			}
			if((!isset($video_appearance_bg['background-repeat'])  || $video_appearance_bg['background-repeat'] == '')){
				if((isset($video_bg_op['background-repeat'])  && $video_bg_op['background-repeat'] != '')){
					$video_appearance_bg['background-repeat'] = $video_bg_op['background-repeat'];
				}else{
					$video_appearance_bg['background-repeat'] = 'no-repeat';
				}
			}
			if((!isset($video_appearance_bg['background-attachment'])  || $video_appearance_bg['background-attachment'] == '')){
				if((isset($video_bg_op['background-attachment'])  && $video_bg_op['background-attachment'] != '')){
					$video_appearance_bg['background-attachment'] = $video_bg_op['background-attachment'];
				}
			}
			if((!isset($video_appearance_bg['background-position'])  || $video_appearance_bg['background-position'] == '')){
				if((isset($video_bg_op['background-position'])  && $video_bg_op['background-position'] != '')){
					$video_appearance_bg['background-position'] = $video_bg_op['background-position'];
				}else{
					$video_appearance_bg['background-position'] = 'center';
				}
			}
			if((!isset($video_appearance_bg['background-size'])  || $video_appearance_bg['background-size'] == '')){
				if((isset($video_bg_op['background-size'])  && $video_bg_op['background-size'] != '')){
					$video_appearance_bg['background-size'] = $video_bg_op['background-size'];
				}else{
					$video_appearance_bg['background-size'] = 'cover';
				}
			}
				
			$css_bg =' style="';
			
			if($video_appearance_bg && isset($video_appearance_bg['background-image']) && $video_appearance_bg['background-image'] != ''){
				$css_bg .= 'background-image:url(' . esc_url($video_appearance_bg['background-image']) . ');';
			}
			
			if($video_appearance_bg && isset($video_appearance_bg['background-color']) && $video_appearance_bg['background-color'] != ''){
				$css_bg .= 'background-color:#'. $video_appearance_bg['background-color'].';';
			}
			if($video_appearance_bg && isset($video_appearance_bg['background-repeat']) && $video_appearance_bg['background-repeat'] != ''){
				$css_bg .= 'background-repeat:'. $video_appearance_bg['background-repeat'].';';
			}
			if($video_appearance_bg && isset($video_appearance_bg['background-attachment']) && $video_appearance_bg['background-attachment'] != ''){
				$css_bg .= 'background-attachment:'. $video_appearance_bg['background-attachment'].';';
			}
			if($video_appearance_bg && isset($video_appearance_bg['background-position']) && $video_appearance_bg['background-position'] != ''){
				$css_bg .= 'background-position:'. $video_appearance_bg['background-position'].';';
			}
			if($video_appearance_bg && isset($video_appearance_bg['background-size']) && $video_appearance_bg['background-size'] != ''){
				$css_bg .= 'background-size:'. $video_appearance_bg['background-size'].';';
			}
			
			$css_bg .= '"';
?>

		<div class="videov2-style dark-div" <?php echo $css_bg;?>>
			<?php
			if(function_exists('videopro_breadcrumbs')){
				videopro_breadcrumbs();
			}
			$ads_top_ct = ot_get_option('ads_top_ct');
			$adsense_slot_ads_top_ct = ot_get_option('adsense_slot_ads_top_ct');
			if($adsense_slot_ads_top_ct != '' || $ads_top_ct != ''){?>
				<div class="ads-system">
					<div class="ads-content">
					<?php
					if($adsense_slot_ads_top_ct != ''){ 
						echo do_shortcode('[adsense pub="' . ot_get_option('adsense_id') . '" slot="' . $adsense_slot_ads_top_ct . '"]');
					}else if($ads_top_ct != ''){
						echo do_shortcode($ads_top_ct);
					}
					?>
					</div>
				</div>
				<?php
			}
			?>
			<div class="style-post">
                <?php do_action('videopro-before-player-wrapper');?>
				<div class="cactus-post-format-video-wrapper">
                    <?php

                    if($video_header == 2){
                        videopro_print_header_thumbnail_image($video_id);
                    } else { ?>
					<div class="cactus-post-format-video<?php if(osp_get('ct_video_settings','video_floating')=='on'){echo ' floating-video '.osp_get('ct_video_settings','video_floating_position');}?>">
						<div class="cactus-video-content-api cactus-video-content"> 
                        	<span class="close-video-floating"><i class="fa fa-times" aria-hidden="true"></i></span>
							<?php echo do_shortcode('[cactus_player]');?>
						</div>
					</div>
                    <?php }?>
                    
					<?php 
						videopro_post_toolbar($video_id, 'video');
					?>                                                    
				</div>
                <?php do_action('videopro-after-player-wrapper');?>
			</div>
            <?php 
			$ads_single_1 = ot_get_option('ads_single_1');
			$adsense_slot_ads_single_1 = ot_get_option('adsense_slot_ads_single_1');
			if($adsense_slot_ads_single_1 != '' || $ads_single_1 != ''){?>
				<div class="ads-system">
					<div class="ads-content">
					<?php
					if($adsense_slot_ads_single_1 != ''){ 
						echo do_shortcode('[adsense pub="' . ot_get_option('adsense_id') . '" slot="' . $adsense_slot_ads_single_1 . '"]');
					}else if($ads_single_1 != ''){
						echo do_shortcode($ads_single_1);
					}
					?>
					</div>
				</div>
				<?php
			}
			?>
			<?php do_action('videopro_video_series', $video_id );?>
		</div>
<?php
		}
		
		$html = ob_get_clean();
		return $html;
	}
}

/**
 * hide breadcrumbs in video post content if there is already a breadcrumb on video header 
 */
add_filter('video_breadcrumbs_filter', 'video_breadcrumbs_filter_hidebreadcrumb', 10, 4);
if(!function_exists('video_breadcrumbs_filter_hidebreadcrumb')){
	function video_breadcrumbs_filter_hidebreadcrumb($html, $post_id, $post_layout, $post_format){
		if($post_format == 'video' && $post_layout == 2)
			return '';
		else 
			return $html;
	}
}


add_filter('videopro_filter_content_after', 'videopro_filter_content_after_return_full_content', 10, 2);
if(!function_exists('videopro_filter_content_after_return_full_content')){
	function videopro_filter_content_after_return_full_content($content, $full_content){
		return $full_content;
	}
}

add_filter('videopro_loop_item_thumbnail', 'videopro_loop_item_thumbnail_filter', 10, 6);

/**
	 * $html - string - HTML to be filtered
	 * $id - int - Post ID	 
	 * $img_size - array - Thumbnail Size
	 * $post_format - string - Post Format
	 * $video_data - array - containt video metadata
	 * $context - string - used to determine where this function is called. Used 'related' if it is called in Related Posts loop
	 */
function videopro_loop_item_thumbnail_filter($html, $id, $img_size, $post_format, $video_data, $context = ''){
		if($post_format != 'video') return $html;

		$html = '';

		$screenshot_preview = osp_get('ct_video_settings','enable_archives_screenshot_preview') ? true : false;
		$link_post = get_the_permalink($id);
		if(is_tax('video-series') ){
			$queried_object = get_queried_object();
			$term_slug = $queried_object->slug;
			$link_post =  add_query_arg( array('series' => $term_slug), $link_post );
		}
		
		if(isset($video_data['playlist'])){
			$link_post = add_query_arg( array('list' => $video_data['playlist']), $link_post );
		}
        
        $link_post = apply_filters('videopro_loop_item_url', $link_post, $id);

		if($screenshot_preview){
			$featured_image_id = get_post_thumbnail_id($id);
			
			$images = get_children( array( 'post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'asc', 'exclude' => $featured_image_id, 'order' => 'ASC', 'orderby' => 'ID' ) );
			
			$thumb_html = '';

			if(count($images) > 0){
				if(class_exists('videopro_thumb_config')){
					// find correct image size using mapping table
					if(is_array($img_size) && count($img_size) == 2){
						$size = videopro_thumb_config::mapping($img_size);
					} else {
						$size = $img_size;
					}
				} else {
					$size = $img_size;
				}

				$size = apply_filters('videopro_thumbnail_size_filter', $size, $id);

				// attach feature image at first index		
				$image_attributes = wp_get_attachment_image_src( $featured_image_id, $size);
				$ratio = '';
				if(!empty($image_attributes)){
					$ratio = 'style="padding-top:'.($image_attributes[2]/$image_attributes[1]*100).'%;"';
				}
						
				$defaultIMGsrc = $image_attributes[0];
				$lazyload = ot_get_option('lazyload', 'off');
				
				if($lazyload == 'on'){
					$lazyload_dfimg = apply_filters('videopro_image_placeholder_url', get_template_directory_uri().'/images/dflazy.jpg', $size);
					$thumb_html   .= 	'<img 
												src="'.$lazyload_dfimg.'"
												data-src="'.$defaultIMGsrc.'"
												data-srcset="'.wp_get_attachment_image_srcset( $featured_image_id, $size ).'"
												data-sizes="'.wp_get_attachment_image_sizes( $featured_image_id, $size ).'"
												alt="'.esc_attr(get_the_title($featured_image_id)).'"
												class="feature-image-first-index lazyload effect-fade"	
												'.$ratio.'											
											 />';	
				}else{
					$lazyload_dfimg = $defaultIMGsrc;
					$thumb_html   .= 	'<img 
												src="'.$defaultIMGsrc.'"
												srcset="'.wp_get_attachment_image_srcset( $featured_image_id, $size ).'"
												sizes="'.wp_get_attachment_image_sizes( $featured_image_id, $size ).'"
												alt="'.esc_attr(get_the_title($featured_image_id)).'"
												class="feature-image-first-index"												
											 />';	
				}
										 
				foreach((array)$images as $attachment_id => $attachment){
					$defaultIMGsrc = wp_get_attachment_image_url( $attachment_id, $size);
					$thumb_html   .= 	'<img 	
											src="'.$lazyload_dfimg.'"
											data-src="'.$defaultIMGsrc.'"									
											data-srcset="'.wp_get_attachment_image_srcset( $attachment_id, $size ).'"
											data-sizes="'.wp_get_attachment_image_sizes( $attachment_id, $size ).'"
											alt="'.esc_attr(get_the_title($attachment_id)).'"
											class="lazyload"												
										 />';
				}
			} else {
				$screenshot_preview = false;		
			}
		}
		

		ob_start();
		?>
		<div class="picture-content <?php echo $screenshot_preview ? 'screenshots-preview-inline' : '';?>">
					<a href="<?php echo esc_url($link_post); ?>" target="<?php echo apply_filters('videopro_loop_item_url_target', '_self', $id);?>"  title="<?php the_title_attribute(array('post' => $id)); ?>">
						<?php 
						
						if($screenshot_preview){
							echo $thumb_html;
						} else {
							echo videopro_thumbnail($img_size, $id);
						}
						
						$enable_lightbox_in_context = apply_filters('videopro_enable_lightbox_in_context', $context == 'related' ? 0 : 1, $context );
						
						echo apply_filters('videopro_loop_item_icon', $post_format == 'video' ? '<div class="ct-icon-video"></div>' : '', $id, $post_format, $enable_lightbox_in_context, '' );
						
						?>                                                               
					</a>
					
					<?php if(videopro_post_rating($id) != ''){
						echo videopro_post_rating($id);
					}
					
					extract($video_data);
					
					?>
						<div class="cactus-note font-size-1"><i class="fa fa-thumbs-up"></i><span><?php echo videopro_get_formatted_string_number($like);?></span></div>
					<?php 

					if($time_video != '00:00' && $time_video != '00' && $time_video != '' ){?>
						<div class="cactus-note ct-time font-size-1"><span><?php echo $time_video;?></span></div>
					<?php }?>    
                    
                    <?php if(osp_get('ct_video_settings', 'videotoolbar_show_watch_later_button') == 'on'){
                        if(isset($playlist) && $playlist == 'WL'){?>
                    <a href="#" title="<?php echo esc_html('Remove from Watch Later', 'videopro');?>" class="btn btn-default video-tb icon-only font-size-1 btn-watch-later" data-id="<?php echo $id;?>" data-action="remove"><i class="fa fa-remove"></i></a>
                    <?php        
                        } else {
                        ?>
                    <a href="#" title="<?php echo esc_html('Watch Later', 'videopro');?>" class="btn btn-default video-tb icon-only font-size-1 btn-watch-later" data-id="<?php echo $id;?>"><i class="fa fa-clock-o"></i></a>
                    <?php 
                        }
                    }
                    ?>
				</div>    
		<?php
		$html = ob_get_clean();

		return $html;
}

add_filter('videopro_get_post_viewlikeduration', 'videopro_get_post_viewlikeduration_filter', 10, 2);
if(!function_exists('videopro_get_post_viewlikeduration_filter')){
	function videopro_get_post_viewlikeduration_filter($data, $id){
		$use_network_data = osp_get('ct_video_settings','use_video_network_data') == 'on' ? 1 : 0;

		$like = $use_network_data ? get_post_meta($id, '_video_network_likes', true) : 0;
		$viewed = $use_network_data ? get_post_meta($id, '_video_network_views', true) : 0;
		
		$unlike = $use_network_data ? get_post_meta($id, '_video_network_dislikes', true) : 0;
		$time_video =  videopro_secondsToTime(get_post_meta($id,'time_video',true));

		$isWTIinstalled = function_exists('GetWtiLikeCount') ? 1 : 0;
		$isTop10PluginInstalled = function_exists('get_tptn_post_count_only') ? 1 : 0;
		
		$like       = ($like ? $like : 0) + ($isWTIinstalled ? str_replace("+", "", GetWtiLikeCount($id)) : 0);
		$unlike     = ($unlike ? $unlike : 0) + ($isWTIinstalled ? str_replace("-", "", GetWtiUnlikeCount($id)) : 0);
		$viewed     = ($viewed ? $viewed : 0) + ($isTop10PluginInstalled ?  get_tptn_post_count_only( $id ) : 0);

		return array('time_video' => $time_video, 'like' => $like, 'unlike' => $unlike, 'viewed' => $viewed);
	}
}

add_filter('get_comments_number', 'videopro_get_comments_number_filter', 10, 2);
if(!function_exists('videopro_get_comments_number_filter')){
	function videopro_get_comments_number_filter($count, $post_id){
		$use_network_data = osp_get('ct_video_settings', 'use_video_network_comment_count');
		$use_network_data = ($use_network_data == 'on') ? 1 : 0;

		if($use_network_data){
			$video_comment_count = get_post_meta($post_id, '_video_network_comments', true)*1;
			return $count + $video_comment_count ? $video_comment_count : 0;
		}
		
		return $count;
	}
}

add_action('comment_post', 'videopro_ajaxify_comments',20, 2);
if(!function_exists('videopro_ajaxify_comments')){
	function videopro_ajaxify_comments($comment_ID, $comment_status){
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			//If AJAX Request Then
			switch($comment_status){
				case '0':
				//notify moderator of unapproved comment
				wp_notify_moderator($comment_ID);
				case '1': //Approved comment
				echo "success";
				$commentdata=&get_comment($comment_ID, ARRAY_A);
				$post=&get_post($commentdata['comment_post_ID']);
				wp_notify_postauthor($comment_ID, $commentdata['comment_type']);
				break;
				default:
				echo "error";
			}
		exit;
		}
	}
}

/**
 * use external (instead of Single Page) for item URL
 */
add_filter('videopro_loop_item_url', 'videopro_loop_item_url_external_url', 10, 2);
if(!function_exists('videopro_loop_item_url_external_url')){
    function videopro_loop_item_url_external_url($url, $post_id){
        $has_single_page = get_post_meta($post_id, 'has_single_page', true);
        if($has_single_page == 'no'){
            
            $external_url = get_post_meta($post_id, 'external_url', true);
            if($external_url != ''){
                return $external_url;
            } else {
                return 'javascript:void(0)';
            }
        }
        
        return $url;
    }
}

/**
 * use external (instead of Single Page) target (blank) for item URL
 */
add_filter('videopro_loop_item_url_target', 'videopro_loop_item_url_target_external_url', 10, 2);
if(!function_exists('videopro_loop_item_url_target_external_url')){
    function videopro_loop_item_url_target_external_url($target, $post_id){
        $has_single_page = get_post_meta($post_id, 'has_single_page', true);
        if($has_single_page == 'no'){
            $external_url = get_post_meta($post_id, 'external_url', true);
            if($external_url != ''){
                return '_blank';
            }
        }
        
        return $target;
    }
}

/**
 * use external (instead of Single Page) for item comment link
 */
add_filter('get_comments_link', 'videopro_get_comments_link_external_url', 10, 1);
if(!function_exists('videopro_get_comments_link_external_url')){
    function videopro_get_comments_link_external_url($comment_link){
        global $post;
        $post_id = $post->ID;
        
        $has_single_page = get_post_meta($post_id, 'has_single_page', true);
        if($has_single_page == 'no'){
            $external_url = get_post_meta($post_id, 'external_url', true);
            if($external_url != ''){
                return $external_url;
            }
        }
        
        return $comment_link;
    }
}

add_action('scb-loop-item-picture-content', 'videopro_scb_loop_item_picture_content', 10, 1);
if(!function_exists('videopro_scb_loop_item_picture_content')){
    function videopro_scb_loop_item_picture_content($post_id){
        $format = get_post_format($post_id);
        if($format == 'video' && osp_get('ct_video_settings', 'videotoolbar_show_watch_later_button') == 'on'){
?>
                <a href="#" title="<?php echo esc_html('Watch Later', 'videopro');?>" class="btn btn-default video-tb icon-only font-size-1 btn-watch-later" data-id="<?php echo $post_id;?>"><i class="fa fa-clock-o"></i></a>
<?php
        }
    }
}

add_action('videopro-before-player-wrapper', 'videopro_before_player_wrapper_ad');
if(!function_exists('videopro_before_player_wrapper_ad')){
    function videopro_before_player_wrapper_ad(){
        $ads_single_3 = ot_get_option('ads_single_3');
        $adsense_slot_ads_single_3 = ot_get_option('adsense_slot_ads_single_3');
        if($adsense_slot_ads_single_3 != '' || $ads_single_3 != ''){?>
            <div class="player-side-ad left">
                <?php
                if($adsense_slot_ads_single_3 != ''){ 
                    echo do_shortcode('[adsense pub="' . ot_get_option('adsense_id') . '" slot="' . $adsense_slot_ads_single_3 . '"]');
                }else if($ads_single_3 != ''){
                    echo do_shortcode($ads_single_3);
                }
                ?>
            </div>
            <?php
        }
    }
}

add_action('videopro-after-player-wrapper', 'videopro_after_player_wrapper_ad');
if(!function_exists('videopro_after_player_wrapper_ad')){
    function videopro_after_player_wrapper_ad(){
    $ads_single_4 = ot_get_option('ads_single_4');
        $adsense_slot_ads_single_4 = ot_get_option('adsense_slot_ads_single_4');
        if($adsense_slot_ads_single_4 != '' || $ads_single_4 != ''){?>
            <div class="player-side-ad right">
                <?php
                if($adsense_slot_ads_single_4 != ''){ 
                    echo do_shortcode('[adsense pub="' . ot_get_option('adsense_id') . '" slot="' . $adsense_slot_ads_single_4 . '"]');
                }else if($ads_single_4 != ''){
                    echo do_shortcode($ads_single_4);
                }
                ?>
            </div>
            <?php
        }
    }
}