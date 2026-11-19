<?php
/**
 * Homepage: content is managed in the WordPress block editor.
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
