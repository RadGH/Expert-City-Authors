jQuery(function() {
	eca_email_link_to_contact_form_lightbox();

	eca_rearrange_submit_article_fields();

	eca_google_preview_and_seo();

	eca_seo_analysis_tool();

	eca_convert_instructions_to_info_bubble();
});

function eca_convert_instructions_to_info_bubble() {
	var $acf_form = jQuery('#post');
	if ( $acf_form.length < 1 ) return;

	var fields = ".acf-field-58ad19d298374"; // Seo title
	fields +=  ", .acf-field-58ad1dc498376"; // Seo url slug
	fields +=  ", .acf-field-58ad1db898375"; // Seo description
	fields +=  ", .acf-field-58ad1dd998377"; // Focus keyword

	var $bubble = jQuery('<div>', {class: 'eca-info-bubble'});

	$bubble.append(
		jQuery('<span>', {class: 'eca-info-bubble-handle'}).html('?')
	);

	var convert_field_instruction_to_bubble = function( index ) {
		var $label = jQuery(this).find('.acf-label label');
		var $info = jQuery(this).find('.acf-label p.description');

		var $bubbleClone = $bubble.clone();

		$bubbleClone.append(
				jQuery('<div>', {class: 'eca-info-bubble-content'}).append( $info )
			);

		$label.append( $bubbleClone );
	};

	$acf_form.find( fields ).each(convert_field_instruction_to_bubble);
}

function eca_seo_analysis_tool() {
	var $acf_form = jQuery('#post');
	if ( $acf_form.length < 1 ) return;

	var $analysis_form = jQuery('<div>');
}

