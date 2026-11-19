<?php
/**
 * BrightNest Cleaning theme functions.
 *
 * @package BrightNest_Cleaning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function brightnest_setup() {
	load_theme_textdomain( 'brightnest-cleaning', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 260, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_editor_style( 'style.css' );
	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'brightnest-cleaning' ),
			'footer'  => __( 'Footer menu', 'brightnest-cleaning' ),
		)
	);
}
add_action( 'after_setup_theme', 'brightnest_setup' );

function brightnest_default_site_icon() {
	if ( has_site_icon() ) {
		return;
	}

	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">',
		esc_url( get_template_directory_uri() . '/assets/images/favicon.svg' )
	);
}
add_action( 'wp_head', 'brightnest_default_site_icon', 1 );

function brightnest_assets() {
	$parent_theme = wp_get_theme( get_template() );
	wp_enqueue_style( 'brightnest-style', get_template_directory_uri() . '/style.css', array(), $parent_theme->get( 'Version' ) );
	if ( is_child_theme() ) {
		wp_enqueue_style( 'brightnest-child-style', get_stylesheet_uri(), array( 'brightnest-style' ), wp_get_theme()->get( 'Version' ) );
	}
	wp_enqueue_script( 'brightnest-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), $parent_theme->get( 'Version' ), true );
	wp_enqueue_script( 'brightnest-quote-form', get_template_directory_uri() . '/assets/js/quote-form.js', array(), $parent_theme->get( 'Version' ), true );
}
add_action( 'wp_enqueue_scripts', 'brightnest_assets' );

function brightnest_page_url( $slug ) {
	$page = get_page_by_path( sanitize_title( $slug ), OBJECT, 'page' );
	return $page ? get_permalink( $page ) : home_url( trailingslashit( sanitize_title( $slug ) ) );
}

function brightnest_page_menu_fallback() {
	wp_page_menu(
		array(
			'container'  => false,
			'menu_class' => 'menu',
			'show_home'  => true,
			'depth'      => 1,
		)
	);
}

function brightnest_footer_fallback() {
	brightnest_page_menu_fallback();
}

function brightnest_render_lead_form_shortcode( $atts = array() ) {
	$atts           = shortcode_atts( array( 'mode' => '' ), $atts, 'brightnest_lead_form' );
	$page_template  = get_page_template_slug( get_queried_object_id() );
	$is_quote_form  = 'quote' === sanitize_key( $atts['mode'] ) || 'page-templates/quote.php' === $page_template;
	ob_start();
	if ( $is_quote_form ) {
		echo '<div class="bn-quote-experience">';
		echo do_shortcode( '[brightnest_packages]' );
	}
	get_template_part( 'template-parts/lead-form', null, array( 'is_quote_form' => $is_quote_form ) );
	if ( $is_quote_form ) {
		echo '</div>';
	}
	return ob_get_clean();
}
add_shortcode( 'brightnest_lead_form', 'brightnest_render_lead_form_shortcode' );

function brightnest_render_cleaning_packages_shortcode() {
	if ( ! post_type_exists( 'bn_cleaning_package' ) ) {
		return '<p class="bn-notice bn-notice--error" role="status">' . esc_html__( 'Activate BrightNest Cleaning Core to manage and display cleaning packages.', 'brightnest-cleaning' ) . '</p>';
	}
	$quote_settings = brightnest_core_quote_settings();

	$packages = new WP_Query(
		array(
			'post_type'              => 'bn_cleaning_package',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		)
	);
	ob_start();
	?>
	<section class="bn-pricing-section bn-section bn-section--soft" aria-labelledby="bn-pricing-title">
		<div class="bn-container">
			<div class="bn-section-heading">
				<p class="bn-eyebrow"><?php echo esc_html( $quote_settings['catalog_eyebrow'] ); ?></p>
				<h2 id="bn-pricing-title"><?php echo esc_html( $quote_settings['catalog_title'] ); ?></h2>
				<p><?php echo esc_html( $quote_settings['catalog_description'] ); ?></p>
			</div>
			<div class="bn-package-grid">
				<?php if ( $packages->have_posts() ) : ?>
					<?php while ( $packages->have_posts() ) : ?>
						<?php
						$packages->the_post();
						$package_id = get_the_ID();
						if ( ! brightnest_core_cleaning_item_is_enabled( $package_id, '_bn_package_' ) ) {
							continue;
						}
						$price_type  = get_post_meta( $package_id, '_bn_package_price_type', true );
						$price_amount = get_post_meta( $package_id, '_bn_package_price_amount', true );
						$popular_label = get_post_meta( $package_id, '_bn_package_popular_label', true );
						$services    = get_the_terms( $package_id, 'bn_package_service' );
						$quote_url   = add_query_arg( 'package', get_post_field( 'post_name', $package_id ), brightnest_page_url( 'get-a-quote' ) ) . '#quote-form';
						?>
						<article class="bn-package-card<?php echo $popular_label ? ' bn-package-card--popular' : ''; ?>">
							<?php if ( $popular_label ) : ?><p class="bn-package-badge"><?php echo esc_html( $popular_label ); ?></p><?php endif; ?>
							<?php if ( has_post_thumbnail() ) : ?><div class="bn-package-image"><?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?></div><?php endif; ?>
							<div class="bn-package-card-body">
								<h3><?php echo esc_html( get_the_title( $package_id ) ); ?></h3>
								<p class="bn-package-description"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_content() ), 24 ) ); ?></p>
								<p class="bn-package-price"><?php echo 'fixed' === $price_type && is_numeric( $price_amount ) ? esc_html( $quote_settings['currency_symbol'] . number_format_i18n( (float) $price_amount, 2 ) ) : esc_html( $quote_settings['custom_price_label'] ); ?></p>
								<?php if ( is_array( $services ) && $services ) : ?>
									<ul class="bn-package-features">
										<?php foreach ( $services as $service ) : ?><li><?php echo esc_html( $service->name ); ?></li><?php endforeach; ?>
									</ul>
								<?php endif; ?>
								<a class="bn-button bn-package-cta" href="<?php echo esc_url( $quote_url ); ?>"><?php echo esc_html( $quote_settings['package_button'] ); ?><span class="bn-arrow" aria-hidden="true">→</span></a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<p class="bn-notice" role="status"><?php esc_html_e( 'No cleaning packages are published yet. Add a package in the WordPress dashboard to display it here.', 'brightnest-cleaning' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'brightnest_packages', 'brightnest_render_cleaning_packages_shortcode' );

function brightnest_render_testimonials_shortcode() {
	if ( ! post_type_exists( 'bn_testimonial' ) ) {
		return '<p class="bn-empty-testimonials">' . esc_html__( 'Activate BrightNest Cleaning Core to manage and display your testimonials.', 'brightnest-cleaning' ) . '</p>';
	}

	$testimonials = new WP_Query(
		array(
			'post_type'              => 'bn_testimonial',
			'post_status'            => 'publish',
			'posts_per_page'         => 3,
			'orderby'                => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	ob_start();
	?>
	<div class="bn-testimonials">
		<?php if ( $testimonials->have_posts() ) : ?>
			<?php while ( $testimonials->have_posts() ) : ?>
				<?php $testimonials->the_post(); ?>
				<article class="bn-testimonial">
					<div class="bn-testimonial-stars" aria-label="<?php esc_attr_e( 'Five stars', 'brightnest-cleaning' ); ?>">★★★★★</div>
					<div class="bn-testimonial-quote"><?php the_content(); ?></div>
					<p class="bn-testimonial-by"><?php echo esc_html( get_the_title() ); ?></p>
				</article>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p class="bn-empty-testimonials"><?php esc_html_e( 'Add verified customer feedback under Testimonials in the WordPress dashboard.', 'brightnest-cleaning' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'brightnest_testimonials', 'brightnest_render_testimonials_shortcode' );

function brightnest_render_business_details_shortcode() {
	$phone = get_theme_mod( 'brightnest_phone', '' );
	$email = get_theme_mod( 'brightnest_email', get_option( 'admin_email' ) );
	$area  = get_theme_mod( 'brightnest_area', '' );
	$hours = get_theme_mod( 'brightnest_hours', '' );
	ob_start();
	?>
	<div class="bn-contact-cards">
		<?php if ( $phone ) : ?>
			<article class="bn-contact-card"><p><?php esc_html_e( 'Call us', 'brightnest-cleaning' ); ?></p><strong><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></strong></article>
		<?php endif; ?>
		<?php if ( is_email( $email ) ) : ?>
			<article class="bn-contact-card"><p><?php esc_html_e( 'Email us', 'brightnest-cleaning' ); ?></p><strong><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></strong></article>
		<?php endif; ?>
		<?php if ( $area || $hours ) : ?>
			<article class="bn-contact-card"><p><?php esc_html_e( 'Service details', 'brightnest-cleaning' ); ?></p><strong><?php echo esc_html( $area ? $area : __( 'Our local service area', 'brightnest-cleaning' ) ); ?></strong><?php if ( $hours ) : ?><p><?php echo esc_html( $hours ); ?></p><?php endif; ?></article>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'brightnest_business_details', 'brightnest_render_business_details_shortcode' );

function brightnest_normalize_internal_links( $content ) {
	foreach ( array( 'get-a-quote', 'residential-cleaning', 'commercial-cleaning', 'contact' ) as $page_slug ) {
		$content = str_replace(
			'href="/' . $page_slug . '/"',
			'href="' . esc_url( home_url( '/' . $page_slug . '/' ) ) . '"',
			$content
		);
	}

	return $content;
}

function brightnest_create_starter_menus( $pages ) {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$menus     = array(
		'primary' => array(
			'name'  => 'BrightNest Main Menu',
			'pages' => array( 'home', 'residential-cleaning', 'commercial-cleaning', 'get-a-quote', 'contact' ),
		),
		'footer'  => array(
			'name'  => 'BrightNest Footer Menu',
			'pages' => array( 'residential-cleaning', 'commercial-cleaning', 'get-a-quote', 'contact' ),
		),
	);
	$failed = false;

	foreach ( $menus as $location => $menu_details ) {
		if ( ! empty( $locations[ $location ] ) ) {
			continue;
		}

		$menu_slug = sanitize_title( $menu_details['name'] );
		$menu      = wp_get_nav_menu_object( $menu_slug );
		$menu_id   = $menu ? (int) $menu->term_id : wp_create_nav_menu( $menu_details['name'] );
		if ( is_wp_error( $menu_id ) ) {
			$failed = true;
			continue;
		}

		if ( ! $menu || ! wp_get_nav_menu_items( $menu_id ) ) {
			$position = 1;
			foreach ( $menu_details['pages'] as $page_slug ) {
				if ( empty( $pages[ $page_slug ] ) ) {
					continue;
				}
				$item_id = wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-object-id' => $pages[ $page_slug ],
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $position,
					)
				);
				if ( is_wp_error( $item_id ) ) {
					$failed = true;
				}
				++$position;
			}
		}

		$locations[ $location ] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
	return ! $failed;
}

function brightnest_add_laundry_navigation_item() {
	if ( get_option( 'brightnest_laundry_navigation_item_added' ) ) {
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) || empty( $locations['primary'] ) ) {
		return;
	}

	$menu = wp_get_nav_menu_object( $locations['primary'] );
	if ( ! $menu || 'BrightNest Main Menu' !== $menu->name ) {
		return;
	}

	$quote_url = add_query_arg( 'addon', 'laundry-service', brightnest_page_url( 'get-a-quote' ) ) . '#quote-form';
	$items     = wp_get_nav_menu_items( $menu->term_id );
	if ( is_array( $items ) ) {
		foreach ( $items as $item ) {
			if ( $quote_url === $item->url ) {
				update_option( 'brightnest_laundry_navigation_item_added', 1 );
				return;
			}
		}
	}

	$item_id = wp_update_nav_menu_item(
		$menu->term_id,
		0,
		array(
			'menu-item-title'     => __( 'Laundry Service', 'brightnest-cleaning' ),
			'menu-item-url'       => $quote_url,
			'menu-item-type'      => 'custom',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => is_array( $items ) ? count( $items ) + 1 : 1,
		)
	);

	if ( ! is_wp_error( $item_id ) ) {
		update_option( 'brightnest_laundry_navigation_item_added', 1 );
	}
}
add_action( 'init', 'brightnest_add_laundry_navigation_item', 30 );

function brightnest_starter_page_content( $slug ) {
	$pages = array(
		'home'                 => <<<'BLOCKS'
<!-- wp:group {"tagName":"section","className":"bn-hero","layout":{"type":"default"}} -->
<section class="wp-block-group bn-hero"><!-- wp:columns {"className":"bn-container bn-hero-inner","verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center bn-container bn-hero-inner"><!-- wp:column {"width":"55%","className":"bn-hero-copy"} -->
<div class="wp-block-column bn-hero-copy" style="flex-basis:55%"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Come home to a little more calm</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">A fresh start, <em>every day.</em></h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A thoughtfully clean home or workplace makes room for what matters. Let our friendly local team take care of the rest.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"bn-button"} -->
<div class="wp-block-button bn-button"><a class="wp-block-button__link wp-element-button" href="/get-a-quote/">Get your free quote <span aria-hidden="true">→</span></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"bn-button bn-button--outline"} -->
<div class="wp-block-button bn-button bn-button--outline"><a class="wp-block-button__link wp-element-button" href="#services">Explore our services</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"bn-proof"} -->
<p class="bn-proof"><span class="bn-proof-stars" aria-label="Five stars">★★★★★</span> Care you can feel in every room.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"45%"} -->
<div class="wp-block-column" style="flex-basis:45%"><!-- wp:group {"className":"bn-hero-art","layout":{"type":"default"}} -->
<div class="wp-block-group bn-hero-art"><!-- wp:group {"className":"bn-art-window","layout":{"type":"default"}} -->
<div class="wp-block-group bn-art-window"></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bn-art-card","layout":{"type":"default"}} -->
<div class="wp-block-group bn-art-card"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Your space, refreshed.</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>More time for the good stuff.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section","layout":{"type":"default"}} -->
<section id="services" class="wp-block-group bn-section"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group bn-section-heading"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">A clean for every kind of day</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Your place deserves a little extra care.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>From the everyday reset to a whole-workspace refresh, we make it simple to get the clean you need.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"bn-service-grid"} -->
<div class="wp-block-columns bn-service-grid"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-service-card","layout":{"type":"default"}} -->
<div class="wp-block-group bn-service-card"><!-- wp:paragraph {"className":"bn-card-icon"} -->
<p class="bn-card-icon">⌂</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Residential cleaning</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Reliable recurring, deep, and move-in or move-out cleaning for the place you call home.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bn-text-link"} -->
<p class="bn-text-link"><a href="/residential-cleaning/">Explore home cleaning →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-service-card","layout":{"type":"default"}} -->
<div class="wp-block-group bn-service-card"><!-- wp:paragraph {"className":"bn-card-icon"} -->
<p class="bn-card-icon">▦</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Commercial cleaning</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A welcoming, cared-for workplace with a cleaning plan that fits your business and schedule.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"bn-text-link"} -->
<p class="bn-text-link"><a href="/commercial-cleaning/">Explore workplace cleaning →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section bn-section--soft","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section bn-section--soft"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group bn-section-heading"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">The BrightNest difference</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Good people. Thoughtful details. A better kind of clean.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"bn-feature-grid"} -->
<div class="wp-block-columns bn-feature-grid"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-feature","layout":{"type":"default"}} -->
<div class="wp-block-group bn-feature"><!-- wp:paragraph {"className":"bn-feature-mark"} -->
<p class="bn-feature-mark">01</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Care in every corner</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A considered checklist and a team that treats your space with respect.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-feature","layout":{"type":"default"}} -->
<div class="wp-block-group bn-feature"><!-- wp:paragraph {"className":"bn-feature-mark"} -->
<p class="bn-feature-mark">02</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">A schedule that works</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Choose a one-time clean or a routine that takes one more thing off your list.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-feature","layout":{"type":"default"}} -->
<div class="wp-block-group bn-feature"><!-- wp:paragraph {"className":"bn-feature-mark"} -->
<p class="bn-feature-mark">03</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Clear, friendly service</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A simple quote, helpful communication, and no guesswork about what comes next.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group bn-section-heading"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Simple from the start</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Three little steps to a brighter space.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"bn-steps"} -->
<div class="wp-block-columns bn-steps"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-step","layout":{"type":"default"}} -->
<div class="wp-block-group bn-step"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Tell us what you need</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Share a few details about your home or workplace and the kind of clean you have in mind.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-step","layout":{"type":"default"}} -->
<div class="wp-block-group bn-step"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Get a clear quote</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Our team will follow up to learn a little more and recommend a plan that fits.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-step","layout":{"type":"default"}} -->
<div class="wp-block-group bn-step"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Enjoy your fresh space</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We take care of the details, so you can get back to what matters to you.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section bn-section--soft","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section bn-section--soft"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group bn-section-heading"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Kind words, close to home</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Our favorite part is making your day.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[brightnest_testimonials]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-cta","layout":{"type":"default"}} -->
<div class="wp-block-group bn-cta"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Ready to make room for more?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Tell us a little about your space. We’ll be in touch with a friendly, no-pressure quote.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"bn-button"} -->
<div class="wp-block-button bn-button"><a class="wp-block-button__link wp-element-button" href="/get-a-quote/">Request your quote <span aria-hidden="true">→</span></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
BLOCKS,
		'residential-cleaning' => <<<'BLOCKS'
<!-- wp:group {"tagName":"section","className":"bn-page-hero","layout":{"type":"default"}} -->
<section class="wp-block-group bn-page-hero"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Residential cleaning</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">More time to feel at home.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A dependable clean, shaped around your home, your schedule, and the details that matter most to you.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section"><!-- wp:columns {"className":"bn-container bn-detail-grid"} -->
<div class="wp-block-columns bn-container bn-detail-grid"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">A little help goes a long way</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">A home that feels like a deep breath.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>From regular upkeep to the bigger seasonal reset, our residential services help make your home feel fresh and cared for. Tell us what matters most and we’ll build a plan around it.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"bn-detail-list"} -->
<ul class="wp-block-list bn-detail-list"><!-- wp:list-item -->
<li>Recurring weekly, biweekly, or monthly visits</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>One-time and detailed deep cleaning</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Move-in and move-out cleaning</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>A plan tailored to your home and priorities</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-form-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group bn-form-wrap"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Let’s find your fit</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Tell us about your home.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Request a free quote and our team will follow up to learn more.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[brightnest_lead_form]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section bn-section--soft","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section bn-section--soft"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group bn-section-heading"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">A few common questions</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Good to know before we begin.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Can I choose what gets cleaned?</summary><!-- wp:paragraph -->
<p>Absolutely. Share your priorities when you request a quote and we’ll discuss a checklist that fits your home.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Do you offer one-time and recurring visits?</summary><!-- wp:paragraph -->
<p>Yes. Let us know whether you are looking for a single visit or an ongoing schedule and we’ll help find the right option.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>How do I get a price?</summary><!-- wp:paragraph -->
<p>Every home is different. Send us a few details and our team will follow up with a quote tailored to your space.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
BLOCKS,
		'commercial-cleaning'  => <<<'BLOCKS'
<!-- wp:group {"tagName":"section","className":"bn-page-hero","layout":{"type":"default"}} -->
<section class="wp-block-group bn-page-hero"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Commercial cleaning</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">A workspace ready for what’s next.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A clean, comfortable workplace helps your team and guests feel welcome. We’ll shape a dependable plan around your space and schedule.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section"><!-- wp:columns {"className":"bn-container bn-detail-grid"} -->
<div class="wp-block-columns bn-container bn-detail-grid"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Thoughtful care for your workplace</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">A cleaning plan that works around your business.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>From shared spaces to everyday touchpoints, our team helps keep your workplace cared for without adding work to your day. Tell us about your facility, priorities, and preferred service times.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"bn-detail-list"} -->
<ul class="wp-block-list bn-detail-list"><!-- wp:list-item -->
<li>Office and professional spaces</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Retail and customer-facing areas</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Flexible recurring schedules</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>A scope of work tailored to your facility</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-form-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group bn-form-wrap"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Let’s talk about your space</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Request a workplace quote.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Share a few details and our team will be in touch.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[brightnest_lead_form]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section bn-section--soft","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section bn-section--soft"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-section-heading","layout":{"type":"default"}} -->
<div class="wp-block-group bn-section-heading"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">Made for your schedule</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Reliable cleaning, with clear communication.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We’ll learn what your business needs, agree on a clear scope, and coordinate service times that work for your team.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"bn-button"} -->
<div class="wp-block-button bn-button"><a class="wp-block-button__link wp-element-button" href="/get-a-quote/">Request a free quote <span aria-hidden="true">→</span></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
BLOCKS,
		'get-a-quote'          => <<<'BLOCKS'
<!-- wp:group {"tagName":"section","className":"bn-page-hero","layout":{"type":"default"}} -->
<section class="wp-block-group bn-page-hero"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">A fresh space starts here</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Tell us what a clean looks like for you.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Share a few details about your home or workplace and we’ll follow up with a friendly, no-pressure quote.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:group {"className":"bn-form-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group bn-form-wrap"><!-- wp:heading -->
<h2 class="wp-block-heading">Request your free quote</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[brightnest_lead_form]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
BLOCKS,
		'contact'              => <<<'BLOCKS'
<!-- wp:group {"tagName":"section","className":"bn-page-hero","layout":{"type":"default"}} -->
<section class="wp-block-group bn-page-hero"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:paragraph {"className":"bn-eyebrow"} -->
<p class="bn-eyebrow">We’re happy to help</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Let’s talk about a brighter day.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ask us a question or tell us what you need cleaned. Our local team will be glad to hear from you.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"bn-section","layout":{"type":"default"}} -->
<section class="wp-block-group bn-section"><!-- wp:group {"className":"bn-container","layout":{"type":"default"}} -->
<div class="wp-block-group bn-container"><!-- wp:columns {"className":"bn-contact-cards"} -->
<div class="wp-block-columns bn-contact-cards"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-contact-card","layout":{"type":"default"}} -->
<div class="wp-block-group bn-contact-card"><!-- wp:paragraph -->
<p>Call us</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Our phone, email, service area, and business hours.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-contact-card","layout":{"type":"default"}} -->
<div class="wp-block-group bn-contact-card"><!-- wp:paragraph -->
<p>Email us</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Get in touch with our friendly local team.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"bn-contact-card","layout":{"type":"default"}} -->
<div class="wp-block-group bn-contact-card"><!-- wp:paragraph -->
<p>Service area and hours</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>We’re here to answer your questions and help you get started.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:shortcode -->
[brightnest_business_details]
<!-- /wp:shortcode -->

<!-- wp:group {"className":"bn-form-wrap","layout":{"type":"default"}} -->
<div class="wp-block-group bn-form-wrap"><!-- wp:heading -->
<h2 class="wp-block-heading">Send us a message</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[brightnest_lead_form]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
BLOCKS,
	);

	if ( ! isset( $pages[ $slug ] ) ) {
		return '';
	}

	return brightnest_normalize_internal_links( $pages[ $slug ] );
}

function brightnest_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'brightnest_business',
		array(
			'title'       => __( 'Cleaning business details', 'brightnest-cleaning' ),
			'description' => __( 'Used in the header, footer, and local business structured data.', 'brightnest-cleaning' ),
			'priority'    => 35,
		)
	);
	$fields = array(
		'brightnest_phone' => array( __( 'Business phone', 'brightnest-cleaning' ), 'sanitize_text_field' ),
		'brightnest_email' => array( __( 'Business email', 'brightnest-cleaning' ), 'sanitize_email' ),
		'brightnest_area'  => array( __( 'Service area', 'brightnest-cleaning' ), 'sanitize_text_field' ),
		'brightnest_hours' => array( __( 'Business hours', 'brightnest-cleaning' ), 'sanitize_text_field' ),
	);
	foreach ( $fields as $setting => $field ) {
		$wp_customize->add_setting(
			$setting,
			array(
				'default'           => '',
				'sanitize_callback' => $field[1],
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$setting,
			array(
				'label'   => $field[0],
				'section' => 'brightnest_business',
				'type'    => 'text',
			)
		);
	}
}
add_action( 'customize_register', 'brightnest_customizer' );

function brightnest_local_business_schema() {
	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'LocalBusiness',
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'description' => get_bloginfo( 'description' ),
	);
	$phone = get_theme_mod( 'brightnest_phone', '' );
	$email = get_theme_mod( 'brightnest_email', '' );
	$area  = get_theme_mod( 'brightnest_area', '' );
	if ( $phone ) {
		$schema['telephone'] = $phone;
	}
	if ( $email && is_email( $email ) ) {
		$schema['email'] = $email;
	}
	if ( $area ) {
		$schema['areaServed'] = $area;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'brightnest_local_business_schema', 30 );

function brightnest_create_starter_pages() {
	$pages = array(
		'home'                 => array( 'Home', 'page.php' ),
		'residential-cleaning' => array( 'Residential Cleaning', 'page-templates/residential.php' ),
		'commercial-cleaning'  => array( 'Commercial Cleaning', 'page-templates/commercial.php' ),
		'get-a-quote'          => array( 'Get a Quote', 'page-templates/quote.php' ),
		'contact'              => array( 'Contact', 'page-templates/contact.php' ),
	);
	$created = array();
	$failed  = false;
	foreach ( $pages as $slug => $details ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$created[ $slug ] = $existing->ID;
			$existing_content = '' === trim( $existing->post_content )
				? brightnest_starter_page_content( $slug )
				: brightnest_normalize_internal_links( $existing->post_content );
			if ( $existing_content !== $existing->post_content ) {
				$updated_page = wp_update_post(
					array(
						'ID'           => $existing->ID,
						'post_content' => $existing_content,
					),
					true
				);
				if ( is_wp_error( $updated_page ) ) {
					$failed = true;
				}
			}
			continue;
		}
		$page_id = wp_insert_post(
			array(
				'post_title'  => $details[0],
				'post_name'   => $slug,
				'post_content' => brightnest_starter_page_content( $slug ),
				'post_status' => 'publish',
				'post_type'   => 'page',
			),
			true
		);
		if ( ! is_wp_error( $page_id ) ) {
			$created[ $slug ] = $page_id;
			if ( 'page.php' !== $details[1] ) {
				update_post_meta( $page_id, '_wp_page_template', $details[1] );
			}
		} else {
			$failed = true;
		}
	}
	if ( ! empty( $created['home'] ) && ! get_option( 'page_on_front' ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $created['home'] );
	}
	if ( ! brightnest_create_starter_menus( $created ) ) {
		$failed = true;
	}
	if ( ! $failed ) {
		update_option( 'brightnest_editor_content_version', '1.2.0' );
	}
}
add_action( 'after_switch_theme', 'brightnest_create_starter_pages' );

function brightnest_migrate_editor_content() {
	if ( '1.2.0' !== get_option( 'brightnest_editor_content_version' ) ) {
		brightnest_create_starter_pages();
	}
}
add_action( 'init', 'brightnest_migrate_editor_content', 20 );
