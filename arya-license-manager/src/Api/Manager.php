<?php
/**
 * @package Arya\LicenseManager
 */

namespace Arya\LicenseManager\Api;

use Arya\LicenseManager\License\License;
use Arya\LicenseManager\License\RemoteActivation as Activation;

/**
 * Manager class.
 *
 * @since 1.0.0
 */
class Manager
{
    /* Endpoint namespace */
    private $namespace = 'license-manager/1.0';

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Manager
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'rest_api_init', [ $this, 'rest' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Manager
     */
    public static function newInstance(): Manager
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Manager;
        }

        return self::$instance;
    }

    /**
     * Register the routes for customers.
     *
     * @since 1.0.0
     */
    public function rest()
    {
        register_rest_route( $this->namespace, '/(?P<license>\S{10,})/activate/', [
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'activate' ],
            'args' => [
                'license' => [
                    'required'    => true,
                    'type'        => 'string',
                    'description' => 'Software license'
                ]
            ]
        ] );

        register_rest_route( $this->namespace, '/(?P<license>\S{10,})/deactivate/', [
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'deactivate' ],
            'args' => [
                'license' => [
                    'required'    => true,
                    'type'        => 'string',
                    'description' => 'Software license'
                ]
            ]
        ] );

        register_rest_route( $this->namespace, '/(?P<license>\S{10,})/validate/', [
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'validate' ],
            'args' => [
                'license' => [
                    'required'    => true,
                    'type'        => 'string',
                    'description' => 'Software license'
                ]
            ]
        ] );

        register_rest_route( $this->namespace, '/(?P<license>\S{10,})/', [
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => [ $this, 'information' ],
            'args' => [
                'license' => [
                    'required'    => true,
                    'type'        => 'string',
                    'description' => 'Software license'
                ]
            ]
        ] );
    }

    /**
     * Establishes a activation for the use of a license.
     *
     * @since 1.0.0
     */
    public function activate( $wp_rest_server )
    {
        $params = $wp_rest_server->get_params();

        $license = new License( $params[ 'license' ] );

        if ( ! $license->exists() ) {
            return new \WP_Error( 'not_found', 'The license provided does not exist.', [ 'status' => 404 ] );
        }

        $activation = (new Activation)->getActivation();

        if ( $license->existsActivation( $activation ) ) {
            return new \WP_Error( 'activation', 'This resource has already been activated.', [ 'status' => 400 ] );
        }

        if ( $license->addActivation( $activation ) ) {
            $response = new \WP_REST_Response( [ 'activation' => 'successfully' ], 201 );
        } else {
            $response = new \WP_Error( 'failed_activation', 'Failed activation.', [ 'status' => 302 ] );
        }

        return rest_ensure_response( $response );
    }

    /**
     * Dissociate a activation for the use of a license.
     *
     * @since 1.0.0
     */
    public function deactivate( $wp_rest_server )
    {
        $params = $wp_rest_server->get_params();

        $license = new License( $params[ 'license' ] );

        if ( ! $license->exists() ) {
            return new \WP_Error( 'not_found', 'The license provided does not exist.', [ 'status' => 404 ] );
        }

        $activation = (new Activation)->getActivation();

        if ( ! $license->existsActivation( $activation ) ) {
            return new \WP_Error( 'bad_request', 'The activation of using the provided license has not been established.', [ 'status' => 400 ] );
        }

        if ( $license->removeActivation( $activation['constraint'] ) ) {
            $response = new \WP_REST_Response( [ 'deactivation' => 'successfully' ], 200 );
        } else {
            $response = new \WP_Error( 'failed_deactivation', 'Failed deactivate.', [ 'status' => 302 ] );
        }

        return rest_ensure_response( $response );
    }

    /**
     * Verifies whether a license is valid.
     *
     * @since 1.0.0
     */
    public function validate( $wp_rest_server )
    {
        $params = $wp_rest_server->get_params();

        $license = new License( $params[ 'license' ] );

        if ( ! $license->exists() ) {
            return new \WP_Error( 'not_found', 'The license provided does not exist.', [ 'status' => 404 ] );
        }

        $response = [ 'status' => $license->getStatus() ];

        return rest_ensure_response( new \WP_REST_Response( $response, 200 ) );
    }

    /**
     * Retrieves the information of a license.
     *
     * @since 1.0.0
     */
    public function information( $wp_rest_server )
    {
        $params = $wp_rest_server->get_params();

        $license = new License( $params[ 'license' ] );

        if ( ! $license->exists() ) {
            return new \WP_Error( 'not_found', 'The license provided does not exist.', [ 'status' => 404 ] );
        }

        return rest_ensure_response( new \WP_REST_Response( $license->getInformation(), 200 ) );
    }
}
