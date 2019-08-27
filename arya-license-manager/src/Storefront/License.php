<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

use Arya\LicenseManager\License\Traits\LicenseTrait;

class License
{
    use LicenseTrait;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var License
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'arya_license_manager_template_license', [ $this, 'license' ], 10, 1 );

        add_action( 'arya_license_manager_template_license_details', [ $this, 'details' ], 10, 1 );

        add_action( 'arya_license_manager_template_license_activations', [ $this, 'activations' ], 10, 1 );

        add_action( 'arya_license_manager_template_license_form', [ $this, 'form' ], 10, 1 );
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
     * Displays the license.
     *
     * @since 1.0.0
     */
    public function license( $license )
    {
        ?>

        <div class="license">
            <span id="license-data" class="<?php echo esc_attr( $license->getStatus() ); ?>"><?php echo esc_html( $license->getLicense() ); ?></span>
            <button class="clipboard" type="button" data-clipboard-target="#license-data" data-toggle="tooltip">
                <span class="dashicons dashicons-admin-page"></span>
            </button>
        </div>

        <?php
    }

    /**
     * Displays the general information.
     *
     * @since 1.0.0
     */
    public function details( $license )
    {
        /* Product */
        $product = $license->getProduct();

        $product_link = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $product->get_permalink() ), esc_html( $product->get_name() ) );

        /* Dates */
        if ( $activation_date = $license->getActivationDate() ) {
            $activation_date = date( 'F j, Y — H:i:s', strtotime( $activation_date ) );
        } else {
            $activation_date = '—';
        }

        if ( $expiration_date = $license->getExpirationDate() ) {
            $expiration_date = date( 'F j, Y — H:i:s', strtotime( $expiration_date ) );
        } else {
            $expiration_date = '—';
        }

        /* Activations */
        $count = count( $license->getActivations() );

        $limit = intval( $license->getActivationsLimit() );

        $activations = sprintf( __( '%1$s of %2$s', 'arya-license-manager' ), $count, $limit ?: __( 'unlimited', 'arya-license-manager' ) ); ?>

        <table class="shop_table license-manager-details-table">
            <tr>
                <td><?php esc_html_e( 'Product', 'arya-license-manager' ); ?></td>
                <td><?php echo wp_kses( $product_link, [ 'a' => [ 'href' => true ] ] ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Service start date', 'arya-license-manager' ); ?></td>
                <td><?php echo esc_html( $activation_date ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Service end date', 'arya-license-manager' ); ?></td>
                <td><?php echo esc_html( $expiration_date ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'License type', 'arya-license-manager' ); ?></td>
                <td><?php echo esc_html( $this->getType( $license->getType() ) ); ?></td>
            </tr>
            <tr>
                <td><?php esc_html_e( 'Allowed activations', 'arya-license-manager' ); ?></td>
                <td><?php echo esc_html( $activations ); ?></td>
            </tr>
        </table>

        <?php
    }

    /**
     * Displays the license activations.
     *
     * @since 1.0.0
     */
    public function activations( $license )
    {
        $activations = $license->getActivations(); ?>

        <table class="shop_table shop_table_responsive activations-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Activation', 'arya-license-manager' ); ?></th>
                    <th><?php esc_html_e( 'Activation date', 'arya-license-manager' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'arya-license-manager' ); ?></th>
                </tr>
            </thead>

            <tbody>

            <?php if ( 0 == count( $activations ) ) : ?>
                <tr>
                    <td class="no-associations" colspan="3">
                        <p><?php esc_html_e( 'This license has not been associated to a website.', 'arya-license-manager' ); ?></p>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach( $activations as $activation ) : ?>
                <tr>
                    <td>
                        <span><?php echo esc_html( $this->getActivationType( $activation['type'] ) ); ?></span>
                        <br />
                        <code><?php echo esc_html( $activation['constraint'] ); ?></code>
                    </td>
                    <td><?php echo esc_html( date( 'F j, Y — H:i:s', $activation['activated_at'] ) ); ?></td>
                    <td>
                        <button class="woocommerce-button button activation-revoke" type="button"
                            data-license="<?php echo esc_attr( $license->getLicense() ) ?>"
                            data-order="<?php echo esc_attr( $license->getOrder()->get_id() ) ?>"
                            data-constraint="<?php echo esc_attr( $activation['constraint'] ) ?>">
                            <?php esc_html_e( 'Revoke', 'arya-license-manager' ); ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>

            </tbody>

        </table>

        <?php
    }

    /**
     * Displays the form to add a activation.
     *
     * @since 1.0.0
     */
    public function form( $license )
    {
        $limit = $license->getActivationsLimit();

        $activations = $license->getActivations();

        $order_id = $license->getOrder()->get_id();

        if ( ! ( 0 == intval( $limit ) || count( $activations ) < intval( $limit ) ) ) {
            return;
        }
        ?>

        <form class="arya-license-manager-form" method="post">

            <p id="field-type" class="woocommerce-form-row form-row form-row-wide">
                <label for="type"><?php esc_html_e( 'Associate', 'arya-license-manager' ); ?></label>
                <select id="type" name="type">
                    <option value="wordpress"><?php esc_html_e( 'WordPress', 'arya-license-manager' ); ?></option>
                    <option value="webapp"><?php esc_html_e( 'Web Application', 'arya-license-manager' ); ?></option>
                </select>
            </p>

            <p id="field-constraint" class="woocommerce-form-row form-row form-row-wide">
                <label id="constraint-label" for="constraint"><?php esc_html_e( 'Website URL', 'arya-license-manager' ); ?></label>
                <input id="constraint" type="text" name="constraint">
                <label id="constraint-description">
                    <?php esc_html_e( "The IP address of the provided website is automatically assigned.", 'arya-license-manager' ); ?>
                </label>
            </p>

            <button class="associate-button button alt" type="button"
                data-license="<?php echo esc_attr( $license ); ?>"
                data-order="<?php echo esc_attr( $order_id ); ?>">
                <?php esc_html_e( 'Accept', 'arya-license-manager' ); ?>
            </button>

        </form>

        <?php
    }
}
