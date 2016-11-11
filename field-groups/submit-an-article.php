<?php
if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array (
		'key' => 'group_57d8db9543c0a',
		'title' => 'Submit an Article',
		'fields' => array (
			array (
				'key' => 'field_57df8bc97e37f',
				'label' => 'Featured Photo',
				'name' => 'eca_featured_photo_type',
				'type' => 'select',
				'instructions' => 'The featured photo will be displayed at the top of your article, and is also used on article listing pages.',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'Choose an image from the stock photo library' => 'Choose an image from the stock photo library',
					'Upload my own image' => 'Upload my own image',
				),
				'default_value' => array (
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_57da1c73315f5',
				'label' => 'Stock Featured Photo',
				'name' => 'eca_featured_photo',
				'type' => 'stock_photo',
				'instructions' => 'Select an image from our stock photo gallery below.',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_57df8bc97e37f',
							'operator' => '==',
							'value' => 'Choose an image from the stock photo library',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'font_size' => 14,
			),
			array (
				'key' => 'field_57df8bff7e380',
				'label' => 'Custom Featured Photo',
				'name' => 'eca_featured_photo_custom',
				'type' => 'image',
				'instructions' => 'Your photo must be at least 730×350 (in pixels), and less than 2mb in filesize. Allowed file types are PNG and JPG.',
				'required' => 1,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_57df8bc97e37f',
							'operator' => '==',
							'value' => 'Upload my own image',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'array',
				'preview_size' => 'thumbnail',
				'library' => 'uploadedTo',
				'min_width' => 730,
				'min_height' => 350,
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => 3,
				'mime_types' => 'jpg, jpeg, png, JPG, JPEG, PNG',
			),
			array (
				'key' => 'field_57f59e0a9f7bd',
				'label' => 'Publish Date',
				'name' => 'eca_publish_date',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => array (
					'Publish Immediately' => 'Publish Immediately',
					'Scheduled' => 'Scheduled',
				),
				'default_value' => array (
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'placeholder' => '',
				'disabled' => 0,
				'readonly' => 0,
			),
			array (
				'key' => 'field_57f59d7658689',
				'label' => 'Schedule On',
				'name' => 'schedule_on',
				'type' => 'date_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array (
					array (
						array (
							'field' => 'field_57f59e0a9f7bd',
							'operator' => '==',
							'value' => 'Scheduled',
						),
					),
				),
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'display_format' => 'F j, Y',
				'return_format' => 'Ymd',
				'first_day' => 0,
			),
			array (
				'key' => 'field_57d8dc01ae949',
				'label' => 'Keywords / Tags',
				'name' => 'eca_article_tags',
				'type' => 'taxonomy',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'taxonomy' => 'post_tag',
				'field_type' => 'multi_select',
				'allow_null' => 0,
				'add_term' => 1,
				'save_terms' => 1,
				'load_terms' => 1,
				'return_format' => 'id',
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'post',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'field',
		'hide_on_screen' => '',
		'active' => 0,
		'description' => '',
	));

endif;