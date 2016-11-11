<?php
if( function_exists('acf_add_local_field_group') ):

	acf_add_local_field_group(array (
		'key' => 'group_57da0cd85a0e1',
		'title' => 'Author\'s Expert Category (Admin-Only)',
		'fields' => array (
			array (
				'key' => 'field_57da0cf10f6f6',
				'label' => 'Author\'s Expertise Category',
				'name' => 'eca_author_category',
				'type' => 'taxonomy',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'taxonomy' => 'category',
				'field_type' => 'select',
				'allow_null' => 1,
				'add_term' => 1,
				'save_terms' => 0,
				'load_terms' => 0,
				'return_format' => 'id',
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'user_form',
					'operator' => '==',
					'value' => 'edit',
				),
				array (
					'param' => 'current_user_role',
					'operator' => '==',
					'value' => 'administrator',
				),
			),
		),
		'menu_order' => -5,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'left',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

endif;