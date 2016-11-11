<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns how many users should be shown per page.
 *
 * @return mixed|void
 */
function eca_get_users_per_page() {
	$users_per_page = (int) get_field( 'eca_users_per_page', 'options' );
	if ( $users_per_page < 1 ) $users_per_page = get_option( 'posts_per_page', 10 );
	if ( $users_per_page < 1 ) $users_per_page = 10;
	
	return apply_filters( 'eca_users_per_page', $users_per_page );
}

/**
 * Returns true if the user is allowed to appear on the directory. False otherwise.
 *
 * @param $user
 * @return bool
 */
function eca_user_appears_on_directory( $user ) {
	if (is_numeric($user) ) return false;

	// Settings Page Filter: Only get users with any of the given roles
	if ( $r = get_field( 'eca_directory_roles', 'options', false ) ) {
		if ( $roles = array_filter( preg_split('/\s*[\r\n]+\s*/', $r) ) ) {
			$has_any_role = false;

			// Correct role formatting, allowing names like "WCN Member" entered by admins to convert to "wcn_member" automatically.
			foreach( $roles as $k=>$r ) $roles[$k] = eca_role_name_to_role_key($r);

			// Search through each of the users role, try to find one that also exists in $r.
			// This section only works with the role "key", such as: wcn_member. It does NOT work for names, like "WCN Member". But that is supported below.
			foreach( $user->roles as $key => $user_role ) {
				if ( in_array($user_role, $roles) ) {
					// Role by key, eg: "wcn_member"
					$has_any_role = true;
					break;
				}
			}

			// User does not have any of the required roles, and should not appear on the directory.
			if ( !$has_any_role ) return false;
		}
	}

	// Settings Page Filter: Exclude specific users
	if ( $e = get_field( 'eca_excluded_users', 'options', false ) ) {
		// User's ID is in the excluded users list, and should not appear on the directory.
		if ( in_array( $user->ID, $e ) ) return false;
	}

	return true;
}

/**
 * Returns the URL of the experts directory, for the given page number
 *
 * @param $number
 * @return string|void
 */
function eca_get_member_directory_page( $number = 1 ) {
	$directory_slug = get_option( 'expert_city_directory_slug' );

	$number = (int) $number;
	if ( $number < 1 ) $number = 1;

	if ( $number === 1 ) {
		return site_url('/' . $directory_slug . '/');
	}else{
		return site_url('/' . $directory_slug . '/page-' . $number . '/');
	}
}

/**
 * Takes a role name, such as "Expert City Author", and returns the role key, such as "expert_city_author". Also works if role key is given, and will correct capitalization.
 *
 * @param $role_name
 * @return bool|int|string
 */
function eca_role_name_to_role_key( $role_name ) {
	// Try to check for role names, such as "WCN Member", when we fail to find their key "wcn_member".
	global $wp_roles;

	foreach( $wp_roles->roles as $role_key => $wp_role ) {
		if ( $wp_role['name'] == $role_name ) return $role_key;
		if ( strtolower($role_key) == strtolower($role_name) ) return $role_key; // Already was the correct format, but maybe case sensitivity was wrong.
	}

	return false;
}

/**
 * Returns an array of users to be displayed on the experts directory page, using optional page and search filters.
 *
 * @return array
 */
function eca_directory_users() {
	global $eca_users;
	if ( isset($eca_users) ) return $eca_users;
	
	$page = (int) get_query_var( "directory_page", 1 );

	// The get_users arguments, which will be extended by different settings.
	$args = array();

	$args['count_total'] = true;
	$args['number'] = (int) eca_get_users_per_page();
	$args['paged'] = max(1, (int) $page);

	// Sort by meta value
	// $args['meta_key'] = '_expert-category-search-name';
	// $args['meta_value'] = '';
	// $args['meta_compare'] = '!=';
	// $args['orderby'] = 'meta_value';

	// Sort by post count
	$args['order'] = 'desc';
	$args['orderby'] = 'post_count';

	// Settings Page Filter: Only get users with any of the given roles
	if ( $r = get_field( 'eca_directory_roles', 'options', false ) ) {
		$roles = preg_split('/\s*[\r\n]+\s*/', $r);
		foreach( $roles as $k => $r ) $roles[$k] = eca_role_name_to_role_key( $r ); // Allow "WCN Member" to convert to "wcn_member" automatically.

		$args['role__in'] = array_filter( $roles );
	}

	// Settings Page Filter: Exclude specific users
	if ( $e = get_field( 'eca_excluded_users', 'options', false ) ) $args['exclude'] = array_filter( $e );

	// Let plugins filter results further
	$args = apply_filters( 'eca_directory_users_args', $args, $page );

	$eca_users = new WP_User_Query($args);

	return $eca_users;
}