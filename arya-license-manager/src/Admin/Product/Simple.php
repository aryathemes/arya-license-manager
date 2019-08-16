<?php
/**
 * @package Arya\LicenseManager\Admin\Product
 */

namespace Arya\LicenseManager\Admin\Product;

use Arya\LicenseManager\Admin\Download;
use Arya\LicenseManager\License\Order;
use Arya\LicenseManager\License\Orders;

/**
 * Simple class.
 *
 * @since 1.0.0
 */
class Simple
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Simple
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'product_type_options', [ $this, 'options' ], 10, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Simple
     */
    public static function newInstance(): Simple
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Simple;
        }

        return self::$instance;
    }

    /**
     * Adds "License" option to simple product type.
     *
     * @since 1.0.0
     */
    public function options( $options )
    {
        $options['licensable'] = [
            'id'            => '_licensable',
            'wrapper_class' => 'show_if_simple',
            'label'         => esc_html__( 'License', 'arya-license-manager' ),
            'description'   => esc_html__( 'License', 'arya-license-manager' ),
            'default'       => 'no'
        ];

        return $options;
    }
}
