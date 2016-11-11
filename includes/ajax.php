<?php

if( !defined( 'ABSPATH' ) ) exit;

function stock_photo_browser_ajax() {
	if ( !isset($_POST['sb_ajax']) ) return;

	$nonce = isset($_POST['sb_nonce']) ? stripslashes($_POST['sb_nonce']) : false;

	if ( wp_verify_nonce( $nonce, 'stock-photo-browse' ) ) {
		$sb = isset($_POST['sb']) ? stripslashes_deep($_POST['sb']) : false;
		$search = isset($sb['search']) ? (string) $sb['search'] : null;
		$library_id = isset($sb['library_id']) ? (int) $sb['library_id'] : null;
		$page_number = isset($sb['page_number']) ? (int) $sb['page_number'] : null;

		$results = stock_photo_get_results( $search, $library_id, $page_number );

		echo json_encode($results);
		exit;
	}

	echo -1;
	exit;
}
add_action( 'init', 'stock_photo_browser_ajax', 15 );


function stock_photo_get_results( $search, $library, $page ) {
	$args = array(
		'post_type' => 'stock_photo',
		'orderby' => 'date',
		'order' => 'DESC',
		'posts_per_page' => 10,
		'paged' => $page > 1 ? $page : 1,
		'meta_query' => array(
			array(
				'key' => 'image',
				'value' => '',
				'compare' => '!=',
			),
		),
	);

	// Also see: stock_photos_post_type.php
	 if ( $search ) $args['s'] = (string) $search;

	if ( $library ) $args['tax_query'] = array(array(
		'taxonomy' => 'stock_library',
		'field' => 'term_id',
		'terms' => (int) $library
	));

	$query = new WP_Query($args);

	if ( $query->found_posts > 0 ) {
		$stock_photos = array();

		if ( $query->posts ) foreach( $query->posts as $p ) {
			$image_id = get_field( 'image', $p->ID, false );

			if ( !$image_id ) continue; // Shouldn't happen due to meta_query above.

			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			$image_src = wp_get_attachment_image_src( $image_id, 'full' );
			$thumb_src = wp_get_attachment_image_src( $image_id, 'medium' );

			$stock_photos[] = array(
				'post_id' => $p->ID,
				'title' => $p->post_title,
				'alt' => $alt,
				'image' => array(
					'src' => $image_src[0],
					'width' => $image_src[1],
					'height' => $image_src[2]
				),
				'thumbnail' => array(
					'src' => $thumb_src[0],
					'width' => $thumb_src[1],
					'height' => $thumb_src[2]
				),
			);
		}

		return array(
			'found_posts' => (int) $query->found_posts,
			'current_page' => isset($query->query_vars['paged']) ? (int) $query->query_vars['paged'] : 1,
			'max_num_pages' => (int) $query->max_num_pages,
			'posts' => $stock_photos,
		);
	}

	return array(
		'found_posts' => 0,
		'current_page' => 1,
		'max_num_pages' => 1,
		'posts' => array(),
	);
}