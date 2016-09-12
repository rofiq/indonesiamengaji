<?php
/**
 * 
 *
 */ 
if(!function_exists('cactus_edit_columns')) { 
	function cactus_edit_columns($columns) {
		global $post;
		if($post->post_type != 'post') return $columns;
		
		return array_merge( $columns, 
				array('ct-channel' => esc_html__('Channel','videopro')) ,
				array('ct-playlist' => esc_html__('Playlist','videopro')) 
		  );
	}
}
add_filter('manage_posts_columns' , 'cactus_edit_columns');
if(!function_exists('ct_custom_columns')) {
	// return the values for each coupon column on edit.php page
	function ct_custom_columns( $column ) {
		global $post;
		global $wpdb;
		
		if($post->post_type != 'post') return;

		$channel_id = get_post_meta($post->ID,'channel_id', true );
		$channel_name = ''; 
		if(is_array($channel_id) && !empty($channel_id)){
			foreach($channel_id as $channel_it){
				if($channel_name==''){
					$channel_name .= '<a href="'.get_permalink($channel_it).'">'.get_the_title($channel_it).'</a>';
				}else{
					$channel_name .= ', <a href="'.get_permalink($channel_it).'">'.get_the_title($channel_it).'</a>';
				}
			}
		}elseif($channel_id!=''){
			$channel_id = explode(",",$channel_id);
			foreach($channel_id as $channel_it){
				if($channel_name==''){
					$channel_name .= '<a href="'.get_permalink($channel_it).'">'.get_the_title($channel_it).'</a>';
				}else{
					$channel_name .= ', <a href="'.get_permalink($channel_it).'">'.get_the_title($channel_it).'</a>';
				}
			}
		}
		$playlist_id = get_post_meta($post->ID,'playlist_id', true );
		$playlist_name = ''; 
		if(is_array($playlist_id) && !empty($playlist_id)){
			foreach($playlist_id as $playlist_it){
				if($playlist_name==''){
					$playlist_name .= '<a href="'.get_permalink($playlist_it).'">'.get_the_title($playlist_it).'</a>';
				}else{
					$playlist_name .= ', <a href="'.get_permalink($playlist_it).'">'.get_the_title($playlist_it).'</a>';
				}
			}
		}elseif($playlist_id!=''){
			$playlist_id =explode(",",$playlist_id);
			foreach($playlist_id as $playlist_it){
				if($playlist_name==''){
					$playlist_name .= '<a href="'.get_permalink($playlist_it).'">'.get_the_title($playlist_it).'</a>';
				}else{
					$playlist_name .= ', <a href="'.get_permalink($playlist_it).'">'.get_the_title($playlist_it).'</a>';
				}
			}
		}
		switch ( $column ) {
			case 'ct-channel':
				echo $channel_name;
				break;
			case 'ct-playlist':
				echo $playlist_name;
				break;
		}
	}
	add_action( 'manage_posts_custom_column', 'ct_custom_columns' );
}

if(class_exists('JWP6_Plugin')) {
	if (JWP6_USE_CUSTOM_SHORTCODE_FILTER)
		add_filter('tm_video_filter', array('JWP6_Shortcode', 'widget_text_filter'));
}
if(function_exists('jwplayer_tag_callback')) {
	add_filter('tm_video_filter', 'jwplayer_tag_callback');
}

/**
 * Determines if the specified post is a video post.
*/
function videopro_is_post_video($post_id = null){
	if($post_id){
		$post = get_post($post_id);
	}else{
		$post = get_post(get_the_ID());
	}
	
	if(!$post->ID){
		return false;
	}
	
	// Back compat, if the post has any video field, it also is a video. 
	$video_file = get_post_meta($post->ID, 'tm_video_file', true);
	$video_url = get_post_meta($post->ID, 'tm_video_url', true);
	$video_code = get_post_meta($post->ID, 'tm_video_code', true);
	// Post meta by Automatic Youtube Video Post plugin
	if(!empty($video_code) || !empty($video_url) || !empty($video_file))
		return $post->ID;
	
	return false;
}

