<?php
/**
 * @package Arya\LicenseManager\License
 */

namespace Arya\LicenseManager\License;

/**
 * AbstractActivation class.
 *
 * @since 1.0.0
 */
abstract class AbstractActivation
{
    /**
     * Retrieves the information of a license in use.
     *
     * @since 1.0.0
     */
    public abstract function getActivation();

    /**
     * Apply filters.
     *
     * @since 1.0.0
     */
    final protected function filter( $activation, $data, $context )
    {
        return apply_filters( 'arya_license_manager_activation', $activation, $data, $context );
    }
}
