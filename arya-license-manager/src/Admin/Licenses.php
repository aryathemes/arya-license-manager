<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

use Arya\LicenseManager\Admin\Analytics\Licenses as Listing;
use Arya\LicenseManager\License\License;
use Arya\LicenseManager\License\Order;

/**
 * Licenses class.
 *
 * @since 1.0.0
 */
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
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct()
    {
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
    public function render()
    {
        $listing = (new Listing)->prepare_items(); ?>

        <h2><?php esc_html_e( 'License Management', 'arya-license-manager' ); ?></h2>

        <form method="post">

            <?php $listing->display(); ?>

        </form>

        <?php
    }

    /**
     * Register and configure the admin screen options.
     *
     * @since 1.0.0
     */
    public function screen()
    {
        $page = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ?: 'listing';

        if ( 'listing' !== $page ) {
            return;
        }

        $args = [
            'label'   => esc_html__( 'Number of licenses per page:', 'arya-license-manager' ),
            'default' => 20,
            'option'  => 'licenses_per_page'
        ];

        add_screen_option( 'per_page', $args );
    }
}
