<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

use Arya\LicenseManager\License\License;
use Arya\LicenseManager\License\Renew;
use Arya\LicenseManager\License\DashboardActivation as Activation;
use Arya\LicenseManager\Security\Credentials;

/**
 * Request class.
 *
 * @since 1.0.0
 */
class Request
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Request
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        /* License */
        add_action( 'wp_ajax_license_action', [ $this, 'licenseAction' ] );

        /* Activations */
        add_action( 'wp_ajax_activation_add', [ $this, 'activationAdd' ] );
        add_action( 'wp_ajax_activation_revoke', [ $this, 'activationRevoke' ] );

        /* Credentials */
        add_action( 'wp_ajax_credentials_create', [ $this, 'credentialsCreate' ] );
        add_action( 'wp_ajax_credentials_download', [ $this, 'credentialsDownload' ] );
        add_action( 'wp_ajax_credentials_revoke', [ $this, 'credentialsRevoke' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Request
     */
    public static function newInstance(): Request
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Request;
        }

        return self::$instance;
    }

    /**
     * AJAX endpoint to edit a license.
     *
     * @since 1.0.0
     */
    public function licenseAction()
    {
        check_ajax_referer( 'arya-license-manager-license-actions', 'security' );

        /* Retrieves the action */
        $action = sanitize_text_field( $_POST['license_action'] );

        if ( 'license_none' == $action ) {
            return;
        }

        /* Retrieves the license and order id */
        $license = sanitize_text_field( $_POST['license'] );

        $order_id = sanitize_text_field( $_POST['order'] ?? 0 );

        if ( ! in_array( wc_get_order( $order_id )->get_status(), wc_get_is_paid_statuses() ) ) {
            wp_send_json_success( [
                'message' => esc_html__( 'It is not possible to perform an action since the order has not been completed.', 'arya-license-manager' )
            ], 202 );
        }

        $response = false;

        switch( $action ) {
            case 'license_activate':
                $response = (new License( $license, intval( $order_id ) ))->activate();
                break;
            case 'license_deactivate':
                $response = (new License( $license, intval( $order_id ) ))->deactivate();
                break;
            case 'license_renew':
                $response = (new Renew( $license ))->renew();
                break;
            default:
                $hook = "arya_license_manager_dashboard_$action";

                if ( has_filter( $hook ) ) {
                    $response = apply_filters( $hook, false, $license );
                }
        }

        wp_send_json_success( [ 'response' => $response ], 200 );
    }

    /**
     * AJAX endpoint to add a activation.
     *
     * @since 1.0.0
     */
    public function activationAdd()
    {
        check_ajax_referer( 'arya-license-manager-activation-add', 'security' );

        /* Verifies */
        if ( empty( $_POST['constraint'] ) ) {
            wp_send_json( [], 500 );
        }

        /* Retrieves the license and order id */
        $license = sanitize_text_field( $_POST['license'] );

        $order_id = sanitize_text_field( $_POST['order'] ?? 0 );

        if ( ! in_array( wc_get_order( $order_id )->get_status(), wc_get_is_paid_statuses() ) ) {
            wp_send_json_success( [
                'message' => esc_html__( 'It is not possible to perform an action since the order has not been completed.', 'arya-license-manager' )
            ], 202 );
        }

        /* Retrieves the activation */
        $data = [
            'constraint'  => $_POST['constraint'],
            'type'        => $_POST['type']
        ];

        $activation = (new Activation( $data ))->getActivation();

        if ( false == $activation ) {
            wp_send_json( [], 500 );
        }

        /* Adds the activation */
        $license = new License( $license, intval( $order_id ) );

        if ( $license->existsActivation( $activation ) ) {
            wp_send_json( [], 500 );
        }

        $response = $license->addActivation( $activation );

        wp_send_json_success( [ 'response' => 200 ] );
    }

    /**
     * AJAX endpoint to revoke a activation.
     *
     * @since 1.0.0
     */
    public function activationRevoke()
    {
        check_ajax_referer( 'arya-license-manager-activation-revoke', 'security' );

        /* Retrieves the license and order id */
        $license = sanitize_text_field( $_POST['license'] );

        $order_id = sanitize_text_field( $_POST['order'] ?? 0 );

        if ( ! in_array( wc_get_order( $order_id )->get_status(), wc_get_is_paid_statuses() ) ) {
            wp_send_json_success( [
                'message' => esc_html__( 'It is not possible to perform an action since the order has not been completed.', 'arya-license-manager' )
            ], 202 );
        }

        /* Retrieves the activation of use */
        $constraint = sanitize_text_field( $_POST['constraint'] );

        $response = (new License( $license, intval( $order_id ) ))->removeActivation( $constraint );

        wp_send_json_success( [ 'response' => 200 ] );
    }

    /**
     * AJAX end point to create security credentials.
     *
     * @since 1.0.0
     */
    public function credentialsCreate()
    {
        check_ajax_referer( 'arya-license-manager-credentials', 'security' );

        /* Retrieves customer id */
        $customer_id = sanitize_text_field( $_POST['customer_id'] );

        $credentials = (new Credentials)->create( intval( $customer_id ) );

        wp_send_json_success( $credentials, 201 );
    }

    /**
     * AJAX endpoint to download security credentials.
     *
     * @since 1.0.0
     */
    public function credentialsDownload()
    {
        check_ajax_referer( 'arya-license-manager-credentials-download', 'security' );

        /* Retrieves customer id */
        $customer_id = sanitize_text_field( $_POST['customer_id'] );

        $credentials = (new Credentials)->getCredentials( intval( $customer_id ) );

        echo json_encode( $credentials, 200 );
        die();
    }

    /**
     * AJAX endpoint to revoke security credentials.
     *
     * @since 1.0.0
     */
    public function credentialsRevoke()
    {
        check_ajax_referer( 'arya-license-manager-credentials-revoke', 'security' );

        /* Retrieves customer id */
        $customer_id = sanitize_text_field( $_POST['customer_id'] );

        $credentials = (new Credentials)->revokeCredentials( intval( $customer_id ) );

        wp_send_json_success( $credentials, 201 );
    }
}