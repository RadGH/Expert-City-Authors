<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include the plugin's CSS and JS on pages that include the shortcode.
 */
function eca_enqueue_front_end_scripts() {
	wp_enqueue_style( 'eca', ECA_URL . '/assets/eca.css', array( 'dashicons' ), ECA_VERSION );
	wp_enqueue_script( 'eca', ECA_URL . '/assets/eca.js', array( 'jquery' ), ECA_VERSION );
}
add_action( 'wp_enqueue_scripts', 'eca_enqueue_front_end_scripts' );

/**
 * Include CSS and JS on the admin screen
 */
function eca_enqueue_admin_scripts() {
	wp_enqueue_style( 'eca-admin', ECA_URL . '/assets/eca-admin.css', array(), ECA_VERSION );
	wp_enqueue_script( 'eca-admin', ECA_URL . '/assets/eca-admin.js', array( 'jquery' ), ECA_VERSION );
}
add_action( 'admin_enqueue_scripts', 'eca_enqueue_admin_scripts' );

/**
 * Includes the required ACF hooks for acf_form to be able to create a new post. Only for pages that include the eca shortcode.
 */
function eca_register_acf_hooks() {
	acf_form_head(); // Allows ACF form to be saved
	add_action( 'get_footer', 'acf_enqueue_uploader', 5 ); // Includes required assets for WP media popup
}
add_action( 'wp', 'eca_register_acf_hooks', 25 );

