<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * Order class.
 *
 * @since 1.0.0
 */
class Order extends \WC_Order
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( $order_id )
    {
        parent::__construct( $order_id );
    }

    /**
     * Gets the licenses acquired in the current order.
     *
     * @since 1.0.0
     */
    public function getLicenses()
    {
        foreach( $this->getLicenseItems() as $item ) {

            $product = $item->get_product();

            $license = wc_get_order_item_meta( $item->get_id(), 'arya_license' );

            yield [ $product, $license ];
        }
    }

    /**
     * Verifies whether current order has a license.
     *
     * @since 1.0.0
     */
    public function hasLicense()
    {
        foreach( $this->get_items() as $item ) {

            $product = $item->get_product();

            /* License product */
            if ( $product->is_type( 'license' ) ) {
                return true;
            }

            /* Simple/Variable product */
            else if ( $product->is_type( [ 'simple', 'variation' ] ) ) {
                $is_license = get_post_meta( $product->get_id(), '_licensable', true );

                if ( 'yes' == $is_license ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifies whether current order has an active license.
     *
     * @since 1.0.0
     */
    public function hasActiveLicense()
    {
        foreach( $this->getLicenseItems() as $item ) {
            $item_id = $item->get_id();

            if ( 'valid' == wc_get_order_item_meta( $item_id, 'arya_license_status' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves a list of license products within this order.
     *
     * @since 1.0.0
     */
    public function getLicenseItems()
    {
        foreach( $this->get_items() as $item ) {

            $product = $item->get_product();

            if ( ! $product->is_type( [ 'license', 'simple', 'variation' ] ) ) {
                continue;
            }

            /* License product */
            if ( $product->is_type( 'license' ) ) {
                yield $item;
            }

            /* Simple/Variable product */
            else if ( $product->is_type( [ 'simple', 'variation' ] ) ) {
                $is_license = get_post_meta( $product->get_id(), '_licensable', true );

                if ( 'yes' == $is_license ) {
                    yield $item;
                }
            }
        }
    }

    /**
     * Grants permissions to download new versions of downloadable articles to
     * customers with active licenses.
     *
     * @since 1.0.0
     */
    public function setDownload()
    {
        foreach( $this->getLicenseItems() as $item ) {
            $item_id = $item->get_id();

            if ( 'active' == wc_get_order_item_meta( $item_id, 'arya_license_status' ) ) {

                $product = $item->get_product();

                if ( $downloads = $product->get_downloads() ) {

                    foreach( $downloads as $download ) {

                        $customer = new \WC_Customer_Download;

                        $customer->set_download_id( $download->get_id() );
                        $customer->set_product_id( $product->get_id() );
                        $customer->set_order_key( $order->get_order_key() );
                        $customer->set_user_id( $order->get_customer_id() );
                        $customer->set_user_email( $order->get_billing_email() );
                        $customer->set_order_id( $order->get_id() );
                        $customer->set_access_granted( gmdate( 'Y-m-d H:i:s', time() ) );

                        $customer->save();
                    }
                }
            }
        }
    }
}
