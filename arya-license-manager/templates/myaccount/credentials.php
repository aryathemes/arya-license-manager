<?php
/**
 * @package Arya\LicenseManager\Templates
 */

defined( 'ABSPATH' ) || exit; ?>

<?php if ( empty( $credentials ) ) : ?>

    <div class="woocommerce-info">
        <a class="woocommerce-Button button credentials-generate" data-customer="<?php echo $customer_id; ?>" href="#">
            <?php esc_html_e( 'Generate and Download', 'arya-license-manager' ); ?>
        </a>
        <?php esc_html_e( 'There are no security credentials.', 'arya-license-manager' ); ?>
    </div>

<?php else : ?>

    <div class="woocommerce-info">
        <a class="woocommerce-Button button credentials-revoke" data-customer="<?php echo $customer_id; ?>" href="#">
            <?php esc_html_e( 'Revoke', 'arya-license-manager' ); ?>
        </a>
        <?php esc_html_e( 'You have a pair of access security keys.', 'arya-license-manager' ); ?>
    </div>

<?php endif; ?>

<p><?php esc_html_e( 'Security credentials are used as an authentication mechanism to activate and deactivate licenses remotely.', 'arya-license-manager' ); ?></p>

<p><?php esc_html_e( 'For your protection, download and store your credentials securely and do not share them.', 'arya-license-manager' ); ?></p>
