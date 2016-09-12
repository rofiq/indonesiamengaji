<?php

/**
 * videopro functions and definitions
 *
 * @package videopro
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1280; /* pixels */
}

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/theme_config.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Core features
 */
require get_template_directory() . '/inc/core/cactus-core.php';

/**
 * Data functions
 */
require get_template_directory() . '/inc/core/data-functions.php';

/**
 * Uncomment below line in Release mode. theme-options.php is generated using Export feature in Option Tree
 */
require get_template_directory() . '/inc/theme-options.php';

/**
 * Welcome page
 */
require get_template_directory() . '/inc/welcome.php';

/**
 * Add metadata (meta-boxes) for all post types
 */
require get_template_directory() . '/inc/metadata.php';

/**
 * Require Megamenu
 */
require get_template_directory() . '/inc/megamenu/megamenu.php';

/**
 * Add metadata for categories
 */
require get_template_directory() . '/inc/category-metadata.php';

/**
 * Require Widgets
 */
require get_template_directory() . '/inc/widgets/widgets_theme.php';

require get_template_directory() . '/inc/author-metadata.php';

require get_template_directory() . '/inc/hook_filters.php';

if ( ! function_exists( 'videopro_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function videopro_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on cactus, use a find and replace
	 * to change 'videopro' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'videopro', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
        'primary' => esc_html__( 'Primary Menu', 'videopro' ),
        'footer-menu' => esc_html__( 'Footer Menu', 'videopro' ),
		'user-menu' => esc_html__( 'Logged In User Menu', 'videopro' ),

	) );

	add_theme_support('title-tag');
	// Enable support for Post Formats.
	add_theme_support( 'post-formats', array( 'video', 'audio', 'gallery' ) );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'cactus_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	// Enable support for HTML5 markup.
	add_theme_support( 'html5', array(
		'comment-list',
		'search-form',
		'comment-form',
		'gallery',
		'caption',
	) );

	/**
	 * Register Image Size
	 */
	$thumb_sizes = videopro_thumb_config::get_configured_sizes();
	do_action( 'videopro_reg_thumbnail', $thumb_sizes );
	
	if(ot_get_option('videopro_wti_integration', 'on') == 'on'){
		/*remove WTI*/
		remove_filter('the_content', 'PutWtiLikePost');
	}
	
	if(ot_get_option('author_page_enabled', 'on') == 'off'){
		add_action( 'template_redirect', 'videopro_remove_author_pages_page' );
		add_filter( 'author_link', 'videopro_remove_author_pages_link' );
	}
    
    add_theme_support( 'woocommerce' );
}
endif; // cactus_setup
add_action( 'after_setup_theme', 'videopro_setup' );

