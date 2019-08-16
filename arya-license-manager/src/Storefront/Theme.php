<?php
/**
 * @package Arya\LicenseManager\Storefront
 */

namespace Arya\LicenseManager\Storefront;

/**
 * Theme class.
 *
 * @since 1.0.0
 */
class Theme
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Theme
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $theme = wp_get_theme();

        if ( 'storefront' == $theme->get( 'TextDomain' ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'storefront' ], 20 );
        }
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Theme
     */
    public static function newInstance(): Theme
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Theme;
        }

        return self::$instance;
    }

    /**
     * Enqueue a CSS stylesheet for Storefront theme.
     *
     * @since 1.0.0
     */
    public function storefront()
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style( 'arya-license-manager-storefront',
            plugins_url( "static/css/storefront$suffix.css", ARYA_LICENSE_MANAGER_FILE ), [], null, 'all' );
    }
}
