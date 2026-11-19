<?php
/**
 * Default template.
 *
 * @package BrightNest_Cleaning
 */
get_header();
?>
<main id="main" class="bn-section">
	<div class="bn-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'bn-page-content' ); ?>>
					<h1><?php echo esc_html( get_the_title() ); ?></h1>
					<div><?php the_content(); ?></div>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="bn-not-found">
				<p class="bn-eyebrow"><?php esc_html_e( 'Nothing here yet', 'brightnest-cleaning' ); ?></p>
				<h1><?php esc_html_e( 'No posts found', 'brightnest-cleaning' ); ?></h1>
				<p><?php esc_html_e( 'Try a search or visit our cleaning service pages.', 'brightnest-cleaning' ); ?></p>
				<a class="bn-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'brightnest-cleaning' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
