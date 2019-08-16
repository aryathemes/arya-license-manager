<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * License class.
 *
 * @since 1.0.0
 */
class License
{
    /**
     * License.
     *
     * @since 1.0.0
     */
    private $license = '';

    /**
     * Order item id.
     *
     * @since 1.0.0
     */
    private $order_item_id = 0;

    /**
     * Order item.
     *
     * @since 1.0.0
     */
    private $order_item = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( string $license, int $order_id = 0 )
    {
        $this->license = esc_sql( $license );

        $this->order_item_id = $this->get_order_item_id( $license, $order_id );

        $this->order_item = new \WC_Order_Item_Product( $this->order_item_id );
    }

    /**
     * Retrieves the license.
     *
     * @since 1.0.0
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Retrieves information from a license.
     *
     * @since 1.0.0
     */
    public function getInformation()
    {
        $information = [];

        $item_id = $this->order_item_id;

        $metadata = [
            'arya_license',
            'arya_license_type',
            'arya_license_status',
            '_arya_license_activated_at',
            '_arya_license_expire_at',
            'downloads'
        ];

        foreach( $metadata as $key ) {

            switch( $key ) {
                case 'arya_license':
                    $information[ 'license' ] = wc_get_order_item_meta( $this->order_item_id, $key );
                    break;
                case 'arya_license_type':
                    $information[ 'type' ] = wc_get_order_item_meta( $this->order_item_id, $key );
                    break;
                case 'arya_license_status':
                    $information[ 'status' ] = wc_get_order_item_meta( $this->order_item_id, $key );
                    break;
                case '_arya_license_activated_at':
                    $information[ 'activated_at' ] = wc_get_order_item_meta( $this->order_item_id, $key );
                    break;
                case '_arya_license_expire_at':
                    $information[ 'expire_at' ] = wc_get_order_item_meta( $this->order_item_id, $key );
                    break;
                case 'downloads':
                    $information[ 'downloads' ] = $this->order_item->get_item_downloads();
                    break;
            }
        }

        return $information;
    }

    /**
     * Retrieves the license activation period.
     *
     * @since 1.0.0
     */
    public function getActivationPeriod()
    {
        return wc_get_order_item_meta( $this->order_item_id, 'arya_license_activation_period' );
    }

    /**
     * Retrieves the license activation date.
     *
     * @since 1.0.0
     */
    public function getActivationDate()
    {
        if ( $date = wc_get_order_item_meta( $this->order_item_id, '_arya_license_activated_at' ) ) {
            return $date;
        }

        return false;
    }

    /**
     * Retrieves the license expiration date.
     *
     * @since 1.0.0
     */
    public function getExpirationDate()
    {
        if ( $date = wc_get_order_item_meta( $this->order_item_id, '_arya_license_expire_at' ) ) {
            return $date;
        }

        return false;
    }

    /**
     * Retrieves the license activations limit.
     *
     * @since 1.0.0
     */
    public function getActivationsLimit()
    {
        return (int) wc_get_order_item_meta( $this->order_item_id, 'arya_license_activations_limit' );
    }

    /**
     * Activates the license.
     *
     * @since 1.0.0
     */
    public function activate()
    {
        if ( 'expired' !== $this->getStatus() ) {
            return $this->setStatus( 'valid' );
        }

        return false;
    }

    /**
     * Deactivates the license.
     *
     * @since 1.0.0
     */
    public function deactivate()
    {
        $expire_at = wc_get_order_item_meta( $this->order_item_id, '_arya_license_expire_at' );

        $now = current_time( 'timestamp', true );

        if ( $now <= strtotime( $expire_at ) ) {
            return $this->setStatus( 'suspended' );
        } else {
            return $this->setStatus( 'expired' );
        }
    }

    /**
     * Retrieves the product associated with a license.
     *
     * @since 1.0.0
     */
    public function getProduct()
    {
        return $this->order_item->get_product();
    }

    /**
     * Retrieves the license type.
     *
     * @since 1.0.0
     */
    public function getType()
    {
        return wc_get_order_item_meta( $this->order_item_id, 'arya_license_type' );
    }

    /**
     * Sets the license status.
     *
     * @since 1.0.0
     */
    public function setStatus( string $status )
    {
        /* Validate status */
        if ( ! in_array( $status, [ 'valid', 'expired', 'suspended', 'inactive' ] ) ) {
            return false;
        }

        do_action( "arya_license_manager_license_{$status}_status", $this->license );

        return wc_update_order_item_meta( $this->order_item_id, 'arya_license_status', $status );
    }

    /**
     * Retrieves the license status.
     *
     * @since 1.0.0
     */
    public function getStatus()
    {
        return wc_get_order_item_meta( $this->order_item_id, 'arya_license_status' );
    }

    /**
     * Retrieves the list of activations associated with a license.
     *
     * @since 1.0.0
     */
    public function getActivations()
    {
        return wc_get_order_item_meta( $this->order_item_id, '_arya_license_activations' ) ?: [];
    }

