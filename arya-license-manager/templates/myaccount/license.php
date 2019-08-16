<?php
/**
 * @package Arya\LicenseManager\Templates
 */

defined( 'ABSPATH' ) || exit; ?>

<?php if ( ! isset( $license ) ) : ?>

    <?php wc_print_notice( esc_html__( 'Invalid license.', 'arya-license-manager' ), 'error' ); ?>

<?php elseif ( ! in_array( $license->getOrder()->get_status(), wc_get_is_paid_statuses() ) ) : ?>

    <?php wc_print_notice( esc_html__( 'License not available.', 'arya-license-manager' ), 'notice' ); ?>

<?php else : ?>

<div class="arya-license-manager">

    <?php do_action( 'arya_license_manager_template_license', $license ); ?>

    <h3><?php esc_html_e( 'Details', 'arya-license-manager' ); ?></h3>

    <?php do_action( 'arya_license_manager_template_license_details', $license ); ?>

    <h3><?php esc_html_e( 'Activations', 'arya-license-manager' ); ?></h3>

    <?php do_action( 'arya_license_manager_template_license_activations', $license ); ?>

    <?php do_action( 'arya_license_manager_template_license_form', $license ); ?>

</div>

<?php endif; ?>
