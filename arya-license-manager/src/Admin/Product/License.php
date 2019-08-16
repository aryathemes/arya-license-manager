<?php
/**
 * @package Arya\LicenseManager\Admin\Product
 */

namespace Arya\LicenseManager\Admin\Product;

use Arya\LicenseManager\Admin\Download;
use Arya\LicenseManager\License\Order;
use Arya\LicenseManager\License\Orders;

/**
 * License class.
 *
 * @since 1.0.0
 */
class License
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var License
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'product_type_selector', [ $this, 'selector' ], 10, 1 );

        add_filter( 'product_type_options', [ $this, 'options' ], 10, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return License
     */
    public static function newInstance(): License
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new License;
        }

        return self::$instance;
    }

    /**
     * Adds "Software license" to the product type selector.
     *
     * @since 1.0.0
     */
    public function selector( $product_types )
    {
        return $product_types + [
            'license' => esc_html__( 'Software license', 'arya-license-manager' )
        ];
    }

    /**
     * Adds "Virtual" and "Downloadable" options.
     *
     * @since 1.0.0
     */
    public function options( $options )
    {
        /* License product type */
        $virtual = $options[ 'virtual' ][ 'wrapper_class' ] . ' show_if_license';
        $options[ 'virtual' ][ 'wrapper_class' ] = $virtual;

        $downloadable = $options[ 'downloadable' ][ 'wrapper_class' ] . ' show_if_license';
        $options[ 'downloadable' ][ 'wrapper_class' ] = $downloadable;

        return $options;
    }
}