    /**
     * Establishes a activation for the use of a license.
     *
     * @since 1.0.0
     */
    public function addActivation( array $activation )
    {
        /* Gets the activations limits */
        $limit = $this->getActivationsLimit();

        /* Activations */
        $activations = $this->getActivations();

        /* Activate */
        if ( 0 == $limit || $limit > count( $activations ) ) {

            if ( in_array( $activation, $activations ) ) {
                return false;
            }

            $activations[] = $activation;

            return wc_update_order_item_meta( $this->order_item_id, '_arya_license_activations', $activations );
        }

        return false;
    }

    /**
     * Dissociate a activation for the use of a license.
     *
     * @since 1.0.0
     */
    public function removeActivation( $constraint )
    {
        /* Activations */
        $activations = $this->getActivations();

        /* Removes a activation */
        $idx = array_search( $constraint, array_column( $activations, 'constraint' ) );

        if ( false === $idx ) {
            return false;
        }

        unset( $activations[$idx] );

        $activations = array_values( $activations );

        return wc_update_order_item_meta( $this->order_item_id, '_arya_license_activations', $activations );
    }

    /**
     * Verifies whether a activation exists.
     *
     * @since 1.0.0
     */
    public function existsActivation( $activation )
    {
        $activations = $this->getActivations();

        $exists = array_search( $activation['constraint'], array_column( $activations, 'constraint' ) );

        return $exists !== false;
    }

    /**
     * Retrieves the order associated with a license.
     *
     * @since 1.0.0
     */
    public function getOrder()
    {
        return $this->order_item->get_order();
    }

    /**
     * Retrieves the user whom the license belongs.
     *
     * @since 1.0.0
     */
    public function getUser()
    {
        return $this->getOrder()->get_user();
    }

    /**
     * Verifies if a license exists.
     *
     * @since 1.0.0
     */
    public function exists()
    {
        return ! ( null == $this->order_item_id );
    }

    /**
     * Retrieves the order_item_id from a license.
     *
     * @since 1.0.0
     * @access private
     */
    private function get_order_item_id( string $license, int $order_id = 0 )
    {
        $order_item_id = 0;

        if ( 0 <> $order_id ) {
            $order_item_id = $this->get_order_item_id_order( $license, $order_id );
        } else {
            $order_item_id = $this->get_order_item_id_last( $license );
        }

        return intval( $order_item_id );
    }

    /**
     * @since 1.0.0
     * @access private
     */
    private function get_order_item_id_order( string $license, int $order_id )
    {
        $key = hash( 'md5', serialize( [ "arya-order-item-id-{$license}-{$order_id}", ARYA_LICENSE_MANAGER_FILE ] ) );

        if ( false === ( $order_item_id = wp_cache_get( $key, 'arya_license_manager' ) ) ) {

            global $wpdb;

            $sql = "SELECT
                        `itemmeta`.`order_item_id`
                    FROM
                        `{$wpdb->prefix}woocommerce_order_items` AS `item`,
                        `{$wpdb->prefix}woocommerce_order_itemmeta` AS `itemmeta`
                    WHERE
                        `item`.`order_id` = %d
                    AND
                        `itemmeta`.`meta_key` LIKE 'arya_license'
                    AND
                        `itemmeta`.`meta_value` LIKE %s
                    AND
                        `item`.`order_item_id` = `itemmeta`.`order_item_id`;";

            $query = $wpdb->prepare( $sql, $order_id, "%" . $wpdb->esc_like( $license ) . "%" );

            $result = $wpdb->get_row( $query, OBJECT );

            $order_item_id = $result->order_item_id;

            wp_cache_set( $key, $order_item_id, 'arya_license_manager', 12 * HOUR_IN_SECONDS );
        }

        return $order_item_id;
    }

    /**
     * @since 1.0.0
     * @access private
     */
    private function get_order_item_id_last( string $license )
    {
        $key = hash( 'md5', serialize( [ "arya-order-item-id-{$license}", ARYA_LICENSE_MANAGER_FILE ] ) );

        if ( false === ( $order_item_id = wp_cache_get( $key, 'arya_license_manager' ) ) ) {

            global $wpdb;

            $sql = "SELECT
                        `itemmeta`.`order_item_id`
                    FROM
                        `{$wpdb->posts}` AS `order`,
                        `{$wpdb->prefix}woocommerce_order_items` AS `item`,
                        `{$wpdb->prefix}woocommerce_order_itemmeta` AS `itemmeta`
                    WHERE
                        `itemmeta`.`meta_key` LIKE 'arya_license'
                    AND
                        `itemmeta`.`meta_value` LIKE %s
                    AND
                        `item`.`order_item_id` = `itemmeta`.`order_item_id`
                    AND
                        `order`.`ID` = `item`.`order_id`
                    AND
                        `order`.`post_status` LIKE 'wc-completed'
                    ORDER BY `order_id` DESC;";

            $query = $wpdb->prepare( $sql, "%" . $wpdb->esc_like( $license ) . "%" );

            $result = $wpdb->get_row( $query, OBJECT );

            $order_item_id = $result->order_item_id;

            wp_cache_set( $key, $order_item_id, 'arya_license_manager', 12 * HOUR_IN_SECONDS );
        }

        return $order_item_id;
    }

    /**
     * Retrieves the license.
     *
     * @since 1.0.0
     */
    public function __toString()
    {
        return (string) $this->license;
    }
}
