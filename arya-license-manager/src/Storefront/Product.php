<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

use Arya\LicenseManager\License\Traits\LicenseTrait;

/**
 * Product class.
 *
 * @since 1.0.0
 */
class Product
{
    use LicenseTrait;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Product
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'woocommerce_is_sold_individually', [ $this, 'soldIndividually' ], 10, 2 );

        add_action( 'woocommerce_license_add_to_cart', [ $this, 'addToCart' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Product
     */
    public static function newInstance(): Product
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Product;
        }

        return self::$instance;
    }

    /**
     * Deactivate the quantity field on the cart page for the license products.
     *
     * @since 1.0.0
     */
    public function soldIndividually( $sold_individually, $product )
    {
        if ( $this->isLicense( $product ) ) {
            return true;
        }

        return $sold_individually;
    }

    /**
     * Adds 'Add to Cart' button.
     *
     * @since 1.0.0
     */
    public function addToCart()
    {
        wc_get_template( 'single-product/add-to-cart/simple.php' );
    }
}
