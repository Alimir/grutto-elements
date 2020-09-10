<?php
/**
 * Include admin files
 * // @echo HEADER
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die('No Naughty Business Please !');
}

Grutto_WC_Settings_Tab::init();

require_once( GRUTTO_DIR . '/admin/admin-functions.php' );
require_once( GRUTTO_DIR . '/admin/admin-hooks.php' );