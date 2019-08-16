<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

use \Arya\LicenseManager\Admin\Emails\Expired;
use \Arya\LicenseManager\Admin\Emails\Suspended;

/**
 * Emails class.
 *
 * @since 1.0.0
 */
class Emails
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Emails
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_filter( 'woocommerce_email_classes', [ $this, 'classes' ], 10, 1 );

        add_action( 'arya_license_manager_license_expired_status_scheduling', [ $this, 'expired'   ], 10, 1 );
        add_action( 'arya_license_manager_license_suspended_status', [ $this, 'suspended' ], 10, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Emails
     */
    public static function newInstance(): Emails
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Emails;
        }

        return self::$instance;
    }

    /**
     * Registers the email classes.
     *
     * @since 1.0.0
     */
    public function classes( $emails )
    {
        $emails[ 'arya_licensemanager_admin_emails_expired' ] = new Expired;
        $emails[ 'arya_licensemanager_admin_emails_suspended' ] = new Suspended;

        return $emails;
    }

    /**
     * Sends a notification to the customer when a license has been suspended.
     *
     * @since 1.0.0
     */
    public function expired( $license )
    {
        WC()->mailer();

        do_action( 'arya_license_manager_expired_notification', $license );
    }

    /**
     * Sends a notification to the customer when a license has been suspended.
     *
     * @since 1.0.0
     */
    public function suspended( $license )
    {
        WC()->mailer();

        do_action( 'arya_license_manager_suspended_notification', $license );
    }
}
