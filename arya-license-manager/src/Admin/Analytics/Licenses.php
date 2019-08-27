<?php
/**
 * @package Arya\LicenseManager\Admin\Analytics
 */

namespace Arya\LicenseManager\Admin\Analytics;

use Arya\LicenseManager\License\License;
use Arya\LicenseManager\License\Licenses as LicensesList;
use Arya\LicenseManager\License\Order;
use Arya\LicenseManager\License\Orders;
use Arya\LicenseManager\License\Traits\LicenseTrait;
use Arya\LicenseManager\Admin\Download;
use Arya\WordPress\Admin\Table;

/**
 * Licenses class.
 *
 * @since 1.0.0
 */
class Licenses extends Table
{
    use LicenseTrait;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        parent::__construct([
            'singular' => 'license',
            'plural'   => 'licenses',
            'ajax'     => false
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'license'      => esc_html__( 'License',            'arya-license-manager' ),
            'type'         => esc_html__( 'Type',               'arya-license-manager' ),
            'product'      => esc_html__( 'Product',            'arya-license-manager' ),
            'activations'  => esc_html__( 'Activations',        'arya-license-manager' ),
            'status'       => esc_html__( 'Status',             'arya-license-manager' ),
            'activated_at' => esc_html__( 'Service start date', 'arya-license-manager' ),
            'expire_at'    => esc_html__( 'Service end date',   'arya-license-manager' ),
            'order'        => esc_html__( 'Order',              'arya-license-manager' )
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function get_hidden_columns()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function get_sortable_columns()
    {
        return [
            'license' => 'license',
            'product' => 'product',
            'order'   => [ 'order', true ]
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'license':
            case 'type':
            case 'product':
            case 'activations':
            case 'status':
            case 'activated_at':
            case 'expire_at':
            case 'order':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     *
     * @return string Name of the default primary column.
     */
    protected function get_default_primary_column_name()
    {
        return 'license';
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function prepare_items()
    {
        $this->process_bulk_action();

        /* Retrieves all licenses */
        $data = $this->get_licenses() ?: [];

        $this->_column_headers = [ $this->get_columns(), $this->get_hidden_columns(), $this->get_sortable_columns() ];

        usort( $data, [ $this, 'sorting' ] );

        $perPage = $this->get_items_per_page( 'licenses_per_page', 20 );
        $pagenum = $this->get_pagenum();

        $this->set_pagination_args( [
            'total_items' => count( $data ),
            'per_page'    => $perPage
        ] );

        $data = array_slice( $data, ( ( $pagenum - 1 ) * $perPage ), $perPage );

        $this->items = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function no_items()
    {
        esc_html_e( 'No licenses found.', 'arya-license-manager' );
    }

    /**
     * Checkboxes.
     *
     * @since 1.0.0
     */
    public function column_cb( $item )
    {
        $data = sprintf( '%1$s,%2$s', $item['license'], $item['order']->get_id() );

        return sprintf( '<input type="checkbox" name="checked[]" value="%s" />', esc_attr( $data ) );
    }

    /**
     * License and actions.
     *
     * @since 1.0.0
     */
    public function column_license( $item )
    {
        /* License */
        $license_url = add_query_arg( [
            'tab'     => 'license',
            'license' => $item['license'],
            'order'   => $item['order']->get_id(),
        ], admin_url( 'admin.php?page=wc-licenses' ) );

        $license_link = sprintf( '<a href="%1$s"><code class="%2$s">%3$s</code></a>',
            esc_url( $license_url ),
            esc_attr( $item['status'] ),
            esc_attr( $item['license'] )
        );

        return $license_link;
    }

    /**
     * Type column.
     *
     * @since 1.0.0
     */
    public function column_type( $item )
    {
        return $this->getType( $item['type'] );
    }

    /**
     * Products column.
     *
     * @since 1.0.0
     */
    public function column_product( $item )
    {
        $product = $item['product'];

        $product_id = 0 <> $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

        return sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $product_id ) ), esc_html( $product->get_name() ) );
    }

    /**
     * Activations column.
     *
     * @since 1.0.0
     */
    public function column_activations( $item )
    {
        extract( $item['activations'] );

        $limit = 0 <> $limit ? intval( $limit ) : __( 'unlimited', 'arya-license-manager' );

        /* translators: %s of %s: number of activations */
        return sprintf( esc_html__( '%1$s of %2$s', 'arya-license-manager' ), $current, $limit );
    }

    /**
     * Statuses column.
     *
     * @since 1.0.0
     */
    public function column_status( $item )
    {
        $status = $item['status'];

        $text = $this->getStatus( $status );

        return sprintf( '<span class="%1$s">%2$s</span>', esc_attr( $status ), esc_html( $text ) );
    }

    /**
     * Activation date column
     *
     * @since 1.0.0
     */
    public function column_activated_at( $item )
    {
        if ( 'inactive' == $item['status'] ) {
            return "-";
        }

        $activated_at = strtotime( $item['activated_at'] );

        if ( $activated_at > current_time( 'timestamp', true ) ) {
            return $item['activated_at'];
        }

        /* translators: %s ago: human-readable time */
        return sprintf( _x( '%s ago', '%s = human-readable time', 'arya-license-manager' ), human_time_diff( $activated_at, time() ) );
    }

    /**
     * Expiration date column.
     *
     * @since 1.0.0
     */
    public function column_expire_at( $item )
    {
        if ( 'inactive' == $item['status'] ) {
            return "-";
        }

        return $item['expire_at'] ?: "-";
    }

    /**
     * Order column.
     *
     * @since 1.0.0
     */
    public function column_order( $item )
    {
        $order = $item['order'];

        $customer = $order->get_billing_company() ?: sprintf( '%1$s %2$s', $order->get_billing_first_name(), $order->get_billing_last_name() );

        return sprintf( '<a href="%1$s">#%2$s %3$s</a>', esc_url( get_edit_post_link( $order->get_id() ) ), esc_html( $order->get_id() ), esc_html( $customer ) );
    }

    /**
     * Retrieves the bulk actions.
     *
     * @since 1.0.0
     */
    public function get_bulk_actions()
    {
        return [
            'bulk-regenerate' => esc_html__( 'Regenerate download permissions', 'arya-license-manager' )
        ];
    }

    /**
     * Performs the bulk actions.
     *
     * @since 1.0.0
     */
    public function process_bulk_action()
    {
        $licenses = $_POST['checked'] ?? [];

        $licenses = array_map( 'esc_sql', $licenses );

        if ( empty( $licenses ) ) {
            return;
        }

        $f = function( $checked ) {

            if ( 1 !== preg_match( '|^(?<license>.{10,}),(?<order_id>\d+)$|', $checked, $matches ) ) {
                return;
            }

            return array_filter( $matches, function( $key ) {
                return ! is_numeric( $key );
            }, ARRAY_FILTER_USE_KEY );
        };

        if ( ( isset( $_POST['action']  ) && 'bulk-regenerate' == $_POST['action']  ) ||
             ( isset( $_POST['action2'] ) && 'bulk-regenerate' == $_POST['action2'] ) ) {

            foreach ( $licenses as $_license ) {

                extract( $f( $_license ) );

                $order = new Order( esc_sql( $order_id ) );

                if ( ! in_array( $order->get_status(), wc_get_is_paid_statuses() ) || ! $order->hasActiveLicense() ) {
                    return;
                }

                (new Download( $order ))->grantAccess();
            }
        }
    }

    /**
     * Sort an array by values using the strcmp function.
     *
     * @since 1.0.0
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return int
     */
    public function sorting( $a, $b )
    {
        $orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING ) ?: 'order';

        if ( $a[$orderby] === $b[$orderby] ) {
            return 0;
        }

        $order = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING ) ?: 'asc';

        if ( 'asc' === $order ) {
            return strcmp( $b[$orderby], $a[$orderby] );
        } else {
            return strcmp( $a[$orderby], $b[$orderby] );
        }
    }

    /**
     * @since 1.0.0
     * @access private
     */
    private function get_licenses()
    {
        $key = hash( 'md5', serialize( [ 'arya-licenses-list-table', ARYA_LICENSE_MANAGER_FILE ] ) );

        if ( false === ( $licences = wp_cache_get( $key, 'arya_license_manager' ) ) ) {

            $args = [
                'orderby' => 'ID',
                'order'   => 'DESC'
            ];

            foreach( (new LicensesList( $args ))->getLicenses() as $licence ) {
                $licences[] = [
                    'license'      => $licence->getLicense(),
                    'type'         => $licence->getType(),
                    'product'      => $licence->getProduct(),
                    'activations'  => [
                        'current'  => count( $licence->getActivations() ),
                        'limit'    => intval( $licence->getActivationsLimit() )
                    ],
                    'status'       => $licence->getStatus(),
                    'activated_at' => $licence->getActivationDate(),
                    'expire_at'    => $licence->getExpirationDate(),
                    'order'        => $licence->getOrder()
                ];
            }

            wp_cache_add( $key, $licences, 'arya_license_manager', DAY_IN_SECONDS );
        }

        return $licences;
    }
}
