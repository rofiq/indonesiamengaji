<?php
get_header(); ?>
<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

		<?php
			if ( have_posts() ) :
				// Start the Loop.
				while ( have_posts() ) : the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<?php
						if(has_post_thumbnail()){
							
							$img_size = array(298, 298);
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
				
							$size = apply_filters('videopro_thumbnail_size_filter', $size, get_the_ID());
							
							$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), $size, true); ?>
							<div class="content-image"><img src="<?php echo $thumbnail[0] ?>" alt="<?php the_title_attribute(); ?>" title="<?php the_title_attribute(); ?>"></div>
						<?php } ?>
						<header class="entry-header">
							<?php
								the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
							?>
						</header><!-- .entry-header -->
						<div class="entry-summary">
							<?php the_excerpt(); ?>
						</div><!-- .entry-summary -->
					</article><!-- #post-## -->
					
					<?php
				endwhile;
			endif;
		?>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->
<?php
get_sidebar();
get_footer();
