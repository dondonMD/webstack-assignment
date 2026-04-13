<?php

if (!defined('ABSPATH')) {
    exit;
}

$front_page_id = 5;
$elementor_data = [
    [
        'id' => 'modisa-shortcode-section',
        'elType' => 'section',
        'settings' => [
            'layout' => 'full_width',
            'gap' => 'no',
            'content_width' => 'full_width',
        ],
        'elements' => [
            [
                'id' => 'modisa-shortcode-column',
                'elType' => 'column',
                'settings' => [
                    '_column_size' => 100,
                    '_inline_size' => null,
                ],
                'elements' => [
                    [
                        'id' => 'modisa-shortcode-widget',
                        'elType' => 'widget',
                        'widgetType' => 'shortcode',
                        'settings' => [
                            'shortcode' => '[modisa_assignment_homepage]',
                        ],
                        'elements' => [],
                    ],
                ],
            ],
        ],
    ],
];

$posts_to_delete = [1, 17, 18, 19, 29, 30, 31];

foreach ($posts_to_delete as $post_id) {
    if (get_post($post_id)) {
        wp_delete_post($post_id, true);
    }
}

$front_page = get_post($front_page_id);

if (!$front_page instanceof WP_Post) {
    WP_CLI::error('Front page not found.');
}

wp_update_post([
    'ID' => $front_page_id,
    'post_title' => 'Home',
    'post_name' => 'home',
    'post_status' => 'publish',
    'post_content' => '',
]);

update_option('show_on_front', 'page');
update_option('page_on_front', $front_page_id);
update_option('page_for_posts', 0);

update_user_meta(1, 'nickname', 'John Doe');
wp_update_user([
    'ID' => 1,
    'display_name' => 'John Doe',
    'first_name' => 'John',
    'last_name' => 'Doe',
]);

update_post_meta($front_page_id, '_wp_page_template', 'elementor_canvas');
update_post_meta($front_page_id, '_elementor_edit_mode', 'builder');
update_post_meta($front_page_id, '_elementor_template_type', 'wp-page');
update_post_meta($front_page_id, '_elementor_version', '4.0.1');
update_post_meta($front_page_id, '_elementor_page_settings', []);
update_post_meta($front_page_id, '_elementor_data', wp_slash(wp_json_encode($elementor_data)));

WP_CLI::success('Homepage configured.');
