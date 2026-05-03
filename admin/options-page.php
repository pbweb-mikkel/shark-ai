<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the options page menu item.
 */
function shark_ai_register_options_page() {
    add_options_page(
        __( 'Shark AI Indstillinger', 'pbweb' ),
        __( 'Shark AI', 'pbweb' ),
        'manage_options',
        'shark-ai-settings',
        'shark_ai_render_options_page'
    );
}
add_action( 'admin_menu', 'shark_ai_register_options_page' );

/**
 * Register settings and fields.
 */
function shark_ai_register_settings() {
    register_setting(
        'shark_ai_options_group',
        'shark-ai-post-types',
        [
            'type'              => 'array',
            'sanitize_callback' => 'shark_ai_sanitize_post_types',
            'default'           => [],
        ]
    );

    add_settings_section(
        'shark_ai_general_section',
        __( 'Generelle indstillinger', 'pbweb' ),
        '__return_false',
        'shark-ai-settings'
    );

    add_settings_field(
        'shark-ai-post-types',
        __( 'Vis schema for post types', 'pbweb' ),
        'shark_ai_render_post_types_field',
        'shark-ai-settings',
        'shark_ai_general_section'
    );
}
add_action( 'admin_init', 'shark_ai_register_settings' );

/**
 * Sanitize the post types option.
 */
function shark_ai_sanitize_post_types( $input ) {
    if ( ! is_array( $input ) ) {
        return [];
    }
    $all_post_types = array_keys( get_post_types( [ 'public' => true ] ) );

    $user = wp_get_current_user();

    new Shark_Log('save_shark_ai_settings', $user->user_login, $input);
    return array_values( array_intersect( $input, $all_post_types ) );
}

/**
 * Render the post types checkboxes field.
 */
function shark_ai_render_post_types_field() {
    $post_types = get_post_types( [ 'public' => true ], 'objects' );
    $saved      = (array) get_option( 'shark-ai-post-types', [] );

    foreach ( $post_types as $post_type ) {
        if(in_array($post_type->name, ['pb-page-element', 'pb-mega-menu','attachment'])){
            continue;
        }
        $checked = in_array( $post_type->name, $saved, true );
        printf(
            '<label style="display:block;margin-bottom:6px;">
                <input type="checkbox" name="shark-ai-post-types[]" value="%1$s" %2$s />
                %3$s <code>(%1$s)</code>
            </label>',
            esc_attr( $post_type->name ),
            checked( $checked, true, false ),
            esc_html( $post_type->label )
        );
    }
}

/**
 * Render the full options page.
 */
function shark_ai_render_options_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'shark_ai_options_group' );
            do_settings_sections( 'shark-ai-settings' );
            submit_button( __( 'Gem', 'pbweb' ) );
            ?>
        </form>
    </div>
    <?php
}

