<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include the plugin's CSS and JS on pages that include the shortcode.
 */
function eca_enqueue_front_end_scripts() {
	// Roboto, as used by Google
	wp_enqueue_style( 'roboto-font', '//fonts.googleapis.com/css?family=Roboto' );
	
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

/**
 * Print some server-side variables that can be accessed via js
 */
function eca_print_scripts() {
	if ( !is_user_logged_in() ) return;
	
	$category = eca_get_user_categories(); // returns an array, but we just want one
	if ( $category ) {
		$category = get_term_by( 'id', $category[0], 'category' );
	}
	
	$eca_seo = array(
		'title_suffix' => get_bloginfo('title') ? ' – ' . get_bloginfo('title') : '',
		'site_url' => untrailingslashit(site_url()),
		'expert_category' => $category,
	);
	
	?>
<script type="text/javascript">
var eca_seo = <?php echo json_encode($eca_seo); ?>;
</script>
<?php
}
add_action( 'wp_print_scripts', 'eca_print_scripts', 8 );