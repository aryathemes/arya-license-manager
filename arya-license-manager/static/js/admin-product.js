jQuery(function($) {

    var product_license = {

        init: function() {

            $( 'select#product-type' ).change( function() {
                show_and_hide_panels();
            }).change();

            $( 'input#_licensable' ).change( function() {
                show_and_hide_panels();
            }).change();

            function show_and_hide_panels() {

                if( $( 'input#_licensable' ).is(":checked") || $( 'select#product-type'  ).val() == 'license' ) {
                    $( '.show_if_licensable' ).show();
                } else {
                    $( '.show_if_licensable' ).hide();
                }
            }

            /* Pricing */
            $('.options_group.pricing').addClass('show_if_license').show();

            /* Inventory */
            $('.inventory_options').addClass('show_if_license').show();
            $('#inventory_product_data ._manage_stock_field').addClass('show_if_license').show();
            $('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_license').show();
            $('#inventory_product_data ._sold_individually_field').addClass('show_if_license').show();
        }
    };

    var product_variations_license = {

        init: function() {
            $( '#variable_product_options' ).on( 'change', 'input.variable_is_licensable', function() {
                $( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_licensable' ).hide();

                if ( $( this ).is( ':checked' ) ) {
                    $( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_licensable' ).show();
                }
            } );

            $( 'input.variable_is_licensable' ).change();

            $( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', function( event, needsUpdate ) {
                needsUpdate = needsUpdate || false;

                var wrapper = $( '#woocommerce-product-data' );

                if ( ! needsUpdate ) {
                    $( 'input.variable_is_licensable', wrapper ).change();
                }
            } );
        }
    };

    product_variations_license.init();
    product_license.init();
});
