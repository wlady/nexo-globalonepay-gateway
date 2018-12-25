<?php
global $Options;

if (@$Options[ store_prefix() . 'nexo_enable_globalonepay' ] != 'no'):
?>
/**
 * Load GlobalOnePay Payment
**/
// if not empty then current order is already created
$scope.openGlobalOnePayPayment	=	function(){

    $scope.orderId = 0;
	if( parseFloat( $scope.paidAmount ) == isNaN() || parseFloat( $scope.paidAmount ) <= 0 || typeof $scope.paidAmount == 'undefined' ) {
    	NexoAPI.Notify().warning( '<?php echo _s( 'Attention', 'nexo-globalonepay-gateway' );?>', '<?php echo _s( 'Le montant spécifié est incorrecte.', 'nexo-globalonepay-gateway' );?>' )
        return false;
    }

    // NB: JPY should be integer! See http://globalonepay.info/documents/GlobalOnePay_XML_API_Guide.pdf
	var	CartToPayLong		=	NexoAPI.Format( $scope.paidAmount, '0.00' );

    $scope.card = new Object;
    $scope.card.type = '';
    $scope.card.number = '';
    $scope.card.expire = '';
    $scope.card.cvv = '';
    $scope.card.holder = '';
    $scope.card.amount = CartToPayLong;
    $scope.card.currency = '<?php echo @$Options[ store_prefix() . 'nexo_currency_iso' ];?>';
    $scope.card.order = '';

    // If order has at least one item
    NexoAPI.Bootbox().confirm({
        message: '<div class="globalonepaywrapper"><globalonepay-form-content/></div>',
        title: '<?php echo _s('GlobalOne Payment', 'nexo');?>',
        buttons: {
            confirm: {
                label: '<?php echo _s('Pay', 'nexo');?>',
                className: 'btn-info'
            },
            cancel: {
                label: '<?php echo _s('Cancel', 'nexo');?>',
                className: 'btn-default'
            }
        },
        callback: function (action) {
            if (action) {

                NexoAPI.events.addFilter( 'process_data', function( ) {
                    if ($scope.orderId > 0) {
                        v2Checkout.CartType = 'nexo_order_devis';
                        return {
                                url			:	'/rest/nexo/order/<?php echo User::id();?>/' + $scope.orderId + '?store_id=<?php echo get_store_id();?>',
                                type		:	'PUT'
                            };
                    } else {
                        return {
                            url			:	v2Checkout.ProcessURL,
                            type		:	v2Checkout.ProcessType
                        };
                    }
                });

                $scope.card.number = $scope.card.number.replace(/[^0-9]/g, "");
                var validNumber = cardValidator.number($scope.card.number);
                //if (!validNumber.isValid) {
                if (validNumber.card == null) {
                    NexoAPI.Notify().error( '<?php echo _s('Verification Error', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Check Card Number', 'nexo-globalonepay-gateway');?>');
                    return false;
                }
                $scope.card.type = cardValidator.supportedBrand(validNumber.card.type);
                if ($scope.card.type == "") {
                    NexoAPI.Notify().error( '<?php echo _s('Verification Error', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Unsupported Card Brand', 'nexo-globalonepay-gateway');?>');
                    return false;
                }
                var expDate = cardValidator.expirationDate($scope.card.expire);
                if ($scope.card.expire == "" || !expDate.isValid) {
                    NexoAPI.Notify().error( '<?php echo _s('Verification Error', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Check Expire Date', 'nexo-globalonepay-gateway');?>');
                    return false;
                }
                $scope.card.cvv = $scope.card.cvv.replace(/[^0-9]/g, "")
                if ($scope.card.cvv == "") {
                    NexoAPI.Notify().error( '<?php echo _s('Verification Error', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Check CVV', 'nexo-globalonepay-gateway');?>');
                    return false;
                }
                if ($scope.card.holder == "") {
                    NexoAPI.Notify().error( '<?php echo _s('Verification Error', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Check Holder Name', 'nexo-globalonepay-gateway');?>');
                    return false;
                }
                if ($scope.orderId == 0 ) {
                    v2Checkout.saveOrder();
                } else {
                    $scope.card.order = $scope.orderId;
                    NexoAPI.events.doAction( 'globalonepay_charged', $scope.card );
                }
            }
        }
    });

    $('.globalonepaywrapper').html($compile($('.globalonepaywrapper').html())($scope));
    $('.card-number').inputmask('999999999999999[9999]');
    $('.card-expire').inputmask({alias: 'mm/yyyy', inputFormat: 'mm/yy'});
    $('.card-cvv').inputmask('999[9]');

};

// Register events
NexoAPI.events.addFilter( 'check_payment_mean', function( object ) {
    object[0] = object[1] == "globalonepay";

    return object;
});

NexoAPI.events.addFilter( 'payment_mean_checked', function( object ) {
    if (object[1] == 'globalonepay') {
        object[0].PAYMENT_TYPE = object[1];
        object[0].SOMME_PERCU = NexoAPI.ParseFloat( v2Checkout.CartToPay );
    }

    return object;
});

NexoAPI.events.addFilter( 'after_order_save', function( object ) {
    if (object[1] == 'globalonepay') {
        $scope.orderId = object[0].order_id;
        $scope.card.order = object[0].order_code;
        NexoAPI.events.doAction( 'globalonepay_charged', $scope.card );
    }
    return object;
});

NexoAPI.events.addFilter( 'nexo_payments_types_object', function( object ) {

    object          =       _.extend( object, _.object( [ 'globalonepay' ], [{
        text            :       '<?php echo _s( 'GLobalOnePay', 'nexo-payments-gateway' );?>',
        active          :       false,
        isCustom        :       true
    }] ) );

    return object;

});

NexoAPI.events.addAction( 'close_paybox', function( ) {
    // reset current order ID
    $scope.orderId = 0;
});

NexoAPI.events.addAction( 'globalonepay_charged', function( data ) {

	$.ajax( '<?php echo site_url(array( 'rest', 'nexo', 'globalonepay', store_get_param( '?' ) ) );?>', {
        beforeSend : 	function(){

            v2Checkout.paymentWindow.showSplash();

            NexoAPI.Notify().success( '<?php echo _s('Veuillez patienter', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Paiement en cours...', 'nexo-globalonepay-gateway');?>' );
        },
        type		:	'POST',
        dataType	:	"json",
        data		:	data,
        success		: 	function( data ) {
            if( data.status == 'payment_success' ) {

                $scope.addPayment( 'globalonepay', $scope.paidAmount );
                $scope.refreshBox();

                v2Checkout.paymentWindow.hideSplash();
				NexoAPI.Notify().success( '<?php echo _s('Paiement effectué', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Le paiement a été effectué.', 'nexo-globalonepay-gateway');?>' );
            }
        },
        error		:	function( data ){
            data			=	$.parseJSON( data.responseText );

            if( typeof data.error != 'undefined' ) {
                var message		=	data.error.message;
            } else if( typeof data.httpBody != 'undefined' ) {
                var message		=	data.jsonBody.error.message;
            } else {
                var message		=	data;
            }

            v2Checkout.paymentWindow.hideSplash();
            // GLobalOnePay cannot process the same order ID
            //$scope.orderId = 0;

            NexoAPI.Notify().error( '<?php echo _s('Une erreur s\'est produite', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Le paiement n\'a pu être effectuée. Une erreur s\'est produite durant la facturation de la carte de crédit.<br>Le serveur à retourner cette erreur : <br><strong>', 'nexo-globalonepay-gateway');?>' + message );
        }
    });
});
<?php endif; ?>
