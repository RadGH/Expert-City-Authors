<?php
if( ! defined( 'ABSPATH' ) ) exit;

class ecaAuthorVideoWidget extends WP_Widget
{

	// Register widget with WordPress.
	public function __construct() {
		parent::__construct( 'ecaAuthorVideoWidget', // Base ID
			'Expert City Authors - Video', // Name
			array( 'description' => 'Displays the post author\'s video.' ) // Args
		);
	}

	// Front-end display of widget.
	public function widget( $widget, $instance ) {
		if ( !is_singular('post') && !is_author() ) return;

		global $post;

		// Widget Title
		$title = !empty( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : false;
		if ( $title ) $title = eca_replace_author_info_tags( $title, $post->post_author );
		
		// Video dimensions
		$width = !empty( $instance['width'] ) ? apply_filters( 'widget_width', $instance['width'] ) : 335;
		$height = !empty( $instance['height'] ) ? apply_filters( 'widget_height', $instance['height'] ) : 188;

		// Video URL
		$video_url = get_field( 'eca_video', 'user_' . $post->post_author );
		if ( !$video_url ) return;

		// Embed code. Display error for admins or the author of the article if this cannot be generated.
		$embed = wp_oembed_get( $video_url, array( 'width' => $width, 'height' => $height ) );
		if ( !$embed ) {
			if ( get_current_user_id() == $post->post_author || current_user_can('manage_options') )
				echo '<h2>Author Video Embed Error: Cannot create embed code. Please check video URL.';
			return;
		}

		if ( empty($embed) || is_wp_error($embed) ) return;

		echo $widget['before_widget'];

		?>
		<div class="eca-widget eca-widget-video">
			<div class="eca-widget-inner">
				<?php if ( $title ) echo $widget['before_title'], esc_html( $title ), $widget['after_title']; ?>

				<div class="eca-video-embed"><?php echo $embed; ?></div>
			</div>
		</div>
		<?php

		echo $widget['after_widget'];
	}

	// Sanitize widget form values as they are saved.
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['width'] = empty($new_instance['width']) ? false : (int) $new_instance['width'];
		$instance['height'] = empty($new_instance['height']) ? false : (int) $new_instance['height'];

		return $instance;
	}


	public function form( $instance ) {
		// Retrieve all of our fields from the $instance variable
		$fields = array(
			'title',
			'width',
			'height'
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

		<p>
			<label for="<?php echo esc_attr( $fields['width']['id'] ); ?>"><?php _e( 'Video Size:' ); ?></label>
			<br>
			<input type="number" style="width: 70px;"
			       id="<?php echo esc_attr( $fields['width']['id'] ); ?>"
			       name="<?php echo esc_attr( $fields['width']['name'] ); ?>"
			       value="<?php echo esc_attr( $fields['width']['value'] ); ?>"
					placeholder="335" />
			<input type="number" style="width: 70px;"
			       id="<?php echo esc_attr( $fields['height']['id'] ); ?>"
			       name="<?php echo esc_attr( $fields['height']['name'] ); ?>"
			       value="<?php echo esc_attr( $fields['height']['value'] ); ?>"
			       placeholder="188" />
		</p>

		<p class="description">The height of the video should be 56.25% of the width, rounded up. That's the aspect ratio of a 1080p video.</p>

		<?php
	}

} // class ecaAuthorVideoWidget

function register_widget_ecaAuthorVideoWidget() {
	register_widget( 'ecaAuthorVideoWidget' );
}
add_action( 'widgets_init', 'register_widget_ecaAuthorVideoWidget' );