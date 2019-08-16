<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * Generator class.
 *
 * @since 1.0.0
 */
class Generator
{
    /**
     * Number of characters to generate a license.
     *
     * @since 1.0.0
     */
    private $length = 0;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( int $length = 10 )
    {
        $this->length = $length;
    }

    /**
     * Retrieves the license.
     *
     * @since 1.0.0
     */
    public function getLicense()
    {
        return $this->create();
    }

    /**
     * Generates a new license.
     *
     * @since 1.0.0
     */
    private function create()
    {
        $length = $this->length;

        $bytes = intval( ceil( $length / 2 ) );

        $license = substr( bin2hex( random_bytes( $bytes ) ), 0, $length );

        $chunklen = (int) get_option( 'arya_license_manager_chunks', 5 );

        if ( 0 <> $chunklen ) {
            $license = trim( chunk_split( $license, $chunklen, '-' ), '-' );
        }

        return apply_filters( 'arya_license_manager_create_license', $license, $length );
    }
}
