<?php

/**
 * Registers the stock photo post type and a "libraries" category.
 */
function register_stock_photo_post_type() {
	$labels = array(
		'name'                  => 'Stock Photos',
		'singular_name'         => 'Stock Photo',
		'menu_name'             => 'Stock Photos',
		'name_admin_bar'        => 'Stock Photo',
		'archives'              => 'Stock Photo Archives',
		'parent_item_colon'     => 'Parent Stock Photo:',
		'all_items'             => 'All Stock Photos',
		'add_new_item'          => 'Add New Stock Photo',
		'add_new'               => 'Add Stock Photo',
		'new_item'              => 'New Stock Photo',
		'edit_item'             => 'Edit Stock Photo',
		'update_item'           => 'Update Stock Photo',
		'view_item'             => 'View Stock Photo',
		'search_items'          => 'Search Stock Photo',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Add into stock photo',
		'uploaded_to_this_item' => 'Uploaded to this stock photo',
		'items_list'            => 'Stock photo list',
		'items_list_navigation' => 'Stock photo list navigation',
		'filter_items_list'     => 'Filter stock photo list',
	);
	$args = array(
		'label'                 => 'Stock Photo',
		'description'           => 'A place to list stock photos that can be used by authors for the featured image of their articles.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'author', 'revisions', ),
		'taxonomies'            => array( 'stock_library' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 90,
		'menu_icon'             => 'dashicons-camera',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'rewrite'               => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'stock_photo', $args );


	// Create the "Library" taxonomy.
	$labels = array(
		'name'                       => 'Libraries',
		'singular_name'              => 'Library',
		'menu_name'                  => 'Libraries',
		'all_items'                  => 'All Libraries',
		'parent_item'                => 'Parent Library',
		'parent_item_colon'          => 'Parent Library:',
		'new_item_name'              => 'New Library Name',
		'add_new_item'               => 'Add New Library',
		'edit_item'                  => 'Edit Library',
		'update_item'                => 'Update Library',
		'view_item'                  => 'View Library',
		'separate_items_with_commas' => 'Separate libraries with commas',
		'add_or_remove_items'        => 'Add or remove Libraries',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Libraries',
		'search_items'               => 'Search Libraries',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Libraries',
		'items_list'                 => 'Libraries list',
		'items_list_navigation'      => 'Libraries list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
		'rewrite'                    => false,
	);
	register_taxonomy( 'stock_library', array( 'stock_photo' ), $args );

}
add_action( 'init', 'register_stock_photo_post_type', 5 );


/**
 * Registers a new column that will display a thumbnail of the stock photo on the stock photo dashboard
 *
 * @param $columns
 * @return array
 */
function eca_stock_photo_thumbnail_column( $columns ) {
	$columns = array_merge(
		array_slice( $columns, 0, 1, true ),
		array( 'stock_photo' => 'Stock Photo' ),
		array_slice( $columns, 1, null, true )
	);

	return $columns;
}
add_filter( 'manage_edit-stock_photo_columns', 'eca_stock_photo_thumbnail_column' ) ;


/**
 * Display image on the stock photo dashboard screen in a column.
 *
 * @param $column
 * @param $post_id
 */
function eca_display_stock_photo_thumbnail_column_content( $column, $post_id ) {
	switch( $column ) {

		case 'stock_photo' :

			$attachment_id = get_field( 'image', $post_id, false );

			if ( $attachment_id ) {
				$full = wp_get_attachment_image_src( $attachment_id, 'full' );
				$medium = wp_get_attachment_image_src( $attachment_id, 'medium' );
				$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

				printf(
					'<a href="%s" target="_blank" title="View full size image"><img src="%s" width="%s" height="%s" alt="%s"></a>',
					esc_attr($full[0]),
					esc_attr($medium[0]),
					(int) $medium[1],
					(int) $medium[2],
					esc_attr($alt)
				);
			}else{
				echo '<em>No image</em>';
			}

			break;

		default: break;
	}
}
add_action( 'manage_stock_photo_posts_custom_column', 'eca_display_stock_photo_thumbnail_column_content', 10, 2 );


/**
 * Filters the media shown on the media library, and in the media uploader popup. This hides stock photo attachments, unless you are editing a stock photo.
 *
 * @param $query
 * @return mixed
 */
function eca_hide_or_show_stock_photos_depending_on_attached_object( $query ) {
	if ( !is_main_query() ) return $query;
	if ( $query->get('post_type') !== "attachment" ) return $query;

	// We either want to show all stock photos, or all regular photos. Not both.
	$show_stock_photos = false;

	// When retrieving media attached to a post, if that post is a stock photo, only return other stock photo media.
	// This should only apply on the stock photo page.
	if ( $query->get('post_parent') && get_post_type( $query->get('post_parent') ) == "stock_photo" ) {
		$show_stock_photos = true;
	}

	if ( $show_stock_photos ) {
		$query->set('meta_key', 'is_stock_photo');
		$query->set('meta_value', '1');
		$query->set('post_parent', false);
		if ( isset($query->query_vars['post_parent']) )unset($query->query_vars['post_parent']);
		if ( isset($query->query['post_parent']) ) unset($query->query['post_parent']);
	}else{
		$query->set('meta_key', 'is_stock_photo');
		$query->set('meta_value', ' ');
		$query->set('meta_compare', 'NOT EXISTS');
	}

	return $query;
}
add_action( 'pre_get_posts','eca_hide_or_show_stock_photos_depending_on_attached_object' );


/**
 * When an image is uploaded, if it was uploaded to a stock photo it is given metadata to identify it as a stock image.
 *
 * @param $attachment_id
 */
function eca_save_stock_photo_identifier_to_media( $attachment_id ) {
	// When adding an attachment to a post, if the post is a stock photo, use a meta key to identify it.
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'upload-attachment' ) {
		if ( isset($_REQUEST['post_id']) ) {
			if ( get_post_type( (int) $_REQUEST['post_id'] ) == "stock_photo" ) {
				update_post_meta( $attachment_id, 'is_stock_photo', 1 );
				return;
			}
		}

		delete_post_meta( $attachment_id, 'is_stock_photo' );
	}
}
add_action( 'add_attachment', 'eca_save_stock_photo_identifier_to_media', 5 );


