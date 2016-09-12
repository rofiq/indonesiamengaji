<div class="about-content body-content">
    <?php the_content();?>
</div>
<div class="about-information">
    <?php 
	$cr_id_cn = get_the_ID();
    $subscribe_counter = get_post_meta(get_the_ID(), 'subscribe_counter',true);
    if($subscribe_counter){
        $subscribe_counter = videopro_get_formatted_string_number($subscribe_counter);
    }else{$subscribe_counter = 0;}
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'ignore_sticky_posts' => 1,
        'posts_per_page' => -1,
        'orderby' => 'latest',
        'meta_query' => array(
            array(
                'key' => 'channel_id',
                 'value' => get_the_ID(),
                 'compare' => 'LIKE',
            ),
        )
    );
    $video_query = new WP_Query( $args );
    $n_video = $video_query->post_count;
    $view_channel = (int)get_post_meta( get_the_ID(), 'view_channel', true );
    $args_pl = array(
        'post_type' => 'ct_playlist',
        'post_status' => 'publish',
        'ignore_sticky_posts' => 1,
        'posts_per_page' => -1,
        'orderby' => 'modified',
        'meta_query' => array(
            array(
                'key' => 'playlist_channel_id',
                 'value' => get_the_ID(),
                 'compare' => 'LIKE',
            ),
        )
    );
    $playlist_query = new WP_Query( $args_pl );
    if($playlist_query->have_posts()){
        while($playlist_query->have_posts()){$playlist_query->the_post();
            $view_playlist = (int)get_post_meta( get_the_ID(), 'view_playlist', true );
            $view_channel = $view_channel + $view_playlist;
        }
    }
    wp_reset_postdata();
	$isTop10PluginInstalled = function_exists('get_tptn_post_count_only') ? 1 : 0;
	$view_channel     = ($isTop10PluginInstalled ?  get_tptn_post_count_only( $cr_id_cn) : 0); 
    ?>                                       	
    <div><i class="fa fa-play-circle"></i><span><?php echo esc_html($n_video); esc_html_e(' videos','videopro'); ?></span></div>
    <div><i class="fa fa-users"></i><span><?php echo esc_html($subscribe_counter); esc_html_e(' subscribers ','videopro'); ?></span></div>
    <div><i class="fa fa-eye"></i><span><?php echo esc_html($view_channel); esc_html_e(' views','videopro'); ?></span></div>
</div>
