<?php
/**
 * @package Arya\LicenseManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers 'License' as a new type of WooCommerce product.
 *
 * @since 1.0.0
 */
function arya_register_license_product_type()
{
    class WC_Product_License extends WC_Product
    {
        public function __construct( $product )
        {
            $this->product_type = 'license';

            parent::__construct( $product );
        }
    }
}
add_action( 'init', 'arya_register_license_product_type' );
