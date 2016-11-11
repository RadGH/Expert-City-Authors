<?php
if( ! defined( 'ABSPATH' ) ) exit;

function eca_replace_email_with_cf7_link( $html, $user ) {
	$cf7 = get_field( 'eca_contact_form', 'options', false );

	if ( $cf7 ) {
		$full_name = eca_author_field_full_name( $user );
		$html = sprintf( '<a href="javascript:void(0);" class="eca-email-cf7" data-author="%d" data-name="%s">Send Email</a>', (int) $user->ID, esc_attr($full_name) );

		add_action( 'wp_footer', 'eca_display_contact_form_footer' );
	}

	return $html;
}
add_filter( 'eca-filter-author-email-formatted', 'eca_replace_email_with_cf7_link', 10, 2 );

function eca_display_contact_form_footer() {
	static $run_once = false; if ( $run_once ) return; $run_once = true;

	$cf7 = (int) get_field( 'eca_contact_form', 'options', false );
	if ( !$cf7 ) return;

	?>
	<div id="eca-cf7-author-email" class="eca-lightbox" style="display: none;">
		<div class="eca-lightbox-underlay"></div>
		<div class="eca-lightbox-main">
			<a href="#" class="eca-lightbox-close" title="Close this popup">&times; <span class="screen-reader-text">Close Dialog</span></a>
			<div class="eca-lightbox-content">
				<h2>Send an email to <span class="eca-cf7-author-name"></span></h2>
				<?php echo do_shortcode( '[contact-form-7 id="'. $cf7 .'" title=""]' ); ?>
			</div>
		</div>
	</div>
	<?php
}

function eca_add_author_name_and_email_to_form_submission( $contact_form ) {
	$data = stripslashes_deep( (array) $_POST );

	$author_id = empty($data['eca-author-id']) ? false : $data['eca-author-id'];
	if ( empty($author_id) ) return;

	$cf7 = (int) get_field( 'eca_contact_form', 'options', false );
	if ( !$cf7 ) return;

	// Check if the submitted form is set as the author contact form in the backend
	if ( $cf7 != $contact_form->id() ) return;

	// Get the author's name and email
	$email = eca_author_field_email( $author_id, false );
	$full_name = eca_author_field_full_name( $author_id );

	// Invalid email, do not send.
	if ( empty( $email ) || !is_email($email) ) return;

	// Change the recipient of the first mail property. It's protected so we have to work around it a bit.
	$mail = $contact_form->prop( 'mail' );
	$mail['recipient'] = "{$full_name} <{$email}>";
	$contact_form->set_properties( array( 'mail' => $mail ) );
}
add_action( 'wpcf7_before_send_mail', 'eca_add_author_name_and_email_to_form_submission' );