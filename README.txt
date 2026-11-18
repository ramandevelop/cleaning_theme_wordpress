=== BrightNest Cleaning ===
Contributors: brightnest
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.6
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, responsive WordPress theme for residential and commercial cleaning businesses.

== Features ==

* Gutenberg-editable home, residential, commercial, quote request, and contact pages.
* Starter pages are created on theme activation and blank BrightNest starter pages are populated with editable blocks on update. Existing page content is preserved.
* Mobile navigation, keyboard focus styles, skip link, and reduced-motion support.
* Starter header and footer menus appear in Appearance > Menus and can be edited there.
* The default header menu includes a Laundry Service link to the quote form with the Laundry add-on preselected; edit or remove it under Appearance > Menus.
* Testimonials managed with Gutenberg in the WordPress dashboard.
* Business phone, email, service area, and hours settings in Appearance > Customize > Cleaning business details.
* Default favicon matching the BrightNest brand mark; a custom Site Icon set in WordPress takes precedence.
* LocalBusiness structured data based on the site and configured business details.
* Dashboard-managed cleaning packages and optional services, custom or fixed pricing, and a multi-step quote request (requires BrightNest Cleaning Core).
* No external font, script, image, or tracking requests.
* Child-theme support for update-safe customization.

== Installation ==

1. Install and activate the companion BrightNest Cleaning Core plugin ZIP. The plugin powers the dashboard-managed packages, quote settings, secure multi-step quote form, and testimonials.
2. In WordPress, go to Appearance > Themes > Add New Theme > Upload Theme.
3. Upload brightnest-cleaning.zip, install it, and activate BrightNest Cleaning. Keep the companion plugin active to use all package, quote, and testimonial features.
4. Activation creates Home, Residential Cleaning, Commercial Cleaning, Get a Quote, and Contact pages with native Gutenberg blocks. The Home page is assigned as the static front page, and starter header/footer menus are created when no menus are assigned. Existing non-empty page content and menu assignments are preserved.
5. Edit each page under Pages > All Pages. Use the block editor to update headings, text, lists, buttons, images, and sections. Quote, testimonial, and business-detail sections are inserted as shortcode blocks and remain editable/removable in the editor.
6. Open Appearance > Customize > Cleaning business details to enter your real phone, email, service area, and hours; set your site name, tagline, and logo under the normal WordPress site identity settings.
7. Edit the starter header and footer menus under Appearance > Menus, or assign your own menus to the Primary menu and Footer menu locations.
8. Manage cleaning packages, included services, prices, images, and display order under Cleaning Packages; manage optional services under Optional Add-ons. Use Cleaning Packages > Quote Settings for the currency, pricing label, catalog copy, form labels, and quote choice options.
9. Add verified customer feedback under Testimonials. No sample reviews are published.
10. Configure and test your host's mail delivery (or a trusted SMTP plugin) before relying on quote submissions.

== Quote and contact forms ==

BrightNest Cleaning Core handles form requests and testimonials separately from the theme. The forms validate and sanitize submitted values, verify a WordPress nonce, use a hidden spam-trap field, rate-limit valid submissions by a keyed hash of the connecting IP for 15 minutes, and send inquiries to the configured business email (or WordPress admin email). The rate limiter allows five valid submissions per IP per 15-minute window. Form messages are emailed, not stored in the database. Hosting must support outgoing email; configure SMTP separately when needed.

== Performance and privacy ==

The theme uses system fonts, a small local navigation script, and no third-party assets or trackers. It does not collect analytics. Contact-form data is sent by email and is not retained by the theme. Configure your own privacy notice and data-retention process.

== Local SEO ==

Set a descriptive site title and tagline under Settings > General. Add accurate business contact and service-area details in the Customizer. The theme emits basic LocalBusiness JSON-LD; it does not guarantee ranking or replace a dedicated SEO plugin or a complete business profile.

== Child theme ==

Install the separate brightnest-cleaning-child.zip only after installing the parent theme. Activate BrightNest Cleaning Child to keep custom CSS and template overrides separate from parent updates. The parent must remain installed.

== License and distribution ==

BrightNest Cleaning is distributed under GPL-2.0-or-later. Review all WordPress.org Theme Review requirements, validate your author identity and trademark rights, and submit the package through the official review process before describing it as WordPress.org-approved or listing it in the directory. This package has not been submitted or approved by WordPress.org.

== Changelog ==

= 1.3.6 =
* Add an editable Laundry Service item to the default primary menu and preselect the enabled laundry add-on on the quote form.

= 1.3.5 =
* Removed the duplicate quote link from the header; the dashboard-managed navigation link remains.

= 1.3.4 =
* Expanded the Get a Quote package section to use the available content width.

= 1.3.3 =
* Refreshed the Get a Quote package and multi-step form presentation.

= 1.2.0 =
* Update older starter-page links to work with WordPress installed in a subdirectory.
* Create editable starter navigation menus without changing existing menu assignments.

= 1.3.1 =
* Tighten section spacing across desktop and mobile layouts.
* Add package pricing and quote-form workflow. Requires BrightNest Cleaning Core 1.3.0 or later.

= 1.1.0 =
* Make starter page content editable with native Gutenberg blocks.
* Add Gutenberg editing styles, contact-detail and testimonial shortcodes, and configurable custom logo.
* Keep existing non-empty page content intact during starter page setup.

= 1.0.0 =
* Initial release.