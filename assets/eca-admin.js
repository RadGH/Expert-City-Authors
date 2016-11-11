jQuery(function() {
	init_hide_wpautbox_social_networks();
});

function init_hide_wpautbox_social_networks() {
	var $networks = jQuery('#wpautbox_user_youtube');
	if ( $networks.length < 1 ) return;

	// Add other networks
	$networks = $networks.add( jQuery('#wpautbox_user_pinterest') );

	// Hide the <tr> for these networks
	$networks.closest('tr').css('display', 'none');
}