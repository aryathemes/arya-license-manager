<?php
/**
 * @package Arya\LicenseManager\Templates
 */

defined( 'ABSPATH' ) || exit; ?>

<h2><?php esc_html_e( 'Licenses', 'arya-license-manager' ); ?></h2>

<table class="td" cellspacing="0" cellpadding="6"
    style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;"
    border="1">

    <thead>
        <tr>
            <th class="td"><span class="nobr"><?php esc_html_e( 'Product', 'arya-license-manager' ); ?></span></th>
            <th class="td"><span class="nobr"><?php esc_html_e( 'License', 'arya-license-manager' ); ?></span></th>
        </tr>
    </thead>

    <?php foreach( $licenses as $_license ) : ?>

        <?php list( $product, $license ) = $_license; ?>

        <?php $product = sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_title() ); ?>

        <tr>
            <td class="td"><?php echo $product; ?></td>
            <td class="td"><code><?php echo $license; ?></code></td>
        </tr>

    <?php endforeach;?>

</table>
