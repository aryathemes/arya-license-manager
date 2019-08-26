<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

use Arya\LicenseManager\License\Generator;
use Arya\LicenseManager\License\Traits\LicenseTrait;

/**
 * Order class.
 *
 * @since 1.0.0
 */
class Order
{
    use LicenseTrait;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Order
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        /* Actions */
        add_filter( 'woocommerce_order_actions', [ $this, 'actions' ], 10, 1 );

        add_action( 'woocommerce_order_action_regenerate_download_permissions_licenses', [ $this, 'regenerate' ], 10, 1 );

        /* Activation */
        foreach( wc_get_is_paid_statuses() as $status ) {
            add_action( "woocommerce_order_status_{$status}", [ $this, 'activate' ], 10, 2 );
        }

        /* Deactivate */
        foreach( wc_get_is_paid_statuses() as $status ) {
            add_action( "woocommerce_order_status_{$status}_to_pending", [ $this, 'deactivate' ], 10, 1 );
            add_action( "woocommerce_order_status_{$status}_to_on-hold", [ $this, 'deactivate' ], 10, 1 );
        }

        /* Renew */
        foreach( wc_get_is_paid_statuses() as $status ) {
            add_action( "woocommerce_order_status_pending_to_{$status}", [ $this, 'renew' ], 10, 2 );
        }

        /* Delete */
        $delete = [
            'woocommerce_order_status_failed',
            'woocommerce_order_status_refunded',
            'woocommerce_order_status_cancelled'
        ];

        foreach( $delete as $status ) {
            add_action( $status, [ $this, 'delete' ], 10, 1 );
        }

        /* Items (Dashboard and Storefront) */
        add_filter( 'woocommerce_order_item_display_meta_key',   [ $this, 'itemDisplayKey'   ], 10, 3 );
        add_filter( 'woocommerce_order_item_display_meta_value', [ $this, 'itemDisplayValue' ], 10, 3 );

        add_filter( 'woocommerce_hidden_order_itemmeta', function( $item_meta ) {

            $item_meta += [
                '_arya_license_activated_at',
                '_arya_license_expire_at',
                'arya_license'
            ];

            return $item_meta;
        }, 10, 1 );

        /* Clear cache */
        add_action( 'woocommerce_order_status_changed', [ $this, 'cache' ], 10, 1 );
        add_action( 'woocommerce_delete_order', [ $this, 'cache' ], 10, 1 );

        add_action( 'woocommerce_order_status_changed', [ $this, 'cacheUser' ], 10, 4 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Order
     */
    public static function newInstance(): Order
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Order;
        }

