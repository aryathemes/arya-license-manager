<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

use Arya\LicenseManager\License\Order;

/**
 * Download class.
 *
 * @since 1.0.0
 */
class Download
{
    /**
     * Order
     *
     * @since 1.0.0
     */
    private $order = null;

    /**
     * Construct.
     *
     * @since 1.0.0
     */
    public function __construct( Order $order )
    {
        $this->order = $order;
    }

    /**
     * Grants access to new downloads of a product to an order with an active
     * license.
     *
     * @since 1.0.0
     */
    public function grantAccess()
    {
        if ( ! $this->order->hasActiveLicense() ) {
            return;
        }

        foreach ( $this->order->getLicenseItems() as $item ) {

            $product = $item->get_product();

            if ( ! $product->exists() || ! $product->is_downloadable() ) {
                continue;
            }

            $product_id = $product->get_id();

            $downloads = $this->getDownloads( $product );

            if ( empty( $downloads ) ) {
                continue;
            }

            foreach( $downloads as $download ) {

                /* Data */
                $customer = new \WC_Customer_Download;

                $customer->set_download_id( $download );
                $customer->set_order_key( $this->order->get_order_key() );
                $customer->set_user_id( $this->order->get_customer_id() );
                $customer->set_user_email( $this->order->get_billing_email() );
                $customer->set_order_id( $this->order->get_id() );
                $customer->set_product_id( $product_id );

                /* Access */
                $customer->set_access_granted( gmdate( 'Y-m-d H:i:s', current_time( 'timestamp', true ) ) );

                if ( 'perpetual' !== ( get_post_meta( $product_id, 'arya_license_type' ) ) ) {

                    $expire_at = wc_get_order_item_meta( $item->get_id(), '_arya_license_expire_at' );

                    $customer->set_access_expires( $expire_at );
                }

                $customer->save();
            }
        }
    }

    /**
     * Retrieves downloads of a product.
     *
     * @since 1.0.0
     */
    private function getDownloads( $product )
    {
        if ( $downloads = $product->get_downloads() ) {

            /* Retrieves the downloadable products */
            $downloads = array_keys( $product->get_downloads() );

            /* Retrieves the downloadable products from an order */
            $downloadables = iterator_to_array( $this->getDownloadables() );

            /* Retrieves the missing downloadable products in the order */
            return array_values( array_diff( $downloads, $downloadables ) );
        }

        return [];
    }

    /**
     * Retrieves the allowed downloads of all items for this order.
     *
     * @since 1.0.0
     */
    private function getDownloadables()
    {
        if ( $items = $this->order->get_downloadable_items() ) {
            foreach( $items as $item ) {
                yield $item['download_id'];
            }
        }
    }
}
