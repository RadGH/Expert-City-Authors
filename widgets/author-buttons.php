<?php
if( ! defined( 'ABSPATH' ) ) exit;

class ecaAuthorButtonsWidget extends WP_Widget
{

	// Register widget with WordPress.
	public function __construct() {
		parent::__construct( 'ecaAuthorButtonsWidget', // Base ID
			'Expert City Authors - Buttons', // Name
			array( 'description' => 'Displays buttons to view the author\'s website or send them an email.' ) // Args
		);
	}

	// Front-end display of widget.
	public function widget( $widget, $instance ) {
		if ( !is_singular('post') && !is_author() ) return;

		global $post;

		$user = get_user_by( 'id', $post->post_author );

		// Widget Title
		$title = !empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		if ( $title ) $title = eca_replace_author_info_tags( $title, $post->post_author );

		$email = eca_author_field_email( $user );
		$website = eca_author_field_website( $user );

		if ( !$email && !$website ) return;

		echo $widget['before_widget'];

		?>
		<div class="eca-widget eca-widget-buttons">
			<div class="eca-widget-inner">
				<?php if ( $title ) echo $widget['before_title'], esc_html( $title ), $widget['after_title']; ?>

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
			</div>
		</div>
		<?php

		echo $widget['after_widget'];
	}

	// Sanitize widget form values as they are saved.
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}


	public function form( $instance ) {
		// Retrieve all of our fields from the $instance variable
		$fields = array(
			'title'
		);

		// Format each field into ID/Name/Value array
		foreach ( $fields as $name ) {
			$fields[$name] = array(
				'id'    => $this->get_field_id( $name ),
				'name'  => $this->get_field_name( $name ),
				'value' => null,
			);

			if ( isset( $instance[$name] ) ) {
				$fields[$name]['value'] = $instance[$name];
			}
		}

		if ( $fields['button_text']['value'] === null ) {
			$fields['button_text']['value'] = '';
		}

		// Display the widget in admin dashboard:
		?>

		<p>
			<label for="<?php echo esc_attr( $fields['title']['id'] ); ?>"><?php _e( 'Title (Optional):' ); ?></label>
			<input class="widefat" type="text"
			       id="<?php echo esc_attr( $fields['title']['id'] ); ?>"
			       name="<?php echo esc_attr( $fields['title']['name'] ); ?>"
			       value="<?php echo esc_attr( $fields['title']['value'] ); ?>" />
		</p>

		<?php
		// Display a line about using filter tags in the ECA shortcodes.
		eca_widget_filter_description();
		?>

		<?php
	}

} // class ecaAuthorButtonsWidget

function register_widget_ecaAuthorButtonsWidget() {
	register_widget( 'ecaAuthorButtonsWidget' );
}
add_action( 'widgets_init', 'register_widget_ecaAuthorButtonsWidget' );