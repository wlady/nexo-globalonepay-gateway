<script>
    /**
     * GlobalOne Payment Form NexoPOS
     **/

    tendooApp.controller('globalonePaymentForm', ['$compile', '$http', '$scope', function ($compile, $http, $scope) {

        /**
         * confirmPayOrder
         **/

        $scope.confirmPayOrder = function (action) {
            if (action) {
                v2Checkout.createGlobalOnePayment($scope.card);
            }
        }

        /**
         * openPaymentForm
         **/

        $scope.openPaymentForm = function (amount, currency) {

            if (!NexoAPI.events.applyFilters('nexo_open_globalonepay_form', true)) {
                return false;
            }

            $scope.card = new Object;
            $scope.card.number = '';
            $scope.card.expire = '';
            $scope.card.cvv = '';
            $scope.card.holder = '';
            $scope.card.amount = amount;
            $scope.card.currency = currency;

            // If order has at least one item
            NexoAPI.Bootbox().confirm({
                message: '<div class="globalonepaywrapper"><globalonepay-form-content/></div>',
                title: '<?php echo _s('GlobalOne Paymenr', 'nexo');?>',
                buttons: {
                    confirm: {
                        label: '<?php echo _s('Mettre en attente', 'nexo');?>',
                        className: 'btn-info'
                    },
                    cancel: {
                        label: '<?php echo _s('Fermer', 'nexo');?>',
                        className: 'btn-default'
                    }
                },
                callback: function (action) {
                    return $scope.confirmPayOrder(action);
                }
            });

            $('.globalonepaywrapper').html($compile($('.globalonepaywrapper').html())($scope));

            angular.element('.modal-dialog').css('width', '50%');
            /*angular.element( '.modal-body' ).css( 'padding-top', '0px' );
             angular.element( '.modal-body' ).css( 'padding-bottom', '0px' );
             angular.element( '.modal-body' ).css( 'padding-left', '0px' );*/
            angular.element('.modal-body').css('height', $scope.wrapperHeight);
            angular.element('.modal-body').css('overflow-x', 'hidden');
        }
    }]);
</script>
