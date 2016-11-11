<?php
if( ! defined( 'ABSPATH' ) ) exit;

// check if class already exists
if( !class_exists('acf_field_stock_photo') ) :
	class acf_field_stock_photo extends acf_field {


		/*
		*  __construct
		*
		*  This function will setup the field type data
		*
		*  @type	function
		*  @date	5/03/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/

		function __construct( $settings ) {

			/*
			*  name (string) Single word, no spaces. Underscores allowed
			*/

			$this->name = 'stock_photo';


			/*
			*  label (string) Multiple words, can include spaces, visible when selecting a field type
			*/

			$this->label = __('Stock Photo', 'acf-stock_photo');


			/*
			*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
			*/

			$this->category = 'content';


			/*
			*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
			*/

			$this->defaults = array(
				// 'preview_size' => 'thumbnail',
			);


			/*
			*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
			*  var message = acf._e('stock_photo', 'error');
			*/

			$this->l10n = array(
				'error'	=> __('Error! Please enter a higher value', 'acf-stock_photo'),
			);


			/*
			*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
			*/

			$this->settings = $settings;


			// do not delete!
			parent::__construct();

		}


		/*
		*  render_field_settings()
		*
		*  Create extra settings for your field. These are visible when editing a field
		*
		*  @type	action
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$field (array) the $field being edited
		*  @return	n/a
		*/

		function render_field_settings( $field ) {

			/*
			*  acf_render_field_setting
			*
			*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
			*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
			*
			*  More than one setting can be added by copy/paste the above code.
			*  Please note that you must also have a matching $defaults value for the field name (font_size)
			*/

			/*
			acf_render_field_setting( $field, array(
				'label'			=> __('Font Size','acf-stock_photo'),
				'instructions'	=> __('Customise the input font size','acf-stock_photo'),
				'type'			=> 'number',
				'name'			=> 'font_size',
				'prepend'		=> 'px',
			));
			*/

			// preview_size
			/*
			acf_render_field_setting( $field, array(
				'label'			=> __('Preview Size','acf'),
				'instructions'	=> __('Size of the preview of the selected image','eca'),
				'type'			=> 'select',
				'name'			=> 'preview_size',
				'choices'		=> acf_get_image_sizes()
			));
			*/

		}



		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param	$field (array) the $field being rendered
		*
		*  @type	action
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$field (array) the $field being edited
		*  @return	n/a
		*/

		function render_field( $field ) {

			/*
			$field = Array (
			    [ID]                => 752
			    [key]               => field_57da1c73315f5
			    [label]             => Stock Featured Photo
			    [name]              => acf[field_57da1c73315f5]
			    [prefix]            => acf
			    [type]              => stock_photo
			    [value]             =>
			    [menu_order]        => 1
			    [instructions]      => This is the image at the top of your article, and is also used on the front page or article listing pages.
			    [required]          => 1
			    [id]                => acf-field_57da1c73315f5
			    [class]             =>
			    [conditional_logic] => Array (
		            [0] => Array (
	                    [0] => Array (
                            [field] => field_57df8bc97e37f
                            [operator] => ==
                            [value] => Choose an image from the stock photo library
                        )
	                )
		        )
			    [parent]            => 730
			    [wrapper]           => Array (
		            [width] =>
		            [class] =>
		            [id] =>
		        )
			    [_name]             => eca_featured_photo
			    [_input]            => acf[field_57da1c73315f5]
			    [_valid]            => 1
			    [font_size]         => 14
			)
			*/

			$title = "";
			$src = "";
			$alt = "";

			if ( (int) $field['value'] ) {
				$image = wp_get_attachment_image_src( (int) $field['value'], 'medium' );

				$title = get_the_title($field['value']);
				$src = $image[0];
				$alt = get_post_meta( (int) $field['value'], '_wp_attachment_image_alt', true );
			}

			// Add an action that will include our stockphoto script at the bottom of the page.
			if ( !has_action( 'wp_footer', 'eca_include_stock_photo_field_scripts' ) ) {
				add_action( 'wp_footer', 'eca_include_stock_photo_field_scripts', 100 ); // see /includes/stock-photos-post-type.php
			}

			?>
			<div class="acf-stockphoto-preview">
				<img src="<?php echo esc_attr($src); ?>" alt="<?php echo esc_attr($alt); ?>">
				<div class="acf-stockphoto-details"><span class="acf-stockphoto-title"><?php echo esc_html($title); ?></span></div>
			</div>

			<div class="acf-stockphoto-controls">
				<button type="button" class="button acf-stockphoto-browse-button">Browse</button> <span class="acf-stockphoto-clear" <?php if ( !$field['value'] ) echo 'style="display: none;"'; ?>>(<a href="#" class="acf-stockphoto-clear-button">Clear Selection</a>)</span>
			</div>

			<input type="hidden" name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($field['value']) ?>" class="acf-stockphoto-id" />
			<?php
		}


		/*
		*  input_admin_enqueue_scripts()
		*
		*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
		*  Use this action to add CSS + JavaScript to assist your render_field() action.
		*
		*  @type	action (admin_enqueue_scripts)
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	n/a
		*  @return	n/a
		*/

		/*

		function input_admin_enqueue_scripts() {

			// vars
			$url = $this->settings['url'];
			$version = $this->settings['version'];


			// register & include JS
			wp_register_script( 'acf-input-stock_photo', "{$url}assets/js/input.js", array('acf-input'), $version );
			wp_enqueue_script('acf-input-stock_photo');


			// register & include CSS
			wp_register_style( 'acf-input-stock_photo', "{$url}assets/css/input.css", array('acf-input'), $version );
			wp_enqueue_style('acf-input-stock_photo');

		}

		*/


		/*
		*  input_admin_head()
		*
		*  This action is called in the admin_head action on the edit screen where your field is created.
		*  Use this action to add CSS and JavaScript to assist your render_field() action.
		*
		*  @type	action (admin_head)
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	n/a
		*  @return	n/a
		*/

		/*

		function input_admin_head() {



		}

		*/


		/*
		   *  input_form_data()
		   *
		   *  This function is called once on the 'input' page between the head and footer
		   *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
		   *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
		   *  seen on comments / user edit forms on the front end. This function will always be called, and includes
		   *  $args that related to the current screen such as $args['post_id']
		   *
		   *  @type	function
		   *  @date	6/03/2014
		   *  @since	5.0.0
		   *
		   *  @param	$args (array)
		   *  @return	n/a
		   */

		/*

		function input_form_data( $args ) {



		}

		*/


		/*
		*  input_admin_footer()
		*
		*  This action is called in the admin_footer action on the edit screen where your field is created.
		*  Use this action to add CSS and JavaScript to assist your render_field() action.
		*
		*  @type	action (admin_footer)
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	n/a
		*  @return	n/a
		*/

		/*

		function input_admin_footer() {



		}

		*/


		/*
		*  field_group_admin_enqueue_scripts()
		*
		*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
		*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
		*
		*  @type	action (admin_enqueue_scripts)
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	n/a
		*  @return	n/a
		*/

		/*

		function field_group_admin_enqueue_scripts() {

		}

		*/


		/*
		*  field_group_admin_head()
		*
		*  This action is called in the admin_head action on the edit screen where your field is edited.
		*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
		*
		*  @type	action (admin_head)
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	n/a
		*  @return	n/a
		*/

		/*

		function field_group_admin_head() {

		}

		*/


		/*
		*  load_value()
		*
		*  This filter is applied to the $value after it is loaded from the db
		*
		*  @type	filter
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$value (mixed) the value found in the database
		*  @param	$post_id (mixed) the $post_id from which the value was loaded
		*  @param	$field (array) the field array holding all the field options
		*  @return	$value
		*/

		/*

		function load_value( $value, $post_id, $field ) {

			return $value;

		}

		*/


		/*
		*  update_value()
		*
		*  This filter is applied to the $value before it is saved in the db
		*
		*  @type	filter
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$value (mixed) the value found in the database
		*  @param	$post_id (mixed) the $post_id from which the value was loaded
		*  @param	$field (array) the field array holding all the field options
		*  @return	$value
		*/

		/*

		function update_value( $value, $post_id, $field ) {

			return $value;

		}

		*/


		/*
		*  format_value()
		*
		*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		*
		*  @type	filter
		*  @since	3.6
		*  @date	23/01/13
		*
		*  @param	$value (mixed) the value which was loaded from the database
		*  @param	$post_id (mixed) the $post_id from which the value was loaded
		*  @param	$field (array) the field array holding all the field options
		*
		*  @return	$value (mixed) the modified value
		*/

		/*

		function format_value( $value, $post_id, $field ) {

			// bail early if no value
			if( empty($value) ) {

				return $value;

			}


			// apply setting
			if( $field['font_size'] > 12 ) {

				// format the value
				// $value = 'something';

			}


			// return
			return $value;
		}

		*/


		/*
		*  validate_value()
		*
		*  This filter is used to perform validation on the value prior to saving.
		*  All values are validated regardless of the field's required setting. This allows you to validate and return
		*  messages to the user if the value is not correct
		*
		*  @type	filter
		*  @date	11/02/2014
		*  @since	5.0.0
		*
		*  @param	$valid (boolean) validation status based on the value and the field's required setting
		*  @param	$value (mixed) the $_POST value
		*  @param	$field (array) the field array holding all the field options
		*  @param	$input (string) the corresponding input name for $_POST value
		*  @return	$valid
		*/

		/*

		function validate_value( $valid, $value, $field, $input ){

			// Basic usage
			if( $value < $field['custom_minimum_setting'] )
			{
				$valid = false;
			}


			// Advanced usage
			if( $value < $field['custom_minimum_setting'] )
			{
				$valid = __('The value is too little!','acf-stock_photo'),
			}


			// return
			return $valid;

		}

		*/


		/*
		*  delete_value()
		*
		*  This action is fired after a value has been deleted from the db.
		*  Please note that saving a blank value is treated as an update, not a delete
		*
		*  @type	action
		*  @date	6/03/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (mixed) the $post_id from which the value was deleted
		*  @param	$key (string) the $meta_key which the value was deleted
		*  @return	n/a
		*/

		/*

		function delete_value( $post_id, $key ) {



		}

		*/


		/*
		*  load_field()
		*
		*  This filter is applied to the $field after it is loaded from the database
		*
		*  @type	filter
		*  @date	23/01/2013
		*  @since	3.6.0
		*
		*  @param	$field (array) the field array holding all the field options
		*  @return	$field
		*/

		/*

		function load_field( $field ) {

			return $field;

		}

		*/


		/*
		*  update_field()
		*
		*  This filter is applied to the $field before it is saved to the database
		*
		*  @type	filter
		*  @date	23/01/2013
		*  @since	3.6.0
		*
		*  @param	$field (array) the field array holding all the field options
		*  @return	$field
		*/

		/*

		function update_field( $field ) {

			return $field;

		}

		*/


		/*
		*  delete_field()
		*
		*  This action is fired after a field is deleted from the database
		*
		*  @type	action
		*  @date	11/02/2014
		*  @since	5.0.0
		*
		*  @param	$field (array) the field array holding all the field options
		*  @return	n/a
		*/

		/*

		function delete_field( $field ) {



		}

		*/


	}

	new acf_field_stock_photo( $this->settings );
endif;