<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

use Arya\LicenseManager\License\Order;

/**
 * Email class.
 *
 * @since 1.0.0
 */
class Email
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Email
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'woocommerce_email_after_order_table', [ $this, 'email' ], 8, 4 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Email
     */
    public static function newInstance(): Email
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Email;
        }

        return self::$instance;
    }

    /**
     * Displays the licenses in the email with the details of the order.
     *
     * @since 1.0.0
     */
    public function email( $order, $sent_to_admin, $plain_text, $email )
    {
        $order = new Order( $order->get_id() );

        if ( ! $order->hasLicense() ) {
            return;
        }

        if ( $licenses = $order->getLicenses() ) {

            $template = $plain_text ? 'plain/email-licenses.php' : 'email-licenses.php';

            wc_get_template( "emails/$template", [
                'licenses' => $licenses
            ], '', ARYA_LICENSE_MANAGER_TEMPLATES );
        }
    }
}
