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
    $saved = (array) get_option( 'shark-ai-post-types', [] );
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

        <h2><?php _e( 'Post Type Status', 'pbweb' ); ?></h2>
        <p><?php _e( 'Oversigt over alle registrerede post types og deres REST API-konfiguration.', 'pbweb' ); ?></p>
        <style>
            #shark-ai-pt-status th, #shark-ai-pt-status td { vertical-align: middle; }
            #shark-ai-pt-status .dashicons { font-size: 18px; width: 18px; height: 18px; }
            #shark-ai-pt-status .yes { color: #46b450; }
            #shark-ai-pt-status .no  { color: #dc3232; }
            #shark-ai-pt-status tr.shark-enabled { background: #f0fff4; }
        </style>
        <table id="shark-ai-pt-status" class="widefat striped" style="max-width:1100px;">
            <thead>
                <tr>
                    <th><?php _e( 'Post Type', 'pbweb' ); ?></th>
                    <th><?php _e( 'Label', 'pbweb' ); ?></th>
                    <th><?php _e( 'Public', 'pbweb' ); ?></th>
                    <th><?php _e( 'show_in_rest', 'pbweb' ); ?></th>
                    <th><?php _e( 'rest_base', 'pbweb' ); ?></th>
                    <th><?php _e( 'rest_controller', 'pbweb' ); ?></th>
                    <th><?php _e( 'has_archive', 'pbweb' ); ?></th>
                    <th><?php _e( 'Hierarkisk', 'pbweb' ); ?></th>
                    <th><?php _e( 'Shark AI aktiv', 'pbweb' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $all_post_types = get_post_types( ['public' => true], 'objects' );
            foreach ( $all_post_types as $pt ) :
                $shark_enabled = in_array( $pt->name, $saved, true );
                $yes = '<span class="dashicons dashicons-yes yes"></span>';
                $no  = '<span class="dashicons dashicons-no-alt no"></span>';

                $show_in_rest = ! empty( $pt->show_in_rest ) ? $yes : $no;
                $public       = ! empty( $pt->public ) ? $yes : $no;
                $hierarchical = ! empty( $pt->hierarchical ) ? $yes : $no;
                $has_archive  = ! empty( $pt->has_archive )
                    ? ( is_string( $pt->has_archive ) ? '<code>' . esc_html( $pt->has_archive ) . '</code>' : $yes )
                    : $no;
                $rest_base    = ! empty( $pt->rest_base ) ? '<code>' . esc_html( $pt->rest_base ) . '</code>' : '<em style="color:#999;">—</em>';
                $rest_ctrl    = ! empty( $pt->rest_controller_class ) ? '<code>' . esc_html( $pt->rest_controller_class ) . '</code>' : '<em style="color:#999;">—</em>';
                ?>
                <tr<?php echo $shark_enabled ? ' class="shark-enabled"' : ''; ?>>
                    <td><code><?php echo esc_html( $pt->name ); ?></code></td>
                    <td><?php echo esc_html( $pt->label ); ?></td>
                    <td><?php echo $public; ?></td>
                    <td><?php echo $show_in_rest; ?></td>
                    <td><?php echo $rest_base; ?></td>
                    <td><?php echo $rest_ctrl; ?></td>
                    <td><?php echo $has_archive; ?></td>
                    <td><?php echo $hierarchical; ?></td>
                    <td><?php echo $shark_enabled ? '<strong style="color:#46b450">' . __( 'Ja', 'pbweb' ) . '</strong>' : '<span style="color:#999">' . __( 'Nej', 'pbweb' ) . '</span>'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2><?php _e( 'REST API Status', 'pbweb' ); ?></h2>
        <p><?php _e( 'Oversigt over WordPress REST API\'ens nuværende status og konfiguration.', 'pbweb' ); ?></p>
        <?php
        $rest_enabled   = ! ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL );
        $rest_url       = get_rest_url();
        $rest_disabled  = apply_filters( 'rest_enabled', true ); // legacy filter check

        // Check if REST API is actually accessible by inspecting filters
        $rest_prefix    = rest_get_url_prefix();
        $namespaces     = [];
        if ( function_exists( 'rest_get_server' ) ) {
            $server     = rest_get_server();
            $namespaces = $server->get_namespaces();
        }
        ?>
        <style>
            #shark-ai-rest-status td:first-child { font-weight: 600; width: 260px; }
            #shark-ai-rest-status td { vertical-align: top; padding: 8px 10px; }
            .shark-rest-tag { display:inline-block; background:#e0e0e0; border-radius:3px; padding:1px 7px; margin:2px 2px 2px 0; font-family:monospace; font-size:12px; }
        </style>
        <table id="shark-ai-rest-status" class="widefat" style="max-width:1100px;margin-bottom:30px;">
            <tbody>
                <tr>
                    <td><?php _e( 'REST API aktiveret', 'pbweb' ); ?></td>
                    <td>
                        <?php if ( $rest_disabled ) : ?>
                            <span class="dashicons dashicons-yes yes" style="color:#46b450;font-size:18px;"></span>
                            <strong style="color:#46b450"><?php _e( 'Ja — REST API er tilgængeligt', 'pbweb' ); ?></strong>
                        <?php else : ?>
                            <span class="dashicons dashicons-no-alt" style="color:#dc3232;font-size:18px;"></span>
                            <strong style="color:#dc3232"><?php _e( 'Nej — REST API er deaktiveret via filter', 'pbweb' ); ?></strong>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'REST API endpoint', 'pbweb' ); ?></td>
                    <td><code><?php echo esc_url( $rest_url ); ?></code></td>
                </tr>
                <tr>
                    <td><?php _e( 'URL-præfiks', 'pbweb' ); ?></td>
                    <td><code>/<?php echo esc_html( $rest_prefix ); ?>/</code></td>
                </tr>
                <tr>
                    <td><?php _e( 'WP JSON endpoint', 'pbweb' ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( $rest_url ); ?>" target="_blank">
                            <?php echo esc_url( $rest_url ); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'SSL / HTTPS', 'pbweb' ); ?></td>
                    <td>
                        <?php if ( is_ssl() ) : ?>
                            <span class="dashicons dashicons-lock" style="color:#46b450;font-size:18px;"></span>
                            <strong style="color:#46b450"><?php _e( 'HTTPS aktiv', 'pbweb' ); ?></strong>
                        <?php else : ?>
                            <span class="dashicons dashicons-unlock" style="color:#f0a500;font-size:18px;"></span>
                            <strong style="color:#f0a500"><?php _e( 'Ikke HTTPS — REST API fungerer men er ikke krypteret', 'pbweb' ); ?></strong>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Registrerede namespaces', 'pbweb' ); ?></td>
                    <td>
                        <?php if ( ! empty( $namespaces ) ) : ?>
                            <?php foreach ( $namespaces as $ns ) : ?>
                                <span class="shark-rest-tag"><?php echo esc_html( $ns ); ?></span>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <em style="color:#999"><?php _e( 'Ingen namespaces fundet', 'pbweb' ); ?></em>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e( 'Shark AI namespace', 'pbweb' ); ?></td>
                    <td>
                        <?php
                        $shark_ns = 'shark-ai/v1';
                        $shark_ns_url = get_rest_url( null, $shark_ns );
                        if ( in_array( $shark_ns, $namespaces, true ) ) : ?>
                            <span class="dashicons dashicons-yes yes" style="color:#46b450;font-size:18px;"></span>
                            <code><?php echo esc_html( $shark_ns ); ?></code> —
                            <a href="<?php echo esc_url( $shark_ns_url ); ?>" target="_blank"><?php echo esc_url( $shark_ns_url ); ?></a>
                        <?php else : ?>
                            <span class="dashicons dashicons-minus" style="color:#999;font-size:18px;"></span>
                            <em style="color:#999"><?php _e( 'Ikke registreret', 'pbweb' ); ?></em>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
    <?php
}
