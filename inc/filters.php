<?php
class Nexo_GlobalOnePayGateway_Filters
{
	public static function payment_gateway( $gateway )
	{
		global $Options;
		if (@$Options[ store_prefix() . 'nexo_enable_globalonepay' ] != 'no'):
			$gateway[ 'globalonepay' ]	=	__( 'GlobalOne', 'nexo-globalonepay-gateway' );
		endif;

		return $gateway;
	}

	/**
	 * Admin Menu
	**/

	public static function admin_menus( $menus )
	{
		$menus[]		=	array(
			'title'		=>		__( 'GlobalOne Payment Gateway', 'nexo-globalonepay-gateway' ),
			'href'		=>		dashboard_url([ 'settings', 'globalonepay' ])
		);

		return $menus;
	}

	/**
	 * PayBox dependency
	 * register Stripe Checkout and Windows_Splash
	**/

	public static function paybox_dependencies( $dependencies )
	{
		return array_merge( $dependencies, array( '__windowSplash', '__globalOnePayCheckout' ) );
	}
}