add_action( 'init', 'videopro_remove_featured_images_from_page', 11 ); 
function videopro_remove_featured_images_from_page() {
    add_theme_support( 'post-thumbnails', array( 'post','ct_playlist','ct_channel','product','ct_actor' ) );
}

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function videopro_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Right Sidebar', 'videopro' ),
		'id'            => 'right-sidebar',
		'description'   => esc_html__('Appears in right column', 'videopro'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h2 class="widget-title h4">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Left Sidebar', 'videopro' ),
		'id'            => 'left-sidebar',
		'description'   => esc_html__('Appears in left column','videopro'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h2 class="widget-title h4">',
		'after_title'   => '</h2>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Search Box Sidebar', 'videopro' ),
		'id'            => 'searchbox-sidebar',
		'description'   => esc_html__('To replace theme\'s default Search Box', 'videopro'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h2 class="widget-title h4">',
		'after_title'   => '</h2>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Breadcrumbs Sidebar', 'videopro' ),
		'id'            => 'breadcrumbs-sidebar',
		'description'   => esc_html__('To replace theme\'s default breadcrumbs', 'videopro'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h2 class="widget-title h4">',
		'after_title'   => '</h2>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Main Menu Sidebar ', 'videopro' ),
		'id'            => 'mainmenu-sidebar',
		'description'   => esc_html__('To replace default main navigation','videopro'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h2 class="widget-title h4">',
		'after_title'   => '</h2>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Main Top Sidebar', 'videopro' ),
		'id'            => 'main-top-sidebar',
		'description'   => esc_html__('Top of page (Under Header)','videopro'),
		'before_widget' => '<aside id="%1$s" class="widget body-widget %2$s"><div class="body-widget-inner widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h4 class="body-widget-title widget-title h4">',
		'after_title'   => '</h4>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Content Top sidebar', 'videopro' ),
		'id'            => 'content-top-sidebar',
		'description'   => esc_html__('Top of content','videopro'),
		'before_widget' => '<aside id="%1$s" class="widget body-widget %2$s"><div class="body-widget-inner widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h4 class="body-widget-title widget-title h4">',
		'after_title'   => '</h4>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Content Bottom sidebar', 'videopro' ),
		'id'            => 'content-bottom-sidebar',
		'description'   => esc_html__('Bottom of content','videopro'),
		'before_widget' => '<aside id="%1$s" class="widget body-widget %2$s"><div class="body-widget-inner widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h4 class="body-widget-title widget-title h4">',
		'after_title'   => '</h4>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Main Bottom Sidebar', 'videopro' ),
		'id'            => 'main-bottom-sidebar',
		'description'   => esc_html__( 'Bottom of page (Above Footer)','videopro' ),
		'before_widget' => '<aside id="%1$s" class="widget body-widget %2$s"><div class="body-widget-inner widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h4 class="body-widget-title widget-title h4">',
		'after_title'   => '</h4>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Footer Sidebar', 'videopro' ),
		'id'            => 'footer-sidebar',
		'description'   => esc_html__('Appears in Footer','videopro'),
		'before_widget' => '<aside id="%1$s" class="widget module widget-col %2$s"><div class="widget-inner">',
		'after_widget'  => '</div></aside>',
		'before_title'  => '<h2 class="widget-title h4">',
		'after_title'   => '</h2>',
	) );
	
	register_sidebar( array(
		'name' => esc_html__( 'User Submit Video Sidebar', 'videopro' ),
		'id' => 'user_submit_sidebar',
		'description' => esc_html__( 'Will be appeared in User Submit Video popup', 'videopro' ),
		'before_widget' => '<aside id="%1$s" class="user-submit"><div class="widget-inner">',
		'after_widget' => '</div></aside>',
		'before_title' => '<h2 class="widget-title h4">',
		'after_title' => '</h2>',
	));

}
add_action( 'widgets_init', 'videopro_widgets_init' );

/**
 *  Get URL to google fonts
 * 
 * @param
 * 	$font_names - array - Array of Google Font Names
 * 
 * @return
 * 	string - URL of Google Fonts to enqueue
 */
function videopro_get_google_fonts_url ($font_names) {

    $font_url = '';

    $font_url = add_query_arg( 'family', urlencode(implode('|', $font_names)) , "//fonts.googleapis.com/css" );
    return $font_url;
}

function videopro_rtl_customCSS(){
	if(ot_get_option('rtl') == 'on'){
		wp_enqueue_style( 'rtl', get_template_directory_uri() . '/rtl.css');
	}	
	$custom_css = videopro_get_custom_css();
	if(!class_exists('CactusThemeShortcodes')){
		wp_add_inline_style('videopro-style', $custom_css);
	}else{
		wp_add_inline_style('ct_shortcode_style', $custom_css);
	}
}
add_action( 'wp_enqueue_scripts', 'videopro_rtl_customCSS', 99 );

/**
 * Enqueue scripts and styles.
 */
