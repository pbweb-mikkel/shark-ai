<?php

define('SHARK_AI_VERSION', '0.1');

/**
 * Plugin Name: Shark AI
 * Description: Tilføjer AI funktionalitet til SEO formål.
 * Plugin URI:        https://pbweb.dk/
 * Version:           0.1
 * Author:            PB Web
 * Author URI:        https://pbweb.dk/
 * Text Domain:       pbweb
 * Domain Path:       /languages
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'admin/admin.php';
require_once 'logger/shark-log.php';

register_activation_hook( __FILE__, 'activate_shark_ai' );
function activate_shark_ai() {
    shark_add_log_table();
}
register_deactivation_hook( __FILE__, 'deactivate_shark_ai' );

function deactivate_shark_ai() {

}