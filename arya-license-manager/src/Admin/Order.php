<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

/**
 * Order class.
 *
 * @since 1.0.0
 */
class Order
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Order
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        /* Actions */
        add_filter( 'woocommerce_order_actions', [ $this, 'actions' ], 10, 1 );

        add_action( 'woocommerce_order_action_regenerate_download_permissions_licenses', [ $this, 'regenerate' ], 10, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Order
     */
    public static function newInstance(): Order
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Order;
        }

        return self::$instance;
    }

    /**
     * Register a new order action in order to customers have access to downloadable products.
     *
     * @since 1.0.0
     */
    public function actions( $actions )
    {
        return $actions + [
            'regenerate_download_permissions_licenses' => esc_html__( 'Regenerate download permissions for licenses', 'arya-license-manager' )
        ];
    }

    /**
     * Grants access to new downloads of a product to an order with an active
     * license.
     *
     * @since 1.0.0
     */
    public function regenerate( $order )
    {
        $order = new \Arya\LicenseManager\License\Order( $order );

        if ( ! $order->hasActiveLicense() ) {
            return;
        }

        (new Download( $order ))->grantAccess();
    }
}
