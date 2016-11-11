<?php
if( ! defined( 'ABSPATH' ) ) exit;

// Adds the "manage_categories" role to authors, so that they can create tags.
$role = get_role( 'author' );
$role->add_cap( 'manage_categories' );