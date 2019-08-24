<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

use Arya\LicenseManager\License\License as LicenseInformation;
use Arya\LicenseManager\License\Traits\LicenseTrait;

/**
 * License class.
 *
 * @since 1.0.0
 */
class License
{
    use LicenseTrait;

    /**
     * License information.
     *
     * @since 1.0.0
     */
    private $information = null;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var License
     */
    private static $instance;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $license = filter_input( INPUT_GET, 'license', FILTER_SANITIZE_STRING ) ?: '';

        $order_id = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING ) ?: '';

        if ( empty( $license ) || empty( $order_id ) ) {
            return;
        }

        $this->license = new LicenseInformation( $license, $order_id );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return License
     */
    public static function newInstance(): License
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new License;
        }

        return self::$instance;
    }

    /**
     * Displays the form to edit a license.
     *
     * @since 1.0.0
     */
    public function render()
    {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! $this->license->exists() ) {
            printf( '<div class="notice-license-error"><p>%s</p></div>', esc_html__( 'License not available.', 'arya-license-manager' ) );
            return;
        }

        $columns = get_current_screen()->get_columns(); ?>

        <h2><?php esc_html_e( 'License details', 'arya-license-manager' ); ?></h2>

        <form method="post">

            <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
            <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-<?php echo $columns; ?>">

                    <div id="postbox-container-1" class="postbox-container">
                        <?php do_meta_boxes( get_current_screen(), 'side', null ); ?>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <?php do_meta_boxes( get_current_screen(), 'normal', null ); ?>
                        <?php do_meta_boxes( get_current_screen(), 'advanced', null ); ?>
                    </div>

                </div>

            </div>

        </form>

        <?php
    }

    /**
     * Adds the meta boxes.
     *
     * @since 1.0.0
     */
    public function metaboxes()
    {
        $screen = get_current_screen();

        /* License information */
        add_meta_box( 'information', esc_html__( 'Information', 'arya-license-manager' ), [ $this, 'information' ], $screen, 'normal', 'high' );

        add_meta_box( 'activations', esc_html__( 'Activations', 'arya-license-manager' ), [ $this, 'activations' ], $screen, 'normal', 'high' );

        /* Actions */
        add_meta_box( 'actions', esc_html__( 'Actions', 'arya-license-manager' ), [ $this, 'actions' ], $screen, 'side', 'high' );
    }

    /**
     * Displays the license information.
     *
     * @since 1.0.0
     */
    public function information()
    {
        /* License information */
        $_limit = intval( $this->license->getActivationsLimit() );

        $limit = 0 == $_limit ?
            __( 'Unlimited activations', 'arya-license-manager' ) :
            /* translators: %d activation: number of activations */
            sprintf( _n( '%d activation', '%d activations', $_limit, 'arya-license-manager' ), $_limit );

        $product = $this->license->getProduct();

        $product_id = 0 <> $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

        /* Order information */
        $order = $this->license->getOrder();

        $order_id = $order->get_id();

        $order_status = $order->get_status();

        /* Dates */
        if ( in_array( $order_status, wc_get_is_paid_statuses() ) ) {
            $activation_date = date( 'F j, Y — H:i:s', strtotime( $this->license->getActivationDate() ) );
        } else {
            $activation_date = '-';
        }

        if ( in_array( $order_status, wc_get_is_paid_statuses() ) ) {
            $expiration_date = date( 'F j, Y — H:i:s', strtotime( $this->license->getExpirationDate() ) );
        } else {
            $expiration_date = '-';
        }

        /* Customer information */
        $customer = $order->get_billing_company() ?: sprintf( '%1$s %2$s', $order->get_billing_first_name(), $order->get_billing_last_name() );

        $order_status = wc_get_order_status_name( $order->get_status() );

        $customer = sprintf( '<a href="%1$s">#%2$s %3$s (%4$s)</a>', get_edit_post_link( $order_id ), $order_id, $customer, $order_status ); ?>

        <table class="information-table widefat">
            <tr>
                <td><?php esc_html_e( 'License', 'arya-license-manager' ); ?></td>
                <td><code><?php echo $this->license; ?></code></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Status', 'arya-license-manager' ); ?></td>
                <td><?php echo $this->getStatus( $this->license->getStatus() ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'License type', 'arya-license-manager' ); ?></td>
                <td><?php echo $this->getType( $this->license->getType() ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Product', 'arya-license-manager' ); ?></td>
                <td><?php printf( '<a href="%1$s">%2$s</a>', get_edit_post_link( $product_id ), $product->get_name() ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Service start date', 'arya-license-manager' ); ?></td>
                <td><?php echo $activation_date; ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Service end date', 'arya-license-manager' ); ?></td>
                <td><?php echo $expiration_date; ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Allowed activations', 'arya-license-manager' ); ?></td>
                <td><?php echo $limit; ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Order / Customer', 'arya-license-manager' ); ?></td>
                <td><?php echo $customer; ?></td>
            </tr>
        </table>

        <?php
    }

    /**
     * Displays the license activations.
     *
     * @since 1.0.0
     */
    public function activations()
    {
        $activations = $this->license->getActivations();

        $count = count( $activations );

        $limit = intval( $this->license->getActivationsLimit() );

        $order_id = $this->license->getOrder()->get_id(); ?>

        <table class="activations-table widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Activation', 'arya-license-manager' ); ?></th>
                    <th><?php esc_html_e( 'Activation date', 'arya-license-manager' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'arya-license-manager' ); ?></th>
                </tr>
            </thead>

            <?php if ( 0 === $limit || $count < $limit ) : ?>

            <tfoot>
                <tr>
                    <th colspan="3">

                    <form>
                        <div class="associate-form">

                            <div class="form-row">
                                <div class="form-column">
                                    <label for="license-type"><?php esc_html_e( 'Associate', 'arya-license-manager' ); ?></label>
                                </div>
                                <div class="form-column">
                                    <select id="license-type" name="license-type">
                                        <option value="wordpress"><?php esc_html_e( 'WordPress', 'arya-license-manager' ); ?></option>
                                        <option value="webapp"><?php esc_html_e( 'Web Application', 'arya-license-manager' ); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-column">
                                    <label id="license-constraint-label" for="license-constraint">
                                        <?php esc_html_e( 'Website URL', 'arya-license-manager' ); ?>
                                    </label>
                                </div>
                                <div class="form-column">
                                    <input id="license-constraint" type="text" name="license-constraint">
                                    <br />
                                    <label id="license-constraint-description">
                                        <?php esc_html_e( 'The IP address of the provided website is automatically assigned.', 'arya-license-manager' ); ?>
                                    </label>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-column">
                                    <button class="components-button is-button is-default is-large activation-add">
                                        <?php esc_html_e( 'Associate license', 'arya-license-manager' ); ?>
                                    </button>
                                </div>
                            </div>

                        </div>

                    </form>

                    </th>
                </tr>
            </tfoot>

            <?php endif; ?>

            <tbody>

            <?php if ( 0 == $count ) : ?>

                <tr>
                    <td class="no-activations" colspan="3">
                        <p><?php esc_html_e( 'This license has not been associated to a website.', 'arya-license-manager' ); ?></p>
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach( $activations as $activation ) : ?>

                <tr>
                    <td>
                        <?php echo $this->getActivationType( $activation['type'] ); ?>
                        <br />
                        <?php echo $activation['constraint'] . ' — ' . $activation['information']; ?>
                    </td>
                    <td><?php echo date( 'F j, Y — H:i:s', $activation['activated_at'] ); ?></td>
                    <td>
                        <button class="components-button is-button is-default is-large activation-revoke"
                            data-constraint="<?php echo $activation['constraint']; ?>">
                            <?php esc_html_e( 'Revoke', 'arya-license-manager' ); ?>
                        </button>
                    </td>
                </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>

        <?php
    }

    /**
     * Displays the actions metabox.
     *
     * @since 1.0.0
     */
    public function actions()
    {
        /* License information */
        $information = $this->license->getInformation();

        /* Actions */
        $actions = [
            'license_activate'   => __( 'Activate',   'arya-license-manager' ),
            'license_deactivate' => __( 'Deactivate', 'arya-license-manager' ),
            'license_renew'      => __( 'Renew',      'arya-license-manager' )
        ];

        /* Order */
        $order_id = $this->license->getOrder()->get_id(); ?>

        <select class="license-action" name="license_action">
            <option value="license_none"><?php esc_html_e( 'Choose an action...', 'arya-license-manager' ); ?></option>
            <?php foreach( $actions as $hook => $action ) : ?>
                <option value="<?php echo esc_attr( $hook ); ?>"><?php echo esc_html( $action ); ?></option>
            <?php endforeach; ?>
        </select>

        <button class="components-button is-button is-default is-large button-actions"
            data-license="<?php echo $information['license']; ?>"
            data-order="<?php echo $order_id; ?>"><?php esc_html_e( 'Update', 'arya-license-manager' ); ?></button>

        <br class="clear" />

        <?php
    }

    /**
     * Enqueue the dashboard scripts.
     *
     * @since 1.0.0
     */
    public function enqueue()
    {
        /* License information */
        $license = $this->license->getInformation();

        wp_enqueue_script( 'postbox' );

        $dashboard = [
            'error'                   => esc_html__( 'It was not possible to associate the license.', 'arya-license-manager' ),
            'license'                 => $this->license->getLicense(),
            'order'                   => $this->license->getOrder()->get_id(),
            'license_actions_nonce'   => wp_create_nonce( 'arya-license-manager-license-actions' ),
            'activation_add_nonce'    => wp_create_nonce( 'arya-license-manager-activation-add' ),
            'activation_revoke_nonce' => wp_create_nonce( 'arya-license-manager-activation-revoke' )
        ];
        wp_localize_script( 'arya-license-manager-admin-license', 'arya_license_manager', $dashboard );

        wp_enqueue_script( 'arya-license-manager-admin-license' );
    }

    /**
     * Registers and configures the admin screen options.
     *
     * @since 1.0.0
     */
    public function screen()
    {
        $page = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ?: 'listing';

        if ( 'listing' === $page ) {
            return;
        }

        add_screen_option( 'layout_columns', [
            'max'     => 2,
            'default' => 2
        ] );
    }

    /**
     * Add a jQuery function to make collapsible metaboxes.
     *
     * @since 1.0.0
     */
    public function footer()
    {
        ?>
        <script>
            jQuery(document).ready(function() {
                postboxes.add_postbox_toggles(pagenow);
            });
        </script>
        <?php
    }
}
