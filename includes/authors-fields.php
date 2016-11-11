<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns a WP_User object from an ID, or if a WP_User object is already supplied, returns that. Otherwise returns false.
 *
 * @param $u
 * @return bool|false|WP_User
 */
function _eca_user( $u ) {
	if ( is_numeric($u) ) $u = get_user_by('id', $u);
	if ( $u && $u instanceof WP_User ) return $u;
	return false;
}


/**
 * Returns the full name of an author, falling back to either first or last name, or display name.
 *
 * @param $u
 * @return bool|mixed|string
 */
function eca_author_field_full_name( $u ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	if ( $user->get('first_name') && $user->get('last_name') ) return $user->get('first_name') .' '. $user->get('last_name');
	else if ( $user->get('first_name') ) return $user->get('first_name');
	else if ( $user->get('last_name') ) return $user->get('last_name');
	else return $user->get('display_name');
}

/**
 * Returns the user's email address.
 *
 * Formatted: The email will become a link if valid. If email is not valid, it is simply html-escaped.
 *
 * @param $u
 * @param bool $formatted
 * @return bool|mixed|string
 */
function eca_author_field_email( $u, $formatted = true ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = get_field( 'eca_email', 'user_' . $user->ID, false );
	if ( !$v ) $v = $user->get('user_email');
	if ( !$v ) return false;
	
	// When using Contact Form 7, we display a link that works with the contact forms instead of the email address itself.
	// We use this hook inside contact-form-7.php
	if ( $formatted ) $override = apply_filters( 'eca-filter-author-email-formatted', $v, $user );
	else $override = apply_filters( 'eca-filter-author-email', $v, $user );
	
	if ( $override ) {
		return $override;
	}

	if ( $formatted ) {
		$v = esc_html($v); // Format email for HTML, no link
		if ( $v && is_email($v) ) $v = sprintf( '<a href="mailto:%s" target="_blank">Send Email</a>', esc_attr($v), esc_html($v) ); // If WP says this is an email, make it a link

	}

	return $v;
}

/**
 * Returns the author's address field.
 *
 * Formatted: The address becomes a link to Google Maps.
 *
 * @param $u
 * @param bool $formatted
 * @return bool|mixed|null|string|void
 */
function eca_author_field_address( $u, $formatted = true ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = get_field( 'eca_address', 'user_' . $user->ID );
	if ( !$v ) return false;

	// Formatting: Address field becomes a link to google maps
	if ( $formatted ) {
		$v = sprintf( '<a href="https://www.google.com/maps?q=%s" target="_blank">%s</a>', esc_attr(urlencode($v)), esc_html($v) );
	}

	return $v;
}

/**
 * Returns the author's phone number.
 *
 * Formatted: Becomes a "tel" link for supporting apps. If the number isn't validated, it's simply html escaped.
 *
 * @param $u
 * @param bool $formatted
 * @return bool|mixed|null|string|void
 */
function eca_author_field_phone( $u, $formatted = true ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = get_field( 'eca_phone', 'user_' . $user->ID );
	if ( !$v ) return false;

	// Formatting: Phone number becomes a clickable link (when valid)
	if ( $formatted ) {
		$v = eca_format_phone( $v );
	}

	return $v;
}

/**
 * Returns the author's website URL.
 *
 * Formatted: The website URL becomes a link, and the unecessary parts (http, www, etc) are removed.
 *
 * @param $u
 * @param bool $formatted
 * @return bool|mixed|null|void
 */
function eca_author_field_website( $u, $formatted = true ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = get_field( 'eca_website', 'user_' . $user->ID );
	if ( !$v ) $v = $user->get('user_url');
	if ( !$v ) return false;

	if ( $formatted ) {
		$v = eca_widget_format_website_url( $v );
	}

	return $v;
}

/**
 * Returns the attachment ID for the user's logo.
 *
 * Formatted: Returns the <img> tag for the logo. You may want to use the $image_size parameter.
 *
 * @param $u
 * @param bool $formatted
 * @param string $image_size
 * @return bool|mixed|null|void
 */
