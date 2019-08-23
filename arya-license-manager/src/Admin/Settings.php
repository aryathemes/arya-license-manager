<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

/**
 * Settings class.
 *
 * @since 1.0.0
 */
class Settings
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Settings
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        /* Products */
        add_filter( 'woocommerce_get_sections_products', [ $this, 'sectionsProductsLicense' ], 10, 1 );
        add_filter( 'woocommerce_get_settings_products', [ $this, 'settingsProductsLicense' ], 10, 2 );

        /* Advanced */
        add_filter( 'woocommerce_get_settings_advanced', [ $this, 'settingsAdvancedLicense' ], 10, 2 );
        add_filter( 'woocommerce_get_settings_advanced', [ $this, 'settingsAdvancedCredentials' ], 20, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Settings
     */
    public static function newInstance(): Settings
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Settings;
        }

        return self::$instance;
    }

    /**
     * Adds the 'Licenses' section to products settings page.
     *
     * @since 1.0.0
     */
    public function sectionsProductsLicense( $sections )
    {
        $sections['license-manager'] = __( 'Licenses', 'arya-license-manager' );

        return $sections;
    }

    /**
     * Adds the fields to 'Licenses' section.
     *
     * @since 1.0.0
     */
    public function settingsProductsLicense( $settings, $section )
    {
        if ( 'license-manager' !== $section ) {
            return $settings;
        }

        /**
         * License
         */
        $license_settings[] = [
            'id'   => 'arya_license_manager_title',
            'type' => 'title',
            'name' => __( 'Arya License Manager', 'arya-license-manager' ),
            'desc' => __( 'The following options are used to configure Arya License Manager', 'arya-license-manager' )
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_length',
            'name'     => __( 'Length', 'arya-license-manager' ),
            'desc_tip' => __( 'Number of characters to generate a new license. Licenses shall have a minimum length of 10 characters.', 'arya-license-manager' ),
            'type'     => 'number',
            'default'  => '25',
            'custom_attributes' => [
                'step' => 1,
                'min'  => 10
            ],
            'css' => 'max-width: 80px;'
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_chunks',
            'name'     => __( 'Chunks', 'arya-license-manager' ),
            'desc_tip' => __( 'Split a license into chunks.', 'arya-license-manager' ),
            'type'     => 'number',
            'default'  => '5',
            'custom_attributes' => [
                'step' => 1,
                'min'  => 0
            ],
            'css' => 'max-width: 80px;'
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_prefix',
            'name'     => __( 'Prefix', 'arya-license-manager' ),
            'desc_tip' => __( 'Add the prefix to the generated license.', 'arya-license-manager' ),
            'type'     => 'text',
            'default'  => '',
            'css'      => 'max-width: 80px;'
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_suffix',
            'name'     => __( 'Suffix', 'arya-license-manager' ),
            'desc_tip' => __( 'Add the suffix to the generated license.', 'arya-license-manager' ),
            'type'     => 'text',
            'default'  => '',
            'css'      => 'max-width: 80px;'
        ];

        $license_settings[] = [
            'type' => 'sectionend',
            'id'   => 'license-manager-section-notifications'
        ];

        /**
         * Account page
         */
        $license_settings[] = [
            'id'    => 'arya_license_manager_account',
            'type'  => 'title',
            'title' => __( 'Account page', 'arya-license-manager' )
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_account_pagination',
            'name'     => __( 'Pagination', 'arya-license-manager' ),
            'desc_tip' => __( 'Limits the number of licenses to display on "My Account" page.', 'arya-license-manager' ),
            'type'     => 'number',
            'default'  => '10',
            'custom_attributes' => [
                'step' => 1,
                'min'  => 1
            ],
            'css' => 'max-width: 80px;'
        ];

        $license_settings[] = [
            'type' => 'sectionend',
            'id'   => 'license-manager-section-account'
        ];

        return $license_settings;
    }

    /**
     * Adds endpoints settings.
     *
     * @since 1.0.0
     */
    public function settingsAdvancedLicense( $settings, $current_section )
    {
        if ( ! empty( $current_section ) ) {
            return $settings;
        }

        $index = array_search( 'woocommerce_myaccount_view_order_endpoint', array_column( $settings, 'id' ) );

        $endpoints = [
            [
                'title'    => __( 'Licenses', 'arya-license-manager' ),
                'desc'     => __( 'Endpoint for the "Licenses" page.', 'arya-license-manager' ),
                'type'     => 'text',
                'id'       => 'arya_license_manager_licenses_endpoint',
                'default'  => 'licenses',
                'desc_tip' => true,
            ],
            [
                'title'    => __( 'View license', 'arya-license-manager' ),
                'desc'     => __( 'Endpoint for the "View license" page.', 'arya-license-manager' ),
                'type'     => 'text',
                'id'       => 'arya_license_manager_view-license_endpoint',
                'default'  => 'view-license',
                'desc_tip' => true,
            ]
        ];

        array_splice( $settings, ( $index + 1 ), 0, $endpoints );

        return $settings;
    }

    /**
     * Adds endpoints settings.
     *
     * @since 1.0.0
     */
    public function settingsAdvancedCredentials( $settings, $current_section )
    {
        if ( ! empty( $current_section ) ) {
            return $settings;
        }

        $index = array_search( 'woocommerce_logout_endpoint', array_column( $settings, 'id' ) );

        $endpoints = [
            [
                'title'    => __( 'Security Credentials', 'arya-license-manager' ),
                'desc'     => __( 'Endpoint for the "Licenses" page.', 'arya-license-manager' ),
                'type'     => 'text',
                'id'       => 'arya_license_manager_credentials_endpoint',
                'default'  => 'crendentials',
                'desc_tip' => true,
            ]
        ];

        array_splice( $settings, ( $index - 1 ), 0, $endpoints );

        return $settings;
    }
}
