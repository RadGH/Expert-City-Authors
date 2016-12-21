<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * If the current user has a role that is allowed to submit articles, returns true. Otherwise, returns false.
 *
 * @return bool
 */
function eca_can_user_submit_article() {
	// When not logged in, do not allow submitting articles.
	if ( !is_user_logged_in() ) return false;

	$allowed_roles = get_field( 'eca_author_roles', 'options' );

	// Check if the user has any of the selected roles. Return true for any of them.
	if ( !empty($allowed_roles) ) foreach( $allowed_roles as $role ) {
		if ( current_user_can( $role ) ) return true;
	}

	// Had no accepted roles, disallow
	return false;
}

/**
 * Returns an array of the author's categories. Currently, only one category is supported, but an array is used anyway to give support for multiple categories in the future.
 * 
 * @return bool|mixed|null|void
 */
function eca_get_user_categories() {
	$category = get_field( 'eca_author_category', 'user_' . get_current_user_id(), false );

	return $category ? (array) $category : false;
}

/**
 * Sanitize the submitted content of ACF to prevent against XSS attacks.
 * @param $value
 * @param $post_id
 * @param $field
 * @return array|string
 */
function eca_sanatize_author_post_data( $value, $post_id = null, $field = null ) {
	if ( is_admin() ) return $value;
	
	if( is_array($value) ) {
		return array_map('eca_sanatize_author_post_data', $value);
	}

	return wp_kses_post( $value );
}
add_filter( 'acf/update_value', 'eca_sanatize_author_post_data', 10, 3 );

/**
 * Add custom image size for author's logo (335 width, any height)
 */
function eca_custom_logo_size() {
	add_image_size( 'eca-logo', 335 );
}
add_action( 'after_setup_theme', 'eca_custom_logo_size' );

function eca_save_submitted_article_with_scheduled_date( $post_id ) {
	if ( get_post_type($post_id) != 'post' ) return $post_id;

	$date_select = isset($_POST['acf']['field_57f59e0a9f7bd']) ? stripslashes($_POST['acf']['field_57f59e0a9f7bd']) : false;
	$date_value = isset($_POST['acf']['field_57f59d7658689']) ? stripslashes($_POST['acf']['field_57f59d7658689']) : false;

	if ( $date_select != 'Scheduled' ) return $post_id;

	// Convert our date (YYYYMMDD) to a timestamp
	$date_timestamp = strtotime( $date_value );

	// If the timestamp is in the future, schedule the post for that date. We don't want people to post things in the past, though.
	if ( $date_timestamp > time() ) {
		$args = array(
			'edit_date' => true,
			'post_status' => 'pending',
			'post_date' => date( 'Y-m-d H:i:s', $date_timestamp ),
			'post_date_gmt' => date( 'Y-m-d H:i:s', $date_timestamp ),
		);

		if ( $post_id ) {
			// Update a post that was already added
			$args['ID'] = $post_id;
			wp_update_post( $args );
		}else{
			// Insert a new post
			$post_id = wp_insert_post( $args );
		}

	}

	return $post_id;
}
add_filter( 'acf/pre_save_post' , 'eca_save_submitted_article_with_scheduled_date', 5 );


/**
 * Replaces tags within the provided string with the author's data
 * @param $string
 * @param null $author_id
 * @return mixed|void
 */
function eca_replace_author_info_tags( $string, $author_id ) {
	$user = get_user_by( 'id', $author_id );

	
	$replacements = array(
		'%full_name%' => eca_author_field_full_name( $user ),
	);
	
	$replacements = apply_filters( 'eca_author_info_tags', $replacements, $author_id );
	
	$string = str_replace( array_keys($replacements), array_values($replacements), $string );

	return apply_filters( 'eca_replace_author_info_tags', $string, $author_id );
}


/**
 * Get's the user's profile picture at the specified size, or a fallback size. Uses the default placeholder photo if none is set for the user.
 *
 * @param $user_id
 * @param string $size
 * @param string $fallback_size
 * @return array
 */
function eca_get_user_profile_photo( $user_id, $size = 'medium', $fallback_size = 'thumbnail' ) {
	$p = get_field( 'cantor-photo', 'user_' . $user_id );
	$photo = !empty($p) ? $p : get_field( 'cantors_default_photo', 'options' );
	$image_url = false;
	$w = null;
	$h = null;

	if ( $photo ) {
		if ( $size && !empty($photo['sizes'][$size]) ) {
			$image_url = $photo['sizes'][$size];
			$w = @$photo['sizes'][$size]['width'];
			$h = @$photo['sizes'][$size]['height'];
		} else if ( $fallback_size && !empty($photo['sizes'][$fallback_size]) ) {
			$image_url = $photo['sizes'][$fallback_size];
			$w = @$photo['sizes'][$fallback_size]['width'];
			$h = @$photo['sizes'][$fallback_size]['height'];
		} else if ( !empty($photo['url']) ) {
			$image_url = $photo['url'];
			$w = @$photo['width'];
			$h = @$photo['height'];
		}

		$alt = $photo['alt'] ? $photo['alt'] : $photo['title'];
	}else{
		$image_url = ECA_URL . '/assets/person.png';
		$w = 400;
		$h = 400;
		$alt = "";
	}

	return array(
		$image_url,
		$w,
		$h,
		$alt
	);
}


/**
 * Informative tip explaining the behavior of eca_replace_author_info_tags to administrators, particularly within the ECA widgets.  
 */
function eca_widget_filter_description() {
	?>
	<p class="description"><em>You can use the tag <code class="code">%full_name%</code> to insert the author's name.</em></p>
	<?php
}


/**
 * Replaces the author's bio page with the "single-author.php" template.
 *
 * @param $content
 * @return string
 */
function eca_replace_author_page_content( $content ) {
	if ( !is_author() ) return $content;

	// Was a user queried? (should have been according to is_author)
	$user = get_queried_object();
	if ( !$user || !($user instanceof WP_User) ) return $content;

	ob_start();
	include( ECA_PATH . '/templates/single-author.php' );
	return ob_get_clean();
}
//add_filter( 'the_content', 'eca_replace_author_page_content', 40 );