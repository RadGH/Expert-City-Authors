jQuery(function() {
	eca_email_link_to_contact_form_lightbox();

	// Functions that rely on the submit an article form
	var $article_form = jQuery('#eca-submit-article-form');

	if ( $article_form.length > 0 ) {

		eca_rearrange_submit_article_fields( $article_form );

		eca_convert_instructions_to_info_bubble( $article_form );

		eca_add_scroll_down_class();

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
	var formData, refreshField, get_slug, get_content;

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

	// The last obtained values of the fields
	formData.field_values = {
		'post_title':        '',
		'post_content':      '',
		'post_content_text': '', // Same as post_content except HTML is stripped
		'seo_title':         '',
		'seo_slug':          '',
		'seo_description':   '',
		'focus_keyword':     ''
	};

	// WYSIWYG fields might change or may not initialize at the beginning of the page. This will reassign the post content fields.
	formData.updatePostContentFields = function() {
		formData.wysiwyg_id = acf.fields.wysiwyg.get_mceInit().id;
		formData.fields.post_content.textarea = jQuery('#'+ formData.wysiwyg_id );
		formData.fields.post_content.wrap     = jQuery('#wp-'+ formData.wysiwyg_id +'-wrap');
	};

	formData.$e = jQuery('<div>'); // reusable element for strip_html

	formData.strip_html = function( html ) {
		// Create a text-only version
		if ( !html || typeof html != 'string' ) return "";

		var string = formData.$e.html( html ).text(); // jquery creates elements of the html, then returns the text value

		formData.$e.html(''); // clear element

		return string;
	};

	// Post content can be from WYSIWYG or from the text area directly, depending on whether TinyMCE is active
	get_content = function() {
		if ( formData.fields.post_content.textarea.length < 1 ) {
			formData.updatePostContentFields();
		}

		var content = "";

		if ( formData.wysiwyg_id && formData.fields.post_content.wrap.hasClass('.tmce-active') ) {
			content = tinyMCE.get( formData.wysiwyg_id ).getContent(); // Get content from visual editor
		}else{
			content = formData.fields.post_content.textarea.val(); // Get content from text editor
		}

		return typeof content == 'string' ? content : "";
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
		if ( typeof formData.field_values[name] == 'undefined' ) return false;

		return formData.field_values[name];
	};

	// Updates the value of a given field
	refreshField = function( name, refreshing_all ) {
		if ( typeof formData.field_values[name] == 'undefined' ) return false;

		switch( name ) {
			case 'post_content':
				formData.field_values[name] = get_content();
				break;
			case 'post_content_text':
				// update post content first, if this is refreshed individually
				if ( typeof refreshing_all == 'undefined' || !refreshing_all ) formData.field_values['post_content'] = get_content();
				formData.field_values[name] = formData.strip_html( formData.field_values['post_content'] );
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
			refreshField( i, true );
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
		o.quality = 'bad';
		o.hidden = false;

		$quality_items.append( o.$row );

		o.update = function() {}; // replace this!

		o.setMessage = function( message ) {
			o.$row_text.html( message );
		};

		o.hide = function() {
			if ( o.hidden ) return; o.hidden = true;
			o.$row.css('display', 'none');
		};

		o.show = function() {
			if ( !o.hidden ) return; o.hidden = false;
			o.$row.css('display', '');
		};

		o.setQuality = function( new_quality ) {
			o.quality = new_quality;
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
				analysisItem.setMessage('You should include your expert category in the article: '+ expert_category +'.');
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
				analysisItem.setMessage('You have included a subheading.');
				analysisItem.setQuality('good');
			}else{
				analysisItem.setMessage('You should organize your article using at least one subheading.');
				analysisItem.setQuality('bad');
			}
		};

		analysis_items.push( analysisItem );
	})();

	// Analysis Item -- Check for long paragraphs
	(function() {
		if ( !eca_seo.expert_category ) return;

		var analysisItem = new analysis_item_object(); // inherit from our abstract

		analysisItem.update = function() {
			var text = formData.getField('post_content_text') || "";

			// No text to parse yet.
			if ( !text ) {
				analysisItem.hide();
				return;
			}else{
				analysisItem.show();
			}

			var paragraphs = text.split(/[\r\n]{2,}/);
			var worst_score = 0;

			if ( paragraphs && paragraphs.length > 0 ) {
				for ( var i in paragraphs ) {
					if ( !paragraphs.hasOwnProperty(i) ) continue;

					worst_score = Math.max(worst_score, paragraphs[i].length);
				}
			}

			if ( worst_score > 900 ) {
				analysisItem.setMessage('You have a paragraph that is extremely long (900+ characters).');
				analysisItem.setQuality('bad');
			}else if ( worst_score > 650 ) {
				analysisItem.setMessage('You have a paragraph that is very long (600+ characters).');
				analysisItem.setQuality('poor');
			}else{
				analysisItem.setMessage('None of your paragraphs are too long.');
				analysisItem.setQuality('good');
			}
		};

		analysis_items.push( analysisItem );
	})();

	// Analysis Item -- Check length of article or seo title
	(function() {
		if ( !eca_seo.expert_category ) return;

		var analysisItem = new analysis_item_object(); // inherit from our abstract

		analysisItem.update = function() {
			var title = formData.getField('seo_title') || "";
			var using_seo_title = true;

			if ( !title ) {
				title = formData.getField('post_title') || "";
				using_seo_title = false;
			}

			// Add the title prefix to the title length
			var article_title_length = title.length;
			var website_title_length = article_title_length + eca_seo.title_suffix.length;

			if ( article_title_length > 68 ) {
				analysisItem.setMessage('The title of the article is extremely long and will be truncated on Google.');
				analysisItem.setQuality('bad');
			}else if ( website_title_length > 68 ) {
				if ( using_seo_title )
					analysisItem.setMessage('The title of the article is long, make sure the title in the Google Search Preview is acceptable.');
				else
					analysisItem.setMessage('The title of the article is long, try using a shorter version in the <a href="#acf-field_58ad19d298374" title="Scroll down to the SEO Title field">SEO Title</a>.');

				analysisItem.setQuality('poor');
			}else if ( article_title_length < 10 ) {
				analysisItem.setMessage('The article title is too short.');
				analysisItem.setQuality('bad');
			}else{
				analysisItem.setMessage('The title of the article is a good length.');
				analysisItem.setQuality('good');
			}
		};

		analysis_items.push( analysisItem );
	})();

	// Analysis Item -- Check length of slug
	(function() {
		if ( !eca_seo.expert_category ) return;

		var analysisItem = new analysis_item_object(); // inherit from our abstract

		analysisItem.update = function() {
			var slug = formData.getField('seo_slug');

			if ( !slug ) {
				analysisItem.hide();
				return;
			}else{
				analysisItem.show();
			}

			if ( slug.length > 50 ) {
				if ( formData.fields.seo_slug.val().length < 1 )
					analysisItem.setMessage('The URL of this article will be extremely long, try adding a shorter version in the <a href="#acf-field_58ad1dc498376" title="Scroll down to the SEO URL Slug field">SEO URL Slug</a>.');
				else
					analysisItem.setMessage('The URL of this article will be extremely long, try shortening it.');

				analysisItem.setQuality('bad');
			}else if ( slug.length > 35 ) {
				if ( formData.fields.seo_slug.val().length < 1 )
					analysisItem.setMessage('The URL of this article will be very long, try adding a shorter version in the <a href="#acf-field_58ad1dc498376" title="Scroll down to the SEO URL Slug field">SEO URL Slug</a>.');
				else
					analysisItem.setMessage('The URL of this article will be very long, try shortening it.');

				analysisItem.setQuality('poor');
			}else{
				analysisItem.setMessage('The URL slug is a good length.');
				analysisItem.setQuality('good');
			}
		};

		analysis_items.push( analysisItem );
	})();

	// Analysis Item -- Links in the article
	(function() {
		if ( !eca_seo.expert_category ) return;

		var analysisItem = new analysis_item_object(); // inherit from our abstract

		analysisItem.update = function() {
			var html = formData.getField('post_content');

			var links = html.match(/<a .*?href/g);
			var number_of_links = links ? links.length : 0;

			if ( number_of_links > 1 ) {
				analysisItem.setMessage('You have multiple links in the article.');
				analysisItem.setQuality('good');
			}else if ( number_of_links == 1 ) {
				analysisItem.setMessage('You have one link in the article, which is okay. Consider adding more.');
				analysisItem.setQuality('poor');
			}else{
				analysisItem.setMessage('Consider adding links to relevant websites in your article.');
				analysisItem.setQuality('bad');
			}
		};

		analysis_items.push( analysisItem );
	})();

	// Analysis Item -- Images with alt text
	(function() {
		if ( !eca_seo.expert_category ) return;

		var analysisItem = new analysis_item_object(); // inherit from our abstract

		analysisItem.update = function() {
			var html = formData.getField('post_content');

			var links = html.match(/<img(.*?)>/ig);

			if ( links ) {
				// We have an image to check
				analysisItem.show();

				for ( var i in links ) {
					if ( !links.hasOwnProperty(i) ) continue;
					var alt = links[i].match(/alt=['"](.*?)['"]/);

					if ( !alt || typeof alt[1] == 'undefined' || alt[1].length < 1 ) {
						analysisItem.show();
						analysisItem.setMessage('The alt text of one or more images is empty.');
						analysisItem.setQuality('bad');
						return;
					}
				}

				analysisItem.setMessage('All images have the required alt text.');
				analysisItem.setQuality('good');
			}else{
				// No images to worry about, don't even show this
				analysisItem.hide();
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

		if ( google_title ) google_title = formData.strip_html( google_title );
					   else google_title = '<em>Enter your article title above</em>';

		if ( google_description ) google_description = formData.strip_html( google_description );
							 else google_description = '<em>Your article content will appear here.</em>';

		if ( !google_url ) google_url = eca_seo.site_url + '/<em>your-article</em>/';

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

function eca_add_scroll_down_class() {
	var at_top = true;
	var $body = jQuery('body');
	var $window = jQuery(window);

	var onWindowScroll = function() {
		if ( $window.scrollTop() >= 150 ) { // have scrolled down
			if ( at_top ) { // last we checked was at the top
				at_top = false;
				$body.removeClass('eca_top').addClass('eca_scrolled');
			}
		}else{
			if ( !at_top ) { // scrolled back to the top
				at_top = true;
				$body.addClass('eca_top').removeClass('eca_scrolled');
			}
		}
	};

	$window.on('scroll', onWindowScroll);

	onWindowScroll(); // initialize with this
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