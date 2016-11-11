<?php
/**
 * Display an author's information on their author archive page.
 */

if( ! defined( 'ABSPATH' ) ) exit;

$eca_user = get_queried_object();

$address = eca_author_field_address( $eca_user );
$phone = eca_author_field_phone( $eca_user );
$email = eca_author_field_email( $eca_user );
$website = eca_author_field_website( $eca_user );

$biography = eca_author_field_biography( $eca_user );
?>
<div class="eca-author-profile">
	<div class="author-profile-outer">
		<div class="author-profile-main">
				<?php if ( $address ) { ?>
					<div class="profile-meta profile-address">
						<span class="profile-icon dashicons dashicons-location-alt"></span>

						<div class="profile-meta-main">
							<div class="profile-meta-label">Address:</div>
							<div class="profile-meta-content"><?php echo $address; ?></div>
						</div>
					</div>
				<?php } ?>

				<?php if ( $phone ) { ?>
					<div class="profile-meta profile-phone">
						<span class="profile-icon dashicons dashicons-phone"></span>

						<div class="profile-meta-main">
							<div class="profile-meta-label">Phone:</div>
							<div class="profile-meta-content"><?php echo $phone; ?></div>
						</div>
					</div>
				<?php } ?>

				<?php if ( $email ) { ?>
					<div class="profile-meta profile-email">
						<span class="profile-icon dashicons dashicons-email-alt"></span>

						<div class="profile-meta-main">
							<div class="profile-meta-label">Email:</div>
							<div class="profile-meta-content"><?php echo $email; ?></div>
						</div>
					</div>
				<?php } ?>

				<?php if ( $website ) { ?>
					<div class="profile-meta profile-website">
						<span class="profile-icon dashicons dashicons-admin-links"></span>

						<div class="profile-meta-main">
							<div class="profile-meta-label">Website:</div>
							<div class="profile-meta-content"><?php echo $website; ?></div>
						</div>
					</div>
				<?php } ?>
		</div>

		<div class="author-profile-content">
			<?php echo wpautop($biography); ?>
		</div>
	</div>
</div>