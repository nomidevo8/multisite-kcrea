<?php
namespace ServtechMPP\Admin\Submenus;

if (!defined('ABSPATH'))
    exit;

class MenuPageVisibility
{

    public static function init()
    {
        add_action('admin_menu', [self::class, 'register_submenu']);
        add_action('wp_ajax_update_page_visibility', [self::class, 'ajax_update_page_visibility']);
        add_action('wp_ajax_load_menu_pages', [self::class, 'wp_ajax_load_menu_pages_handler']);
        add_action('wp_nav_menu_objects', [self::class, 'wp_nav_menu_objects_handler']);

    }

    public static function register_submenu()
    {
        add_submenu_page(
            'pastor-settings',
            'Menu Page Visibility',
            'Menu Visibility',
            'manage_options',
            'menu-page-visibility',
            [self::class, 'render_page']
        );
    }

    public static function render_page()
    {
        ?>
        <div class="wrap">
            <h1>Menu Page Visibility</h1>

            <h2>Select Menu</h2>
            <?php
            $menus = wp_get_nav_menus();
            $current_menu_id = wp_get_nav_menu_object('main-menu')->term_id ?? 0;

            echo '<select name="selected_menu" id="selected_menu">';
            foreach ($menus as $menu) {
                $selected = ($menu->term_id === $current_menu_id) ? 'selected' : '';
                echo "<option value='{$menu->term_id}' {$selected}>{$menu->name}</option>";
            }
            echo '</select>';
            ?>

            <h2>Pages</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Visibility</th>
                    </tr>
                </thead>
                <tbody id="menu-pages-tbody">
                    <!-- Pages will populate via JS -->
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function ajax_update_page_visibility()
    {
        check_ajax_referer('menu_visibility_save', 'nonce');

        $page_id = intval($_POST['page_id']);
        $visible = intval($_POST['visible']);

        if ($page_id) {
            update_post_meta($page_id, '_menu_visible', $visible);
        }

        wp_send_json_success();
    }

    public static function wp_ajax_load_menu_pages_handler()
    {
        check_ajax_referer('menu_visibility_save', 'nonce');

        $menu_id = intval($_POST['menu_id']);
        $pages = [];

        if ($menu_id) {
            $menu_items = wp_get_nav_menu_items($menu_id);
            foreach ($menu_items as $item) {
                $pages[] = [
                    'ID' => $item->ID,
                    'title' => $item->title,
                    'type' => $item->object, // page, custom, category, etc.
                    'visible' => get_post_meta($item->object_id, '_menu_visible', true) !== '0' ? 1 : 0
                ];
            }
        }

        wp_send_json_success(['pages' => $pages]);
    }

    public static function wp_nav_menu_objects_handler($items)
    {
        foreach ($items as $key => $item) {
            $visible = get_post_meta($item->object_id, '_menu_visible', true);
            if ($visible === '0')
                unset($items[$key]);
        }
        return $items;
    }
}
