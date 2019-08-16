<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

/**
 * Integration class.
 *
 * @since 1.0.0
 */
class Integration
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Integration
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'wc_admin_current_screen_id', [ $this, 'screen' ], 10, 2 );

        add_action( 'admin_menu', [ $this, 'breadcrumbs' ], 15 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Integration
     */
    public static function newInstance(): Integration
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Integration;
        }

        return self::$instance;
    }

    /**
     * Sets the screen id.
     *
     * @since 1.0.0
     */
    public function screen( $screen, $current_screen )
    {
        if ( 'woocommerce_page_wc-licenses' == $current_screen->id ) {

            $page = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ?: 'licenses';

            if ( in_array( $page, [ 'licenses', 'license' ] ) ) {
                $screen = "{$screen}-{$page}";
            }
        }

        return $screen;
    }

    /**
     * Shows the WooCommerce Admin header.
     *
     * @since 1.0.0
     */
    public function breadcrumbs()
    {
        wc_admin_connect_page( [
            'id'        => 'woocommerce-licenses',
            'screen_id' => 'woocommerce_page_wc-licenses-licenses',
            'title'     => esc_html_x( 'Licenses', 'WooCommerce header breadcrumbs', 'arya-license-manager' ),
            'path'      => add_query_arg( 'page', 'wc-licenses', 'admin.php' )
        ] );

        wc_admin_connect_page( [
            'id'        => 'woocommerce-licenses-license',
            'parent'    => 'woocommerce-licenses',
            'screen_id' => 'woocommerce_page_wc-licenses-license',
            'title'     => [
                esc_html_x( 'License details', 'WooCommerce header breadcrumbs', 'arya-license-manager' )
            ],
            'path' => add_query_arg( [
                'page' => 'wc-licenses',
                'tab'  => 'license'
            ], 'admin.php' )
        ] );

        wc_admin_connect_page( [
            'id'        => 'woocommerce-settings-products-license-manager',
            'parent'    => 'woocommerce-settings-products',
            'screen_id' => 'woocommerce_page_wc-settings-products-license-manager',
            'title'     => esc_html_x( 'Licenses', 'WooCommerce header breadcrumbs', 'arya-license-manager' )
        ] );
    }
}
