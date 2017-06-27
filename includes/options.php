<?php
if( ! defined( 'ABSPATH' ) ) exit;

function eca_register_settings_menu() {
	acf_add_options_sub_page(array(
		'parent_slug' => 'options-general.php',
		'page_title' => 'Expert City Authors &ndash; Settings',
		'menu_title' => 'Expert City Authors',
		'autoload' => false,
	));
}
add_action( 'admin_menu', 'eca_register_settings_menu' );


function eca_after_install_setup_notice() {
	if ( !is_admin() ) return;
	if ( get_option('eca_installed') ) return; // This option is autoloaded and won't slow down every admin menu request

	if ( get_field('eca_enable_author_submissions', 'options') != null ) { // User has entered a value, remember that to not ask again
		update_option('eca_installed', '1', true);
		return;
	}

	$screen = get_current_screen();
	if ( $screen->id == "settings_page_acf-options-expert-city-authors" ) return;

	?>
	<div class="notice notice-info">
		<p><strong>Expert City Authors: Install Successful</strong></p>
		<p>To get started with Expert City Authors, head over to the <a href="<?php echo esc_attr(admin_url('options-general.php?page=acf-options-expert-city-authors')); ?>">settings page</a>.</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'eca_after_install_setup_notice' );