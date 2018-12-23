<?php
global $Options;
?>
<script>

<?php
if (@$Options[ store_prefix() . 'nexo_enable_globalonepay' ] != 'no'):
?>

NexoAPI.events.addFilter( 'nexo_payments_types_object', function( object ) {

	object		=	_.extend( object, _.object( [ 'globalonepay' ], [{
		text		:	'<?php echo _s( 'GlobalOne', 'nexo-globalonepay-gateway' );?>',
		active		:	false,
		isCustom	:	true
	}] ) );

	return object;

});

<?php
endif;
?>

var	previous_text	=	null;

NexoAPI.events.addAction( 'pos_select_payment', function( data ) {

	if( previous_text == null ) {
		previous_text	=	data[0].defaultAddPaymentText;
	}

	if( data[1] == 'globalonepay' ) {
		// Disable payment for GlobalOnePay
		data[0].defaultAddPaymentText	=	'<?php echo _s( 'Facturer une carte', 'nexo-globalonepay-gateway' );?>';
	} else {
		data[0].defaultAddPaymentText	=	previous_text;
	}

});

// Disable payment edition for GlobalOnePay
NexoAPI.events.addFilter( 'allow_payment_edition', function( data ) {
	if( data[1] == 'globalonepay' ) {
		NexoAPI.Notify().warning( '<?php echo _s( 'Attention', 'nexo-globalonepay-gateway' );?>', '<?php echo _s( 'Vous ne pouvez pas modifier un paiement déjà effectué, car une carte a déjà été débitée.', 'nexo-globalonepay-gateway' );?>' );

		return [ false, data[1] ];
	}

	return data;
});
</script>
