<?php
class GlobalOnePayGateway_Controller
{
	public static function globalonepay_settings()
	{
		$core	=	get_instance();
		$core->Gui->set_title( 'Settings' );
		$core->load->module_view( 'nexo-globalonepay-gateway', 'globalonepay-settings' );
	}

	/**
	 * Gateway Settings
	**/

	public static function settings()
	{
		$core	=	get_instance();
		$core->Gui->set_title( 'Settings' );
		$core->load->module_view( 'nexo-globalonepay-gateway', 'globalonepaygateway-settings' );
	}
}
