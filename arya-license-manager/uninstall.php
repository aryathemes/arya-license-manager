<?php
/**
 * @link https://developer.wordpress.org/plugins/the-basics/uninstall-methods/
 *
 * @package Arya\LicenseManager
 */

/* if uninstall.php is not called by WordPress, die */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

/* Removes options */
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE 'arya_license_manager%';" );

/* Removes all cache items */
wp_cache_flush();
