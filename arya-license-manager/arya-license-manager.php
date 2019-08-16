<?php
/**
 * Plugin Name: Arya License Manager
 * Plugin URI: https://gitlab.com/arya-license-manager
 * Description: Arya License Manager integrates with WooCommerce to simplify the creation and management of software licenses.
 * Author: Arya Themes
 * Author URI: https://aryathemes.com
 * Version: 1.0.0
 * License: GNU General Public License, version 3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: arya-license-manager
 * Domain Path: /languages
 *
 * WC requires at least: 3.6
 * WC tested up to: 3.7
 *
 * @package   Arya\LicenseManager
 * @author    Luis A. Ochoa
 * @copyright 2019 Luis A. Ochoa
 * @license   GNU General Public License, version 3
 */

defined( 'ABSPATH' ) || exit;

/* Defines the constants */
if ( ! defined( 'ARYA_LICENSE_MANAGER_FILE' ) ) {
    define( 'ARYA_LICENSE_MANAGER_FILE', __FILE__ );
}

if ( ! defined( 'ARYA_LICENSE_MANAGER_TEMPLATES' ) ) {
    define( 'ARYA_LICENSE_MANAGER_TEMPLATES', dirname( __FILE__ ) . '/templates/' );
}

/* Check if WooCommerce is active */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once( dirname( ARYA_LICENSE_MANAGER_FILE ) . '/compatibility.php' );
    return;
}

/* PHP namespace autoloader */
require_once( dirname( ARYA_LICENSE_MANAGER_FILE ) . '/vendor/autoload.php' );

\Arya\LicenseManager\Loader::newInstance();
