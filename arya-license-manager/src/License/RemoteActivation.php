<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * RemoteActivation class.
 *
 * @since 1.0.0
 */
class RemoteActivation extends AbstractActivation
{
    /**
     * Constructor.
     *
     * @sice 1.0.0
     */
    public function __construct()
    {
    }

    /**
     * Retrieves the user agent information.
     *
     * @since 1.0.0
     */
    public function getActivation()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ( empty( $user_agent ) ) {
            return false;
        }

        $activation = false;

        $software = [ 'wordpress', 'webapp' ];

        foreach( $software as $type ) {
            if ( false !== ( $activation = call_user_func( [ $this, $type ], $user_agent ) ) ) {
                break;
            }
        }

        return parent::filter( $activation, $user_agent, 'remotely' );
    }

    /**
     * Retrieve user agent information from WordPress.
     *
     * @since 1.0.0
     */
    public function wordpress( $user_agent )
    {
        /* WordPress/<version>; <URL> */
        $pattern = '|(^WordPress/(?:(?:[0-9]+\.?)+));\s+(.+?)$|i';

        if ( 1 === preg_match( $pattern , $user_agent, $matches ) ) {

            /* WordPress URL */
            $constraint = esc_url_raw( untrailingslashit( $matches[2] ) );

            /* Retrieve the ip assigned to the WordPress */
            if ( false === filter_var( $constraint, FILTER_VALIDATE_URL ) ) {
                return false;
            }

            if ( false === filter_var( $hostname = parse_url( $constraint, PHP_URL_HOST ), FILTER_VALIDATE_DOMAIN ) ) {
                return false;
            }

            if ( false === filter_var( $host = gethostbyname( $hostname ), FILTER_VALIDATE_IP ) ) {
                return false;
            }

            return [
                'activated_at' => current_time( 'timestamp', true ),
                'constraint'   => $constraint,
                'information'  => $host,
                'type'         => 'wordpress'
            ];
        }

        return false;
    }

    /**
     * Retrieve user agent information from a Web Applications.
     *
     * @since 1.0.0
     */
    public function webapp( $user_agent )
    {
        /* <product>/<version> (<webapp-url>) */
        $pattern = '#(^[a-zA-Z]+/(?:(?:[0-9]+\.?)+))\s+\((https?\://.+)\)#i';

        if ( 1 === preg_match( $pattern , $user_agent, $matches ) ) {

            /* WebApp URL */
            $constraint = esc_url_raw( untrailingslashit( $matches[2] ) );

            /* Retrieve the ip assigned to the web app */
            if ( false === filter_var( $constraint, FILTER_VALIDATE_URL ) ) {
                return false;
            }

            if ( false === filter_var( $hostname = parse_url( $constraint, PHP_URL_HOST ), FILTER_VALIDATE_DOMAIN ) ) {
                return false;
            }

            if ( false === filter_var( $host = gethostbyname( $hostname ), FILTER_VALIDATE_IP ) ) {
                return false;
            }

            return [
                'activated_at' => current_time( 'timestamp', true ),
                'constraint'   => $constraint,
                'information'  => $information,
                'type'         => 'webapp'
            ];
        }

        return false;
    }
}
