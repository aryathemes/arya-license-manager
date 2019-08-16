jQuery(function($) {

    /**
     * Associate licenses
     */
    $('.woocommerce-account .arya-license-manager .associate-button').click(function(e) {
        e.preventDefault();

        var license = $(this).data("license");

        var order = $(this).data("order");

        var type = $("#type"),
            constraint = $("#constraint");

        var data = {
            'action': 'activation_add',
            'license': license,
            'order': order,
            'type': type.val(),
            'constraint': constraint.val(),
            'security': arya_license_manager_activation.activation_add_nonce
        };

        $.post(arya_license_manager_activation.ajaxurl, data, function(response) {
            location.reload();
        }).fail(function() {
            alert( arya_license_manager_activation.error );
        });
    });

    /**
     * Revoke
     */
    $('.woocommerce-account .arya-license-manager .activation-revoke').click(function(e) {

        var constraint = $(this).data("constraint");

        var license = $(this).data("license");

        var order = $(this).data("order");

        var data = {
            'action': 'activation_revoke',
            'constraint': constraint,
            'license': license,
            'order': order,
            'security': arya_license_manager_activation.activation_revoke_nonce
        };

        $.post(arya_license_manager_activation.ajaxurl, data, function(response) {
            location.reload();
        });
    });

    /**
     * Clipboard
     */
    $('.woocommerce-account .clipboard').tooltip({
        trigger: 'click',
        placement: 'top'
    });

    function setTooltip(message) {
        $('.woocommerce-account .clipboard').tooltip('hide')
            .attr('data-original-title', message)
            .tooltip('show');
    }

    function hideTooltip() {
        setTimeout(function() {
            $('.woocommerce-account .clipboard').tooltip('hide');
        }, 1000);
    }

    $('.woocommerce-account .clipboard').click(function(e) {
        var clipboard = new ClipboardJS('.woocommerce-account .clipboard');

        clipboard.on('success', function(e) {
            setTooltip('Copied!');
            hideTooltip();
        });
    });
});
