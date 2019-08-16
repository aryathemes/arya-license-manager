<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * DashboardActivation class.
 *
 * @since 1.0.0
 */
class DashboardActivation extends AbstractActivation
{
    /**
     * Activation data.
     *
     * @sice 1.0.0
     */
    private $data = [];

    /**
     * Constructor.
     *
     * @sice 1.0.0
     */
    public function __construct( array $data )
    {
        $this->data = $data;
    }

    /**
     * Retrieves the usage activation by using information.
     *
     * @since 1.0.0
     */
    public function getActivation()
    {
        $data = $this->data;

        $activation = false;

        switch( $data['type'] ) {
            case "wordpress":
            case "webapp":
                $activation = $this->getWebsiteActivation( $data );
                break;
            default:
        }

        return parent::filter( $activation, $data, 'dashboard' );
    }

    /**
     * Verify that the data entered by the user is correct and register the
     * IP address.
     *
     * @since 1.0.0
     */
    private function getWebsiteActivation( $data )
    {
        $constraint = untrailingslashit( $data['constraint'] );

        if ( false === filter_var( $constraint, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        $url = parse_url( $constraint );

        if ( false === filter_var( $url['host'], FILTER_VALIDATE_DOMAIN ) ) {
            return false;
        }

        if ( false === filter_var( $host = gethostbyname( $url['host'] ), FILTER_VALIDATE_IP ) ) {
            return false;
        }

        $data['activated_at'] = time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
        $data['constraint']   = "{$url['scheme']}://{$url['host']}";
        $data['information']  = $host;

        return $data;
    }
}
