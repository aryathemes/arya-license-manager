<?php
/**
 * @package Arya\LicenseManager\Templates
 */

defined( 'ABSPATH' ) || exit; ?>

<h2><?php esc_html_e( 'Licenses', 'arya-license-manager' ); ?></h2>

<table class="woocommerce-table woocommerce-table-order-licenses shop_table shop_table_responsive licenses-table">

    <thead>
        <tr>
            <th><span class="nobr"><?php esc_html_e( 'Product', 'arya-license-manager' ); ?></span></th>
            <th><span class="nobr"><?php esc_html_e( 'License', 'arya-license-manager' ); ?></span></th>
            <th><span class="nobr"><?php esc_html_e( 'Actions', 'arya-license-manager' ); ?></span></th>
        </tr>
    </thead>

    <tbody>

    <?php foreach( $licenses as $_license ) : ?>

        <?php list( $product, $license ) = $_license; ?>

        <?php
        /* Product */
        $product = sprintf( '<a href="%1$s">%2$s</a>', $product->get_permalink(), $product->get_title() );

        /* Actions */
        $license_endpoint = wc_get_endpoint_url( 'view-license', $license, wc_get_page_permalink( 'myaccount' ) );

        $license_endpoint = add_query_arg( 'order', $order_id, $license_endpoint );

        $license_endpoint = esc_url( wp_nonce_url( $license_endpoint, -1, '_nonce' ) );

        $license_details = esc_html__( 'Manage', 'arya-license-manager' );

        $action = sprintf( '<a class="woocommerce-button button alt" href="%1$s">%2$s</a>', $license_endpoint, $license_details ); ?>

        <tr>
            <td data-title="<?php esc_html_e( 'Product', 'arya-license-manager' ); ?>"><?php echo $product; ?></td>
            <td data-title="<?php esc_html_e( 'License', 'arya-license-manager' ); ?>"><code><?php echo $license; ?></code></td>
            <td data-title="<?php esc_html_e( 'Actions', 'arya-license-manager' ); ?>"><?php echo $action; ?></td>
        </tr>

    <?php endforeach;?>

    </tbody>

</table>
