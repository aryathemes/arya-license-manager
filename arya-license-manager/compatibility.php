<?php
/**
 * @package Arya\LicenseManager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Deactivates the plugin if WooCommerce is not activated.
 *
 * @since 1.0.0
 */
function arya_license_manager_deactivate()
{
    /* Deactivates the plugin */
    deactivate_plugins( plugin_basename( ARYA_LICENSE_MANAGER_FILE ) );

    unset( $_GET['activate'] );

    /* Display an admin notice */
    add_action( 'admin_notices', 'arya_license_manager_notice' );
}
add_action( 'admin_menu', 'arya_license_manager_deactivate' );

/**
 * Display an admin notice.
 *
 * @since 1.0.0
 */
function arya_license_manager_notice()
{
    echo '<div class="notice error is-dismissible"><p>';
    echo esc_html__( 'Arya License Manager requires WooCommerce 3.6.0 or greater.', 'arya-license-manager' );
    echo '</p></div>';
}
