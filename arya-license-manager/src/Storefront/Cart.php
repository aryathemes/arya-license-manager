<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

use Arya\LicenseManager\License\Traits\LicenseTrait;

/**
 * Cart class.
 *
 * @since 1.0.0
 */
class Cart
{
    use LicenseTrait;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Cart
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'items' ], 10, 4 );

        add_filter( 'woocommerce_get_item_data', [ $this, 'details' ], 10, 2 );
    }

    /**
     * Splits licensed product quantities into multiple cart items.
     *
     * @since 1.0.0
     */
    public function items( $cart_item_data, $product_id, $variation_id, $quantity )
    {
        $product = wc_get_product( $variation_id ? $variation_id : $product_id );

        if ( $product->get_sold_individually() ) {
            return $cart_item_data;
        }

        if ( $this->isLicense( $product ) ) {
            $cart_item_data['unique_key'] = uniqid( 'arya' );
        }

        return $cart_item_data;
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Cart
     */
    public static function newInstance(): Cart
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Cart;
        }

        return self::$instance;
    }

    /**
     * Adds relevant information about the item buyed into shopping cart table.
     *
     * @since 1.0.0
     */
    public function details( $item_data, $cart_item )
    {
        /* Product */
        $product = $cart_item['data'];

        if ( ! $this->isLicense( $product ) ) {
            return $item_data;
        }

        $product_id = intval( $product->get_id() );

        /* Limit activations */
        $item_data += [ 'license_activation_limit' => [
            'name'    => esc_html__( 'Activation limit', 'arya-license-manager' ),
            'display' => $this->getActivationLimit( $product_id )
        ] ];

        /* Activation period */
        $item_data += [ 'license_activation_period' => [
            'name'    => esc_html__( 'Activation period', 'arya-license-manager' ),
            'display' => $this->getActivationPeriod( $product_id )
        ] ];

        return $item_data;
    }

    /**
     * Retrieves the activation limits.
     *
     * @since 1.0.0
     */
    private function getActivationLimit( int $product_id )
    {
        $limit = intval( get_post_meta( $product_id, '_arya_license_activation_limit', true ) );

        if ( 0 === $limit ) {
            $display = __( 'Unlimited activations', 'arya-license-manager' );
        } else if ( 0 < $limit ) {
            $display = sprintf( _n( '%d activation', '%d activations', $limit, 'arya-license-manager' ), $limit );
        } else {
            $display = __( 'Unknown', 'arya-license-manager' );
        }

        return esc_html( $display );
    }

    /**
     * Retrieves the activation interval.
     *
     * @since 1.0.0
     */
    private function getActivationPeriod( int $product_id )
    {
        if ( empty( $value = get_post_meta( $product_id, '_arya_license_expire_value', true ) ) ) {
            $value = 1;
        }

        if ( empty( $interval = get_post_meta( $product_id, '_arya_license_expire_interval', true ) ) ) {
            $interval = 'year';
        }

        $interval = $value > 1 ? $interval : mb_substr( $interval, 0, -1 );

        return sprintf( '%1$s %2$s', $value, $interval );
    }
}