function videopro_scripts() {
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome/css/font-awesome.min.css', array(), '4.3.0');
	wp_enqueue_style( 'slick', get_template_directory_uri() . '/css/slick.css');
	wp_enqueue_style( 'malihu-scroll', get_template_directory_uri() . '/css/jquery.mCustomScrollbar.min.css');
	wp_enqueue_style( 'videopro-style', get_stylesheet_uri() );

	/**
	 * Register Google Font
	 */
	$g_fonts = array();

	$google_font = ot_get_option('google_font', 'on');
	if($google_font == 'on'){
		
		$body_font = ot_get_option('main_font_family',''); // for example, Playfair+Display:900
		if($body_font != ''){
			array_push($g_fonts, $body_font);
		}

		$heading_font = ot_get_option('heading_font_family', ''); // for example, Playfair+Display:900
		if($heading_font != ''){
			array_push($g_fonts, $heading_font);
		}
		
		$navigation_font = ot_get_option('navigation_font_family', ''); // for example, Playfair+Display:900
		if($navigation_font != ''){
			array_push($g_fonts, $navigation_font);
		}
		
		$meta_font = ot_get_option('meta_font_family', ''); // for example, Playfair+Display:900
		if($meta_font != ''){
			array_push($g_fonts, $meta_font);
		}
	}
	
	if(count($g_fonts)>0){	
		wp_enqueue_style( 'videopro-google-fonts', videopro_get_google_fonts_url($g_fonts), array(), '1.0.0' );
	}
	
	$smoothScroll = ot_get_option('scroll_effect', 'off');
	if($smoothScroll=='on') {
		wp_enqueue_script('videopro_smoothScroll', get_template_directory_uri() . '/js/smoothscroll.js', array(), '1.4.4', true);
	}
	
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/js/bootstrap-lib.js', array('jquery'), '3.1.1', true );
	wp_enqueue_script( 'jquery-migrate', get_template_directory_uri() . '/js/jquery-migrate-1.2.1.min.js', array('jquery'), '1.2.1', true );
	wp_enqueue_script( 'slick', get_template_directory_uri() . '/js/slick.min.js', array('jquery'), '1.1.0', true );
	wp_enqueue_script( 'malihu-scroll', get_template_directory_uri() . '/js/jquery.mCustomScrollbar.concat.min.js', array('jquery'), '3.1.12', true );
	wp_enqueue_script( 'js-cookie', get_template_directory_uri() . '/js/js.cookie.js', array('jquery'), '2.1.1', true );
	wp_enqueue_script( 'js-isotope', get_template_directory_uri() . '/js/isotope.js', array('jquery'), '3.0.1', true );		
	
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// code to embed the java script file that makes the Ajax request
	wp_enqueue_script( 'videopro-ajax-request', get_template_directory_uri() . '/js/ajax.js', array( 'jquery' ) );
	// main theme javascript code
	wp_enqueue_script( 'videopro-theme-js', get_template_directory_uri() . '/js/template.js', array( 'jquery' ), '', true  );

	// code to declare the URL to the file handling the AJAX request <p></p>
	$js_params = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
	$js_params['video_pro_main_color'] = (ot_get_option('main_color', '#d9251d')!='#d9251d')?ot_get_option('main_color', '#d9251d'):'#d9251d';
	global $wp_query, $wp;
	$js_params['query_vars'] = $wp_query->query_vars;
	$js_params['current_url'] =  esc_url(home_url($wp->request));

	$scroll_to_next_post = get_post_meta(get_the_ID(),'enable_scroll_to_next_post',true) != '' ? get_post_meta(get_the_ID(),'enable_scroll_to_next_post',true) : ot_get_option('single_post_scroll_next','off');
	if($scroll_to_next_post == 'on')
	{
		if(ot_get_option('single_post_scroll_next_change_url','on') == 'on'){
			$js_params['scroll_effect_change_url'] = 1;
		}
	}

	wp_localize_script( 'videopro-ajax-request', 'cactus', $js_params  );
}

add_action( 'wp_enqueue_scripts', 'videopro_scripts' );

function videopro_admin_init(){
	add_editor_style('editor-style.css');
}
add_action( 'admin_init', 'videopro_admin_init' );

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

function videopro_remove_author_pages_page() {
	if ( is_author() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
	}
}

function videopro_remove_author_pages_link( $author_url ) {
	return 'javascript:void(0)';
}



if(!function_exists('videopro_get_custom_css')){
	function videopro_get_custom_css(){
		// Retina Logo
		$css = '';
		$retina_logo = ot_get_option('retina_logo','');
		if(is_page_template('page-templates/front-page.php') || is_page_template('page-templates/demo-menu.php')){
			$front_page_logo_retina = get_post_meta(get_the_ID(),'front_page_logo_retina',true);
			$retina_logo = $front_page_logo_retina !='' ? $front_page_logo_retina : $retina_logo;
		}
		if($retina_logo!=''){
			$css .= 
				'@media only screen and (-webkit-min-device-pixel-ratio: 2),(min-resolution: 192dpi) {
					/* Retina Logo */
					.primary-header .cactus-logo.navigation-font a{background:url(' . esc_url($retina_logo) . ') no-repeat center; background-size:contain;}
					.primary-header .cactus-logo.navigation-font a img{ opacity:0; visibility:hidden}
				}';
		}
		
		$css .= 'img.gform_ajax_spinner{background:url(' . apply_filters('videopro_contact_form_loader_url', get_template_directory_uri() . '/images/ajax-loader.gif') . ');}';
		
		require get_template_directory() . '/css/custom.css.php';
		$css .= videopro_custom_css();
		
		$css = apply_filters('videpro_custom_css', $css);
		
		return $css;
	}
}

