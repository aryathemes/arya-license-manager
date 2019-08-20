jQuery(function($) {

    /**
     * Security Credentials
     */
    $('.woocommerce-account .credentials-generate').click(function(e) {
        e.preventDefault();

        var customer_id = $(this).data("customer");

        var data = {
            'action': 'credentials_create',
            'customer_id': customer_id,
            'security': arya_license_manager_credentials.credentials_create_nonce
        };

        $.post(arya_license_manager_credentials.ajaxurl, data, function(response) {
            location.reload();

            var a = document.createElement("a");
            a.style.display = "none";
            document.body.appendChild(a);

            a.href = window.URL.createObjectURL(
                new Blob([response], {type : 'application/json'})
            );

            a.setAttribute("download", 'credentials.json');
            a.click();

            window.URL.revokeObjectURL(a.href);
            document.body.removeChild(a);
        });
    });

    $('.woocommerce-account .credentials-revoke').click(function(e) {
        e.preventDefault();

        var customer_id = $(this).data("customer");

        var data = {
            'action': 'credentials_revoke',
            'customer_id': customer_id,
            'security': arya_license_manager_credentials.credentials_revoke_nonce
        };

        $.post(arya_license_manager_credentials.ajaxurl, data, function(response) {
            location.reload();
        });
    });
});
