<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

use Arya\LicenseManager\License\License;

/**
 * Scheduling class.
 *
 * @since 1.0.0
 */
class Scheduling
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Scheduling
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        if( ! wp_next_scheduled( 'arya_license_manager_schedule_twicedaily' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'arya_license_manager_schedule_twicedaily' );
        }
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Scheduling
     */
    public static function newInstance(): Scheduling
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Scheduling;
        }

        return self::$instance;
    }

    /**
     * Hooks.
     *
     * @since 1.0.0
     */
    public function events()
    {
        add_action( 'arya_license_manager_schedule_twicedaily', [ $this, 'licensesExpired' ] );
    }

    /**
     * Updates the expired licenses status.
     *
     * @since 1.0.0
     */
    public function licensesExpired()
    {
        foreach( $this->getLicensesExpired() as $item ) {

            $item_id = $item->id;

            $expire_at = wc_get_order_item_meta( $item_id, '_arya_license_expire_at' );

            if ( strtotime( $expire_at ) > current_time( 'timestamp', true ) ) {
                return;
            }

            if ( 'valid' == wc_get_order_item_meta( $item_id, 'arya_license_status' ) ) {

                $license = wc_get_order_item_meta( $item_id, 'arya_license' );

                do_action( 'arya_license_manager_license_expired_status_scheduling', $license );

                wc_update_order_item_meta( $item_id, 'arya_license_status', 'expired' );
            }
        }
    }

    /**
     * Retrieves licenses expired.
     *
     * @since 1.0.0
     * @access private
     */
    private function getLicensesExpired()
    {
        $key = hash( 'md5', serialize( [ 'arya-licenses-expired', ARYA_LICENSE_MANAGER_FILE ] ) );

        if ( false === ( $results = wp_cache_get( $key, 'arya_license_manager' ) ) ) {

            global $wpdb;

            $sql = "SELECT DISTINCT
                        `itemmeta`.`order_item_id` as id
                    FROM
                        `{$wpdb->prefix}woocommerce_order_itemmeta` AS `itemmeta`
                    WHERE
                        `itemmeta`.`meta_key` LIKE '_arya_license_expire_at'
                    AND
                        `itemmeta`.`meta_value` <= NOW() AND `itemmeta`.`meta_value` >= NOW() - INTERVAL 2 DAY;";

            $results = $wpdb->get_results( $sql, OBJECT );

            wp_cache_add( $key, $results, 'arya_license_manager', 12 * HOUR_IN_SECONDS );
        }

        return $results;
    }
}
