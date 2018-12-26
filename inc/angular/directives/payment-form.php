<script>
/**
 * GlobalOnePay Form Content
 * @type 	:	directive
**/

tendooApp.directive( 'globalonepayFormContent', function(){

	HTML.body.add( 'angular-cache' );

	HTML.query( 'angular-cache' )
	    .add( 'div.form' )
	    .add( 'div.col-lg-12.input-group.group-content.row1' );

	HTML.query( '.group-content.row1' )
	    .add( 'span.input-group-addon' )
        .textContent	=	'<?php echo _s( 'Card Number', 'nexo' );?>';
    HTML.query( '.group-content.row1' )
        .add( 'input.form-control.card-number' )
        .each( 'ng-model', 'card.number' )
        .each( 'placeholder', '<?php echo _s( 'Card Number', 'nexo' );?>' );

	HTML.query( 'div.form')
	    .add( 'div.col-lg-12.input-group.group-content.row2' );

    HTML.query( '.group-content.row2')
        .add( 'span.input-group-addon' )
        .textContent	=	'<?php echo _s( 'Card Expire', 'nexo' );?>';

    HTML.query( '.group-content.row2')
        .add( 'input.form-control.card-expire' )
        .each( 'ng-model', 'card.expire' )
        .each( 'placeholder', '<?php echo _s( 'MM/YY', 'nexo' );?>' );

    HTML.query( 'div.form')
        .add( 'div.col-lg-12.input-group.group-content.row3' );

    HTML.query( '.group-content.row3')
        .add( 'span.input-group-addon' )
        .textContent	=	'<?php echo _s( 'CVV', 'nexo' );?>';

    HTML.query( '.group-content.row3')
        .add( 'input.form-control.card-cvv' )
        .each( 'ng-model', 'card.cvv' )
        .each( 'placeholder', '<?php echo _s( 'CVV', 'nexo' );?>' );

    HTML.query( 'div.form')
        .add( 'div.col-lg-12.input-group.group-content.row4' );

    HTML.query( '.group-content.row4')
        .add( 'span.input-group-addon' )
        .textContent	=	'<?php echo _s( 'Holder Name', 'nexo' );?>';

    HTML.query( '.group-content.row4')
        .add( 'input.form-control' )
        .each( 'ng-model', 'card.holder' )
        .each( 'placeholder', '<?php echo _s( 'Holder Name', 'nexo' );?>' );

	var domHTML			=	angular.element( 'angular-cache' ).html();

	angular.element( 'angular-cache' ).remove();

	return {
		template		:	domHTML
	}
});
</script>
