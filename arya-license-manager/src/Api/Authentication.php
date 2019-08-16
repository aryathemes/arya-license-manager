<?php
/**
 * @package Arya\LicenseManager
 */

namespace Arya\LicenseManager\Api;

use Arya\LicenseManager\License\License;
use Arya\LicenseManager\Security\Credentials;

/**
 * Authentication class.
 *
 * @since 1.0.0
 */
class Authentication
{
    /**
     * User ID
     *
     * @since 1.0.0
     */
    private $user_id = false;

    /**
     * Error
     *
     * @since 1.0.0
     */
    private $error = false;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Authentication
     */
    private static $instance;

    /**
     * Construct.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'determine_current_user', [ $this, 'currentUser' ], 10, 1 );

        add_filter( 'rest_authentication_errors', [ $this, 'authenticationError' ], 10, 1 );

        add_filter( 'rest_post_dispatch', [ $this, 'postDispatch' ], 10, 3 );

        add_filter( 'rest_pre_dispatch', [ $this, 'preDispatch' ], 10, 3 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Authentication
     */
    public static function newInstance(): Authentication
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Authentication;
        }

        return self::$instance;
    }

    /**
     * Filter the current user to allow the use of the API.
     *
     * @since 1.0.0
     */
    public function currentUser( $user_id )
    {
        if ( ! empty( $user_id ) || ! $this->isRequestApi() ) {
            return $user_id;
        }

        if ( $this->endpointRequireAuthentication() ) {
            return $this->authentication();
        }

        return false;
    }

    /**
     * Filters REST authentication errors.
     *
     * @since 1.0.0
     */
    public function authenticationError( $wp_error )
    {
        if ( is_wp_error( $wp_error ) ) {
            return $wp_error;
        }

        return $this->error;
    }

    /**
     * Send the WWW-Authenticate header in case of error.
     *
     * @since 1.0.0
     */
    public function postDispatch( $result, $wp_rest_server, $wp_rest_request )
    {
        if ( is_wp_error( $this->error ) ) {

            $message = 'Use a access key in the username field and a private access key in the password field';

            $result->header( 'WWW-Authenticate', 'Basic realm="' . $message . '"', true );
        }

        return $result;
    }

    /**
     * Verifies whether the request comes from the POST method.
     *
     * @since 1.0.0
     */
    public function preDispatch( $result, $wp_rest_server, $wp_rest_request )
    {
        if ( $this->user_id ) {
            if ( 'POST' !== $wp_rest_request->get_method() ) {
                return new \WP_Error( 'rest_authentication_error', '401 Unauthorized', [ 'status' => 401 ] );
            }
        }

        return $result;
    }

    /**
     * Retrieves the user ID by using security credentials.
     *
     * @since 1.0.0
     */
    private function authentication()
    {
        $user = $_SERVER['PHP_AUTH_USER'] ?? '';
        $pass = $_SERVER['PHP_AUTH_PW']   ?? '';

        if ( empty( $user ) || empty( $pass ) ) {

            $this->error = new \WP_Error( 'rest_authentication_error', 'Authentication error.', [ 'status' => 401 ] );

            return false;
        }

        $credentials = new Credentials;

        $user_id = $credentials->getUserId( $user, $pass );

        if ( false === $user_id ) {

            $this->error = new \WP_Error( 'rest_authentication_error', 'Authentication error.', [ 'status' => 401 ] );

            return $user_id;
        }

        $credentials->accessRecord( $user, $pass );

        return $user_id;
    }

    /**
     * Verifies whether the request has been made to our REST API.
     *
     * @return bool
     */
    private function isRequestApi() {

        if ( empty( $_SERVER['REQUEST_URI'] ) ) {
            return false;
        }

        $request_uri = trim( esc_url_raw( $_SERVER['REQUEST_URI'] ), '/' );

        $prefix = trim( rest_get_url_prefix(), '/' );

        if ( preg_match( "|^$prefix/license-manager/|i", $request_uri ) ) {
            return true;
        }

        return false;
    }

    /**
     * Verifies whether the endpoint require authentication.
     *
     * @return bool
     */
    private function endpointRequireAuthentication() {

        if ( empty( $_SERVER['REQUEST_URI'] ) ) {
            return false;
        }

        $request_uri = trim( esc_url_raw( $_SERVER['REQUEST_URI'] ), '/' );

        $pattern = "/validate/i";

        if ( preg_match( $pattern, $request_uri, $matches ) ) {
            return false;
        }

        return true;
    }
}
