jQuery(function() {
	eca_email_link_to_contact_form_lightbox();

	// Functions that rely on the submit an article form
	var $article_form = jQuery('#eca-submit-article-form');

	if ( $article_form.length > 0 ) {

		eca_rearrange_submit_article_fields( $article_form );

		eca_convert_instructions_to_info_bubble( $article_form );

		// Object to handle getting and updating values from the form, to improve efficiency
		var formData = new EcaFormDataHandler( $article_form );

		eca_google_preview_and_seo( $article_form, formData );

		eca_seo_analysis_tool( $article_form, formData );

		// Event to handle updating the formData values, and triggers a custom "eca_update_fields" event which is used to refresh inside our functions after fields change.
		var keyup_interval = false;

		var eventUpdateFormData = function() {
			if ( keyup_interval !== false ) clearTimeout(keyup_interval);

			keyup_interval = setTimeout(function() {
				formData.refreshAll();
				$article_form.trigger( 'eca_update_fields' );
			}, 250);
		};

		$article_form.on('change keyup', ':input', eventUpdateFormData);
		eventUpdateFormData();
	}
});

function EcaFormDataHandler( $article_form ) {
	var formData, field_values, get_slug, get_content;

	formData = this;

	formData.wysiwyg_id = acf.fields.wysiwyg.get_mceInit().id;

	// Field elements themselves
	formData.fields = {
		'post_title':            jQuery('#acf-_post_title'),
		'post_content': {
			textarea: jQuery('#'+ formData.wysiwyg_id ),
			wrap:     jQuery('#wp-'+ formData.wysiwyg_id +'-wrap')
		},
		'seo_title':             jQuery('#acf-field_58ad19d298374'),
		'seo_slug':              jQuery('#acf-field_58ad1dc498376'),
		'seo_description':       jQuery('#acf-field_58ad1db898375'),
		'focus_keyword':         jQuery('#acf-field_58ad1dd998377')
	};

	// WYSIWYG fields might change or may not initialize at the beginning of the page. This will reassign the post content fields.
	formData.updatePostContentFields = function() {
		formData.wysiwyg_id = acf.fields.wysiwyg.get_mceInit().id;
		formData.fields.post_content.textarea = jQuery('#'+ formData.wysiwyg_id );
		formData.fields.post_content.wrap     = jQuery('#wp-'+ formData.wysiwyg_id +'-wrap');
	};

	// The last obtained values of the fields
	formData.field_values = {
		'post_title':     '',
		'post_content':   '',
		'seo_title':      '',
		'seo_slug':       '',
		'seo_description':'',
		'focus_keyword':  ''
	};

	// Post content can be from WYSIWYG or from the text area directly, depending on whether TinyMCE is active
	get_content = function() {
		if ( formData.fields.post_content.textarea.length < 1 ) {
			formData.updatePostContentFields();
		}

		if ( formData.wysiwyg_id && formData.fields.post_content.wrap.hasClass('.tmce-active') ) {
			return tinyMCE.get( formData.wysiwyg_id ).getContent(); // Get content from visual editor
		}else{
			return formData.fields.post_content.textarea.val(); // Get content from text editor
		}
	};

	// The slug should inherit from the post title or seo title;
	get_slug = function() {
		// Lowercase, alphanumeric, hyphens allowed inside only
		var slug = formData.fields.seo_slug.val();
		if ( !slug ) slug = formData.fields.seo_title.val();
		if ( !slug ) slug = formData.fields.post_title.val();


		if ( slug ) {
			slug = slug.toLowerCase();                                // slug always lower case
			slug = slug.replace(/['"]/g, '');                         // remove unneeded characters
			slug = slug.replace(/[^a-zA-Z0-9]+/g, '-');               // replace other characters with hyphens
			slug = slug.replace(/(^-|-$)/g, '');                      // remove leading/trailing hyphens
			return slug;
		}else{
			return false;
		}
	};

	// Retreives a value for a field
	formData.getField = function( name ) {
		if ( typeof formData.fields[name] == 'undefined' ) return false;

		return formData.field_values[name];
	};

	// Updates the value of a given field
	formData.refreshField = function( name ) {
		if ( typeof formData.fields[name] == 'undefined' ) return false;

		switch( name ) {
			case 'post_content':
				formData.field_values[name] = get_content();
				break;
			case 'seo_slug':
				formData.field_values[name] = get_slug();
				break;
			default:
				formData.field_values[name] = formData.fields[name].val();
				break;
		}
	};

	// Refresh all the fields
	formData.refreshAll = function() {
		for ( var i in formData.field_values ) {
			if ( !formData.field_values.hasOwnProperty(i) ) continue;
			formData.refreshField( i );
		}
	};


}

function eca_seo_analysis_tool( $article_form, formData ) {
	var $analysis_form = jQuery('<div>', {class: 'eca-seo-analysis fixed'});
	var $quality_items = jQuery('<div>', {class: 'eca-quality-list'});
	var $toggle_button = jQuery('<a>', {class: 'eca-analysis-toggle', href: '#'});
	var collapsed = false;

	// Form structure
	$analysis_form.append(
		jQuery('<div>', {class: 'eca-analysis-header'}).append(
			jQuery('<span>', {class: 'eca-header-text'}).html('Article SEO Analysis')
		).append(
			$toggle_button.append('<span>')
		)
	).append(
		jQuery('<div>', {class: 'eca-analysis-content'}).append(
			$quality_items
		)
	);

	// Toggle whether or not the menu is collapsed
	$toggle_button.on('click', function(e) {
		collapsed = !collapsed;

		$analysis_form
			.toggleClass('collapsed', collapsed)
			.toggleClass('expanded', !collapsed);

		return false;
	});

	// Add the form to the dom, after the seo fields
	$article_form.find('.eca-seo-fields').after( $analysis_form );

	// Make a blueprint of the new rows we will be adding
	var $analysis_row_blueprint = jQuery('<div>', {class: 'eca-quality-item'});

	$analysis_row_blueprint.append(
		jQuery('<span>', {class: 'eca-score-indicator'})
	).append(
		jQuery('<span>', {class: 'eca-score-text'})
	);

	// Make the abstract for the analysis objects. All analysis item should use this.
	var analysis_item_object = function() {
		var o = this;
		o.$row = $analysis_row_blueprint.clone();
		o.$row_text = o.$row.find('.eca-score-text');

		$quality_items.append( o.$row );

		o.update = function() {}; // replace this!

		o.setMessage = function( message ) {
			o.$row_text.html( message );
		};

		o.setQuality = function( new_quality ) {
			o.$row
				.toggleClass('eca-quality-good', new_quality == 'good')
				.toggleClass('eca-quality-poor', new_quality == 'poor')
				.toggleClass('eca-quality-bad', new_quality == 'bad');
		};
	};

	// Create an array to list each analysis item under.
	var analysis_items = [];

	// Analysis Item -- Expert Category
	(function() {
		if ( !eca_seo.expert_category ) return;

		var analysisItem = new analysis_item_object(); // inherit from our abstract
		var expert_category = eca_seo.expert_category.name.toLowerCase();

		analysisItem.update = function() {
			var found_in = [];

			var post_title = formData.getField('post_title') || "";
			var post_content = formData.getField('post_content') || "";

			if ( post_title.toLowerCase().indexOf( expert_category ) >= 0 ) found_in.push('post_title');
			if ( post_content.toLowerCase().indexOf( expert_category ) >= 0 ) found_in.push('post_content');

			if ( found_in.length > 0 ) {
				analysisItem.setMessage('Your expert category is in your article.');
				analysisItem.setQuality('good');
			}else{
				analysisItem.setMessage('Your expert category ('+ expert_category +') should be included in your article.');
				analysisItem.setQuality('bad');
			}
		};

		analysis_items.push( analysisItem );
	})();

	// Analysis Item -- Check for headings
	(function() {
		if ( !eca_seo.expert_category ) return;

		var analysisItem = new analysis_item_object(); // inherit from our abstract

		analysisItem.update = function() {
			var post_content = formData.getField('post_content') || "";

			// Check if a heading exists
			if ( post_content.match(/<h[1-6]>/) ) {
				analysisItem.setMessage('You have included at least one heading.');
				analysisItem.setQuality('good');
			}else{
				analysisItem.setMessage('Organize your article using at least one heading.');
				analysisItem.setQuality('bad');
			}
		};

		analysis_items.push( analysisItem );
	})();

	// Refresh each analysis when fields are updated
	$article_form.on('eca_update_fields', function() {
		for ( var i in analysis_items ) {
			if ( !analysis_items.hasOwnProperty(i) ) continue;

			analysis_items[i].update();
		}
	});

/*
<div class="eca-seo-analysis fixed">

	<div class="eca-analysis-header">
		Article SEO Analysis
		<span class="eca-analysis-toggle"></span>
	</div>

	<div class="eca-analysis-content">
		<div class="eca-quality-list">

			<div class="eca-quality-item eca-quality-good eca-quality-field__category">
			<span class="eca-score-indicator"></span>
			<span class="eca-score-item eca-score-good">The article content includes your Expert Category (<span class="eca-data" data-field="expert-category">Dog Photo Master</span>).</span>
			<span class="eca-score-item eca-score-poor eca-score-bad">The article content does not include your Expert Category (<span class="eca-data" data-field="expert-category">Dog Photo Master</span>).</span>
			</div>

			<div class="eca-quality-item eca-quality-poor eca-quality-field__category">
			<span class="eca-score-indicator"></span>
			<span class="eca-score-item eca-score-good">The article content includes your Expert Category (<span class="eca-data" data-field="expert-category">Dog Photo Master</span>).</span>
			<span class="eca-score-item eca-score-poor eca-score-bad">The article content does not include your Expert Category (<span class="eca-data" data-field="expert-category">Dog Photo Master</span>).</span>
			</div>

			<div class="eca-quality-item eca-quality-bad eca-quality-field__category">
			<span class="eca-score-indicator"></span>
			<span class="eca-score-item eca-score-good">The article content includes your Expert Category (<span class="eca-data" data-field="expert-category">Dog Photo Master</span>).</span>
			<span class="eca-score-item eca-score-poor eca-score-bad">The article content does not include your Expert Category (<span class="eca-data" data-field="expert-category">Dog Photo Master</span>).</span>
			</div>

		</div>
	</div>

</div>
*/
}

function eca_google_preview_and_seo( $article_form, formData ) {
	var $google_search_preview = $article_form.find('.acf-field.acf-field-58ad1dfe98379');
	var $googlePlacementTarget = $article_form.find('div.acf-field.acf-field-58ad1dfe98379 .acf-input');
	var $googleTitle = jQuery('<span>', {class: 'eca-google-title'});
	var $googleURL = jQuery('<span>', {class: 'eca-google-url'});
	var $googleDescription = jQuery('<div>', {class: 'eca-google-description'});

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

		var escape_regexp = function(str) {
			return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		};

		var highlight_focus_keywords = function( string, keyword, counter_object, counter_key ) {
			if ( !keyword ) return string;
			if ( !string ) return false;

			keyword = escape_regexp(keyword);

			// Allow any non alphanumeric to be anything else. Eg, spaces for hyphens.
			keyword = keyword.replace(/[^a-zA-Z0-9]+/g, '.');

			var pattern = new RegExp("\\b(" + keyword + ")\\b", "gi");

			if ( typeof counter_object != 'undefined' ) {
				var matches = string.match( pattern );
				counter_object[counter_key] = matches ? matches.length : 0;
			}

			return string.replace( pattern, "<span class=\"eca-google-focus-keyword\" title=\"Your focus keyword\">$1</span>" );
		};

		var focus_keyword = formData.getField('focus_keyword');

		var google_title = formData.getField('seo_title') || formData.getField('post_title');
		var google_description = formData.getField('seo_description') || formData.getField('post_content');

		var slug = formData.getField('seo_slug');
		var google_url = slug ? eca_seo.site_url + '/' + slug + '/' : false;

		if ( !google_title ) google_title = '<em>Enter your article title above</em>';
		if ( !google_url ) google_url = eca_seo.site_url + '/<em>your-article</em>/';
		if ( !google_description ) google_description = '<em>Your article content will appear here.</em>';

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
	};

	$article_form.on('eca_update_fields', function() {
		refreshGooglePreview();
	});
}

function eca_convert_instructions_to_info_bubble( $article_form ) {
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

	$article_form.find( fields ).each(convert_field_instruction_to_bubble);
}

function eca_rearrange_submit_article_fields( $article_form ) {
	var $keyword_field = $article_form.find('.acf-field.acf-field-57d8dc01ae949');
	var $content_field = $article_form.find('.acf-field.acf-field--post-content');

	// Move keyword field after content
	$content_field.after( $keyword_field );

	// SEO stuff in a group
	var $seo_title = $article_form.find('.acf-field.acf-field-58ad19d298374');
	var $seo_slug = $article_form.find('.acf-field.acf-field-58ad1dc498376');
	var $seo_description = $article_form.find('.acf-field.acf-field-58ad1db898375');
	var $focus_keyword = $article_form.find('.acf-field.acf-field-58ad1dd998377');
	var $google_search_preview = $article_form.find('.acf-field.acf-field-58ad1dfe98379');

	var $seo_fields = jQuery('<div>', {class: 'eca-seo-fields acf-fields'});
	$seo_fields.append( $seo_title, $seo_slug, $seo_description, $google_search_preview, $focus_keyword );

	$keyword_field.after( $seo_fields );
}

// When clicking the "Send Email" link for an author, open a lightbox with a contact form
// Note: This is only enabled if Contact Form 7 is running and a form has been assigned in the ECA settings page
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