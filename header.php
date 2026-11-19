<?php
/**
 * Theme header.
 *
 * @package BrightNest_Cleaning
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="bn-skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'brightnest-cleaning' ); ?></a>
<div class="bn-topbar">
	<div class="bn-container bn-topbar-inner">
		<span><?php echo esc_html( get_theme_mod( 'brightnest_area', __( 'Friendly, reliable cleaning in your neighborhood', 'brightnest-cleaning' ) ) ); ?></span>
		<?php $phone = get_theme_mod( 'brightnest_phone', '' ); ?>
		<?php if ( $phone ) : ?>
			<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
		<?php else : ?>
			<span><?php esc_html_e( 'A little more calm. A lot more clean.', 'brightnest-cleaning' ); ?></span>
		<?php endif; ?>
	</div>
</div>
<header class="bn-header">
	<div class="bn-container bn-header-inner">
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a class="bn-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="bn-brand-mark" aria-hidden="true"><span>✦</span></span>
				<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			</a>
		<?php endif; ?>
		<button class="bn-menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-label-open="<?php esc_attr_e( 'Open menu', 'brightnest-cleaning' ); ?>" data-label-close="<?php esc_attr_e( 'Close menu', 'brightnest-cleaning' ); ?>" aria-label="<?php esc_attr_e( 'Open menu', 'brightnest-cleaning' ); ?>">☰</button>
		<nav id="primary-navigation" class="bn-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'brightnest-cleaning' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'primary-menu',
					'fallback_cb'    => 'brightnest_page_menu_fallback',
					'depth'          => 1,
				)
			)
			?>
		</nav>
	</div>
</header>
