<?php
/**
 * @package Arya\LicenseManager\Templates
 */

defined( 'ABSPATH' ) || exit;

echo  "\n\n" . "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=" . "\n\n";

esc_html_e( 'Licenses', 'arya-license-manager' ) . "\n";

echo "\n\n";

foreach( $licenses as $_license ) {

    list( $product, $license ) = $_license;

    printf( 'Product: %s', $product->get_title() );

    echo "\n";

    printf( 'License: %s', $license );

    echo "\n\n";
}