/*
 * Get label of an option in Option Tree / Theme Options
 */
function videopro_setting_label_by_id( $id ) {
  if ( empty( $id ) )
    return false;
  $settings = get_option( 'option_tree_settings' );
  if ( empty( $settings['settings'] ) )
    return false;
  foreach( $settings['settings'] as $setting ) {
    if ( $setting['id'] == $id && isset( $setting['label'] ) ) {
      return $setting['label'];
    }
  }
}

/**
 * Ajax page navigation
 */

// when the request action is 'load_more', the cactus_ajax_load_next_page() will be called
add_action( 'wp_ajax_load_more', 'videopro_ajax_load_next_page' );
add_action( 'wp_ajax_nopriv_load_more', 'videopro_ajax_load_next_page' );

function videopro_ajax_load_next_page() {
	//get blog listing style
	global $blog_layout;

	$test_layout = isset($_POST['blog_layout']) ? $_POST['blog_layout'] : '';

	if(isset($test_layout) && $test_layout != '' && ($test_layout == 'layout_1' || $test_layout == 'layout_2' || $test_layout == 'layout_3' || $test_layout == 'layout_4' || $test_layout == 'layout_5' || $test_layout == 'layout_6' || $test_layout == 'layout_7'))
	    $blog_layout = $test_layout;
	else
	    $blog_layout = ot_get_option('blog_layout', 'layout_1');


    // Get current page
	$page = intval($_POST['page']);

	// number of published sticky posts
	$sticky_posts = videopro_get_sticky_posts_count();

	// current query vars
	$vars = $_POST['vars'];

	// convert string value into corresponding data types
	foreach($vars as $key=>$value){
		if(is_numeric($value)) $vars[$key] = intval($value);
		if($value == 'false') $vars[$key] = false;
		if($value == 'true') $vars[$key] = true;
	}

	// item template file
	$template = $_POST['template'];

	// Return next page
	$page = intval($page) + 1;

	$posts_per_page = isset($vars['posts_per_page']) ? $vars['posts_per_page'] : get_option('posts_per_page');

	if($page == 0) $page = 1;
	$offset = ($page - 1) * $posts_per_page;
	/*
	 * This is confusing. Just leave it here to later reference
	 *

	 *
	 */


	// get more posts per page than necessary to detect if there are more posts
	$args = array('post_status'=>'publish','posts_per_page' => $posts_per_page + 1,'offset' => $offset);
	$args = array_merge($vars,$args);

	// remove unnecessary variables
	unset($args['paged']);
	unset($args['p']);
	unset($args['page']);
	unset($args['pagename']); // this is neccessary in case Posts Page is set to a static page

	$query = new WP_Query($args);




	$idx = 0;
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$idx = $idx + 1;
			if($idx < $posts_per_page + 1){
				if((strpos($template, 'plugins') !== false)){
					include($template);
				}else{
					get_template_part( $template, get_post_format() );
				}
			}
		}

		if($query->post_count <= $posts_per_page){
			// there are no more posts
			// print a flag to detect
			echo '<div class="invi no-posts"><!-- --></div>';
		}
	} else {
		// no posts found
	}

	/* Restore original Post Data */
	wp_reset_postdata();

	die('');
}

/* Ajax load next posts in single post */
add_action( 'wp_ajax_scroll_next_post', 'videopro_ajax_scroll_next_post' );
add_action( 'wp_ajax_nopriv_scroll_next_post', 'videopro_ajax_scroll_next_post' );

