<?php
/**
 * @package Arya\LicenseManager\Admin\Emails
 */

namespace Arya\LicenseManager\Admin\Emails;

/**
 * Expired class.
 *
 * @since 1.0.0
 */
class Expired extends \WC_Email
{
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        /* Settings */
        $this->id = 'license_expired';
        $this->title = esc_html__( 'Expired license', 'arya-license-manager' );
        $this->description = esc_html__( 'Expired license emails are sent when a license has been deactivated by manager.', 'arya-license-manager' );
        $this->customer_email = true;

        /* Template paths and placeholders */
        $this->template_html  = 'emails/customer-expired-license.php';
        $this->template_plain = 'emails/plain/customer-expired-license.php';
        $this->template_base  = ARYA_LICENSE_MANAGER_TEMPLATES;

        $this->placeholders = [
            '{site_title}' => $this->get_blogname(),
            '{license}'    => ''
        ];

        add_action( 'arya_license_manager_expired_notification', [ $this, 'trigger' ], 10, 1 );

        /* Parent constructor */
        parent::__construct();

        /* Other settings */
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function get_default_subject()
    {
        return esc_html__( '[{site_title}]: License {license} has expired', 'arya-license-manager' );
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function get_default_heading()
    {
        return esc_html__( 'License expired: <code>{license}</code>', 'arya-license-manager' );
    }

    /**
     *  Trigger the sending of this email.
     *
     * @since 1.0.0
     */
    public function trigger( $license )
    {
        $this->setup_locale();

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function get_content_html()
    {
        return wc_get_template_html( $this->template_html, [
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => true,
            'plain_text'    => false,
        ], '', $this->template_base );
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function get_content_plain()
    {
        return wc_get_template_html( $this->template_plain, [
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => true,
            'plain_text'    => true,
        ], '', $this->template_base );
    }

    /**
     * {@inheritdoc}
     *
     * @since 1.0.0
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title'   => esc_html__( 'Enable/Disable', 'arya-license-manager' ),
                'type'    => 'checkbox',
                'label'   => esc_html__( 'Enable this email notification', 'arya-license-manager' ),
                'default' => 'yes'
            ],
            'recipient' => [
                'title'       => esc_html__( 'Recipient(s)', 'arya-license-manager' ),
                'type'        => 'text', /* translators: Defaults to %s: Recipients for this email */
                'description' => sprintf( esc_html__( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'arya-license-manager' ), '<code>' . get_option( 'admin_email' ) . '</code>' ),
                'desc_tip'    => true,
                'placeholder' => '',
                'default'     => ''
            ],
            'subject' => [
                'title'       => esc_html__( 'Subject', 'arya-license-manager' ),
                'type'        => 'text', /* translators: Available placeholders: %s: Available placeholders */
                'description' => sprintf( esc_html__( 'Available placeholders: %s', 'arya-license-manager' ), '<code>{site_title}, {license}</code>' ),
                'desc_tip'    => true,
                'placeholder' => $this->get_default_subject(),
                'default'     => ''
            ],
            'heading' => [
                'title'       => esc_html__( 'Email heading', 'arya-license-manager' ),
                'type'        => 'text',
                'desc_tip'    => true, /* translators: Available placeholders: %s: Available placeholders */
                'description' => sprintf( esc_html__( 'Available placeholders: %s', 'arya-license-manager' ), '<code>{site_title}, {license}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => ''
            ],
            'email_type' => [
                'title'       => esc_html__( 'Email type', 'arya-license-manager' ),
                'type'        => 'select',
                'description' => sprintf( esc_html__( 'Choose which format of email to send.', 'arya-license-manager' ), '<code>{site_title}, {license}</code>' ),
                'desc_tip'    => true,
                'class'       => 'email_type wc-enhanced-select',
                'default'     => 'html',
                'options'     => $this->get_email_type_options()
            ]
        ];
    }
}