function eca_google_preview_and_seo() {
	var $acf_form = jQuery('#post');
	if ( $acf_form.length < 1 ) return;

	var $seo_title = jQuery('#acf-field_58ad19d298374');
	if ( $seo_title.length < 1 ) return;

	var $post_title = jQuery('#acf-_post_title');

	var $post_slug = jQuery('#acf-field_58ad1dc498376');

	var $focus_keyword = jQuery('#acf-field_58ad1dd998377');

	var wysiwyg_id = acf.fields.wysiwyg.get_mceInit().id;

	var $seo_desc = jQuery('#acf-field_58ad1db898375');
	var $post_desc_textarea = jQuery('#'+ wysiwyg_id);
	var $post_desc_wrap = jQuery('#wp-'+ wysiwyg_id +'-wrap');

	var $google_search_preview = $acf_form.find('.acf-field.acf-field-58ad1dfe98379');
	var $googlePlacementTarget = $acf_form.find('div.acf-field.acf-field-58ad1dfe98379 .acf-input');
	var $googleTitle = jQuery('<span>', {class: 'eca-google-title'});
	var $googleURL = jQuery('<span>', {class: 'eca-google-url'});
	var $googleDescription = jQuery('<div>', {class: 'eca-google-description'});

	var $submit_article_form = jQuery('#post.acf-form');

	$googlePlacementTarget.html("").append(
		jQuery('<div>', {class: 'eca-google-wrap'}).append(
			jQuery('<div>', {class: 'eca-google-title-wrap'}).append(
				$googleTitle
			),
			jQuery('<div>', {class: 'eca-google-url-wrap'}).append(
				$googleURL,
				jQuery('<span>', {class: 'eca-google-url-arrow'})
			),
			$googleDescription
		)
	);

	var refreshGooglePreview = function() {
		var get_focus_keyword = function() {
			return $focus_keyword.val();
		};

		var get_post_title = function() {
			return $post_title.val();
		};

		var get_seo_title = function() {
			return $seo_title.val();
		};

		var get_post_content = function() {
			if ( wysiwyg_id && $post_desc_wrap.hasClass('.tmce-active') ) {
				return tinyMCE.get( wysiwyg_id ).getContent(); // Get content from visual editor
			}else{
				return $post_desc_textarea.val(); // Get content from text editor
			}
		};

		var get_seo_description = function() {
			return $seo_desc.val();
		};

		var get_slug = function() {
			var slug = $post_slug.val();

			// Fall back to get slug from title
			if ( !slug ) {
				slug = get_post_title() || get_seo_title();
			}

			// Clean up the slug
			if ( slug ) {
				return slug.toLowerCase().replace(/[^a-zA-Z0-9]+/g, '-').replace(/(^-|-$)/g, '');
			}

			return false;
		};

		var escape_regexp = function(str) {
			return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		};

		var highlight_focus_keywords = function( string, keyword, counter_object, counter_key ) {
			if ( !keyword ) return string;
			if ( !string ) return false;

			keyword = escape_regexp(keyword);

			// Allow any non alphanumeric to be anything else. Eg, spaces for hyphens.
			keyword = keyword.replace(/[^a-zA-Z0-9]/g, '.');

			var pattern = new RegExp("\\b(" + keyword + ")\\b", "gi");

			if ( typeof counter_object != 'undefined' ) {
				var matches = string.match( pattern );
				counter_object[counter_key] = matches ? matches.length : 0;
			}

			return string.replace( pattern, "<span class=\"eca-google-focus-keyword\" title=\"Your focus keyword\">$1</span>" );
		};

		var focus_keyword = get_focus_keyword();

		var google_title = get_seo_title() || get_post_title();
		var google_description = get_seo_description() || get_post_content();
		var google_url = get_slug() ? window.location.protocol + '//' + window.location.hostname + '/' + get_slug() + '/' : false;

		if ( !google_title && !google_description ) {
			$google_search_preview
				.css('display', 'none');
		}else{
			// Add title suffix, if given by the server
			if ( google_title && typeof eca_seo.title_suffix == 'string' ) google_title += " " + eca_seo.title_suffix;

			// Truncate strings to google length
			if ( google_title && google_title.length > 68 ) google_title = google_title.substr(0, 65) + "&hellip;";
			if ( google_description && google_description.length > 163 ) google_description = google_description.substr(0, 160) + "&hellip;";

			var focus_keywords_counts = {
				title: 0,
				description: 0,
				url: 0
			};

			google_title = highlight_focus_keywords( google_title, focus_keyword, focus_keywords_counts, 'title' );
			google_description = highlight_focus_keywords( google_description, focus_keyword, focus_keywords_counts, 'description' );
			google_url = highlight_focus_keywords( google_url, focus_keyword, focus_keywords_counts, 'url' );

			$googleTitle.html( google_title );
			$googleDescription.html( google_description );
			$googleURL.html( google_url );

			$google_search_preview
				.css('display', '')
				.attr('data-fc-title', focus_keywords_counts.title)
				.attr('data-fc-description', focus_keywords_counts.description)
				.attr('data-fc-url', focus_keywords_counts.url);
		}
	};


	$submit_article_form.on('keyup change', ':input', function(e) {
		refreshGooglePreview();
	});

	refreshGooglePreview();
}

function eca_rearrange_submit_article_fields() {
	var $acf_form = jQuery('#eca-submit-article-form').find('form');
	if ( $acf_form.length < 1 ) return;

	var $keyword_field = $acf_form.find('.acf-field.acf-field-57d8dc01ae949');
	var $content_field = $acf_form.find('.acf-field.acf-field--post-content');

	// Move keyword field after content
	$content_field.after( $keyword_field );

	// SEO stuff in a group
	var $seo_title = $acf_form.find('.acf-field.acf-field-58ad19d298374');
	var $seo_slug = $acf_form.find('.acf-field.acf-field-58ad1dc498376');
	var $seo_description = $acf_form.find('.acf-field.acf-field-58ad1db898375');
	var $focus_keyword = $acf_form.find('.acf-field.acf-field-58ad1dd998377');
	var $google_search_preview = $acf_form.find('.acf-field.acf-field-58ad1dfe98379');

	var $seo_fields = jQuery('<div>', {class: 'eca-seo-fields acf-fields'});
	$seo_fields.append( $seo_title, $seo_slug, $seo_description, $focus_keyword, $google_search_preview );

	$keyword_field.after( $seo_fields );

	console.log( $seo_fields);
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