function videopro_ajax_scroll_next_post()
{
	global $is_auto_load_next_post;
	$timestamp 		= isset($_POST[sanitize_key('timestamp')]) ? $_POST[sanitize_key('timestamp')] : '' ;
	$post_id 		= isset($_POST[sanitize_key('id')]) ? $_POST[sanitize_key('id')] : 0;
	$data_count 	= isset($_POST[sanitize_key('data_count')]) ? $_POST[sanitize_key('data_count')] : 0;
	$is_auto_load_next_post 	= isset($_POST[sanitize_key('is_auto_load_next_post')]) ? $_POST[sanitize_key('is_auto_load_next_post')] : 0;
	if($timestamp != ''){
		$order = ot_get_option('single_post_scroll_next_order'); // before or after
		$condition = ot_get_option('single_post_scroll_next_condition');

		$args = array('posts_per_page'   => 1,'date_query' => array(
				  array(
						$order => date('Y-m-d H:i:s',$timestamp),
					)
					)
					,'post_status' => 'publish');

		if($condition == 'category' || $condition == 'custom-cats'){
			if($condition == 'custom-cats') {
				$cats = ot_get_option('single_post_scroll_next_custom_values');
			} else {
				$post_categories = wp_get_object_terms($post_id,'category');
				$cats = array();
				foreach($post_categories as $cat){
					$cats[] = $cat->slug;
				}
				$cats = implode(',',$cats);
			}

			$args = array_merge($args,array('category_name'=>$cats));
		} else if($condition == 'tag' || $condition == 'custom-tags'){
			if($condition == 'custom-tags') {
				$tags = ot_get_option('single_post_scroll_next_custom_values');
			} else {
				$post_tags = get_the_tags($post_id);
				$tags = array();
				foreach($post_tags as $tag){
					$tags[] = $tag->name;
				}
				$tags = implode(',',$tags);
			}


			$args = array_merge($args,array('tag'=> $tags));
		}

		$the_query = new WP_Query($args);

		$index = 0;

		global $ajax_layout;
		$ajax_layout = 2;
		global $post;
		global $withcomments;
		$withcomments = true;

		while($the_query->have_posts()) : $the_query->the_post();
				// ignore current post
				$data_count = $data_count + 1;
				if($data_count <= 6)
				{
					$ajax_post_id = get_the_ID();

					if($data_count < 6)
					{
						global $post_video_layout;
						$post_video_layout = '1';
						global $thumb_url;
						$thumbnail_id = get_post_thumbnail_id( get_the_ID() );
						$thumb_url = wp_get_attachment_url( $thumbnail_id );

						echo "<article class='cactus-single-content ajaxed' data-url='".get_permalink()."' data-timestamp='".get_post_time('U')."' data-count='" . $data_count . "'>";

						get_template_part( 'html/single/content', get_post_format() );

						get_template_part( 'html/single/single', 'related' );

						if ( comments_open() || '0' != get_comments_number() ) :
							comments_template();
						endif;
						echo "</article>";
					}

					if($data_count == 6)
					{
						echo '<div class="page-navigation">
								<nav class="navigation-ajax">
									<div class="wp-pagenavi">
										<a class="load-more btn btn-default font-1" id="navigation-ajax" href="' . esc_url(get_permalink($ajax_post_id)) . '">
											<div class="load-title">' . esc_html__('Load More','videopro') . '</div>
										</a>
									</div>
								</nav>
							</div>';
					}
				}

		endwhile;

		wp_reset_postdata();
	}
	die('');
}

/* Functions, Hooks, Filters and Registers in Admin */
require_once get_template_directory() . '/inc/functions-admin.php';

if(!class_exists( 'OT_Loader' ) && !class_exists( 'trueMagRating' ))
{
	if ( ! function_exists( 'ot_get_option' ) )
	{
		function ot_get_option($id, $default_value=null)
		{
			return $default_value;
		}
	}

	if ( ! function_exists( 'ot_settings_id' ) )
	{
		function ot_settings_id()
		{
			return null;
		}
	}

	if ( ! function_exists( 'ot_register_meta_box' ) )
	{
		function ot_register_meta_box()
		{
			return null;
		}
	}
}

if(!class_exists('CactusThemeShortcodes'))
{
	if ( ! function_exists( 'videopro_display_ads' ) )
	{
		function videopro_display_ads()
		{
			return null;
		}
	}
}

function videopro_show_cat($show_once = false, $class=false,$unlink=false){
	$category = get_the_category();
	$ct_class = $link = '';
	if(isset($class) && $class !=''){
		$ct_class = $class;
	}
	if(isset($unlink) && $unlink !=''){
		$link = $unlink;
	}
	if(!empty($category)){
		foreach($category as $cat_item){
			echo videopro_get_category($cat_item,$ct_class,$link);
			if($show_once==1){
				break;
			}
		}?>
	<?php
	}
}

function videopro_enable_extended_upload ( $mime_types =array() ) {
 
   // The MIME types listed here will be allowed in the media library.
   // You can add as many MIME types as you want.
   $mime_types['woff2']  = 'application/font-woff2';
   $mime_types['woff']  = 'application/x-font-woff';
 
   // If you want to forbid specific file types which are otherwise allowed,
   // specify them here.  You can add as many as possible.
   unset( $mime_types['exe'] );
   unset( $mime_types['bin'] );
 
   return $mime_types;
}
add_filter('upload_mimes', 'videopro_enable_extended_upload');

