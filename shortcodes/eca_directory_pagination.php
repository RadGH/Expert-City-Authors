<?php
if( ! defined( 'ABSPATH' ) ) exit;

function eca_directory_pagination_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts(array(
	), $atts, 'eca_directory_pagination');
	
	ob_start();

	include( ECA_PATH . '/templates/pagination.php' );

	return ob_get_clean();
}
add_shortcode( 'eca_directory_pagination', 'eca_directory_pagination_shortcode' );


/**
 * Automatically adds the [eca_directory] shortcode to the directory page, if it is not already present.
 *
 * @param $content
 * @return string
 */
function eca_auto_add_directory_pagination_shortcode( $content ) {
	// Ensure we are on the cantors' directory page
	if ( !is_main_query() ) return $content;
	if ( !is_singular('page') ) return $content;
	if ( (int) get_the_ID() !== (int) get_field( 'eca_directory_page', 'options', false ) ) return $content;
	
	// Don't add the shortcode if it already exists
	global $post;
	if ( stristr($post->post_content, "[eca_directory_pagination") ) return $content;

	return $content . "\n\n[eca_directory_pagination]";
}
add_action( 'the_content', 'eca_auto_add_directory_pagination_shortcode', 3  );