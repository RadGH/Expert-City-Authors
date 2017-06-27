<?php
/**
 * Display a member on the member directory.
 *
 * To access current user, use the $eca_user variable.
 */

if( ! defined( 'ABSPATH' ) ) exit;

$eca_user = $u; // from eca_directory.php

$user_profile_url = get_author_posts_url( $eca_user->ID );
$category = eca_author_field_category( $eca_user );
$full_name = eca_author_field_full_name( $eca_user );
$phone = eca_author_field_phone( $eca_user );
$address = eca_author_field_address( $eca_user );
$email = eca_author_field_email( $eca_user );
$website = eca_author_field_website( $eca_user );

$photo_id = eca_author_field_photo( $eca_user, false );
$photo_post = $photo_id ? get_post( $photo_id ) : false;
$photo = $photo_post ? acf_get_attachment( $photo_post ) : false;

$image = false;
$alt = false;

if ( $photo ) {
	$image = empty($photo['sizes']['medium']) ? false : array( $photo['sizes']['medium'], $photo['sizes']['medium-width'], $photo['sizes']['medium-height']);
	if ( !$image ) $image = empty($photo['sizes']['thumbnail']) ? false : array( $photo['sizes']['thumbnail'], $photo['sizes']['thumbnail-width'], $photo['sizes']['thumbnail-height']);
	if ( !$image ) $image = empty($photo['sizes']['url']) ? false : array( $photo['url'], $photo['width'], $photo['height']);

	$alt = $photo['alt'];
	if ( empty($photo['alt']) ) $alt = $photo['caption'];
	if ( empty($photo['alt']) ) $alt = $photo['title'];
}

if ( $image ) {
	$img_element = '<img src="'. esc_attr($image[0]) .'" alt="'. $alt .'" width="'. $image[1] .'" height="'. $image[2] .'" title="View '. esc_attr("$full_name") .'\'s Profile">';
}else{
	// Fall back to default user's avatar
	$img_element = get_avatar( $eca_user->ID, 250, ECA_URL.'/assets/person.png', 'An avatar of the user ' . $eca_user->display_name );
}
?>
<div class="eca-directory-item">
	<div class="item-outer">
		<div class="item-inner">

			<div class="directory-column column-1">
				<div class="photo-area">
					<a href="<?php echo esc_attr($user_profile_url); ?>">
						<?php echo $img_element; ?>
					</a>
				</div>
			</div>

			<div class="directory-column column-2">
				<div class="profile-area">
					<h3><a href="<?php echo esc_attr($user_profile_url); ?>"><?php echo $full_name; ?></a></h3>

					<?php if ( $category ) {
						$label = get_field( 'Expert Category', 'options' );
						if ( !$label ) $label = "Expert Category";
						?>
					<div class="profile-category-label"><?php echo $label; ?>:</div>

					<div class="profile-category">
						<?php echo $category; ?>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</div>
					<?php } ?>

					<?php if ( $address ) { ?>
					<div class="profile-meta profile-address">
						<span class="profile-icon dashicons dashicons-location-alt"></span>&nbsp;<?php echo $address; ?>
					</div>
					<?php } ?>

					<?php if ( $phone ) { ?>
					<div class="profile-meta profile-phone">
						<span class="profile-icon dashicons dashicons-phone"></span>&nbsp;<?php echo $phone; ?>
					</div>
					<?php } ?>

					<?php if ( $email || $website ) { ?>
						<div class="eca-author-buttons-container <?php echo ($email && $website) ? 'eca-two-buttons' : 'eca-one-button'; ?>">
							<?php if ( $email ) {
								// <div class="icon icon-email"><span class="dashicons dashicons-email-alt"></span></div>
								?>
								<span class="eca-button-item eca-button-email">
									<?php echo $email; ?>
								</span>
							<?php } ?>

							<?php if ( $website ) {
								// <div class="icon icon-website"><span class="dashicons dashicons-admin-links"></span></div>
								?>
								<span class="eca-button-item eca-button-website">
									<?php echo $website; ?>
								</span>
							<?php } ?>
						</div>
					<?php } ?>

				</div>
			</div>
			
		</div>
	</div>
</div>