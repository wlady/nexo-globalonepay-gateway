<?php
$this->load->module_config('nexo', 'nexo');
global $Options, $store_id, $register_id;

$url = @$Options['nexo_globalonepay_test_mode'] != 'no' ? Nexo_GlobalOnePay_Gateway::PROD_MODE_SALE_URL : Nexo_GlobalOnePay_Gateway::TEST_MODE_SALE_URL;
$terminalId = $Options['nexo_globalonepay_terminal_id'];
$secret = $Options['nexo_globalonepay_shared_secret'];
$date = date('j-n-Y:H:m:i:v', time());
?>
<script type="text/javascript">

    v2Checkout.createGlobalOnePayment = function(card) {
        var payment_means = 'globalonepay';
        var order_items				=	new Array;

        _.each( this.CartItems, function( value, key ){

            var ArrayToPush			=	{
                id 						:	value.ID,
                qte_added 				:	value.QTE_ADDED,
                codebar 				:	value.CODEBAR,
                sale_price 				:	value.PROMO_ENABLED ? value.PRIX_PROMOTIONEL : ( v2Checkout.CartShadowPriceEnabled ? value.SHADOW_PRICE : value.PRIX_DE_VENTE_TTC ),
                qte_sold 				:	value.QUANTITE_VENDU,
                qte_remaining 			:	value.QUANTITE_RESTANTE,
                // @since 2.8.2
                stock_enabled 			:	value.STOCK_ENABLED,
                // @since 2.9.0
                discount_type 			:	value.DISCOUNT_TYPE,
                discount_amount			:	value.DISCOUNT_AMOUNT,
                discount_percent 		:	value.DISCOUNT_PERCENT,
                metas 					:	typeof value.metas == 'undefined' ? {} : value.metas,
                // @since 3.1
                name 					:	value.DESIGN,
                alternative_name 		:	value.ALTERNATIVE_NAME, // @since 3.11.8
                inline 					:	typeof value.INLINE != 'undefined' ? value.INLINE : 0 // if it's an inline item
            };

            // improved @since 2.7.3
            // add meta by default
            ArrayToPush.metas	=	NexoAPI.events.applyFilters( 'items_metas', ArrayToPush.metas );

            order_items.push( ArrayToPush );
        });

        var order_details					=	new Object;
        order_details.TOTAL					=	NexoAPI.ParseFloat( this.CartToPay );
        order_details.REMISE_TYPE			=	this.CartRemiseType;

        // @since 2.9.6
        if( this.CartRemiseType == 'percentage' ) {
            order_details.REMISE_PERCENT	=	NexoAPI.ParseFloat( this.CartRemisePercent );
            order_details.REMISE			=	0;
        } else if( this.CartRemiseType == 'flat' ) {
            order_details.REMISE_PERCENT	=	0;
            order_details.REMISE			=	NexoAPI.ParseFloat( this.CartRemise );
        } else {
            order_details.REMISE_PERCENT	=	0;
            order_details.REMISE			=	0;
        }
        // @endSince
        order_details.RABAIS			=	NexoAPI.ParseFloat( this.CartRabais );
        order_details.RISTOURNE			=	NexoAPI.ParseFloat( this.CartRistourne );
        order_details.TVA				=	NexoAPI.ParseFloat( this.CartVAT );
        // @since 3.11.7
        order_details.REF_TAX 			=	this.REF_TAX;
        order_details.REF_CLIENT		=	this.CartCustomerID == null ? this.customers.DefaultCustomerID : this.CartCustomerID;
        order_details.PAYMENT_TYPE		=	this.CartPaymentType;
        order_details.GROUP_DISCOUNT	=	NexoAPI.ParseFloat( this.CartGroupDiscount );
        order_details.DATE_CREATION		=	this.CartDateTime.format( 'YYYY-MM-DD HH:mm:ss' )
        order_details.ITEMS				=	order_items;
        order_details.DEFAULT_CUSTOMER	=	this.DefaultCustomerID;
        order_details.DISCOUNT_TYPE		=	'<?php echo @$Options[ store_prefix() . 'discount_type' ];?>';
        order_details.HMB_DISCOUNT		=	'<?php echo @$Options[ store_prefix() . 'how_many_before_discount' ];?>';
        // @since 2.7.5
        order_details.REGISTER_ID		=	'<?php echo $register_id;?>';

        // @since 2.7.1, send editable order to Rest Server
        order_details.EDITABLE_ORDERS	=	<?php echo json_encode( $this->events->apply_filters( 'order_editable', array( 'nexo_order_devis' ) ) );?>;

        // @since 2.7.3 add Order note
        order_details.DESCRIPTION		=	this.CartNote;

        // @since 2.9.0
        order_details.TITRE				=	this.CartTitle;

        // @since 2.8.2 add order meta
        this.CartMetas					=	NexoAPI.events.applyFilters( 'order_metas', this.CartMetas );
        order_details.metas				=	this.CartMetas;

        /**
         * Make sure to return order_details
         **/
        order_details		=	NexoAPI.events.applyFilters( 'payment_mean_checked', [ order_details, payment_means ] )[0];

        var ProcessObj	=	NexoAPI.events.applyFilters( 'process_data', {
            url			:	this.ProcessURL,
            type		:	this.ProcessType
        });

        // Filter Submitted Details
        order_details	=	NexoAPI.events.applyFilters( 'before_submit_order', order_details );

        $.ajax(  ProcessObj.url, {
            dataType		:	'json',
            type			:	ProcessObj.type,
            data			:	order_details,
            beforeSend		: function(){
                v2Checkout.paymentWindow.showSplash();
            },
            success			:	function( returned ) {

                if( _.isObject( returned ) ) {
                    console.log([card, order_details]);

                }

            },
            error			:	function(){
                v2Checkout.paymentWindow.hideSplash();
                NexoAPI.Notify().warning( '<?php echo _s('Une erreur s\'est produite', 'nexo');?>', '<?php echo _s('Le paiement n\'a pas pu être effectuée.', 'nexo');?>' );
            }
        });
    };
</script>
