<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

/**
 * Query class.
 *
 * @since 1.0.0
 */
class Query extends \WC_Query
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        if ( ! is_admin() ) {

            /* Register query variables */
            add_filter( 'woocommerce_get_query_vars', [ $this, 'addQueryVars' ], 10, 1 );

            /* Account page titles */
            add_filter( 'woocommerce_endpoint_view-license_title', [ $this, 'title' ], 10, 2 );
            add_filter( 'woocommerce_endpoint_licenses_title',     [ $this, 'title' ], 10, 2 );
        }
    }

    /**
     * Register query variables.
     *
     * @since 1.0.0
     */
    public function addQueryVars( $query_vars )
    {
        $query_license = [
            'licenses'     => get_option( 'arya_license_manager_licenses_endpoint',     'licenses'     ),
            'view-license' => get_option( 'arya_license_manager_view-license_endpoint', 'view-license' )
        ];

        return array_merge( $query_vars, $query_license );
    }

    /**
     * Changes the 'My account' title.
     *
     * @since 1.0.0
     */
    public function title( $title, $endpoint )
    {
        return $this->endpointTitle( $endpoint ) ?: $title;
    }

    /**
     * Sets the licenses page title.
     *
     * @since 1.0.0
     */
    private function endpointTitle( $endpoint )
    {
        $title = '';

        switch( $endpoint ) {
            case 'licenses':
                $title = esc_html__( 'Licenses', 'arya-license-manager' );
                break;
            case 'view-license':
                $title = esc_html__( 'License', 'arya-license-manager' );
                break;
        }

        return $title;
    }
}
