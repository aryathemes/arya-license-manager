<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * Licenses class.
 *
 * @since 1.0.0
 */
class Licenses
{
    /**
     * Arguments to define the licenses search.
     *
     * @since 1.0.0
     */
    private $args = [];

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( $args = [] )
    {
        $this->args = $args;
    }

    /**
     * Retrieves a list of licenses.
     *
     * @since 1.0.0
     */
    public function getLicenses()
    {
        $orders = (new Orders( $this->args ))->getOrders();

        foreach( $orders as $_order ) {

            $order = new Order( $_order );

            foreach ( $order->getLicenseItems() as $item ) {

                $license = wc_get_order_item_meta( $item->get_id(), 'arya_license' );

                if ( empty( $license ) ) {
                    continue;
                }

                yield (new License( $license, $order->get_id() ));
            }
        }
    }
}