function eca_author_field_logo( $u, $formatted = true, $image_size = 'eca-logo' ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = get_field( 'eca_logo', 'user_' . $user->ID, false );
	if ( !$v ) return false;

	if ( $formatted ) {
		// Get the logo at medium size if possible.
		$logo_img = wp_get_attachment_image_src( $v, $image_size );
		if ( !$logo_img ) return false; // Could not get image URL from attachment.

		// If the ECA Logo size is specified but the size isn't generated (falling back to full size), use the medium size instead.
		if ( $logo_img && $image_size == 'eca-logo' && $logo_img[1] > 1024 ) $logo_img = wp_get_attachment_image_src( $v, 'medium' );

		$alt = get_post_meta( $v, '_wp_attachment_image_alt', true );
		if ( !$alt ) $alt = get_the_title( $v );

		$v = sprintf(
			'<img src="%s" alt="%s" width="%s" height="%s">',
			esc_attr($logo_img[0]),
			esc_attr($alt),
			esc_attr($logo_img[1]),
			esc_attr($logo_img[2])
		);
	}

	return $v;
}

/**
 * Returns the attachment ID of the user's photo/avatar. Note: Does not work with Gravatar.
 *
 * Formatted: Returns an <img> code of the user's avatar. You may want to use the $image_size parameter.
 *
 * @param $u
 * @param bool $formatted
 * @param string $image_size
 * @return bool|mixed|null|string|void|WP_Term
 */
function eca_author_field_photo( $u, $formatted = true, $image_size = 'large' ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = get_field( 'eca_photo', 'user_' . $user->ID, false );
	
	// Allow fallback to WP Author Box Pro's avatar, which is serialized and base64-encoded (for some reason...)
	if ( !$v ) {
		global $wpautbox_pro;

		if ( isset($wpautbox_pro) ) {
			$wpautbox_meta = get_user_meta( $user->ID, 'wpautbox_user_fields', false );
			if(isset($wpautbox_meta[0])){
				$wpautbox_meta = unserialize( base64_decode($wpautbox_meta[0]) );
				if ( !empty($wpautbox_meta['user']['image']) ) $v = (int) $wpautbox_meta['user']['image'];
			}
		}
	}

	if ( !$v ) return false;

	if ( $formatted ) {
		// Get the logo at medium size if possible.
		$logo_img = wp_get_attachment_image_src( $v, $image_size );
		if ( !$logo_img ) return false; // Could not get image URL from attachment.

		// If the ECA Logo size is specified but the size isn't generated (falling back to full size), use the medium size instead.
		if ( $logo_img && $image_size == 'eca-logo' && $logo_img[1] > 1024 ) $logo_img = wp_get_attachment_image_src( $v, 'medium' );

		$alt = get_post_meta( $v, '_wp_attachment_image_alt', true );
		if ( !$alt ) $alt = get_the_title( $v );

		$v = sprintf(
			'<img src="%s" alt="%s" width="%s" height="%s">',
			esc_attr($logo_img[0]),
			esc_attr($alt),
			esc_attr($logo_img[1]),
			esc_attr($logo_img[2])
		);
	}

	return $v;
}

/**
 * Returns the user's biography as plain text.
 *
 * Formatted: Returns the user's biography with paragraphs being added.
 *
 * @param $u
 * @param bool $formatted
 * @param string $image_size
 * @return bool|mixed|null|string|void|WP_Term
 */
function eca_author_field_biography( $u, $formatted = true ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = $user->get('description');

	// Allow fallback to WP Author Box Pro's avatar, which is serialized and base64-encoded (for some reason...)
	if ( !$v ) {
		global $wpautbox_pro;

		if ( isset($wpautbox_pro) ) {
			$wpautbox_meta = get_user_meta( $user->ID, 'wpautbox_user_fields', false );
			if(isset($wpautbox_meta[0])){
				$wpautbox_meta = unserialize( base64_decode($wpautbox_meta[0]) );
				if ( !empty($wpautbox_meta['user']['tabs']) ) $v = implode("\n\n", $wpautbox_meta['user']['tabs']);
			}
		}
	}

	if ( !$v ) return false;

	if ( $formatted ) {
		$v = wpautop($v);
	}

	return $v;
}

/**
 * Returns a WP_Term object of the user's expert category, or false otherwise.
 * 
 * Formatted: Returns a link to the term's archive page.
 * 
 * @param $u
 * @param bool $formatted
 * @return bool|mixed|null|string|void|WP_Term
 */
function eca_author_field_category( $u, $formatted = true ) {
	$user = _eca_user($u);
	if ( !$user ) return false;

	$v = get_field( 'eca_author_category', 'user_' . $user->ID, false );
	if ( !$v ) return false;

	$v = get_term_by( 'id', $v, 'category' );
	if ( !$v ) return false;

	if ( $formatted ) {
		$v = sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_attr(get_term_link( $v )),
			esc_attr("View posts in " . $v->name),
			esc_html($v->name)
		);
	}

	return $v;
}