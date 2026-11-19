<?php
/**
 * Theme footer.
 *
 * @package BrightNest_Cleaning
 */
?>
<footer class="bn-footer">
	<div class="bn-container">
		<div class="bn-footer-grid">
			<div>
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a class="bn-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<span class="bn-brand-mark" aria-hidden="true"><span>✦</span></span>
						<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( get_bloginfo( 'description' ) ) : ?><p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p><?php endif; ?>
				<?php $area = get_theme_mod( 'brightnest_area', '' ); ?>
				<?php if ( $area ) : ?><p><?php echo esc_html( $area ); ?></p><?php endif; ?>
			</div>
			<div>
				<h2><?php esc_html_e( 'Explore', 'brightnest-cleaning' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'fallback_cb'    => 'brightnest_footer_fallback',
						'depth'          => 1,
					)
				);
				?>
			</div>
			<div>
				<h2><?php esc_html_e( 'Get in touch', 'brightnest-cleaning' ); ?></h2>
				<ul>
					<?php $phone = get_theme_mod( 'brightnest_phone', '' ); ?>
					<?php $email = get_theme_mod( 'brightnest_email', get_option( 'admin_email' ) ); ?>
					<?php if ( $phone ) : ?><li><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li><?php endif; ?>
					<?php if ( is_email( $email ) ) : ?><li><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></li><?php endif; ?>
					<li><a href="<?php echo esc_url( brightnest_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'brightnest-cleaning' ); ?></a></li>
				</ul>
			</div>
		</div>
		<div class="bn-footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'brightnest-cleaning' ); ?></p>
			<p><?php esc_html_e( 'Made for a brighter, cleaner day.', 'brightnest-cleaning' ); ?></p>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
