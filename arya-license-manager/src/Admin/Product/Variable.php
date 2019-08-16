<?php
/**
 * @package Arya\LicenseManager\Admin\Product
 */

namespace Arya\LicenseManager\Admin\Product;

/**
 * Variable class.
 *
 * @since 1.0.0
 */
class Variable
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Variable
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'woocommerce_variation_options', [ $this, 'variation' ], 10, 3 );

        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'attributes' ], 10, 3 );

        add_action( 'woocommerce_save_product_variation', [ $this, 'save' ], 10, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Variable
     */
    public static function newInstance(): Variable
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Variable;
        }

        return self::$instance;
    }

    /**
     * Adds 'License' as option.
     *
     * @since 1.0.0
     */
    public function variation( $loop, $variation_data, $variation )
    {
        $name = sprintf( 'variable_is_licensable[%s]', esc_attr( $loop ) );

        $checked = get_post_meta( $variation->ID, '_licensable', true ); ?>

        <label class="tips" data-tip="<?php esc_html_e( 'Enable this option if the product is associated to a license.', 'arya-license-manager' ); ?>">
            <?php esc_html_e( 'License', 'arya-license-manager' ); ?>:
            <input type="checkbox" class="checkbox variable_is_licensable" name="<?php echo( $name ); ?>" <?php checked( $checked, 'yes' ); ?> />
        </label>

        <?php
    }

    /**
     * License attributes.
     *
     * @since 1.0.0
     */
    public function attributes( $loop, $variation_data, $variation )
    {
        $variation_id = $variation->ID;

        wp_nonce_field( 'arya-license-manager-variation-save', "arya-license-manager-variation-$variation_id-nonce" ); ?>

        <div class="show_if_variation_licensable" style="display: none;">

            <?php
            $expire_value = get_post_meta( $variation_id, '_arya_license_expire_value', true );

            woocommerce_wp_text_input( [
                'id'    => "_arya_license_expire_value[$loop]",
                'label' => esc_html__( 'Expiry time', 'arya-license-manager' ),
                'value' => 0 <> intval( $expire_value ) ? $expire_value : 1,
                'wrapper_class' => 'form-row form-row-first'
            ] );

            woocommerce_wp_select( [
                'id'      => "_arya_license_expire_interval[$loop]",
                'label'   => esc_html__( 'Expire interval', 'arya-license-manager' ),
                'value'   => get_post_meta( $variation_id, '_arya_license_expire_interval', true ),
                'options' => [
                    'years'  => esc_html__( 'Years',  'arya-license-manager' ),
                    'months' => esc_html__( 'Months', 'arya-license-manager' ),
                    'days'   => esc_html__( 'Days',   'arya-license-manager' )
                ],
                'wrapper_class' => 'form-row form-row-last'
            ] );

            woocommerce_wp_text_input( [
                'id'                => "_arya_license_activation_limit[$loop]",
                'label'             => esc_html__( 'Activation limit', 'arya-license-manager' ),
                'desc_tip'          => true,
                'description'       => esc_html__( 'Leave blank for unlimited activations.', 'arya-license-manager' ),
                'value'             => get_post_meta( $variation_id, '_arya_license_activation_limit', true ),
                'type'              => 'number',
                'placeholder'       => esc_html__( 'Unlimited', 'arya-license-manager' ),
                'custom_attributes' => [ 'step' => 1, 'min' => 0 ],
                'wrapper_class'     => 'form-row form-row-first'
            ] );

            woocommerce_wp_select( [
                'id'      => "_arya_license_license_type[$loop]",
                'label'   => esc_html__( 'License type', 'arya-license-manager' ),
                'value'   => get_post_meta( $variation_id, '_arya_license_license_type', true ),
                'options' => [
                    'on_demand_software' => esc_html__( 'On-demand software', 'arya-license-manager' ),
                    'perpetual'          => esc_html__( 'Perpetual',          'arya-license-manager' )
                ],
                'wrapper_class' => 'form-row form-row-last'
            ] );
            ?>

        </div>

        <?php
    }

    /**
     * Saves the license attributes.
     *
     * @since 1.0.0
     */
    public function save( $variation_id, $loop )
    {
        /* Check if the nonce is set */
        $nonce = $_POST["arya-license-manager-variation-$variation_id-nonce"] ?? '';

        if ( empty( $nonce ) ) {
            return;
        }

        /* Verify that the nonce is valid */
        if ( ! wp_verify_nonce( $nonce, 'arya-license-manager-variation-save' ) ) {
            return;
        }

        /* Don't update if running an autosave */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        /* Save */
        $is_licensable = isset( $_POST['variable_is_licensable'][$loop] ) ? 'yes' : 'no';

        update_post_meta( $variation_id, '_licensable', $is_licensable );

        $metadata = [
            '_arya_license_expire_value',
            '_arya_license_expire_interval',
            '_arya_license_is_perpetual',
            '_arya_license_activation_limit',
            '_arya_license_license_type'
        ];

        foreach ( $metadata as $meta ) {

            $value = get_post_meta( $variation_id, $meta, true );

            $new_value = $_POST[$meta][$loop] ?? '';

            if ( ! empty( $new_value ) && $new_value !== $value ) {
                update_post_meta( $variation_id, $meta, $new_value );
            } elseif ( empty( $new_value ) && ! empty( $value ) ) {
                delete_post_meta( $variation_id, $meta );
            }
        }
    }
}
