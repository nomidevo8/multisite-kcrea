<?php
namespace ServtechMPP\Includes\MinistriesGroup;

if (!defined('ABSPATH')) exit;

class MinistriesVisibility {

    private $post_type = 'ministries-group';
    private $acf_field_name = 'is_visible';
    private $acf_field_key  = 'field_69184e564b7d0';

    /**
     * Initialize class via static method
     */
    public static function init() {
        $instance = new self();
        $instance->register_hooks();
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Private to enforce init()
    }

    /**
     * Register hooks (only if CPT exists)
     */
    private function register_hooks() {
        add_action('init', [$this, 'maybe_init_hooks'], 20);
    }

    public function maybe_init_hooks() {
        // Only run if CPT exists
        if (!post_type_exists($this->post_type)) {
            return;
        }

        // Admin column hooks
        add_filter("manage_{$this->post_type}_posts_columns", [$this, 'add_column']);
        add_action("manage_{$this->post_type}_posts_custom_column", [$this, 'render_column'], 10, 2);

        // AJAX
        add_action('wp_ajax_toggle_is_visible_acf', [$this, 'ajax_toggle_acf']);

        // JS
        add_action('admin_footer', [$this, 'admin_js']);
    }

    public function add_column($columns) {
        $columns['is_visible_column'] = 'Visible';
        return $columns;
    }

    public function render_column($column, $post_id) {
        if ($column !== 'is_visible_column') return;

        $value = get_field($this->acf_field_name, $post_id);
        $icon  = $value ? '🟢' : '⚪';

        echo "<a href='#' class='acf-toggle-visible' data-id='{$post_id}'>{$icon}</a>";
    }

    public function admin_js() {
        $screen = get_current_screen();
        if ($screen->post_type !== $this->post_type) return;
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.acf-toggle-visible').on('click', function(e) {
                e.preventDefault();

                let el = $(this);
                let post_id = el.data('id');

                $.post(ajaxurl, {
                    action: 'toggle_is_visible_acf',
                    post_id: post_id,
                }, function(response) {
                    if (response === '1') {
                        el.text('🟢');
                    } else {
                        el.text('⚪');
                    }
                });
            });
        });
        </script>
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
