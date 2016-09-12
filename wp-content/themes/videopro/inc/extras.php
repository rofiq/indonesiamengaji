<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package cactus
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function videopro_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'videopro_page_menu_args' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function videopro_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}
	$classStickyMenu = '';
	$stickyNavigation = ot_get_option('sticky_navigation', 'off');
	$stickyBehavoir = ot_get_option('sticky_up_down');
	if($stickyNavigation=='on') {
		$classes[] = 'enable-sticky-menu';
		if($stickyBehavoir=='down') {
			$classes[] =' behavior-down';
		}elseif($stickyBehavoir=='up'){
			$classes[] =' behavior-up';
		};
	};

	return $classes;
}
add_filter( 'body_class', 'videopro_body_classes' );
function videopro_post_classes( $classes ) {
	if( is_page()){ $classes[] = 'cactus-single-content'; }

	return $classes;
}
add_filter( 'post_class', 'videopro_post_classes' );
/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function videopro_setup_author() {
	global $wp_query;

	if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
		$GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
	}
}
add_action( 'wp', 'videopro_setup_author' );
