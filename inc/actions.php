<?php
class Nexo_GlobalOnePayGateway_Actions
{
    public static function angular_paybox_footer()
	{
		get_instance()->load->module_view( 'nexo-globalonepay-gateway', 'angular-paybox-controller' );
	}
}