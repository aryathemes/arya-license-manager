<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

/**
 * Admin class.
 *
 * @since 1.0.0
 */
class Admin
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Admin
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $classes = [
            '\Arya\LicenseManager\Admin\Product',
            '\Arya\LicenseManager\Admin\Order',
            '\Arya\LicenseManager\Admin\Settings',
            '\Arya\LicenseManager\Admin\Product\License',
            '\Arya\LicenseManager\Admin\Product\Simple',
            '\Arya\LicenseManager\Admin\Product\Variable'
        ];

        foreach( $classes as $class ) {
            call_user_func( "$class::newInstance" );
        }

        if ( defined( 'WC_ADMIN_APP' ) ) {
            call_user_func( '\Arya\LicenseManager\Admin\Integration::newInstance' );
        }

        add_action( 'admin_menu', [ $this, 'menu' ] );

        add_action( 'admin_init', [ $this, 'scripts' ],  5, 1 );
        add_action( 'admin_init', [ $this, 'enqueue' ], 10, 1 );

        add_action( 'arya_license_manager_admin_page', [ $this, 'licenses' ], 10, 1 );
        add_action( 'arya_license_manager_admin_page', [ $this, 'license'  ], 10, 1 );

        /**
         * Cache
         */
        $statuses = [
            'valid', 'expired', 'suspended', 'inactive'
        ];

        foreach( $statuses as $status ) {
            add_action( "arya_license_manager_license_{$status}_status", [ $this, 'cache' ], 10, 1 );
        }
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Admin
     */
    public static function newInstance(): Admin
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Admin;
        }

        return self::$instance;
    }

    /**
     * Add submenu 'Licenses' to WooCommerce menu.
     *
     * @since 1.0.0
     */
    public function menu()
    {
        add_submenu_page( 'woocommerce',
            esc_html__( 'Licenses Management', 'arya-license-manager' ),
            esc_html__( 'Licenses', 'arya-license-manager' ),
            'manage_woocommerce',
            'wc-licenses',
            [ $this, 'render' ]
        );

        /* License list */
        add_action( 'load-woocommerce_page_wc-licenses', [ Licenses::newInstance(), 'screen' ] );

        /* Manage license */
        $license = License::newInstance();

        add_action( 'load-woocommerce_page_wc-licenses', [ $license, 'metaboxes' ] );
        add_action( 'load-woocommerce_page_wc-licenses', [ $license, 'screen' ] );
        add_action( 'admin_footer-woocommerce_page_wc-licenses', [ $license, 'footer' ] );
    }

    /**
     * Displays the pages to manipulate the generated licenses.
     *
     * @since 1.0.0
     */
    public function render()
    {
        $page = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ?: 'listing';

        if ( ! in_array( $page, [ 'listing', 'license' ] ) ) {
            return;
        }
        ?>

        <div class="wrap">

            <?php do_action( 'arya_license_manager_admin_page', $page ); ?>

            <br class="clear">

        </div>

        <?php
    }

    /**
     * Display the license table.
     *
     * @since 1.0.0
     */
    public function licenses( $page )
    {
        if ( 'listing' !== $page ) {
            return;
        }

        (Licenses::newInstance())->render();
    }

    /**
     * Displays the page to edit a license.
     *
     * @since 1.0.0
     */
    public function license( $page )
    {
        if ( 'license' !== $page ) {
            return;
        }

        (License::newInstance())->render();
    }

    /**
     * Clear cache.
     *
     * @since 1.0.0
     */
    public function cache( $license )
    {
        /* Cache */
        $key = hash( 'md5', serialize( [ "arya-order-item-id-{$license}", ARYA_LICENSE_MANAGER_FILE ] ) );

        /* Delete */
        wp_cache_delete( $key, 'arya_license_manager' );
    }

    /**
     * Register the admin stylesheets and scripts.
     *
     * @since 1.0.0
     */
    public function scripts()
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_register_style( 'arya-license-manager-admin',
            plugins_url( "static/css/admin$suffix.css", ARYA_LICENSE_MANAGER_FILE ), [ 'wp-editor' ], null, 'all' );

        wp_register_script( 'arya-license-manager-admin-license',
            plugins_url( "static/js/admin-license$suffix.js", ARYA_LICENSE_MANAGER_FILE ), [ 'jquery' ], null, true );

        wp_register_script( 'arya-license-manager-admin-product',
            plugins_url( "static/js/admin-product$suffix.js", ARYA_LICENSE_MANAGER_FILE ), [ 'jquery' ], null, true );
    }

    /**
     * Enqueue the admin stylesheets and scripts.
     *
     * @since 1.0.0
     */
    public function enqueue( $hook_suffix )
    {
        wp_enqueue_style( 'arya-license-manager-admin' );
    }
}