/**
 * Permanently delete the attachment when deleting a stock photo, unless another stock photo is using the same image.
 * @param $post_id
 */
function eca_delete_attachment_when_deleting_stock_photo( $post_id ) {
	if ( get_post_type($post_id) == "stock_photo" ) {
		$thumbnail_id = get_field( 'image', $post_id, false );
		if ( !$thumbnail_id ) return;

		// Check if another stock photo is using this image too (shouldn't, but it might)
		$query = new WP_Query(array(
			'post_type' => 'stock_photo',
			'post_status' => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ),
			'post__not_in' => array( $post_id ),
			'meta_query' => array(
				array(
					'key' => 'image',
					'value' => $thumbnail_id,
					'type' => 'NUMERIC',
				)
			),
			'posts_per_page' => 1, // Only need one post for us to leave the attachment alone
		));

		if ( empty($query->posts) ) {
			wp_delete_attachment( $thumbnail_id, true );
		}
	}
}
add_action( 'before_delete_post', 'eca_delete_attachment_when_deleting_stock_photo', 40 );


/**
 * Include our stockphoto script at the bottom of the page. This is hooked by the stock photo field on pages where it is used.
 */
function eca_include_stock_photo_field_scripts() {
	// Enqueueing a script is not an option, because this action is triggered after the wp_enqueue_scripts action. So we display these directly.
	// We also add the markup for the stock photo browser here.

	echo "\n";
	
	$sb_data = array(
		'nonce' => wp_create_nonce('stock-photo-browse'),
		'url' => site_url(),
	);

	?>
	<script type="text/javascript">var sb_data = <?php echo json_encode($sb_data); ?>;</script>
	<link rel="stylesheet" href="<?php echo esc_attr(ECA_URL . '/assets/stock_photo.css'); ?>?v=<?php echo esc_attr(ECA_VERSION); ?>" type="text/css" media="all" />
	<script type="text/javascript" src="<?php echo esc_attr(ECA_URL . '/assets/stock_photo.js'); ?>?v=<?php echo esc_attr(ECA_VERSION); ?>" defer></script>

	<div class="sp-browser-wrapper sp-closed" id="sp-browser-container" style="display: none;">
		<div class="sp-browser-underlay"></div>
		<div class="sp-browser">
			<div class="sp-browser-inner">

				<div class="spb-title">Stock Photo Browser <a href="#" class="spb-close spb-close-x">&times;</a></div>

				<div class="spb-filters">
					<?php
					$dropdown_html = wp_dropdown_categories(array(
						'echo' => 0,
						'hide_if_empty' => true,
						'show_option_all' => 'All Stock Photos',
						'show_count' => true,
						'name' => 'spb-library',
						'id' => 'spb-library',
						'hierarchical' => true,
						'taxonomy' => 'stock_library',
					));
					if ( $dropdown_html ) {
						?>
						<div class="spb-library">
							<label for="spb-library">Photo Library:</label>
							<?php echo $dropdown_html; ?>
						</div>
						<?php
					}
					?>

					<?php
                    // Also see: ajax.php
					?>
					<div class="spb-search">
						<label for="spb-search" class="screen-reader-text">Search Stock Photos:</label>
						<input type="text" id="spb-search" placeholder="search">
					</div>
				</div>

				<div class="spb-no-results" style="display: none;">
					<p>No stock photos found.</p>
				</div>

				<div class="spb-gallery">
				</div>

				<div class="spb-pagination">
					<span class="spb-count-total"></span>
					<input type="button" value="&laquo;" id="spb-page-first" class="spb-page-button button button-secondary">
					<input type="button" value="&lt;" id="spb-page-previous" class="spb-page-button button button-secondary">
					Page <input type="number" title="Specify page to go to" id="spb-page-number" placeholder="1">
					of <span class="spb-page-total">1</span>
					<input type="button" value="&gt;" id="spb-page-next" class="spb-page-button button button-secondary">
					<input type="button" value="&raquo;" id="spb-page-last" class="spb-page-button button button-secondary">
				</div>

				<div class="spb-controls">
					<?php /* <input type="button" value="Select Photo" id="spb-select-photo" class="spb-button button button-primary button-alt"> */ ?>
					<input type="button" value="Cancel" id="spb-cancel" class="spb-button spb-close button button-secondary">
				</div>

				<!-- Template to be cloned for each item that is loaded -->
				<div class="spb-item spb-template" style="display:none">
					<div class="spb-item-inner">
						<div class="spb-item-wrap">
							<a href=""><img src="" alt=""><span class="spb-image-name"></span></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}


