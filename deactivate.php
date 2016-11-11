<?php
if( ! defined( 'ABSPATH' ) ) exit;

// Remove the "manage_categories" role from authors, which is the WordPress default behavior
$role = get_role( 'author' );
$role->remove_cap( 'manage_categories' );