        return self::$instance;
    }

    /**
     * Register a new order action in order to customers have access to downloadable products.
     *
     * @since 1.0.0
     */
    public function actions( $actions )
    {
        return $actions + [
            'regenerate_download_permissions_licenses' => esc_html__( 'Regenerate download permissions for licenses', 'arya-license-manager' )
        ];
    }

    /**
     * Grants access to new downloads of a product to an order with an active
     * license.
     *
     * @since 1.0.0
     */
    public function regenerate( $order )
    {
        $order = new \Arya\LicenseManager\License\Order( $order );

        if ( ! $order->hasActiveLicense() ) {
            return;
        }

        (new Download( $order ))->grantAccess();
    }

    /**
     * Renew the license.
     *
     * @since 1.0.0
     */
    public function renew( $order_id, $wc_order )
    {
        foreach ( $wc_order->get_items() as $item ) {

            $product = $item->get_product();

            if ( ! $this->isLicense( $product ) ) {
                continue;
            }

            wc_update_order_item_meta( $item->get_id(), 'arya_license_status', 'valid' );
        }
    }

    /**
     * Activate the license.
     *
     * @since 1.0.0
     */
    public function activate( $order_id, $wc_order )
    {
        /* Activation date */
        $activated_at_timestamp = current_time( 'timestamp', true );

        $activated_at = gmdate( 'Y-m-d H:i:s', $activated_at_timestamp );

        /* Items */
        foreach ( $wc_order->get_items() as $item ) {

            $product = $item->get_product();

            if ( ! $this->isLicense( $product ) ) {
                continue;
            }

            $item_id = $item->get_id();

            $meta = wc_get_order_item_meta( $item_id, 'arya_license' );

            if ( empty( $meta ) ) {

                $product_id = $product->get_id();

                /* Retrieves a new license */
                $length = get_option( 'arya_license_manager_length', 25 );

                $license = (new Generator( intval( $length ) ))->getLicense();

                /* License type */
                $type = get_post_meta( $product_id, '_arya_license_license_type', true );

                /* Extra */
                $activations = [];

                /* Activation limit */
                $limit = intval( get_post_meta( $product_id, '_arya_license_activation_limit', true ) );

                /* Expire date */
                if ( empty( $value = get_post_meta( $product_id, '_arya_license_expire_value', true ) ) ) {
                    $value = 1;
                }

                if ( empty( $interval = get_post_meta( $product_id, '_arya_license_expire_interval', true ) ) ) {
                    $interval = 'years';
                }

                $activation_period = $value > 1 ? "{$value} {$interval}" : rtrim( "{$value} {$interval}", 's' );

                $expire_at = gmdate( 'Y-m-d H:i:s', strtotime( "+ {$activation_period}", $activated_at_timestamp ) );

                wc_add_order_item_meta( $item_id, 'arya_license_activation_period', $activation_period );
                wc_add_order_item_meta( $item_id, 'arya_license_activations_limit', $limit );
                wc_add_order_item_meta( $item_id, 'arya_license_type', $type );
                wc_add_order_item_meta( $item_id, 'arya_license_status', 'valid' );
                wc_add_order_item_meta( $item_id, 'arya_license', $license );

                wc_add_order_item_meta( $item_id, '_arya_license_activations', $activations );
                wc_add_order_item_meta( $item_id, '_arya_license_activated_at', $activated_at );
                wc_add_order_item_meta( $item_id, '_arya_license_expire_at', $expire_at );
            }
        }
    }

    /**
     * Deactivate the license.
     *
     * @since 1.0.0
     */
    public function deactivate( $order_id )
    {
        $order = wc_get_order( $order_id );

        foreach ( $order->get_items() as $item ) {

            $product = $item->get_product();

            $product_id = $product->get_id();

            if ( ! $this->isLicense( $product ) ) {
                continue;
            }

            $item_id = $item->get_id();

            wc_update_order_item_meta( $item_id, 'arya_license_status', 'suspended' );
        }
    }

    /**
     * Deletes the license.
     *
     * @since 1.0.0
     */
    public function delete( $order_id )
    {
        $order = wc_get_order( $order_id );

        foreach ( $order->get_items() as $item ) {

            $product = $item->get_product();

            if ( ! $this->isLicense( $product ) ) {
                continue;
            }

            $item_id = $item->get_id();

            $meta = [
                'arya_license_activation_period',
                'arya_license_activations_limit',
                'arya_license_type',
                'arya_license_status',
                'arya_license',
                '_arya_license_activations',
                '_arya_license_activated_at',
                '_arya_license_expire_at'
            ];

            foreach( $meta as $key ) {
                wc_delete_order_item_meta( $item_id, $key );
            }
        }
    }

    /**
     * Formats the title of the metadata.
     *
     * @since 1.0.0
     */
    public function itemDisplayKey( $display_key, $meta, $wc_order_item )
    {
        switch ( $meta->key ) {
            case 'arya_license':
                $display_key = esc_html__( 'License', 'arya-license-manager' );
                break;
            case 'arya_license_status':
                $display_key = esc_html__( 'Status', 'arya-license-manager' );
                break;
            case 'arya_license_activation_period':
                $display_key = esc_html__( 'Activation period', 'arya-license-manager' );
                break;
            case 'arya_license_activations_limit':
                $display_key = esc_html__( 'Activations limit', 'arya-license-manager' );
                break;
            case 'arya_license_type':
                $display_key = esc_html__( 'Type', 'arya-license-manager' );
                break;
            case '_arya_license_activated_at':
                $display_key = esc_html__( 'Activation date', 'arya-license-manager' );
                break;
            case '_arya_license_expire_at':
                $display_key = esc_html__( 'Expiry date', 'arya-license-manager' );
                break;
        }

        return $display_key;
    }

    /**
     * Formats the value of the metadata.
     *
     * @since 1.0.0
     */
    public function itemDisplayValue( $display_value, $meta, $wc_order_item )
    {
        $order_status = $wc_order_item->get_order()->get_status();

        switch ( $meta->key ) {
            case 'arya_license':
                $display_value = "<code>$display_value</code>";
                break;
            case 'arya_license_activations_limit':
                if ( 0 === intval( $display_value ) ) {
                    $display_value = esc_html__( 'Unlimited activations', 'arya-license-manager' );
                } else {
                    $display_value = sprintf( _n( '%d activation', '%d activations', intval( $display_value ), 'arya-license-manager' ), $display_value );
                }

                break;
            case 'arya_license_type':
                $display_value = $this->getType( $display_value );
                break;
            case 'arya_license_status':
                $display_value = $this->getStatus( $display_value );
                break;
            case '_arya_license_activated_at':
                if ( ! in_array( $order_status, wc_get_is_paid_statuses() ) ) {
                    $display_value = '-';
                }
                break;
            case '_arya_license_expire_at':
                if ( ! in_array( $order_status, wc_get_is_paid_statuses() ) ) {
                    $display_value = '-';
                }
                break;
        }

        return $display_value;
    }

    /**
     * Clear cache.
     *
     * @since 1.0.0
     */
    public function cache( $order_id )
    {
        $contexts = [
            'arya-licenses-list-table',
            'arya-orders-without-license'
        ];

        foreach( $contexts as $context ) {

            /* Cache */
            $key = hash( 'md5', serialize( [ $context, ARYA_LICENSE_MANAGER_FILE ] ) );

            /* Delete */
            wp_cache_delete( $key, 'arya_license_manager' );
        }
    }

    /**
     * Clear customer cache.
     *
     * @since 1.0.0
     */
    public function cacheUser( $order_id, $from, $to, $order )
    {
        $args = [
            'orderby'     => 'ID',
            'order'       => 'DESC',
            'customer_id' => $order->get_user_id(),
            'status'      => 'completed'
        ];

        $key = hash( 'md5', serialize( [ 'arya-customer-licenses', ARYA_LICENSE_MANAGER_FILE, $args ] ) );

        wp_cache_delete( $key, 'arya_license_manager' );
    }
}