/* When saving a stock photo, if there is no title, use the caption or title of the image */
function eca_save_stock_photo_automatic_title_from_image( $stock_photo_id, $ignore_existing_title = false ) {
	if ( get_post_type( $stock_photo_id ) !== "stock_photo" ) return;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

	// Remove the hook so we don't get an infinite loop
	remove_action( 'save_post', 'eca_save_stock_photo_automatic_title_from_image' );

	$thumbnail_id = get_field( 'image', $stock_photo_id, false );
	if ( !$thumbnail_id ) return;

	$existing_title = get_the_title( $stock_photo_id );

	if ( $ignore_existing_title || empty($existing_title) || strtolower($existing_title) == "(no title)" || strtolower($existing_title) == "auto-draft"  ) {
		$attachment = get_post( $thumbnail_id );

		$new_title = $attachment->post_excerpt; // Caption
		if ( !$new_title ) $new_title = $attachment->post_content; // Description
		if ( !$new_title ) $new_title = $attachment->post_title; // Title
		if ( !$new_title ) $new_title = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ); // Alt

		$new_title = ucwords(str_replace(array('-', '_'), ' ', $new_title));

		$args = array(
			'ID' => $stock_photo_id,
			'post_title' => $new_title
		);

		wp_update_post( $args );
	}

	add_action( 'save_post', 'eca_save_stock_photo_automatic_title_from_image', 30 );
}
add_action( 'save_post', 'eca_save_stock_photo_automatic_title_from_image', 30 );