if(!function_exists('tm_player')){
    function tm_player($player = '', $args = array()) {
        if(empty($player) || empty($args['files']))
            return;
        
        $defaults = array(
            'files' => array(),
            'poster' => '',
            'autoplay' => false
        );
        $args = wp_parse_args($args, $defaults);
        
        extract($args);
        
            
        /* JWPlayer */
        if($player == 'jwplayer') {
            $options = array(
                'file' => trim($files[0]), // JWPlayer WordPress Plugin doesn't support multiple codecs
                'image' => get_post_thumbnail_id(get_the_ID())
            );
            $atts = arr2atts($options);
            $jwplayer_shortcode = '[jwplayer'.$atts.']';
            echo apply_filters('tm_video_filter', $jwplayer_shortcode);
        }	
        /* FlowPlayer */
        elseif($player == 'flowplayer' && function_exists('flowplayer_content_handle')) {
            $atts = array(
                'splash' => $poster
            );
            foreach($files as $key => $file) {
                $att = ($key == 0) ? 'src' : 'src'.$key;
                $atts[$att] = $file;
            }
            echo flowplayer_content_handle($atts, '', '');
            tm_flowplayer_script();
        }	
        elseif($player == 'videojs' && function_exists('video_shortcode')){
            $atts = array(
                'poster' => $poster,
            );
            foreach($files as $key => $file) {
                $att = ($key == 0) ? 'src' : 'src'.$key;
                if(strpos($file, '.mp4') !== false){$atts['mp4'] = $file;}
                if(strpos($file, '.webm') !== false){$atts['webm'] = $file;}
                if(strpos($file, '.ogg') !== false){$atts['ogg'] = $file;}
            }
            echo video_shortcode($atts, '', '');
            tm_add_videojs_swf();
        }
        /* WordPress Native Player: MediaElement */
        else{
            $atts = array();
            foreach($files as $file) {
                $file = trim($file);
                
                if(strpos($file, 'youtube.com') !== false)
                    $atts['youtube'] = $file;
                else {
                    $type = wp_check_filetype($file, wp_get_mime_types());
                    $atts[$type['ext']] = $file;
                }
            }
                
            echo wp_video_shortcode($atts);
        } 
    }
}
/**
 */

if(!function_exists('tm_extend_video_html')){
    function tm_extend_video_html($html, $autoplay = false, $wmode = 'opaque') {
        $replace = false;
        if(function_exists('ot_get_option')){$color_bt = ot_get_option('main_color_1');}
        if($color_bt==''){$color_bt = 'f9c73d';}
        preg_match('/src=[\"|\']([^ ]*)[\"|\']/', $html, $matches);
        $color_bt = str_replace('#','',$color_bt);
        if(isset($matches[1])) {
            $url = $matches[1];
            
            // Vimeo
            if(strpos($url, 'vimeo.com')) {
                // Remove the title, byline, portrait on Vimeo video
                $url = add_query_arg(array('title'=>0,'byline'=>0,'portrait'=>0,'player_id'=>'player_1','color'=>$color_bt), $url);
                //
                // Set autoplay
                if($autoplay)
                    $url = add_query_arg('autoplay', '1', $url);
                $replace = true;
            }
                
            // Youtube
            if(strpos($url, 'youtube.com')) {
                // Set autoplay
                if($autoplay)
                    $url = add_query_arg('autoplay', '1', $url);
            
                // Add wmode
                if($wmode)
                    $url = add_query_arg('wmode', $wmode, $url);
                
                // Disabled suggested videos on YouTube video when the video finishes
                $url = add_query_arg(array('rel'=>0), $url);
                // Remove top info bar
                $url = add_query_arg(array('showinfo'=>0), $url);
                $remove_annotations = ot_get_option('remove_annotations');
                if($remove_annotations!= '1'){
                    $url = add_query_arg(array('iv_load_policy'=>3), $url);
                }
                // Remove YouTube Logo
                $url = add_query_arg(array('modestbranding'=>0), $url);
                // Remove YouTube video annotations
                // $url = add_query_arg('iv_load_policy', 3, $url);
                
                $replace = true;
            }
            
            if($replace) {
                $url = esc_attr($url);	
                $html = preg_replace('/src=[\"|\']([^ ]*)[\"|\']/', 'src="'.$url.'"', $html);
            }
        }
        
        return $html;
    }
}

