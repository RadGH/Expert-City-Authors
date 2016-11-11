<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register our custom query vars with WordPress, which are used on the directory pages.
 *
 * @param $vars
 * @return array
 */
function eca_directory_query_vars( $vars ) {
	$vars[] = "directory_page";
	return $vars;
}
add_filter( 'query_vars', 'eca_directory_query_vars' );

/**
 * Adds custom rewrite rules, to allow URL structures like /directory/profile/{profile_slug}/
 */
function eca_directory_url_routing() {
	$directory_slug = get_option( 'expert_city_directory_slug', 'members' );

	// Eg: /directory/page-2/
	add_rewrite_rule(
		$directory_slug . '/page-([0-9]+)/?$',
		'index.php?pagename='.$directory_slug.'&directory_page=$matches[1]',
		'top'
	);
}
add_action( 'init', 'eca_directory_url_routing' );

/**
 * When saving the ECA settings page, update the directory and profile slugs. Also add new rewrite rules, then flush permalinks.
 *
 * @param $post_id
 */
function eca_update_routing_slug( $post_id ) {
	if ( !is_admin() ) return;
	$screen = get_current_screen();

	if ( $screen->id == "settings_page_acf-options-expert-city-authors" ) {
		if ( $member_page = get_field( 'eca_directory_page', 'options' ) ) {
			$full_slug = wp_make_link_relative( untrailingslashit(get_permalink($member_page->ID) ) );
			if ( strpos($full_slug, "/") === 0 ) $full_slug = substr($full_slug, 1); // remove leading slash
			update_option( 'expert_city_directory_slug', $full_slug, true );
		}else{
			delete_option( 'expert_city_directory_slug' );
		}

		// Set the routing URL and rewrite permalinks
		eca_directory_url_routing();
		flush_rewrite_rules();
	}
}
add_action( 'acf/save_post', 'eca_update_routing_slug', 30 );