/**
 * When saving a post that used a stock photo, get the stock photo's media ID and save that as the featured image.
 * Then, remove the stock photo fromt he stock photos library.
 *
 * Note that the stock photo ID is different than the stock photo image ID. Stock photos are posts which have their own attached image.
 * 
 * @param $post_id
 */
function eca_save_stock_photo_as_featured_image( $post_id ) {
	remove_action( 'acf/save_post', 'eca_save_stock_photo_as_featured_image' );

	$thumbnail_id = false;

	$photo_type = get_post_meta( $post_id, 'eca_featured_photo_type', true );
	if ( !$photo_type ) $photo_type = get_post_meta( $post_id, 'eca_featured_photo_type', true );

	$stock_photo_id = get_post_meta( $post_id, 'eca_featured_photo', true);
	if ( !$stock_photo_id ) $stock_photo_id = get_post_meta( $post_id, 'eca_featured_photo', true );


	if ( $photo_type == "Upload my own image" ) {
		if ( $p = get_post_meta( $post_id, 'eca_featured_photo_custom', true ) ) {
			$thumbnail_id = $p;
		}
	}else{
		// Stock photo returns the stock photo ID. The attachment ID is the "image" field of the stock photo.
		if ( $stock_photo_id ) {
			$thumbnail_id = get_post_meta( $post_id, 'image', true );

			// Unhook the stock photo image so it is a regular media item, then trash the stock photo item.
			wp_trash_post( $stock_photo_id );
			delete_post_meta( $thumbnail_id, 'is_stock_photo' );
		} 
	}

	// Set the post's featured image as the selected image
	if ( $thumbnail_id ) {
		set_post_thumbnail( $post_id, $thumbnail_id );
	}

	add_action( 'acf/save_post', 'eca_save_stock_photo_as_featured_image', 20 );
}
add_action( 'acf/save_post', 'eca_save_stock_photo_as_featured_image', 20 );


/**
 * Define the "Stock Photo" ACF field (see acf-field.php)
 */
if( !class_exists('acf_plugin_stock_photo') ) :
	class acf_plugin_stock_photo {

		function __construct() {
			$this->settings = array(
				'version'	=> '1.0.0',
				'url'		=> trailingslashit(ECA_URL),
				'path'		=> ECA_PATH
			);

			// include field
			add_action('acf/include_field_types', 	array($this, 'include_field_types')); // v5
			add_action('acf/register_fields', 		array($this, 'include_field_types')); // v4 (Not supported)
		}


		/*
		*  include_field_types
		*
		*  This function will include the field type class
		*
		*  @type	function
		*  @date	17/02/2016
		*  @since	1.0.0
		*
		*  @param	$version (int) major ACF version. Defaults to false
		*  @return	n/a
		*/

		function include_field_types( $version = false ) {
			// ACF v4 did not supply version, and I don't care to support it.
			if( !$version ) {
				echo '<p><strong>Expert City Authors - ERROR:</strong> This plugin requires ACF Version 5 or higher for the stock photo field to work correctly.</p>';
				return;
			}

			include_once( ECA_PATH . '/stock-photo/acf-field.php' );
		}

	}

	new acf_plugin_stock_photo();
endif;