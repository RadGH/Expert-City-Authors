<?php
if( ! defined( 'ABSPATH' ) ) exit;

function eca_directory_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts(array(
	), $atts, 'eca_directory');

	$user_list = eca_directory_users();

	$page = (int) get_query_var( "directory_page", 1 );
	
	ob_start();
	?>
	<div class="eca-directory">
		<div class="eca-directory-inner">
			<?php
			if ( $user_list->get_total() < 1 ) {
				?>
				<p class="wcd-no-items"><em>Sorry, no results found.</em></p>
				<?php
			}else {
				foreach( $user_list->get_results() as $u ) {

					// Don't show users who aren't supposed to appear here.
					if ( empty($u) || is_wp_error($u) || !eca_user_appears_on_directory($u) ) {
						continue;
					}
	
					// We don't need to let people filter the template file here.
					// If you are editing this directory template, you can change this file yourself in your new directory template for yourself.
					include( ECA_PATH . '/templates/directory-item.php' );
				}
			}
			?>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'eca_directory', 'eca_directory_shortcode' );


/**
 * Automatically adds the [eca_directory] shortcode to the directory page, if it is not already present.
 *
 * @param $content
 * @return string
 */
function eca_auto_add_directory_shortcode( $content ) {
	// Ensure we are on the cantors' directory page
	if ( !is_main_query() ) return $content;
	if ( !is_singular('page') ) return $content;
	if ( (int) get_the_ID() !== (int) get_field( 'eca_directory_page', 'options', false ) ) return $content;
	
	// Don't add the shortcode if it already exists
	global $post;
	if ( stristr($post->post_content, "[eca_directory") ) return $content;

	return $content . "\n\n[eca_directory]";
}
add_action( 'the_content', 'eca_auto_add_directory_shortcode', 2 );