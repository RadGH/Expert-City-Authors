jQuery(function() {
	eca_email_link_to_contact_form_lightbox();

	eca_rearrange_submit_article_fields();
});

function eca_rearrange_submit_article_fields() {
	var $acf_form = jQuery('#eca-submit-article-form').find('form');
	if ( $acf_form.length < 1 ) return;

	var $keyword_field = $acf_form.find('.acf-field.acf-field-57d8dc01ae949');
	var $content_field = $acf_form.find('.acf-field.acf-field--post-content');

	// Move keyword field after content
	$content_field.after($keyword_field);
}

// When clicking the "Send Email" link for an author, open a lightbox with a contact form
// Note: This is only enabled if Contact Form 7 is running and a form has been assigned in the ECA settings page.
function eca_email_link_to_contact_form_lightbox() {
	var $lightbox = jQuery('#eca-cf7-author-email');
	if ( $lightbox.length < 1 ) return;

	var $form = $lightbox.find('form.wpcf7-form');
	if ( $form.length < 1 ) return;

	var $input = jQuery( '<input>', { type: 'hidden', name: 'eca-author-id' } );
	$form.append( $input );

	var $author_name = $lightbox.find('.eca-cf7-author-name');

	// When clicking on a link, fill the author id and show the lightbox
	jQuery('body').on('click', '.eca-email-cf7', function(e) {
		var author_id = jQuery(this).attr('data-author');
		if ( author_id < 1 ) return;

		var full_name = jQuery(this).attr('data-name');

		$input.val( author_id );
		$author_name.text( full_name );

		console.log( full_name );
		console.log( $author_name[0] );

		$lightbox.css('display', 'block' );

		return false;
	});

	// Allow closing the lightbox
	var close_lightbox = function(force_reset) {
		if ( typeof force_reset == "undefined" || force_reset !== true ) {
			var is_form_filled = false;

			jQuery(":text, :file, :checkbox, select, textarea").each(function() {
				if ( jQuery(this).is(':visible') === false ) return; // Ignore hidden fields, those can be filled

				if ( jQuery(this).val() != "" ) {
					is_form_filled = true;
					console.log( this, jQuery(this).val() );
				}
			});

			if ( is_form_filled ) {
				if ( !confirm('You are about to close the contact form. You will lose your changes.' + "\n\n" + 'Close anyway?') ) {
					return;
				}
			}
		}

		$lightbox.css('display', 'none');
		$form.resetForm();
	};

	// Close lightbox via underlay
	$lightbox.children('.eca-lightbox-underlay').on('click', function(e) {
		if ( jQuery(e.target).is('.eca-lightbox-underlay') ) {
			close_lightbox();
			return false;
		}
	});

	// Close lightbox using the X button
	$lightbox.on( 'click', 'a.eca-lightbox-close', function(e) {
		close_lightbox();
		return false;
	});
}