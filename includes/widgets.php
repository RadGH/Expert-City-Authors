<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Converts a phone number to a clickable link using some complicated regex.
 *
 * @param $string
 * @param bool $html
 * @return string
 */
function eca_format_phone( $string, $html = true ) {
	// Pattern to collect 10 digits from a phone number, and optional extension
	// Extensions can be identified using: + - x ex ex. ext ext. extension extension.
	$pattern = '/\+?([0-9]{0,3})?[^0-9]*([0-9]{3,3})[^0-9]*([0-9]{3,3})[^0-9]*([0-9]{4,4})[^0-9]*([^0-9]*(-|e?xt?\.?)[^0-9]*([0-9]{1,}))?[^0-9]*$/i';

	if ( preg_match($pattern, $string, $matches) ) {
		// Input: "1 (541) 123-4567 x999"
		// 1 => 1
		// 2 => 541
		// 3 => 123
		// 4 => 4567
		// 7 => 999

		$result = array();
		if ( isset($matches[7]) ) $ext = $matches[7];
		else $ext = '';

		$countrycode = $matches[1] ? $matches[1] : 1;

		// Output (HTML):
		// <span class="tel"><a href="tel+15411234567" class="tel-link">541-123-4567</a><span class="tel-ext"><span> x</span>999</span></span>
		// Output (Raw):
		// 541-123-4567 x999
		if ( $html ) $result[] = '<span class="tel">';
		if ( $html ) $result[] = sprintf('<a href="tel:+%s%s%s%s" class="tel-link">', $countrycode, $matches[2], $matches[3], $matches[4]);
		$result[] = sprintf('%s (%s) %s-%s', $countrycode, $matches[2], $matches[3], $matches[4]);
		if ( $html ) $result[] = sprintf('</a>');

		// Note: tel: links cannot *reliably* include an extension, so it comes after the link.
		if ( $ext && $html ) $result[] = sprintf('<span class="tel-ext"><span> x</span>%s</span>', $ext);
		else if ( $ext )     $result[] = sprintf(' x%s', $ext);
		if ( $html ) $result[] = '</span>';

		return implode($result);
	}

	// Pattern not found
	return esc_html($string); // The phone number isn't valid, but that's ok. Keep the original.
}


/**
 * Returns a user's formatted website URL. If WP can sanitize the URL, it becoems a link. Otherwise, it is plain text.
 *
 * @param $url
 * @return bool|string
 */
function eca_widget_format_website_url( $url ) {
	if ( $url ) {
		if ( !strstr($url, "http") ) $url = "http://" . $url;

		if ( $url = esc_url_raw($url) ) {
			// Clean up URL for display
			$label = str_replace(array('http://','https://'), '', $url);
			$label = untrailingslashit( $label );

			return sprintf('<a href="%s" target="_blank">Visit Website</a>', esc_attr($url), esc_html($label));
		}
	}

	return esc_html($url);
}