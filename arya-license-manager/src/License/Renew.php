<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * Renew class.
 *
 * @since 1.0.0
 */
class Renew
{
    /**
     * License.
     *
     * @since 1.0.0
     */
    private $license;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( $license )
    {
        $this->license = new License( $license );
    }

    /**
     * Renew a license.
     *
     * @since 1.0.0
     */
    public function renew()
    {
        $product = $this->getProduct();

        if ( ! $product->exists() ) {
            return false;
        }

        $user_id = $this->getUserId();

        $args = [
            'customer_id' => $user_id,
            'status'      => 'pending'
        ];

        $new_order = wc_create_order( $args );

        $new_order->add_product( $product, 1 );

        $customer = new \WC_Customer( $user_id );

        $new_order->set_address( $customer->get_billing(),  'billing'  );
        $new_order->set_address( $customer->get_shipping(), 'shipping' );

        /* Calculate totals */
        $new_order->calculate_totals();

        /* Save metadata */
        $order = wc_get_order( $new_order );

        /* License information */
        $activated_at = $this->license->getActivationDate();

        $expiration_at = $this->license->getExpirationDate();

        $activation_period = $this->license->getActivationPeriod();

        $new_expire_at = gmdate( 'Y-m-d H:i:s', strtotime( "+ {$activation_period}", strtotime( $expiration_at ) ) );

        $limit = $this->license->getActivationsLimit();

        $type = $this->license->getType();

        $license = $this->license->getLicense();

        $activations = $this->license->getActivations();

        foreach ( $order->get_items() as $item ) {

            $item_id = $item->get_id();

            wc_add_order_item_meta( $item_id, '_arya_license_activated_at', $activated_at );
            wc_add_order_item_meta( $item_id, '_arya_license_expire_at', $new_expire_at );
            wc_add_order_item_meta( $item_id, '_arya_license_activations', $activations );
            wc_add_order_item_meta( $item_id, 'arya_license_activation_period', $activation_period );
            wc_add_order_item_meta( $item_id, 'arya_license_activations_limit', $limit );
            wc_add_order_item_meta( $item_id, 'arya_license_type', $type );
            wc_add_order_item_meta( $item_id, 'arya_license_status', 'inactive' );
            wc_add_order_item_meta( $item_id, 'arya_license', $license );
        }
    }

    /**
     * Retrieves the user id.
     *
     * @since 1.0.0
     */
    private function getUserId()
    {
        return $this->license->getOrder()->get_user_id();
    }

    /**
     * Retrieves a product.
     *
     * @since 1.0.0
     */
    private function getProduct()
    {
        return $this->license->getProduct();
    }
}
