<?php
/**
 * Template Name: Commercial Cleaning
 * Template Post Type: page
 * Description: Displays the page content from the Gutenberg block editor.
 *
 * @package BrightNest_Cleaning
 */
get_header();
?>
<main id="main" class="bn-content">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
