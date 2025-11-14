<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MPP_Giving_Link {

    private static $added_menus = []; // Track which menus we've added to
    private static $target_locations = ['main-menu', 'header', 'footer', 'Header', 'Footer']; // Menu locations to target

    public static function init() {
        // Print menus in admin (optional)
        add_action( 'admin_init', [ __CLASS__, 'print_selected_menus_info' ] );

        // Append Giving link on every page load
        add_action( 'wp_loaded', [ __CLASS__, 'append_giving_link_to_all_menus' ] );

        // Customize menu item display
        add_filter( 'wp_setup_nav_menu_item', [ __CLASS__, 'setup_giving_menu_item' ] );
    }

    /**
     * Print menus assigned to selected locations (admin debug)
     */
    public static function print_selected_menus_info() {
        $locations = get_nav_menu_locations();

        foreach ( self::$target_locations as $loc ) {
            if ( ! empty($locations[$loc]) ) {
                $menu_obj = wp_get_nav_menu_object( $locations[$loc] );
                if ( ! $menu_obj ) continue;
            }
        }
    }

    /**
     * Append Giving link to all target menus
     */
    public static function append_giving_link_to_all_menus() {
        $locations = get_nav_menu_locations();

        foreach ( self::$target_locations as $loc ) {
            if ( ! empty($locations[$loc]) ) {
                $menu_id = $locations[$loc];
                if ( ! in_array($menu_id, self::$added_menus) ) {
                    self::add_link_to_menu( $menu_id );
                    self::$added_menus[] = $menu_id;
                }
            }
        }
    }

    /**
     * Add the Giving link to a specific menu ID
     */
    private static function add_link_to_menu( $menu_id ) {
        $menu_items = wp_get_nav_menu_items( $menu_id, [ 'post_status' => 'publish' ] ) ?: [];

        // Check if Giving link already exists
        foreach ( $menu_items as $item ) {
            if ( false !== strpos( $item->url, 'kingdom-creatives.com/giving' ) ) {
                return; // Already exists
            }
        }

        // Build URL
        $url = add_query_arg( 'blog_id', get_current_blog_id(), 'https://kingdom-creatives.com/giving/' );

        // Calculate proper position
        $positions = wp_list_pluck( $menu_items, 'menu_order' );
        $max = empty($positions) ? 0 : max($positions);

        $item_id = wp_update_nav_menu_item( $menu_id, 0, [
            'menu-item-title'   => __( 'Giving', 'multi-purpose-plugin' ),
            'menu-item-url'     => esc_url_raw( $url ),
            'menu-item-status'  => 'publish',
            'menu-item-type'    => 'custom',
            'menu-item-position'=> $max + 1,
        ] );

        if ( is_wp_error( $item_id ) ) {
            return;
        }

        // Refresh menu cache
        clean_post_cache( $menu_id );

        error_log( "MPP Giving Link: Added to menu ID $menu_id" );
    }

    /**
     * Customize menu item display for Giving link
     */
    public static function setup_giving_menu_item( $item ) {
        if ( 'custom' === $item->type && false !== strpos( $item->url, 'kingdom-creatives.com/giving' ) ) {
            $item->title      = __( 'Giving', 'multi-purpose-plugin' );
            $item->type_label = __( 'Giving Link', 'multi-purpose-plugin' );
        }
        return $item;
    }
}
