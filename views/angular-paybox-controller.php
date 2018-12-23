<?php
global $Options;

if (@$Options[ store_prefix() . 'nexo_enable_globalonepay' ] != 'no'):
?>
/**
 * Load GlobalOnePay Payment
**/
$scope.openGlobalOnePayPayment	=	function(){

	if( parseFloat( $scope.paidAmount ) == isNaN() || parseFloat( $scope.paidAmount ) <= 0 || typeof $scope.paidAmount == 'undefined' ) {
    	NexoAPI.Notify().warning( '<?php echo _s( 'Attention', 'nexo-globalonepay-gateway' );?>', '<?php echo _s( 'Le montant spécifié est incorrecte.', 'nexo-globalonepay-gateway' );?>' )
        return false;
    }

    // NB: JPY should be integer! See http://globalonepay.info/documents/GlobalOnePay_XML_API_Guide.pdf
	var	CartToPayLong		=	NexoAPI.Format( $scope.paidAmount, '0.00' );

    $scope.card = new Object;
    $scope.card.number = '';
    $scope.card.expire = '';
    $scope.card.cvv = '';
    $scope.card.holder = '';
    $scope.card.amount = CartToPayLong;
    $scope.card.currency = '<?php echo @$Options[ store_prefix() . 'nexo_currency_iso' ];?>';

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
                var validNumber = cardValidator.number($scope.card.number);
                $scope.card.type = cardValidator.supportedBrand(validNumber.card.type);
                if (!validNumber.isValid) {
                    NexoAPI.Notify().error( '<?php echo _s('Verification Error', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Check Card Number', 'nexo-globalonepay-gateway');?>');
                    return false;
                }
                if ($scope.card.type == "") {
                    NexoAPI.Notify().error( '<?php echo _s('Verification Error', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Unsupported Card Brand', 'nexo-globalonepay-gateway');?>');
                    return false;
                }
                console.log($scope.card);
                //return v2Checkout.createGlobalOnePayment($scope.card);
            }
        }
    });

    $('.globalonepaywrapper').html($compile($('.globalonepaywrapper').html())($scope));

};

// Register events when payment is proceeded

NexoAPI.events.addAction( 'globalonepay_charged', function( token ) {

	$.ajax( '<?php echo site_url(array( 'rest', 'nexo', 'globalonepay', store_get_param( '?' ) ) );?>', {
        beforeSend : 	function(){

            __windowSplash.showSplash();

            NexoAPI.Notify().success( '<?php echo _s('Veuillez patienter', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Paiement en cours...', 'nexo-globalonepay-gateway');?>' );
        },
        type		:	'POST',
        dataType	:	"json",
        data		:	$scope.globalOnePayDetails,
        success		: 	function( data ) {
            if( data.status == 'payment_success' ) {

                $scope.addPayment( 'globalonepay', $scope.paidAmount );
                $scope.refreshBox();

                __windowSplash.hideSplash();
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
                var message		=	'N/A';
            }

            __windowSplash.hideSplash();

            NexoAPI.Notify().warning( '<?php echo _s('Une erreur s\'est produite', 'nexo-globalonepay-gateway');?>', '<?php echo _s('Le paiement n\'a pu être effectuée. Une erreur s\'est produite durant la facturation de la carte de crédit.<br>Le serveur à retourner cette erreur : ', 'nexo-globalonepay-gateway');?>' + message );
        }
    });
});
<?php endif; ?>
