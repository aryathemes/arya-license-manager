<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * Orders class.
 *
 * @since 1.0.0
 */
class Orders
{
    /**
     * Parameters.
     *
     * @since 1.0.0
     */
    private $args = [];

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( array $args = [] )
    {
        $this->args = $args;
    }

    /**
     * Retrieves orders that contain a license.
     *
     * @since 1.0.0
     */
    public function getOrders()
    {
        $args = [
            'exclude' => (array) $this->getExcludeOrders() ?? []
        ];

        $args += $this->args;

        return wc_get_orders( $args );
    }

    /**
     * Retrieves the ids list of orders without licenses.
     *
     * @since 1.0.0
     */
    private function getExcludeOrders()
    {
        $key = hash( 'md5', serialize( [ 'arya-orders-without-license', ARYA_LICENSE_MANAGER_FILE ] ) );

        if ( false === ( $results = wp_cache_get( $key, 'arya_license_manager' ) ) ) {

            global $wpdb;

            $sql = "SELECT DISTINCT
                        `item`.`order_id` as ids
                    FROM
                        `{$wpdb->prefix}woocommerce_order_items` AS `item`,
                        `{$wpdb->prefix}woocommerce_order_itemmeta` AS `itemmeta`
                    WHERE
                        `item`.`order_item_type` LIKE 'line_item'
                    AND
                        `item`.`order_item_id` = `itemmeta`.`order_item_id`
                    AND
                        `item`.`order_item_id` NOT IN (
                            SELECT
                                `itemmeta`.`order_item_id` as item_id
                            FROM
                                `{$wpdb->prefix}woocommerce_order_itemmeta` AS `itemmeta`
                            WHERE
                                `itemmeta`.`meta_key` LIKE 'arya_license');";

            $results = $wpdb->get_results( $sql, OBJECT );

            wp_cache_add( $key, $results, 'arya_license_manager', DAY_IN_SECONDS );
        }

        foreach( $results as $result ) {
            yield $result->ids;
        }
    }
}