add_action('after_switch_theme', 'videopro_after_activated');

function videopro_after_activated () {}

if(!function_exists('videopro_wpcf7_ajax_loader')){
	function videopro_wpcf7_ajax_loader() {
		return apply_filters('videopro_contact_form_loader_url', get_template_directory_uri() . '/images/ajax-loader.gif');
	}

	if(function_exists('wpcf7_ajax_loader')){
		add_filter('wpcf7_ajax_loader', 'videopro_wpcf7_ajax_loader');		
	}	
}

if(!function_exists('remove_pages_from_search')){
	function remove_pages_from_search() {
		if(ot_get_option('search_exclude_page')!='off'){
			global $wp_post_types;
			$wp_post_types['page']->exclude_from_search = true;
		}
	}
}

add_action('init', 'remove_pages_from_search');

if(!function_exists('videopro_get_post_viewlikeduration')){
	function videopro_get_post_viewlikeduration($id){
		$isWTIinstalled = function_exists('GetWtiLikeCount') ? 1 : 0;
		$isTop10PluginInstalled = function_exists('get_tptn_post_count_only') ? 1 : 0;
		$like       = ($isWTIinstalled ? str_replace("+", "", GetWtiLikeCount($id)) : 0);
		$unlike     = ($isWTIinstalled ? str_replace("-", "", GetWtiUnlikeCount($id)) : 0);
		$viewed     = ($isTop10PluginInstalled ?  get_tptn_post_count_only( $id ) : 0);
		$time_video =  videopro_secondsToTime(get_post_meta($id,'time_video',true));

		return apply_filters('videopro_get_post_viewlikeduration', array('time_video' => $time_video, 'like' => $like, 'unlike' => $unlike, 'viewed' => $viewed), $id);
	}
}

// remove number format of Top 10 Count as we need integer value
add_filter('tptn_post_count_only', 'tptn_post_count_remove_format', 10, 1);
function tptn_post_count_remove_format($count){
	return preg_replace('%\D%','',$count);
}

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

//live comment
add_action( 'init', 'ajax_get_comment' );

function ajax_get_comment(){
	if(isset($_GET['ct_comment_wpnonce']) && wp_verify_nonce($_GET['ct_comment_wpnonce'], 'idn' . $_GET['id'])&&!is_admin()){
		$arr = array(
			'post_id' => $_GET['id'],
			'comment__not_in' => $_GET['idlist'],
			'number' => '-1',
			'date_query' => array(
				array(
					'year'  => date('Y', $_GET['dateim']),
					'month' => date('m', $_GET['dateim']),
					'day'   => date('d', $_GET['dateim']),
					'hour'   => date('h', $_GET['dateim']),
					'minute'   => date('i', $_GET['dateim']),
					'second'   => date('s', $_GET['dateim']),
					'compare'   => '>=',
				),
			),
		);
		$cm = get_comments($arr);
		wp_list_comments(array('style'=> 'ol','short_ping' => true,'avatar_size' => 50,),$cm);
		exit;
	}
}
//live comment
add_action( 'init', 'videopro_ajax_loadmore_comment' );

function videopro_ajax_loadmore_comment(){
	if(isset($_GET['cactus_load_cm']) && wp_verify_nonce($_GET['cactus_load_cm'], 'idn' . $_GET['id'])&&!is_admin()){
		$comments_per_page = get_option( 'comments_per_page' );
		$arr = array(
			'comment__not_in' => $_GET['idlist'],
			'post_id' => $_GET['id'],
			'order' => 'DESC',
			'number' => $comments_per_page,
			'offset' => ( ( ( (int)$_GET['page'] ) - 1 ) * ((int)$comments_per_page) ),
		);
		$cm = get_comments($arr);		
		wp_list_comments(array('style'=> 'ol','short_ping' => true,'avatar_size' => 50,),$cm);
		exit;
	}
}
add_action('init', 'videopro_change_author_base');
function videopro_change_author_base(){
	$GLOBALS['wp_rewrite']->author_base = ot_get_option('author_base_slug', 'uploader');
}

/**
 * woocommerce support
 */
 add_filter('woocommerce_show_page_title', 'videopro_woocommerce_show_page_title');
 function videopro_woocommerce_show_page_title(){
     return false;
 }
