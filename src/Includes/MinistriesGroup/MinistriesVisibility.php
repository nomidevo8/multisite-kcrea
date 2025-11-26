<?php
namespace ServtechMPP\Includes\MinistriesGroup;

if (!defined('ABSPATH')) exit;

class MinistriesVisibility {

    private $post_type = 'ministries-group';
    private $acf_field_name = 'is_visible';
    private $acf_field_key  = 'field_69184e564b7d0';

    public static function init() {
        $instance = new self();
        $instance->register_hooks();
    }

    private function __construct() {
        // Private to enforce init()
    }

    private function register_hooks() {
        add_action('init', [$this, 'maybe_init_hooks'], 20);
    }

    public function maybe_init_hooks() {
        if (!post_type_exists($this->post_type)) return;

        add_filter("manage_{$this->post_type}_posts_columns", [$this, 'add_column']);
        add_action("manage_{$this->post_type}_posts_custom_column", [$this, 'render_column'], 10, 2);

        add_action('wp_ajax_toggle_is_visible_acf', [$this, 'ajax_toggle_acf']);
        add_action('admin_footer', [$this, 'admin_js']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function add_column($columns) {
        $columns['is_visible_column'] = 'Visible';
        return $columns;
    }

    public function render_column($column, $post_id) {
        if ($column !== 'is_visible_column') return;

        $value = get_field($this->acf_field_name, $post_id);
        $checked = $value ? 'checked' : '';

        echo '<label class="min-toggle">';
        echo '<input type="checkbox" class="acf-toggle-visible" data-id="' . esc_attr($post_id) . '" ' . $checked . ' />';
        echo '<span class="slider"></span>';
        echo '</label>';
        echo '<span class="acf-toggle-loader" style="display:none;margin-left:5px;">⏳</span>';
    }

    public function admin_js() {
        $screen = get_current_screen();
        if ($screen->post_type !== $this->post_type) return;
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.acf-toggle-visible').on('change', function() {
                var el = $(this);
                var post_id = el.data('id');
                var loader = el.closest('label').next('.acf-toggle-loader');

                loader.show();
                el.prop('disabled', true);

                $.post(ajaxurl, {
                    action: 'toggle_is_visible_acf',
                    post_id: post_id
                }, function(response) {
                    loader.hide();
                    el.prop('disabled', false);
                });
            });
        });
        </script>
        <?php
    }

    public function enqueue_styles() {
        $screen = get_current_screen();
        if ($screen->post_type !== $this->post_type) return;

        ?>
        <style>
        .min-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .min-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .min-toggle .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .min-toggle .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        .min-toggle input:checked + .slider {
            background-color: #4CAF50;
        }
        .min-toggle input:checked + .slider:before {
            transform: translateX(26px);
        }
        </style>
        <?php
    }

    public function ajax_toggle_acf() {
        if (!current_user_can('edit_posts')) wp_die('0');

        $post_id = intval($_POST['post_id']);
        $current = get_field($this->acf_field_name, $post_id);
        $new_value = $current ? 0 : 1;

        update_field($this->acf_field_key, $new_value, $post_id);

        echo $new_value;
        wp_die();
    }
}
