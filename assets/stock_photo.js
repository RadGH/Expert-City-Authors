(function($){

	function Stock_image_upload_dialog( cb ) {
		var frame = this;

		frame.ajax_request = false;
		frame.did_initial_request = false;
		frame.currentResult = {
			current_page: 1,
			found_posts: 0,
			max_num_pages: 1,
			posts: []
		};

		// Get the stock photo browser element, which is created in stock_photos_post_type.php
		var $sp_frame = jQuery('#sp-browser-container');
		var $list = $sp_frame.find('.spb-gallery');
		var $list_blueprint = $sp_frame.find('.spb-template');

		frame.close = function() {
			$sp_frame.css('display', 'none');

			setTimeout(function() {
				$sp_frame.addClass('sp-closed').removeClass('sp-open');
			}, 10);

			// Kill any active ajax requests
			if ( frame.ajax_request !== false ) {
				frame.ajax_request.abort();
				frame.ajax_request = false;
			}
		};

		frame.open = function() {
			$sp_frame.css('display', '');

			setTimeout(function() {
				$sp_frame.removeClass('sp-closed').addClass('sp-open');
			}, 10);
		};

		frame.update_markup = function() {
			var current_page = frame.currentResult.current_page;
			var found_posts = frame.currentResult.found_posts;
			var max_num_pages = frame.currentResult.max_num_pages;
			var posts = frame.currentResult.posts;

			$sp_frame.find('#spb-page-number').val(current_page);
			$sp_frame.find('span.spb-page-total').text(max_num_pages);

			$list.find('.spb-item').remove();

			for( var i in frame.currentResult.posts ) {
				if ( !frame.currentResult.posts.hasOwnProperty(i) ) continue;
				frame.addListItem(frame.currentResult.posts[i]);
			}
		};

		frame.addListItem = function( postData ) {
			var $newItem = $list_blueprint.clone();

			$newItem
				.css('display', '')
				.removeClass('spb-template');


			//10, "http://kwexperts.ca/wp-content/uploads/2015/09/expcity.png", "Some text!", "This is a test title."

			$newItem
				.find('a')
					.attr('data-post_id', postData.post_id)
					.attr('data-image', postData.image.src)
					.attr('data-thumb', postData.thumbnail.src)
					.attr('data-title', postData.title)
					.attr('data-alt', postData.alt)
					.attr('href', postData.image.src)
					.attr('title', postData.title)
					.end()
				.find('img')
					.attr('src', postData.thumbnail.src)
					.attr('width', postData.thumbnail.width)
					.attr('height', postData.thumbnail.height)
					.attr('alt', postData.alt)
					.end()
				.find('.spb-image-name')
					.text( postData.title );

			$list.append( $newItem );
		};

		frame.get_server_results = function() {
			$sp_frame.addClass('sp-loading');

			var search = $sp_frame.find('#spb-search').val();
			var library_id = $sp_frame.find('#spb-library').val();
			var page_number = $sp_frame.find('#spb-page-number').val();

			// Kill any active ajax requests
			if ( frame.ajax_request !== false ) {
				frame.ajax_request.abort();
				frame.ajax_request = false;
			}

			frame.ajax_request = jQuery.ajax({
				url: sb_data.url,
				data: {
					sb_ajax: 1,
					sb_nonce: sb_data.nonce,
					sb: {
						page_number: page_number,
						library_id: library_id,
						search: search
					}
				},
				type: "POST",
				dataType: "json",
				complete: function( ajax_response, status, xhr ) {
					frame.ajax_request = false;
					$sp_frame.removeClass('sp-loading');

					// Ajax result has been returned
					if ( status === "success" ) {
						var data = jQuery.parseJSON(ajax_response.responseText);

						if ( !data ) {
							alert( "Failed to parse JSON response. See console for more info." );
							if ( typeof console.log == 'function' ) console.log(ajax_response, status);
							return false;
						}

						frame.currentResult = data;
						frame.update_markup();
						return;
					}

					// Acceptable abort message - user closed dialog or another query was started.
					if ( status === "abort" ) return;

					// Unknown status
					alert( "Uncaught Server Return Status: " + status + "\n\nFor debugging information, see console." );
					if ( typeof console.log == 'function' ) console.log(ajax_response, status);
				}
			});
		};

		// When browsing, open a popup. Send submitted results in the callback (cb)
		frame.browse = function() {
			frame.open();

			if ( !frame.did_initial_request ) {
				frame.get_server_results();
				frame.did_initial_request = true;
			}
		};

		// Changing the page using buttons
		frame.goToPage = function( new_page_number ) {
			if ( new_page_number < 1 ) new_page_number = 1;
			if ( new_page_number > frame.currentResult.max_num_pages ) new_page_number = frame.currentResult.max_num_pages;
			if ( frame.currentResult.current_page === new_page_number ) return false;

			$sp_frame.find('#spb-page-number').val(new_page_number);
			frame.currentResult.current_page = new_page_number;
			frame.get_server_results();
		};

		// ------------------
		// EVENTS
		// ------------------

		// Clicking on an image item in the list
		$list.on('click', 'a', function() {
			var $item = jQuery(this);

			cb(
				// id, full_src, thumb_src, title, alt
				$item.attr('data-post_id'),
				$item.attr('data-image'),
				$item.attr('data-thumb'),
				$item.attr('data-title'),
				$item.attr('data-alt')
			);

			frame.close();
			return false;
		});

		// Clicking the cancel button should close the popup
		$sp_frame.on('click', '.spb-close', function() {
			frame.close();
			return false;
		});

		// Clicking on the background should close the popup
		$sp_frame.on('click', 'div.sp-browser-underlay', function(e) {
			if ( jQuery(e.target).hasClass('sp-browser-underlay') ) {
				frame.close();
				return false;
			}
		});

		// When stop typing, update frame (Search, Page Number)
		$sp_frame.on('keyup', '#spb-search, #spb-page-number', function(e){
			if ( typeof this.timeout != 'undefined' ) clearTimeout(this.timeout);

			this.timeout = setTimeout(function() {
				frame.get_server_results();
			}, 200);
		});

		// When changing, update frame (Page number, Library dropdown)
		$sp_frame.on('change', '#spb-page-number, #spb-library', frame.get_server_results );

		// Paging buttons
		$sp_frame
			.on('click', '#spb-page-first', function() { frame.goToPage( 1 ); })
			.on('click', '#spb-page-previous', function() { frame.goToPage( frame.currentResult.current_page - 1 ); })
			.on('click', '#spb-page-next', function() { frame.goToPage( frame.currentResult.current_page + 1 ); })
			.on('click', '#spb-page-last', function() { frame.goToPage( frame.currentResult.max_num_pages ); });
	}

	function initialize_field( $el ) {
		var $attachment_id = $el.find('input.acf-stockphoto-id');
		var $img = $el.find('div.acf-stockphoto-preview img');
		var $title = $el.find('span.acf-stockphoto-title');
		var $browse = $el.find('button.acf-stockphoto-browse-button');
		var $clear = $el.find('a.acf-stockphoto-clear-button');

		var $preview_containers = $el.find('div.acf-stockphoto-preview, span.acf-stockphoto-clear');

		var setPhotoValues = function( id, full_src, thumb_src, title, alt ) {
			$attachment_id.val(id);

			$img.attr( 'src', thumb_src )
			    .attr( 'alt', alt );

			$title.text(title);

			// Toggle the visibility of the preview area and "Clear" button based on whether value is given.
			if ( id || thumb_src ) {
				$preview_containers.css( 'display', 'inline' );
			}else{
				$preview_containers.css( 'display', 'none' );
			}
		};

		var uploader = new Stock_image_upload_dialog( setPhotoValues );

		$browse.on('click', function(e) {
			uploader.browse();
			return false;
		});

		$clear.on('click', function(e) {
			setPhotoValues( false, false, false, false, false );
			return false;
		});
	}

	if( typeof acf.add_action !== 'undefined' ) {

		/*
		 *  Initialize our fields when the document is ready, or when appended via repeater field.
		 */

		acf.add_action('ready append', function( $el ){

			acf.get_fields({ type : 'stock_photo'}, $el).each(function(){
				initialize_field( $(this) );
			});

		});

		// This is specific to Brant Experts website!
		var $editor_field = jQuery('.wp-editor-container').closest('.acf-field');

		if ( $editor_field.length > 0 ) {
			var $last_field = $editor_field.siblings('.acf-field').last();
			$last_field.before($editor_field);
		}


	}

})(jQuery);