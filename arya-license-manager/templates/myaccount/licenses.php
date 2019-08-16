<?php
/**
 * @package Arya\LicenseManager\Templates
 */

defined( 'ABSPATH' ) || exit; ?>

<?php if ( empty( $licenses ) ) : ?>

    <div class="woocommerce-info">
        <a class="woocommerce-Button button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
            <?php esc_html_e( 'Go shop', 'arya-license-manager' ); ?>
        </a>
        <?php esc_html_e( 'There are no active licenses.', 'arya-license-manager' ); ?>
    </div>

<?php else : ?>

<div class="arya-license-manager">

    <?php do_action_ref_array( 'arya_license_manager_template_licenses', [ &$licenses ] ); ?>

</div>

<?php endif; ?>
