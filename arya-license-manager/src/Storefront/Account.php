<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

use Arya\LicenseManager\License\License;
use Arya\LicenseManager\License\Licenses;
use Arya\LicenseManager\License\Order;
use Arya\LicenseManager\Security\Credentials;

/**
 * Account class.
 *
 * @since 1.0.0
 */
class Account
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Account
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        /* Account page */
        add_filter( 'woocommerce_account_menu_items', [ $this, 'menuItems' ], 10, 1 );

        add_filter( 'woocommerce_get_endpoint_url', [ $this, 'endpointUrl' ], 10, 4 );

        add_action( 'woocommerce_account_view-license_endpoint', [ $this, 'endpointLicense' ] );
        add_action( 'woocommerce_account_licenses_endpoint', [ $this, 'endpointLicenses' ] );
        add_action( 'woocommerce_account_credentials_endpoint', [ $this, 'endpointCredentials' ] );

        add_action( 'woocommerce_order_details_before_order_table', [ $this, 'orderLicenses' ], 10, 1 );

        /* Enqueues the account stylesheets and scripts */
        add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 10 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue'  ], 15 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Account
     */
    public static function newInstance(): Account
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Account;
        }

        return self::$instance;
    }

    /**
     * Adds 'Licenses' to the account menu.
     *
     * @since 1.0.0
     */
    public function menuItems( $items )
    {
        /* Menu size */
        $count = count( $items );

        /* Preserves the order of Dashboard and Orders */
        $top = array_slice( $items, 0, 2, true );

        /* Extracts the other elements of the menu */ 
        $bottom = array_slice( $items, 2, ( $count - 1 ), true );

        /* Adds 'Licenses' link */
        $top += [ 'licenses' => esc_html__( 'Licenses', 'arya-license-manager' ) ];

        $items = $top + $bottom;

        /* Adds 'Security' link */
        $customer = $items['customer-logout'];

        unset( $items['customer-logout'] );

        $items += [
            'credentials'     => esc_html__( 'Security Credentials', 'arya-license-manager' ),
            'customer-logout' => $customer
        ];

        return $items;
    }

    /**
     * Adds a nonce value to the url to access the security credentials.
     *
     * @since 1.0.0
     */
    public function endpointUrl( $url, $endpoint, $value, $permalink )
    {
        if ( $endpoint == get_option( 'arya_license_manager_credentials_endpoint', 'credentials' ) ) {
            $url = wp_nonce_url( $url, -1, '_nonce' );
        }

        return $url;
    }

    /**
     * Gets the 'License' template.
     *
     * @since 1.0.0
     */
    public function endpointLicense()
    {
        if ( ! isset( $_GET['_nonce'] ) || ! wp_verify_nonce( $_GET['_nonce'] ) ) {
            wc_print_notice( esc_html__( 'You do not have permission to view the license information.', 'arya-license-manager' ), 'error' );
            return;
        }

        $license = esc_attr( get_query_var( 'view-license' ) );

        $order_id = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING ) ?: 0;

        if ( 0 == intval( $order_id ) ) {
            return;
        }

        $license = new License( $license, $order_id );

        if ( ! $license->exists() ) {
            $license = null;
        }

        wc_get_template( 'myaccount/license.php', [
            'license' => $license
        ], '', ARYA_LICENSE_MANAGER_TEMPLATES );
    }

    /**
     * Gets the 'Licenses' template.
     *
     * @since 1.0.0
     */
    public function endpointLicenses()
    {
        $args = [
            'orderby'     => 'ID',
            'order'       => 'DESC',
            'customer_id' => get_current_user_id()
        ];

        $key = hash( 'md5', serialize( [ 'arya-customer-licenses', ARYA_LICENSE_MANAGER_FILE, $args ] ) );

        $licenses = wp_cache_get( $key, 'arya_license_manager' );

        if ( false === $licenses ) {

            $licenses = iterator_to_array( (new Licenses( $args ))->getLicenses(), true );

            wp_cache_add( $key, $licenses, 'arya_license_manager', DAY_IN_SECONDS );
        }

        wc_get_template( 'myaccount/licenses.php', [
            'licenses' => $licenses
        ], '', ARYA_LICENSE_MANAGER_TEMPLATES );
    }

    /**
     * Gets the 'Security Credentials' template.
     *
     * @since 1.0.0
     */
    public function endpointCredentials()
    {
        if ( ! isset( $_GET['_nonce'] ) || ! wp_verify_nonce( $_GET['_nonce'] ) ) {
            wc_print_notice( esc_html__( 'You do not have permission to access this page.', 'arya-license-manager' ), 'error' );
            return;
        }

        $customer_id = get_current_user_id();

        $credentials = (new Credentials)->getCredentials( $customer_id );

        wc_get_template( 'myaccount/credentials.php', [
            'customer_id' => $customer_id,
            'credentials' => $credentials
        ], '', ARYA_LICENSE_MANAGER_TEMPLATES );
    }

    /**
     * Adds the license information into order details.
     *
     * @since 1.0.0
     */
    public function orderLicenses( $order )
    {
        $order = new Order( $order->get_id() );

        if ( ! $order->hasLicense() || ! in_array( $order->get_status(), wc_get_is_paid_statuses() ) ) {
            return;
        }

        if ( $licenses = $order->getLicenses() ) {
            wc_get_template( 'myaccount/order-licenses.php', [
                'licenses' => $licenses,
                'order_id' => $order->get_id()
            ], '', ARYA_LICENSE_MANAGER_TEMPLATES );
        }
    }

    /**
     * Register the admin stylesheets and scripts.
     *
     * @since 1.0.0
     */
    public function register()
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        /* Stylesheet */
        wp_register_style( 'arya-license-manager-account',
            plugins_url( "static/css/account$suffix.css", ARYA_LICENSE_MANAGER_FILE ), [ 'dashicons' ], null, 'all' );

        /* Scripts */
        wp_register_script( 'bootstrap-js',
            plugins_url( 'static/js/bootstrap.bundle.min.js', ARYA_LICENSE_MANAGER_FILE ), [ 'jquery' ], null, true );

        wp_register_script( 'clipboard-js',
            plugins_url( 'static/js/clipboard.min.js', ARYA_LICENSE_MANAGER_FILE ), [ 'bootstrap-js' ], null, true );

        wp_register_script( 'license-manager-activations',
            plugins_url( "static/js/account-license$suffix.js", ARYA_LICENSE_MANAGER_FILE ), [ 'jquery', 'clipboard-js' ], null, true );

        wp_register_script( 'license-manager-credentials',
            plugins_url( "static/js/account-credentials$suffix.js", ARYA_LICENSE_MANAGER_FILE ), [ 'jquery' ], null, true );
    }

    /**
     * Enqueues the account stylesheets and scripts.
     *
     * @since 1.0.0
     */
    public function enqueue()
    {
        /* Stylesheet */
        wp_enqueue_style( 'arya-license-manager-account' );

        /* Script */
        $admin_ajax = esc_url( admin_url( 'admin-ajax.php' ) );

        $activations = [
            'ajaxurl'             => $admin_ajax,
            'error'               => esc_html__( 'It was not possible to associate the license.', 'arya-license-manager' ),
            'activation_add_nonce'    => wp_create_nonce( 'arya-license-manager-activation-add' ),
            'activation_revoke_nonce' => wp_create_nonce( 'arya-license-manager-activation-revoke' )
        ];
        wp_localize_script( 'license-manager-activations', 'arya_license_manager_activation', $activations );

        wp_enqueue_script( 'license-manager-activations' );

        $credentials = [
            'ajaxurl'                    => $admin_ajax,
            'credentials_create_nonce'   => wp_create_nonce( 'arya-license-manager-credentials' ),
            'credentials_download_nonce' => wp_create_nonce( 'arya-license-manager-credentials-download' ),
            'credentials_revoke_nonce'   => wp_create_nonce( 'arya-license-manager-credentials-revoke' )
        ];
        wp_localize_script( 'license-manager-credentials', 'arya_license_manager_credentials', $credentials );

        wp_enqueue_script( 'license-manager-credentials' );
    }
}
