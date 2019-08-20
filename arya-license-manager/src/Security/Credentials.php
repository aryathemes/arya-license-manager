<?php
/**
 * @package Arya\LicenseManager\Security
 */

namespace Arya\LicenseManager\Security;

/**
 * Credentials class.
 *
 * @since 1.0.0
 */
class Credentials
{
    /**
     * Table
     *
     * @since 1.0.0
     */
    private $table = '';

    /**
     * Construct
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        global $wpdb;

        $this->table = "{$wpdb->prefix}license_manager_credentials";
    }

    /**
     * Creates a pair of credentials.
     *
     * @since 1.0.0
     */
    public function create( int $user_id )
    {
        global $wpdb;

        if ( $credentials = $this->getCredentials( $user_id ) ) {
            $wpdb->update( $this->table, [ 'status' => 'inactive' ], $credentials );
        }

        $access_key = bin2hex( random_bytes( 10 ) );

        $private_access_key = bin2hex( random_bytes( 20 ) );

        $credentials = [
            'user_id'            => $user_id,
            'access_key'         => $access_key,
            'private_access_key' => password_hash( $private_access_key, PASSWORD_DEFAULT )
        ];

        $wpdb->insert( $this->table, $credentials );

        return [
            'access_key'         => $access_key,
            'private_access_key' => $private_access_key
        ];
    }

    /**
     * Retrieves the password hash.
     *
     * @since 1.0.0
     */
    public function getHash( string $access_key )
    {
        global $wpdb;

        $sql = "SELECT
                    `private_access_key`
                FROM
                    `{$this->table}`
                WHERE
                    `access_key` LIKE %s
                AND
                    `status` LIKE 'active';";

        $result = $wpdb->get_row( $wpdb->prepare( $sql, $access_key ), OBJECT );

        return $result ? $result->private_access_key : false;
    }

    /**
     * Retrieves the user id by using the access key.
     *
     * @since 1.0.0
     */
    public function getUserId( string $access_key )
    {
        global $wpdb;

        $sql = "SELECT
                    `user_id`
                FROM
                    `{$this->table}`
                WHERE
                    `access_key` LIKE %s
                AND
                    `status` LIKE 'active';";

        $result = $wpdb->get_row( $wpdb->prepare( $sql, $access_key ), OBJECT );

        return $result ? intval( $result->user_id ) : false;
    }

    /**
     * Retrieves the credentials by using the user id.
     *
     * @since 1.0.0
     */
    public function getCredentials( int $user_id )
    {
        global $wpdb;

        $sql = "SELECT
                    `access_key`, `private_access_key`
                FROM
                    `{$this->table}`
                WHERE
                    `status` LIKE 'active'
                AND
                    `user_id` = %d;";

        $result = $wpdb->get_row( $wpdb->prepare( $sql, $user_id ), ARRAY_A );

        return $result ?: null;
    }

    /**
     * Registers the last access using the current active credentials.
     *
     * @since 1.0.0
     */
    public function accessRecord( string $access_key )
    {
        global $wpdb;

        $where = [
            'access_key' => esc_sql( $access_key )
        ];

        return $wpdb->update( $this->table, [ 'last_access' => current_time( 'mysql', true ) ], $where );
    }

    /**
     * Revokes the use of current active credentials.
     *
     * @since 1.0.0
     */
    public function revokeCredentials( int $user_id )
    {
        global $wpdb;

        if ( $credentials = $this->getCredentials( $user_id ) ) {
            return $wpdb->update( $this->table, [ 'status' => 'inactive' ], esc_sql( $credentials ) );
        }

        return false;
    }
}
