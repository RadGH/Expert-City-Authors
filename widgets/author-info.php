<?php
if( ! defined( 'ABSPATH' ) ) exit;

class ecaAuthorInfoWidget extends WP_Widget
{

	// Register widget with WordPress.
	public function __construct() {
		parent::__construct( 'ecaAuthorInfoWidget', // Base ID
			'Expert City Authors - Info', // Name
			array( 'description' => 'Displays the author\'s information fields including Address, Phone, Email, and Website.' ) // Args
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

		$address = eca_author_field_address( $user );
		$phone = eca_author_field_phone( $user );

		if ( !$address && !$phone ) return;

		echo $widget['before_widget'];

		?>
		<div class="eca-widget eca-widget-info">
			<div class="eca-widget-inner">
				<?php if ( $title ) echo $widget['before_title'], esc_html( $title ), $widget['after_title']; ?>

				<div class="eca-author-info-container">

					<?php if ( $address ) { ?>
						<div class="eca-info-item eca-info-address">
							<div class="icon icon-address"><span class="dashicons dashicons-location-alt"></span></div>
							<div class="eca-info-text">
								<div class="eca-info-title"><strong>Address:</strong></div>
								<div class="eca-info-content"><?php echo $address; ?></div>
							</div>
						</div>
					<?php } ?>

					<?php if ( $phone ) { ?>
						<div class="eca-info-item eca-info-phone">
							<div class="icon icon-phone"><span class="dashicons dashicons-phone"></span></div>
							<div class="eca-info-text">
								<div class="eca-info-title"><strong>Phone:</strong></div>
								<div class="eca-info-content"><?php echo $phone; ?></div>
							</div>
						</div>
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

} // class ecaAuthorInfoWidget

function register_widget_ecaAuthorInfoWidget() {
	register_widget( 'ecaAuthorInfoWidget' );
}
add_action( 'widgets_init', 'register_widget_ecaAuthorInfoWidget' );