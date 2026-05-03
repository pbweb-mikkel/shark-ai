<?php

function shark_ai_admin_style() {
    wp_enqueue_style('shark-ai-admin-styles', PB_PLUGIN_URL . '/admin/css/admin-style.css');
    wp_enqueue_script('shark-ai-admin-script', PB_PLUGIN_URL . '/admin/js/admin.min.js');
}
//add_action('admin_enqueue_scripts', 'pb_core_admin_style');