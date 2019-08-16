<?php
/**
 * @package Arya\LicenseManager
 */

namespace Arya\LicenseManager;

/**
 * Hook the WordPress plugin into the appropriate WordPress actions and filters.
 *
 * @since 1.0.0
 */
class Loader
{
    /**
     * Plugin version
     *
     * @since 1.0.0
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Loader
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'init', [ $this, 'install' ] );

        add_action( 'init', [ $this, 'loadTextdomain' ] );

        add_action( 'init', [ $this, 'storefront' ] );

        add_action( 'init', [ $this, 'api' ] );

        add_action( 'init', [ $this, 'scheduling' ] );

        add_action( 'init', [ $this, 'admin' ] );

        add_action( 'plugins_loaded', [ $this, 'query' ] );

        add_action( 'plugins_loaded', [ $this, 'authentication' ] );

        register_deactivation_hook( ARYA_LICENSE_MANAGER_FILE, [ $this, 'unschedule' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Loader
     */
    public static function newInstance(): Loader
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Loader;
        }

        return self::$instance;
    }

    /**
     * Install the database to manage the licenses.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function install()
    {
        if ( false === ( $db_version = get_option( 'arya_license_manager_db_version', false ) ) ) {
            Install::newInstance()->create();
            return;
        }

        flush_rewrite_rules();
    }

    /**
     * Load translated strings for the current locale.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function loadTextdomain()
    {
        load_plugin_textdomain( 'arya-license-manager' );
    }

    /**
     * Adds functionalities to the customer's account page and the shopping
     * cart.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function storefront()
    {
        $classes = [
            '\Arya\LicenseManager\Storefront\Account',
            '\Arya\LicenseManager\Storefront\Cart',
            '\Arya\LicenseManager\Storefront\Email',
            '\Arya\LicenseManager\Storefront\License',
            '\Arya\LicenseManager\Storefront\Licenses',
            '\Arya\LicenseManager\Storefront\Order',
            '\Arya\LicenseManager\Storefront\Product',
            '\Arya\LicenseManager\Storefront\Theme'
        ];

        foreach( $classes as $class ) {
            call_user_func( "$class::newInstance" );
        }
    }

    /**
     * Creates the query variables for licenses.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function query()
    {
        new \Arya\LicenseManager\Storefront\Query;
    }

    /**
     * Determines that a user is who he claims to be through security
     * credentials.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function authentication()
    {
        \Arya\LicenseManager\Api\Authentication::newInstance();
    }

    /**
     * Loads the REST API.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function api()
    {
        \Arya\LicenseManager\Api\Manager::newInstance();
    }

    /**
     * Scheduling events.
     *
     * @since 1.0.0
     */
    public function scheduling()
    {
        (\Arya\LicenseManager\Admin\Scheduling::newInstance())->events();
    }

    /**
     * Unscheduling events.
     *
     * @since 1.0.0
     */
    public function unschedule()
    {
        $events = [
            'arya_license_manager_schedule_twicedaily'
        ];

        foreach( $events as $event ) {
            wp_unschedule_event( wp_next_scheduled( $event ), $event );
        }
    }

    /**
     * Hook into actions and filters for administrative interface page.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin()
    {
        /* AJAX Request */
        \Arya\LicenseManager\Admin\Request::newInstance();

        /* Emails */
        \Arya\LicenseManager\Admin\Emails::newInstance();

        if ( is_admin() ) {
            \Arya\LicenseManager\Admin\Admin::newInstance();
        }
    }
}
