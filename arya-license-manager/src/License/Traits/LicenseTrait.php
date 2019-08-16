<?php
/**
 * @package Arya\LicenseManager\License\Traits
 */

namespace Arya\LicenseManager\License\Traits;

/**
 * License trait.
 *
 * @since 1.0.0
 */
trait LicenseTrait
{
    /**
     * Verifies whether a product is a license.
     *
     * @since 1.0.0
     */
    public function isLicense( $product )
    {
        /* License product */
        if ( $product->is_type( 'license' ) ) {
            return true;
        }

        /* Simple/Variable product */
        if ( $product->is_type( [ 'simple', 'variation' ] ) ) {
            $licensable = get_post_meta( $product->get_id(), '_licensable', true );

            return filter_var( $licensable, FILTER_VALIDATE_BOOLEAN );
        }

        return false;
    }

    /**
     * Retrieves the license type.
     *
     * @since 1.0.0
     */
    public function getType( $type )
    {
        $text = '';

        switch( $type ) {
            case 'perpetual':
                $text = esc_html__( 'Perpetual', 'arya-license-manager' );
                break;
            case 'on_demand_software':
                $text = esc_html__( 'On demand software', 'arya-license-manager' );
                break;
            default:
                $text = $type;
        }

        return $text;
    }

    /**
     * Retrieves the allowed license statuses.
     *
     * @since 1.0.0
     */
    public function getStatuses()
    {
        return apply_filters( 'arya_license_manager_license_statuses', [
            'valid'     => esc_html__( 'Valid',     'arya-license-manager' ),
            'expired'   => esc_html__( 'Expired',   'arya-license-manager' ),
            'suspended' => esc_html__( 'Suspended', 'arya-license-manager' ),
            'inactive'  => esc_html__( 'Inactive',  'arya-license-manager' )
        ] );
    }

    /**
     * Retrieves the license status.
     *
     * @since 1.0.0
     */
    public function getStatus( $status )
    {
        $statuses = $this->getStatuses();

        return $statuses[ $status ] ?? esc_html__( 'Unknown', 'arya-license-manager' );
    }

    /**
     * Retrieves the activation type.
     *
     * @since 1.0.0
     */
    public function getActivationType( $type )
    {
        $text = '';

        switch( $type ) {
            case 'wordpress':
                $text = esc_html__( 'WordPress', 'arya-license-manager' );
                break;
            case 'webapp':
                $text = esc_html__( 'Web Application', 'arya-license-manager' );
                break;
            default:
                $text = $type;
        }

        return $text;
    }
}