if(!function_exists('tm_video')){
    function tm_video($post_id, $autoplay = false, $video_code = '') {
        $file = get_post_meta($post_id, 'tm_video_file', true);
        $files = !empty($file) ? explode("\n", $file) : array();
        $url = trim(get_post_meta($post_id, 'tm_video_url', true));
        
        if($video_code == '') {
            $code = trim(get_post_meta($post_id, 'tm_video_code', true));
        } else {
            $code = $video_code;
        }	
        $id_vid = trim(get_post_meta($post_id, 'tm_video_id', true));

        global $link_arr;
        if(isset($_GET['link']) && $_GET['link']!=''){
            $url = $link_arr[$_GET['link']]['url'];
        }
        
        if(!empty($id_vid)) {
            if(is_plugin_active( 'contus-video-gallery/hdflvvideoshare.php' )){
                if(is_numeric($id_vid)){
                    echo do_shortcode( '[hdvideo  id="'.$id_vid.'"]');
                }else{
                    echo do_shortcode( '[hdvideo  '.$id_vid.']');
                }
            }elseif(is_plugin_active( 'all-in-one-video-pack/all_in_one_video_pack.php' )){
                echo do_shortcode( '[kaltura-widget  '.$id_vid.']');
            }
        } elseif(!empty($code)) {

            $video = do_shortcode($code);

            $video = apply_filters('tm_video_filter', $video);
            
            $video = tm_extend_video_html($video, $autoplay);
            
            if(has_shortcode($code, 'fvplayer') || has_shortcode($code, 'flowplayer'))
                tm_flowplayer_script();
            echo $video;
            
        } elseif(!empty($url)) {
            $url = trim($url);
            $video = '';
            $youtube_player = '';
            //facebook
            if(strpos($url, 'facebook.com') !== false) {
                $video = '<iframe src="https://www.facebook.com/v2.3/plugins/video.php?allowfullscreen=true&autoplay='.$autoplay.'&href='.urlencode($url).'&width=500&show_text=false&appId=850978544979890&height=281" width="500" height="281" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
            } 
            else
            // Youtube List
            if(preg_match('/http:\/\/www.youtube.com\/embed\/(.*)?list=(.*)/', $url)) {
                $video = '<iframe width="560" height="315" src="'.$url.'" frameborder="0" allowfullscreen></iframe>';
            
            } 
            // Youtube Player
            elseif(strpos($url, 'youtube.com') !== false && !empty($youtube_player)) {
                $args = array(
                    'files' => array($url),
                    'poster' => $poster,
                    'autoplay' => $autoplay
                );
                tm_player($youtube_player, $args);
            } 
            // WordPress Embeds
            else {
                global $wp_embed;
                $orig_wp_embed = $wp_embed;
                
                $wp_embed->post_ID = $post_id;
                $video = $wp_embed->autoembed($url);
                
                if(trim($video) == $url) {
                    $wp_embed->usecache = false;
                    $video = $wp_embed->autoembed($url);
                }
                
                $wp_embed->usecache = $orig_wp_embed->usecache;
                $wp_embed->post_ID = $orig_wp_embed->post_ID;
            }
            
            $video = tm_extend_video_html($video, $autoplay);

            echo $video;
        } 
        elseif(!empty($files)) {
            if(has_post_thumbnail($post_id) && $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'custom-large')){
                $poster = $thumb[0];}
            $player = osp_get('ct_video_settings','single_player_video');
            if($player =='jwplayer' && class_exists('JWP6_Plugin')){
                $player ='jwplayer';}
            else if($player ==''){
                $player = 'mediaelement';
            }
            $args = array(
                'files' => $files,
                'poster' => $poster,
                'autoplay' => $autoplay
            );
            tm_player($player, $args);
        }
    }
}
/*
 * Output Flowplayer script
 * 
 */
