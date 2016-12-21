<?php
if( ! defined( 'ABSPATH' ) ) exit;

function eca_submit_article_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts(array(
	), $atts, 'eca_submit_article');
	
	ob_start();

	$allow_author_submissions = get_field( 'eca_enable_author_submissions', 'options' );

	// Error: Article submissions disabled
	if ( !$allow_author_submissions ) {
		?>
		<div class="eca-submission-disabled eca-notice eca-info">
			<p>Article submissions are currently <strong>disabled</strong>.</p>
		</div>
		<?php
		return ob_get_clean();
	}
	
	// Error: Not logged in
	if ( !is_user_logged_in() ) {
		?>
		<div class="eca-not-logged-in eca-notice eca-error">
			<p><strong>You must be logged in to submit an article.</strong></p>

			<?php
			if ( get_option( 'users_can_register' ) ) {
				?>
				<p>Please proceed to <a href="<?php echo esc_attr(wp_login_url(get_permalink())); ?>">login</a>,
					or <a href="<?php echo esc_attr(wp_registration_url()); ?>">create an account</a>.</p>
				<?php
			}else{
				?>
				<p>Please proceed to <a href="<?php echo esc_attr(wp_login_url(get_permalink())); ?>">login</a>.
					New account registrations are currently closed.</p>
				<?php
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	// Error: Do not have permission to post articles
	if ( !eca_can_user_submit_article() ) {
		?>
		<div class="eca-account-not-authorized eca-notice eca-error">
			<p><strong>Sorry, your account is not authorized to submit articles.</strong></p>

			<p>If you believe you are seeing this message in error, please contact the site administrator.</p>
		</div>
		<?php
		return ob_get_clean();
	}

	// Article was submitted
	if ( isset($_GET['eca_action']) && $_GET['eca_action'] == "save-post" ) {
		$all_posts = admin_url( 'edit.php/?post_type=post&author=' . get_current_user_id() );
		$preview_post = isset($_GET['post_url']) ? esc_url(stripslashes($_GET['post_url'])) : false;

		?>
		<div class="eca-article-submitted eca-notice eca-success">
			<p><strong>Success!</strong> Your article has been submitted. <?php if ( $preview_post ) { ?><a href="<?php echo esc_attr($preview_post); ?>">Preview your article</a>.<?php } ?></p>


		</div>

		<p><a href="<?php echo esc_attr(get_permalink()); ?>" class="button">Write another article</a></p>
		<?php
		return ob_get_clean();
	}

	// No messages have stopped us, display the article submission form.
	$redirect_url = add_query_arg( array(
		'updated' => true,
		'eca_action' => 'save-post',
		'post_url' => '%post_url%',
	), get_permalink() );

	$args = array(
		/* (string) Unique identifier for the form. Defaults to 'acf-form' */
		'id' => 'eca-article-submission-form',

		/* (int|string) The post ID to load data from and save data to. Defaults to the current post ID. Can also be set to 'new_post' to create a new post on submit */
		'post_id' => 'new_post',

		/* (array) An array of post data used to create a post. See wp_insert_post for available parameters. The above 'post_id' setting must contain a value of 'new_post' */
		'new_post' => array(
			'post_status' => 'pending',
			'post_category' => eca_get_user_categories(),
		),

		/* (array) An array of field group IDs/keys to override the fields displayed in this form */
		'field_groups' => array( 'group_57d8db9543c0a' ),

		/* (boolean) Whether or not to show the post title text field. Defaults to false */
		'post_title' => true,

		/* (boolean) Whether or not to show the post content editor field. Defaults to false */
		'post_content' => true,

		/* (string) The URL to be redirected to after the form is submit. Defaults to the current URL with a GET parameter '?updated=true'. */
		'return' => $redirect_url,

		/* (string) The text displayed on the submit button */
		'submit_value' => __("Save", 'eca'),

		/* (string) A message displayed above the form after being redirected. Can also be set to false for no message */
		'updated_message' => __("Article has been saved", 'eca'),
	);

	$args = apply_filters( 'eca-acf-form-args', $args );

	echo '<div id="eca-submit-article-form">';
	acf_form($args);
	echo '</div>';

	return ob_get_clean();
}
add_shortcode( 'eca_submit_article', 'eca_submit_article_shortcode' );
add_shortcode( 'eca_author_form', 'eca_submit_article_shortcode' ); // DEPRECATED!

/**
 * Automatically adds the [eca_submit_article] shortcode to the directory page, if it is not already present.
 *
 * @param $content
 * @return string
 */
function eca_auto_add_submit_article_shortcode( $content ) {
	// Ensure we are on the cantors' directory page
	if ( !is_main_query() ) return $content;
	if ( !is_singular('page') ) return $content;
	if ( (int) get_the_ID() !== (int) get_field( 'eca_submit_article_page', 'options', false ) ) return $content;

	// Don't add the shortcode if it already exists
	global $post;
	if ( stristr($post->post_content, "[eca_submit_article") ) return $content;
	if ( stristr($post->post_content, "[eca_author_form") ) return $content; // DEPRECATED

	return $content . "\n\n[eca_submit_article]";
}
add_action( 'the_content', 'eca_auto_add_submit_article_shortcode', 2 );