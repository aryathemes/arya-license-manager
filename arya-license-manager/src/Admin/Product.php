<?php
/**
 * @package Arya\LicenseManager\Admin
 */

namespace Arya\LicenseManager\Admin;

use Arya\LicenseManager\Admin\Download;
use Arya\LicenseManager\License\Order;
use Arya\LicenseManager\License\Orders;
use Arya\LicenseManager\License\Traits\LicenseTrait;

/**
 * Product class.
 *
 * @since 1.0.0
 */
class Product
{
    use LicenseTrait;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Product
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'woocommerce_product_data_tabs', [ $this, 'tabs' ], 10, 1 );

        add_action( 'woocommerce_product_data_panels', [ $this, 'panels' ], 10, 1 );

        add_action( 'save_post_product', [ $this, 'save' ], 10, 2 );

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Product
     */
    public static function newInstance(): Product
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Product;
        }

        return self::$instance;
    }

    /**
     * Adds "Software license" tab.
     *
     * @since 1.0.0
     */
    public function tabs( $tabs )
    {
        $tabs[ 'license' ] = [
            'label'    => esc_html__( 'Software License', 'arya-license-manager' ),
            'target'   => 'license_product_options',
            'class'    => [ 'show_if_license', 'show_if_licensable' ],
            'priority' => 15
        ];

        return $tabs;
    }

    /**
     * Adds software license fields.
     *
     * @since 1.0.0
     */
    public function panels()
    {
        $product_id = get_the_ID();

        wp_nonce_field( 'arya-license-manager-license-save', "arya-license-manager-license-$product_id-nonce" ); ?>

        <div id="license_product_options" class="panel woocommerce_options_panel">

            <div class="options_group">
            <?php

            $expire_value = get_post_meta( $product_id, '_arya_license_expire_value', true );

            woocommerce_wp_text_input( [
                'id'    => '_arya_license_expire_value',
                'label' => esc_html__( 'Expiry time', 'arya-license-manager' ),
                'value' => 0 <> intval( $expire_value ) ? $expire_value : 1,
                'type'  => 'number',
                'custom_attributes' => [
                    'step' => 1,
                    'min'  => 1
                ]
            ] );

            woocommerce_wp_select( [
                'id'      => '_arya_license_expire_interval',
                'label'   => esc_html__( 'Expire interval', 'arya-license-manager' ),
                'value'   => get_post_meta( $product_id, '_arya_license_expire_interval', true ),
                'options' => [
                    'years'  => esc_html__( 'Years',  'arya-license-manager' ),
                    'months' => esc_html__( 'Months', 'arya-license-manager' ),
                    'days'   => esc_html__( 'Days',   'arya-license-manager' )
                ]
            ] );

            woocommerce_wp_select( [
                'id'      => '_arya_license_license_type',
                'label'   => esc_html__( 'License type', 'arya-license-manager' ),
                'value'   => get_post_meta( $product_id, '_arya_license_license_type', true ),
                'options' => [
                    'on_demand_software' => esc_html__( 'On-demand software', 'arya-license-manager' ),
                    'perpetual'          => esc_html__( 'Perpetual',          'arya-license-manager' )
                ]
            ] );
            ?>
            </div>

            <div class="options_group">
            <?php
            woocommerce_wp_text_input( [
                'id'                => '_arya_license_activation_limit',
                'label'             => esc_html__( 'Activation limit', 'arya-license-manager' ),
                'description'       => esc_html__( 'Leave blank for unlimited activations.', 'arya-license-manager' ),
                'value'             => get_post_meta( $product_id, '_arya_license_activation_limit', true ),
                'type'              => 'number',
                'placeholder'       => esc_html__( 'Unlimited', 'arya-license-manager' ),
                'custom_attributes' => [
                    'step' => 1,
                    'min'  => 0
                ]
            ] );
            ?>
            </div>

        </div>

        <?php
    }

    /**
     * Saves product meta data.
     *
     * @since 1.0.0
     */
    public function save( $product_id, $object )
    {
        /* Check if the nonce is set */
        $nonce = sanitize_text_field( $_POST["arya-license-manager-license-$product_id-nonce"] ?? '' );

        if ( empty( $nonce ) ) {
            return;
        }

        /* Verify that the nonce is valid */
        if ( ! wp_verify_nonce( $nonce, 'arya-license-manager-license-save' ) ) {
            return;
        }

        /* Don't update if running an autosave */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        /* Save */
        $is_licensable = isset( $_POST['_licensable'] ) ? 'yes' : 'no';

        update_post_meta( $product_id, '_licensable', $is_licensable );

        $metadata = [
            '_arya_license_expire_value',
            '_arya_license_expire_interval',
            '_arya_license_license_type',
            '_arya_license_activation_limit'
        ];

        foreach ( $metadata as $meta ) {

            $value = get_post_meta( $product_id, $meta, true );

            $new_value = sanitize_text_field( $_POST[ $meta ] ?? '' );

            if ( ! empty( $new_value ) && $new_value !== $value ) {
                update_post_meta( $product_id, $meta, $new_value );
            } elseif ( empty( $new_value ) && ! empty( $value ) ) {
                delete_post_meta( $product_id, $meta );
            }
        }
    }

    /**
     * Metaboxes.
     *
     * @since 1.0.0
     */
    public function enqueue()
    {
        if ( 'product' !== get_post_type() ) {
            return;
        }

        wp_enqueue_script( 'arya-license-manager-admin-product' );
    }
}