if(!function_exists('tm_flowplayer_script')){
    function tm_flowplayer_script(){
        if(!defined('DOING_AJAX') || !DOING_AJAX)
            return;

        echo '
        <script type="text/javascript">
            (function ($) {
                $(function(){typeof $.fn.flowplayer=="function"&&$("video").parent(".flowplayer").flowplayer()});
            }(jQuery));
        </script>
        ';
        
        flowplayer_display_scripts();
    }
}

/*
 * Output videojs script
 * 
 */
if(!function_exists('tm_add_videojs_swf')){
    function tm_add_videojs_swf(){
            echo '
            <script type="text/javascript">
                videojs.options.flash.swf = "'. get_template_directory_uri().( '/js/videojs/video-js.swf') .'";
            </script>
            ';
    }
}

///already - vote
if(!function_exists('TmAlreadyVoted')){
    function TmAlreadyVoted($post_id, $ip = null) {
        global $wpdb;
        
        if (null == $ip) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $tm_has_voted = $wpdb->get_var("SELECT value FROM {$wpdb->prefix}wti_like_post WHERE post_id = '$post_id' AND ip = '$ip'");
        
        return $tm_has_voted;
    }
}

add_filter('pre_post_title', 'wpse28021_mask_empty');
add_filter('pre_post_content', 'wpse28021_mask_empty');
if(!function_exists('wpse28021_mask_empty')){
    function wpse28021_mask_empty($value)
    {
        if ( empty($value) ) {
            return ' ';
        }
        return $value;
    }
}

add_filter('wp_insert_post_data', 'wpse28021_unmask_empty');
if(!function_exists('wpse28021_unmask_empty')){
    function wpse28021_unmask_empty($data)
    {
        if ( ' ' == $data['post_title'] ) {
            $data['post_title'] = '';
        }
        if ( ' ' == $data['post_content'] ) {
            $data['post_content'] = '';
        }
        return $data;
    }
}

// * Convert seconds to timecode
// * http://stackoverflow.com/q/8273804
// 
if(!function_exists('videopro_secondsToTime')){	
	function videopro_secondsToTime($inputSeconds) 
	{

		$secondsInAMinute = 60;
		$secondsInAnHour  = 60 * $secondsInAMinute;
		$secondsInADay    = 24 * $secondsInAnHour;

		// extract days
		$days = floor($inputSeconds / $secondsInADay);

		// extract hours
		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);

		// extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);

		// extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);

		// DAYS
		if( (int)$days == 0 )
			$days = '';
		elseif( (int)$days < 10 )
			$days = '0' . (int)$days . ':';
		else
			$days = (int)$days . ':';

		// HOURS
		if( (int)$hours == 0 )
			$hours = '';
		elseif( (int)$hours < 10 )
			$hours = '0' . (int)$hours . ':';
		else 
			$hours = (int)$hours . ':';

		// MINUTES
		if( (int)$minutes == 0 )
			$minutes = '00:';
		elseif( (int)$minutes < 10 )
			$minutes = '0' . (int)$minutes . ':';
		else 
			$minutes = (int)$minutes . ':';

		// SECONDS
		if( (int)$seconds == 0 )
			$seconds = '00';
		elseif( (int)$seconds < 10 )
			$seconds = '0' . (int)$seconds;

		return $days . $hours . $minutes . $seconds;
	}
}

add_filter( 'video_thumbnail_markup', 'tm_video_thumbnail_markup', 10, 2 );
if(!function_exists('tm_video_thumbnail_markup')){
    function tm_video_thumbnail_markup( $markup, $post_id ) {
        $markup .= ' ' . get_post_meta($post_id, 'tm_video_code', true);
        $markup .= ' ' . get_post_meta($post_id, 'tm_video_url', true);

        return $markup;
    }
}
/**
 * Convert array to attributes string
 */
