<?php
/*
Plugin Name: Expert City Authors
Version:     1.6.0
Plugin URI:  http://radgh.com/
Description: Simplify the way authors submit content for your website. Assign each author to their own category, and use the shortcode <code>[eca_submit_article]</code> to let authors submit articles.
Author:      Radley Sustaire &lt;radley@radgh.com&gt;
Author URI:  mailto:radleygh@gmail.com
License:     Copyright (c) 2016 Jamie Stephens, All Rights Reserved.
*/

if( !defined( 'ABSPATH' ) ) exit;

define( 'ECA_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'ECA_PATH', dirname(__FILE__) );
define( 'ECA_VERSION', '1.6.0' );

function eca_init_plugin() {
	if ( !class_exists( 'acf' ) ) {
		add_action( 'admin_notices', 'eca_acf_not_running' );
		return;
	}
	
	include_once( ECA_PATH . '/includes/options.php' );
	include_once( ECA_PATH . '/includes/authors.php' );
	include_once( ECA_PATH . '/includes/authors-fields.php' );
	include_once( ECA_PATH . '/includes/directory.php' );
	include_once( ECA_PATH . '/includes/enqueue.php' );
	include_once( ECA_PATH . '/includes/widgets.php' );
	include_once( ECA_PATH . '/includes/ajax.php' );
	include_once( ECA_PATH . '/includes/routing.php' );

	// Shortcodes
	include_once( ECA_PATH . '/shortcodes/eca_submit_article.php' );
	include_once( ECA_PATH . '/shortcodes/eca_directory.php' );
	include_once( ECA_PATH . '/shortcodes/eca_directory_pagination.php' );

	// Widgets
	include_once( ECA_PATH . '/widgets/author-logo.php' );
	include_once( ECA_PATH . '/widgets/author-video.php' );
	include_once( ECA_PATH . '/widgets/author-info.php' );
	include_once( ECA_PATH . '/widgets/author-buttons.php' );
	
	// ACF field groups
	include_once( ECA_PATH . '/field-groups/configuration.php' );
	include_once( ECA_PATH . '/field-groups/author-category.php' );
	include_once( ECA_PATH . '/field-groups/author-information.php' );
	include_once( ECA_PATH . '/field-groups/stock-photo.php' );
	include_once( ECA_PATH . '/field-groups/submit-an-article.php' );
	
	// Stock Photos
	include_once( ECA_PATH . '/stock-photo/post-type.php' );
	include_once( ECA_PATH . '/stock-photo/importer.php' );

	// Contact Form 7 integration
	if ( defined('WPCF7_VERSION') ) {
		include_once( ECA_PATH . '/includes/contact-form-7.php' );
	}
}
add_action( 'plugins_loaded', 'eca_init_plugin' );

function eca_acf_not_running() {
	?>
	<div class="error">
		<p><strong>Expert City Authors: Error</strong></p>
		<p>The required plugin <strong>Advanced Custom Fields Pro</strong> is not running. Please activate this required plugin, or disable Expert City Authors.</p>
	</div>
	<?php
}

function eca_activate_plugin() {
	include_once( ECA_PATH . '/stock-photo/post-type.php' );
	include_once( ECA_PATH . '/includes/routing.php' );
	include_once( ECA_PATH . '/activate.php' );
	flush_rewrite_rules();
}
function eca_deactivate_plugin() {
	include_once( ECA_PATH . '/deactivate.php' );
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'eca_activate_plugin' );
register_deactivation_hook( __FILE__, 'eca_deactivate_plugin' );
