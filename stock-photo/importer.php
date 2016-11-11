<?php
if( !defined( 'ABSPATH' ) ) exit;

function eca_stockphoto_import_submenu_item() {
	add_submenu_page(
		'edit.php?post_type=stock_photo',
		'Importer',
		'Importer',
		'edit_pages',
		'eca-sp-importer',
		'eca_stockphoto_import_page'
	);
}
add_action( 'admin_menu', 'eca_stockphoto_import_submenu_item' );

function eca_stockphoto_number_imported_per_run() {
	return apply_filters( 'eca_sp_imported_per_run', 1 );
}

function eca_stockphoto_import_page() {
	$images = eca_stockphoto_get_images();

	$imported_per_page = eca_stockphoto_number_imported_per_run();

	$initial_count = isset($_REQUEST['eca-sp-initial-count']) ? (int) $_REQUEST['eca-sp-initial-count'] : 0;

	$progress = isset($_REQUEST['eca-sp-import']) ? eca_stockphoto_process_images( $images, $imported_per_page ) : null;

	$remaining_photos = $progress ? (count($images) - $progress['imported']) : 0;
	$total_imported = $initial_count - $remaining_photos; // Initial count minus the number that have been imported so far

	?>
	<div class="wrap">
		<h2>Stock Photo Importer</h2>

		<p class="description">Import Directory: <code class="code"><?php echo esc_html( eca_stockphoto_get_image_dir() ); ?></code></p>

		<?php if ( $images ) { ?>

			<?php if ( $progress === null ) { ?>
				<h3><strong>You have <?php echo count($images); ?> image(s) ready to be imported.</strong></h3>

				<p>We will import <?php echo $imported_per_page; ?> images per run. This page will automatically refresh to continue with the next batch of images until completed. Once each photo is imported, the photo will be removed from the import directory. If any errors occur during the import, the process will stop allowing you to correct the errors.</p>

				<p>You will be able to stop the import manually by pressing the Abort Import button once it begins. Or, simply navigate away from this page.</p>

				<p><a href="<?php echo esc_attr( add_query_arg( array('eca-sp-import' => 1, 'eca-sp-initial-count' => count($images)) ) ); ?>" class="button button-primary">Begin Import</a></p>
			<?php }else{ ?>

				<?php if ( $progress === false || $progress['error'] ) { ?>
					<p><strong>Import Error: </strong> An error occurred while importing photos. The process has been halted. Please correct any issues.</p>

					<?php if ( $progress ) { ?>
						<h3>Error Description:</h3>

						<?php echo wpautop($progress['error']); ?>
					<?php } ?>

					<p><a href="<?php echo esc_attr( add_query_arg( array('eca-sp-import' => 1) ) ); ?>" class="button button-primary">Resume Import</a></p>
				<?php }else{ ?>
					<h3><?php echo $total_imported; ?> of <?php echo $initial_count; ?> image(s) have been imported (<?php echo round(100*($total_imported/$initial_count)); ?>% complete).</h3>

					<p>We will import <?php echo $imported_per_page; ?> images per run. This page will automatically refresh to continue with the next batch of images until completed. Once each photo is imported, the photo will be removed from the import directory. If any errors occur during the import, the process will stop allowing you to correct the errors.</p>

					<?php if ( $progress['message'] ) echo wpautop($progress['message']); ?>

					<?php if ( $remaining_photos > 0 ) { ?>
						<p>You can abort the process by pressing Abort Import below, or by navigating to a different page.</p>

						<p><a href="<?php echo esc_attr( remove_query_arg( 'eca-sp-import' ) ); ?>" class="button button-secondary" id="stop-import">Stop Importing</a></p>

						<script type="text/javascript">
							jQuery(function() {
								var timeout = false;

								jQuery('#stop-import').click(function(e) {
									clearTimeout(timeout);
									return true;
								});

								timeout = setTimeout(function() {
									jQuery('#stop-import').closest('p').after( jQuery('<p>').html('Importing the next set of photos&hellip; please wait.') );
									window.location.reload();
								}, 2000);
							});
						</script>
					<?php }else{ ?>
						<p>We're done!</p>
					<?php } ?>

				<?php } ?>
			<?php } ?>

		<?php }else{ ?>

			<?php if ( $progress !== null ) { ?>
				<p>All images have been imported successfully.</p>
			<?php }else{ ?>
				<p>Nothing to import.</p>
			<?php } ?>

		<?php } ?>
	</div>
	<?php
}

function eca_stockphoto_get_image_dir() {
	return apply_filters( 'eca_stockphoto_import_dir', ABSPATH . 'images/' );
}

function eca_stockphoto_get_images() {
	$dir = eca_stockphoto_get_image_dir();

	$allowed_extensions = array(
		'png',
		'jpg',
		'jpeg',
	);

	$files = scandir( $dir );

	$first = true;

	if ( $files ) foreach( $files as $key => $filename ) {
		$file_path = $dir . $filename;

		// Make sure it's not a directory
		if ( !is_file( $file_path ) ) {
			unset($files[$key]);
			continue;
		}

		// Check if it has an image extension
		$extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		if ( !in_array($extension, $allowed_extensions) ) {
			unset($files[$key]);
			continue;
		}

		// For the first valid image, check if it is writable. If not, complain
		if ( $first ) {
			$first = false;

			// If first image is not writable, throw an error and return false.
			if ( !is_writeable( $file_path ) ) {
				echo '<div class="error"><p><strong>Stock Photo Importer &ndash; Error:</strong> No write permission for the first file in your import directory. Please ensure all files are writable.</p><p><code class="code">', esc_attr($file_path), '</code></p></div>';
				return false;
			}
		}
	}

	return array_values($files);
}