if(!function_exists('arr2atts')){
    function arr2atts($array = array(), $include_empty_att = false) {
        if(empty($array))
            return;
        
        $atts = array();
        foreach($array as $key => $att) {
            if(!$include_empty_att && empty($att))
                continue;
            
            $atts[] = $key.'="'.$att.'"';
        }
        
        return ' '.implode(' ', $atts);
    }
}
/**
 * Shorten long numbers
 */

if(!function_exists('tm_short_number')) {
function tm_short_number($n, $precision = 3) {
	$n = $n*1;
    if ($n < 1000000) {
        // Anything less than a million
        $n_format = number_format($n);
    } else if ($n < 1000000000) {
        // Anything less than a billion
        $n_format = number_format($n / 1000000, $precision) . 'M';
    } else {
        // At least a billion
        $n_format = number_format($n / 1000000000, $precision) . 'B';
    }

    return $n_format;
}
}

if(!function_exists('videopro_build_multi_link')) {
function videopro_build_multi_link($arr, $echo=false) {
	if($arr){
		ob_start();
		$link_arr = array();
		$link_count = 0;
		?>
        <div class="ct-series multilink-style">
        	<div class="series-content">
        <?php
		foreach($arr as $group){ ?>
            
                
                    <div class="series-content-row">
                        <div class="series-content-item">
                            <div class="content-title"><?php echo isset($group['title'])?$group['title']:'' ?></div>
                        </div>
                        <div class="series-content-item">
                            <div class="content-epls">
                            	<?php 
								$multi_link = explode("\n",$group['links']); //raw array
								$temp_title = '';
								$link_number = 0;
								foreach($multi_link as $link){
									if(strpos($link, 'http') !== false){ //is a url
										$link_arr[]=array(
											'title' => $temp_title,
											'url' => $link
										);
										?>
										<a class="<?php if(isset($_GET['link']) && $_GET['link']==$link_count){ echo 'active'; } ?>" href="<?php echo add_query_arg( 'link', $link_count, get_permalink(get_the_ID()) ); ?>"><i class="fa fa-play"></i> <?php echo $temp_title?$temp_title:esc_html__('Link ','videopro').($link_number+1); ?> </a>
										<?php
										$temp_title = '';
										$link_count++;
										$link_number++;
									}else{
										$temp_title = $link;
									}
								}
								?>
                            </div>
                        </div>
                    </div>
		<?php }?>
        	</div>
        </div>
        <?php 
		$html = ob_get_clean();
	}//if arr
	if($echo){
		echo $html;
	}else{
		return $link_arr;
	}
}
}

function extractIDFromURL($url){
	$channel = extractChanneldFromURL($url);
	$id = '';
	
	switch($channel){
		 case 'youtube':
            if(strpos($url,'youtu.be') !== false){
                $id = substr($url, strrpos($url,'/'));
            } else {
                $id = substr($url, strrpos($url,'v=')+2);
            }
			break;
		case 'dailymotion':
			$id = substr($url, strrpos($url,'video/')+6);
			break;	
		case 'vimeo':
			$id = substr($url, strrpos($url,'/')+1);
			break;
		default:
			$id = '';
	}
	
	return $id;
}
function extractChanneldFromURL($url){
	if(strpos($url,'youtube.com') !== false || (strpos($url,'youtu.be') !== false)){
		return 'youtube';
	} else if(strpos($url,'vimeo.com') !== false){
		return 'vimeo';
	} else if(strpos($url,'dailymotion.com') !== false){
		return 'dailymotion';
	} else return '';
}
// custom field taxonomy  
function playlist_taxonomy_custom_fields($tag) {  
   // Check for existing taxonomy meta for the term you're editing  
    $t_id = $tag->term_id; // Get the ID of the term you're editing  
    $term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check  
?>  

<tr class="form-field">  
    <th scope="row" valign="top">  
        <label for="channel_id"><?php _e('Channel ID'); ?></label>  
    </th>  
    <td>  
        <input type="text" name="term_meta[channel_id]" id="term_meta[channel_id]" size="25" style="width:60%;" value="<?php echo $term_meta['channel_id'] ? $term_meta['channel_id'] : ''; ?>"><br />  
        <span class="description"><?php _e('The Channel ID, Ex: 1, 2, 3'); ?></span>  
    </td>  
</tr>  

<?php  
}  
// A callback function to save our extra taxonomy field(s)  
function save_taxonomy_custom_fields( $term_id ) {  
    if ( isset( $_POST['term_meta'] ) ) {  
        $t_id = $term_id;  
        $term_meta = get_option( "taxonomy_term_$t_id" );  
        $cat_keys = array_keys( $_POST['term_meta'] );  
            foreach ( $cat_keys as $key ){  
            if ( isset( $_POST['term_meta'][$key] ) ){  
                $term_meta[$key] = $_POST['term_meta'][$key];  
            }  
        }  
        //save the option array  
        update_option( "taxonomy_term_$t_id", $term_meta );  
    }  
}  
add_action( 'ct_playlist_add_form_fields', 'playlist_taxonomy_custom_fields', 10, 2 );  
add_action( 'ct_playlist_edit_form_fields', 'playlist_taxonomy_custom_fields', 10, 2 );  

