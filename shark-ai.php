<?php

define('SHARK_AI_VERSION', '1.0');

/**
 * Plugin Name: Shark AI
 * Description: Tilføjer AI funktionalitet til SEO formål.
 * Plugin URI:        https://pbweb.dk/
 * Version:           1.0
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


add_action('init', function() {
    register_post_meta('', '_ai_schema_markup', [
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
        'auth_callback' => function() { return current_user_can('edit_posts'); }
    ]);
});

add_action('wp_head', function() {
    if (is_singular()) {
        global $post;
        $schema = get_post_meta($post->ID, '_ai_schema_markup', true);
        $post_types = get_option( 'shark-ai-post-types', ['sb_accordion_faqs', 'page', 'post'] );

        if (!empty($schema) && $schema != '{}' && in_array($post->post_type, $post_types)) {
            echo "\n<!-- AI GEO Schema Markup -->\n";
            echo "<script type=\"application/ld+json\">\n";
            echo wp_unslash($schema);
            echo "\n</script>\n";
        }
    }
});

add_filter('register_post_type_args', function($args, $post_type){

    $post_types = get_option( 'shark-ai-post-types', ['sb_accordion_faqs', 'page', 'post'] );
    if(in_array($post_type, $post_types)){
        $args['show_in_rest'] = true;
        $args['rest_base'] = $post_type;
    }

    return $args;
}, 10, 2);