function eca_stockphoto_process_images( $images, $imported_per_page ) {
	$result = array(
		'message' => '',
		'imported' => 0,
		'error' => false,
	);

	$dir = eca_stockphoto_get_image_dir();

	
	foreach( $images as $key => $filename ) {
		@set_time_limit( 60 ); // If doing many imports, this keeps php from giving a "response took to long" error. Doesn't work if safe mode is on, but who uses that?

		if ( $result['imported'] >= $imported_per_page ) {
			$result['message'] = 'Import iteration completed.';
			return $result;
		}

		$file_path = $dir . $filename;

		$attachment = eca_handle_upload_from_path( $file_path );

		if ( $attachment && !empty($attachment['attachment_id']) ) {
			update_post_meta( $attachment['attachment_id'], 'is_stock_photo', 1 );

			$args = array(
				'post_type' => 'stock_photo',
				'post_title' => ucwords(str_replace(array('-', '_'), ' ', pathinfo( $filename, PATHINFO_FILENAME ) )),
				'post_status' => 'publish',
			);

			// We will call this manually.
			remove_action( 'save_post', 'eca_save_stock_photo_automatic_title_from_image' );
			$post_id = wp_insert_post( $args );

			if ( $post_id ) {
				// Set image as field
				update_field( 'image', $attachment['attachment_id'], $post_id );
				
				if ( @unlink( $file_path ) || file_exists( $file_path ) ) {
					// Success!
					$result['imported'] += 1;
					
					// Let post-type.php assign the title from the image's metadata
					eca_save_stock_photo_automatic_title_from_image( $post_id, true );
					continue;
				}else{
					// Adding stock photo and attachment succeeded, but failed to delete attachment.
					$result['message'] = 'Import succeeded, but we were unable to remove the file: ' . $filename . '. Fix the file permissions, then try again.';
				}
			}else{
				// Adding stock photo post failed. Give error, delete the attachment.
				$result['message'] = 'Image was added to the media library, but we were unable to create the stock photo object: ' . $filename;
				wp_delete_attachment( $attachment['attachment_id'], true );
			}
		}else{
			$result['message'] = 'Failed to import attachment.';
			if ( isset($attachment['error']) ) $result['message'] .= ' Error: ' . $attachment['error'];
		}

		// Abort here. If we didn't have an error, the loop should have hit a "continue".
		$result['error'] = true;
		return $result;
	}

	$result['message'] = 'Import complete.';

	return $result;
}

/**
 * eca_handle_upload_from_path( $path, $add_to_media = true )
 *
 * Takes a path to a file, simulates an upload and passes it through wp_handle_upload. If $add_to_media
 * is set to true (default), the file will appear under Media in the dashboard. Otherwise, it's hidden,
 * but stored in the uploads folder.
 *
 * Return Values: Similar to wp_handle_upload, but with attachment_id:
 *  - Success: Returns an array including file, url, type, attachment_id.
 *  - Failure: Returns an array with the key "error" and a value including the error message.
 *
 * @param $path
 * @param bool $add_to_media
 *
 * @return array
 */
function eca_handle_upload_from_path( $path, $add_to_media = true ) {
	if ( !file_exists($path) ) {
		return array( 'error' => 'File does not exist.' );
	}

	$filename = basename($path);
	$filename_no_ext = pathinfo($path, PATHINFO_FILENAME);
	$extension = pathinfo($path, PATHINFO_EXTENSION);

	// Simulate uploading a file through $_FILES. We need a temporary file for this.
	$tmp = tmpfile();
	$data = stream_get_meta_data($tmp);
	$tmp_path = $data['uri'];
	fwrite($tmp, file_get_contents( $path ));
	fseek($tmp, 0); // If we don't do this, WordPress thinks the file is empty

	$fake_FILE = array(
		'name'      => $filename,
		'type'      => 'image/' . $extension,
		'tmp_name'  => $tmp_path,
		'error'     => UPLOAD_ERR_OK,
		'size'      => filesize($path),
	);

	// Trick is_uploaded_file() by adding it to the superglobal
	$_FILES[basename($tmp_path)] = $fake_FILE;

	$result = wp_handle_upload( $fake_FILE, array( 'test_form' => false, 'action' => 'local' ) );

	fclose($tmp); // Close tmp file
	@unlink($tmp_path); // Delete the tmp file. Closing it should also delete it, so hide any warnings with @
	unset( $_FILES[basename($tmp_path)] ); // Clean up our $_FILES mess.

	$result['attachment_id'] = 0;

	if ( empty($result['error']) && $add_to_media ) {
		$args = array(
			'post_title' => $filename_no_ext,
			'post_content' => '',
			'post_status' => 'publish',
			'post_mime_type' => $result['type'],
		);

		$result['attachment_id'] = wp_insert_attachment( $args, $result['file'] );

		if ( is_wp_error( $result ) ) {
			return false;
		}else if ( is_wp_error( $result['attachment_id'] ) ) {
			$result['attachment_id'] = 0;
		}else{
			$attach_data = wp_generate_attachment_metadata( $result['attachment_id'], $result['file'] );
			wp_update_attachment_metadata( $result['attachment_id'], $attach_data );
		}
	}

	return $result;
}