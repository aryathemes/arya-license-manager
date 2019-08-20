<?php
/**
 * @package Arya\LicenseManager
 */

namespace Arya\LicenseManager;

/**
 * Install class.
 *
 * @since 1.0.0
 */
class Install
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Install
     */
    private static $instance;

    /**
     * Constructor.
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
     * @return Install
     */
    public static function newInstance(): Install
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Install;
        }

        return self::$instance;
    }

    /**
     * Creates the table to store the security credentials.
     *
     * @since 1.0.0
     */
    public function create()
    {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        /* Tables */
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}license_manager_credentials(
                    credential_id BIGINT NOT NULL AUTO_INCREMENT,
                    user_id BIGINT NOT NULL,
                    access_key VARCHAR(50) NOT NULL,
                    private_access_key VARCHAR(255) NOT NULL,
                    last_access BIGINT NULL DEFAULT NULL,
                    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                    PRIMARY KEY (credential_id)
                ) $charsetCollate;";

        dbDelta( $sql );

        add_option( 'arya_license_manager_db_version', Loader::VERSION );
    }
}
