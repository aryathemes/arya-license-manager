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
        $sections['license-manager'] = esc_html__( 'Licenses', 'arya-license-manager' );

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
            'name' => esc_html__( 'Arya License Manager', 'arya-license-manager' ),
            'desc' => esc_html__( 'The following options are used to configure Arya License Manager', 'arya-license-manager' )
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_length',
            'name'     => esc_html__( 'Length', 'arya-license-manager' ),
            'desc'     => esc_html__( 'Number of characters to generate a new license.', 'arya-license-manager' ),
            'desc_tip' => esc_html__( 'Licenses shall have a minimum length of 10 characters.', 'arya-license-manager' ),
            'type'     => 'number',
            'default'  => '25',
            'custom_attributes' => [
                'step' => 1,
                'min'  => 10
            ],
            'css' => 'max-width: 70px;'
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_chunks',
            'name'     => esc_html__( 'Chunks', 'arya-license-manager' ),
            'desc'     => esc_html__( 'Split a license into chunks.', 'arya-license-manager' ),
            'type'     => 'number',
            'default'  => '5',
            'custom_attributes' => [
                'step' => 1,
                'min'  => 0
            ],
            'css' => 'max-width: 70px;'
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
            'title' => esc_html__( 'Account page', 'arya-license-manager' )
        ];

        $license_settings[] = [
            'id'       => 'arya_license_manager_account_pagination',
            'name'     => esc_html__( 'Pagination', 'arya-license-manager' ),
            'desc'     => esc_html__( 'Number of licenses per page.', 'arya-license-manager' ),
            'desc_tip' => esc_html__( 'Limits the number of licenses to display on "My Account" page.', 'arya-license-manager' ),
            'type'     => 'number',
            'default'  => '10',
            'custom_attributes' => [
                'step' => 1,
                'min'  => 1
            ],
            'css' => 'max-width: 70px;'
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
                'title'    => esc_html__( 'Licenses', 'arya-license-manager' ),
                'desc'     => esc_html__( 'Endpoint for the "Licenses" page.', 'arya-license-manager' ),
                'type'     => 'text',
                'id'       => 'arya_license_manager_licenses_endpoint',
                'default'  => 'licenses',
                'desc_tip' => true,
            ],
            [
                'title'    => esc_html__( 'View license', 'arya-license-manager' ),
                'desc'     => esc_html__( 'Endpoint for the "View license" page.', 'arya-license-manager' ),
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
                'title'    => esc_html__( 'Security Credentials', 'arya-license-manager' ),
                'desc'     => esc_html__( 'Endpoint for the "Licenses" page.', 'arya-license-manager' ),
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
