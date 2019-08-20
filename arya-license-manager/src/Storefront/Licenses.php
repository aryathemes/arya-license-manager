<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

class Licenses
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Licenses
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'arya_license_manager_template_licenses', [ $this, 'licenses' ], 10, 1 );

        add_action( 'arya_license_manager_template_licenses', [ $this, 'pagination' ], 10, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Licenses
     */
    public static function newInstance(): Licenses
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Licenses;
        }

        return self::$instance;
    }

    /**
     * Displays the licenses list.
     *
     * @since 1.0.0
     */
    public function licenses( $licenses )
    {
        $total = count( $licenses );

        $pagination = (int) get_option( 'arya_license_manager_account_pagination', 10 );

        if ( $total > $pagination ) {

            $pagenum = $_REQUEST['paged'] ?? 0;

            $current = max( 1, abs( intval( $pagenum ) ) );

            $licenses = array_slice( $licenses, ( $current - 1 ) * $pagination, $pagination );
        }

        ?>

        <table class="woocommerce-table shop_table shop_table_responsive licenses-table">

        <thead>
            <tr>
                <th><?php esc_html_e( 'Order',    'arya-license-manager' ); ?></th>
                <th><?php esc_html_e( 'Software', 'arya-license-manager' ); ?></th>
                <th><?php esc_html_e( 'License',  'arya-license-manager' ); ?></th>
                <th><?php esc_html_e( 'Actions',  'arya-license-manager' ); ?></th>
            </tr>
        </thead>

        <tbody>
    
        <?php foreach( $licenses as $detail ) : ?>

        <?php
        /* License */
        $license = $detail->getLicense();

        /* Order ID */
        $order_id = intval( $detail->getOrder()->get_id() );

        $order_endpoint = wc_get_endpoint_url( 'view-order', $order_id, wc_get_page_permalink( 'myaccount' ) );

        $order = sprintf( '<a href="%1$s">#%2$s</a>', esc_url( $order_endpoint ), $order_id );

        /* Product ID */
        $product_id = intval( $detail->getProduct()->get_id() );

        $product = sprintf( '<a href="%1$s">%2$s</a>', get_permalink( $product_id ), get_the_title( $product_id ) );

        /* Actions */
        $license_endpoint = wc_get_endpoint_url( 'view-license', $license, wc_get_page_permalink( 'myaccount' ) );

        $license_endpoint = add_query_arg( 'order', $order_id, $license_endpoint );

        $license_endpoint = esc_url( wp_nonce_url( $license_endpoint, -1, '_nonce' ) );

        $license_details = esc_html__( 'Manage', 'arya-license-manager' );

        $action = sprintf( '<a class="woocommerce-button button alt" href="%1$s">%2$s</a>', $license_endpoint, $license_details ); ?>

        <tr>
            <td data-title="<?php esc_html_e( 'Order',    'arya-license-manager' ); ?>"><?= $order ?></td>
            <td data-title="<?php esc_html_e( 'Software', 'arya-license-manager' ); ?>"><?= $product ?></td>
            <td data-title="<?php esc_html_e( 'License',  'arya-license-manager' ); ?>"><code><?= $license ?></code></td>
            <td data-title="<?php esc_html_e( 'Actions',  'arya-license-manager' ); ?>"><?= $action ?></td>
        </tr>

        <?php endforeach; ?>

        </tbody>

        <tfoot>
            <tr>
                <td><?php esc_html_e( 'Order',    'arya-license-manager' ); ?></td>
                <td><?php esc_html_e( 'Software', 'arya-license-manager' ); ?></td>
                <td><?php esc_html_e( 'License',  'arya-license-manager' ); ?></td>
                <td><?php esc_html_e( 'Actions',  'arya-license-manager' ); ?></td>
            </tr>
        </tfoot>

        </table>

        <?php
    }

    /**
     * Displays the pagination link for licenses customer.
     *
     * @since 1.0.0
     */
    public function pagination( $licenses )
    {
        $pagination = get_option( 'arya_license_manager_account_pagination', 10 );

        $total = ceil( count( $licenses ) / $pagination );

        $pagenum = $_REQUEST['paged'] ?? 0;

        $current = max( 1, abs( intval( $pagenum ) ) );

        $paginate = '';

        $args = [
            'total'     => $total,
            'current'   => $current,
            'format'    => '?paged=%#%',
            'type'      => 'list',
            'prev_text' => esc_html__( '«', 'arya-license-manager' ),
            'next_text' => esc_html__( '»', 'arya-license-manager' )
        ];

        if ( ! empty( $paginate = paginate_links( $args ) ) ) {
            printf( '<nav class ="woocommerce-pagination licenses-pagination">%s</nav>', $paginate );
        }
    }
}