add_action ( 'edited_ct_playlist', 'save_taxonomy_custom_fields');
add_action( 'created_ct_playlist', 'save_taxonomy_custom_fields', 10, 2 );
/*
Auto fetch data
*/

// End Fetch
//jwplayer 7
if(!function_exists('cactus_hook_get_meta')){
	function cactus_hook_get_meta($metadata, $object_id, $meta_key, $single) {
		$fieldtitle="_jwppp-video-url-1";
		if($meta_key==$fieldtitle&& isset($meta_key)) {
			//use $wpdb to get the value
			global $wpdb;
			$value = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $object_id AND  meta_key = 'tm_video_url'" );
			if($value==''){
				$value = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $object_id AND  meta_key = 'tm_video_file'" );
			}
			//do whatever with $value
			
			return $value;
		}
	}
}
if(!is_admin()){
	add_filter('get_post_metadata', 'cactus_hook_get_meta', 10, 4);
}

if(!function_exists('cactus_jwplayer7')) {
	function cactus_jwplayer7(){
		echo '<div class="jwplayer playlist-none cactus-jw7">';
		echo do_shortcode('[jw7-video]');
		echo '</div>';
	}
}

// Subscribe user
add_action( 'wp_ajax_videopro_subscribe_user', 'videopro_subscribe_user' );
add_action( 'wp_ajax_nopriv_videopro_subscribe_user', 'videopro_subscribe_user' );

if(!function_exists('videopro_subscribe_user')){
	function videopro_subscribe_user(){
		if( !is_user_logged_in()){
			echo '1';
		}else{
			$user = new WP_User(get_current_user_id());
			$my_user_sub		= get_user_meta($user->ID, 'my_user_sub', true);
			$id_user = $_POST['id_user'];
			$subuser_counter =  (int)get_user_meta($id_user, 'subuser_counter',true);
			if($my_user_sub){
				if(!is_array($my_user_sub)){
					if($my_user_sub!= $id_user){
						$arr = array();
						array_push($arr, $my_user_sub);
						array_push($arr, $id_user);
						$my_user_sub = $arr;
						update_user_meta( $user->ID, 'my_user_sub', $my_user_sub);
						$subuser_counter = $subuser_counter +1; 
						update_user_meta( $id_user, 'subuser_counter', $subuser_counter);
					}else{
						$my_user_sub='';
						update_user_meta( $user->ID, 'my_user_sub', $my_user_sub);
						$subuser_counter = $subuser_counter - 1; 
						update_user_meta( $id_user, 'subuser_counter', $subuser_counter);
					}
				}else{
					if(!in_array($id_user, $my_user_sub)){
						array_push($my_user_sub, $id_user);
						update_user_meta( $user->ID, 'my_user_sub', $my_user_sub);
						$subuser_counter = $subuser_counter +1; 
						update_user_meta( $id_user, 'subuser_counter', $subuser_counter);
					}else{
						if(($key = array_search($id_user, $my_user_sub)) !== false) {
							unset($my_user_sub[$key]);
						}
						update_user_meta( $user->ID, 'my_user_sub', $my_user_sub);
						$subuser_counter = $subuser_counter - 1; 
						update_user_meta( $id_user, 'subuser_counter', $subuser_counter);
					}
				}
			}else{
				$my_user_sub = array($id_user);
				update_user_meta( $user->ID, 'my_user_sub', $my_user_sub);
				$subuser_counter = $subuser_counter + 1; 
				update_user_meta( $id_user, 'subuser_counter', $subuser_counter);
			}
		}
	}
}

