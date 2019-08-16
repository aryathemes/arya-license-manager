jQuery(function($) {

    /**
     * License (Actions)
     */
    $('.wp-admin.woocommerce_page_wc-licenses #actions .button-actions').click(function(e) {
        e.preventDefault();

        var license_action = $( ".wp-admin.woocommerce_page_wc-licenses #actions .license-action" ).val();

        var data = {
            'action': 'license_action',
            'license': arya_license_manager.license,
            'order': arya_license_manager.order,
            'license_action': license_action,
            'security': arya_license_manager.license_actions_nonce
        };

        $.post(ajaxurl, data, function(response) {

            if (typeof response.data.message !== 'undefined') {
                alert( response.data.message );
                return;
            }

            location.reload();
        });
    });

    /**
     * Associate licenses from dashboard
     */
    $('.wp-admin.woocommerce_page_wc-licenses #activations .activation-add').click(function(e) {
        e.preventDefault();

        var type = $("#license-type"),
            constraint = $("#license-constraint");

        var data = {
            'action': 'activation_add',
            'license': arya_license_manager.license,
            'order': arya_license_manager.order,
            'type': type.val(),
            'constraint': constraint.val(),
            'security': arya_license_manager.activation_add_nonce
        };

        $.post(ajaxurl, data, function(response) {

            if (typeof response.data.message !== 'undefined') {
                alert( response.data.message );
                return;
            }

            location.reload();
        }).fail(function() {
            alert( arya_license_manager.error );
        });
    });

    /**
     * Revoke a activation from dashboard
     */
    $('.wp-admin.woocommerce_page_wc-licenses #activations .activation-revoke').click(function(e) {
        e.preventDefault();

        var constraint = $(this).data("constraint");

        var data = {
            'action': 'activation_revoke',
            'constraint': constraint,
            'license': arya_license_manager.license,
            'order': arya_license_manager.order,
            'security': arya_license_manager.activation_revoke_nonce
        };

        $.post(ajaxurl, data, function(response) {
            location.reload();
        });
    });
});