if(!function_exists('videopro_addto_subscribe')){
	/**
	 * call in loop only
	 */
	 function videopro_addto_subscribe(){
		ob_start();
		$subscribe = 0;
		$subscribe_cl ='';
		$id_us = get_the_author_meta( 'ID' );
		$my_subscribe = get_user_meta(get_current_user_id(), 'my_user_sub',true);
		if(!is_array($my_subscribe)&& $my_subscribe == $id_us){
			$subscribe = 1;
			$subscribe_cl ='added';
			$label = esc_html__('subscribed','videopro');
		}elseif(is_array($my_subscribe)&& in_array($id_us, $my_subscribe)){
			$subscribe = 1;
			$subscribe_cl ='added';
			$label = esc_html__('subscribed','videopro');
		}
		$subscribe_counter = get_user_meta($id_us, 'subuser_counter',true);
		if($subscribe_counter){
			$subscribe_counter = number_format($subscribe_counter);
		}else{$subscribe_counter = 0;}
		$id = rand(1,1000);
		?>
        <div class="channel-button subscribe-user <?php echo $subscribe_cl;?>" data-id="<?php echo get_the_author_meta( 'ID' );?>" id="subuser-<?php echo $id;?>">
            <a href="<?php if(!is_user_logged_in()){ echo wp_login_url( get_permalink() );}else{?>#<?php }?>" class="btn btn-default subscribe font-size-1 metadata-font <?php if(is_user_logged_in()){?>logged<?php }?>">
                <i class="fa fa-circle"></i><i class="fa fa-check"></i> <span><?php esc_html_e('subscribe','videopro');?></span><span><?php esc_html_e('subscribed','videopro');?></span>
            </a>
            <span class="font-size-1 metadata-font sub-count"><?php echo $subscribe_counter;?></span>
            <input type="hidden"  name="ajax_url" value="<?php echo esc_url(admin_url( 'admin-ajax.php' ));?>">
        </div>
        <?php
		$output_string = ob_get_contents();
		ob_end_clean();
		return $output_string;
	}
}

if(!function_exists('videopro_numbervideo_byauthor')){
	/**
	 * call in loop only
	 */
	function videopro_numbervideo_byauthor(){
		ob_start();
		$args = array(
			'author'        =>  get_the_author_meta( 'ID' ),
			'posts_per_page' => 1,
			'tax_query' => array(
				array(
					'taxonomy' => 'post_format',
					'field'    => 'slug',
					'terms'    => array( 'post-format-video' ),
				),
			),
		);
		$query = new WP_Query( $args );
		$count = $query->found_posts;
		?>
        <div class="channel-button">                                                                
            <span class="font-size-1 metadata-font sub-count"><?php echo $count.' '. esc_html__('Videos','videopro');?></span>
        </div>
        <?php
		$output_string = ob_get_contents();
		ob_end_clean();
		return $output_string;
		
	}
}
if(!function_exists('videopro_AlreadyVoted')){
	function videopro_AlreadyVoted($post_id, $ip = null) {
		global $wpdb;
	
		if (null == $ip) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	
		$tm_has_voted = $wpdb->get_var($wpdb->prepare("SELECT value FROM {$wpdb->prefix}wti_like_post WHERE post_id = %d AND ip = %s", $post_id, $ip));
	
		return $tm_has_voted;
	}
}