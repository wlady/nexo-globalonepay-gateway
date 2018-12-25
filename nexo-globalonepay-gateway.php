<?php

include_once( dirname( __FILE__ ) . '/inc/filters.php' );
include_once( dirname( __FILE__ ) . '/inc/actions.php' );

class Nexo_GlobalOnePay_Gateway extends CI_Model
{
    const TEST_MODE_SALE_URL = 'https://testpayments.globalone.me/merchant/xmlpayment';
    // will be available after code review
    const PROD_MODE_SALE_URL = 'https://testpayments.globalone.me/merchant/xmlpayment';

	public function __construct()
	{
		parent::__construct();
		$this->events->add_action( 'load_dashboard', array( $this, 'dashboard' ) );
	}

	/**
	 * Load Dashboard
	**/

	public function dashboard()
	{
		global $Options;
		// Set default options for Stripe
		if (@$Options[ store_prefix() . 'nexo_enable_globalonepay' ] != 'no'):
			$this->load->module_config( 'nexo', 'nexo' );
			$payments	=	$this->config->item( 'nexo_payments_types' );
			$payments[ 'globalonepay' ]	=	__( 'GlobalOne' , 'nexo-globalonepay-gateway' );
			$this->config->set_item( 'nexo_payments_types', $payments );

			$payments_all	=	$this->config->item( 'nexo_all_payment_types' );
			$payments_all[ 'globalonepay' ]	=	__( 'GlobalOne' , 'nexo-globalonepay-gateway' );
			$this->config->set_item( 'nexo_all_payment_types', $payments_all );
		endif;

		$this->events->add_action( 'dashboard_footer', array( $this, 'dashboard_footer' ) );
		$this->events->add_action( 'dashboard_header', array( $this, 'dashboard_header' ) );
		$this->events->add_action( 'angular_paybox_footer', array( 'Nexo_GlobalOnePayGateway_Actions', 'angular_paybox_footer' ) );
		$this->events->add_action( 'load_register_content', array( $this, 'register_content' ) );
		$this->events->add_filter( 'nexo_payments_types', array( 'Nexo_GlobalOnePayGateway_Filters', 'payment_gateway' ) );
		$this->events->add_filter( 'nexo_settings_menu_array', array( 'Nexo_GlobalOnePayGateway_Filters', 'admin_menus' ) );
		$this->events->add_filter( 'paybox_dependencies', array( 'Nexo_GlobalOnePayGateway_Filters', 'paybox_dependencies' ) );
        $this->enqueue->css( '../modules/nexo-globalonepay-gateway/css/styles' );
        $this->enqueue->js( '../modules/nexo-globalonepay-gateway/js/bundle.min' );
        $this->enqueue->js( '../plugins/input-mask/jquery.inputmask' );
        $this->enqueue->js( '../plugins/input-mask/jquery.inputmask.date.extensions' );
	}

	/**
	 * Dashboard Footer
	**/

	public function dashboard_footer()
	{
		global $PageNow;

		if( $PageNow == 'nexo/registers/__use' ) {
			$this->load->module_view( 'nexo-globalonepay-gateway', 'dashboard-footer' );
		}
	}

	/**
	 * Dashboard Headed
	**/

	public function dashboard_header()
	{
		global $PageNow;

		if( $PageNow == 'nexo/registers/__use' ) {
			$this->load->module_view( 'nexo-globalonepay-gateway', 'dashboard-header' );
		}
	}

	/**
	 *
	**/

	public function register_content()
	{
		include_once( MODULESPATH . '/nexo/inc/angular/order-list/services/window-splash.php' );
		include_once( MODULESPATH . '/nexo-globalonepay-gateway/inc/angular/directives/payment.php' );
		include_once( MODULESPATH . '/nexo-globalonepay-gateway/inc/angular/directives/payment-form.php' );
		include_once( MODULESPATH . '/nexo-globalonepay-gateway/inc/angular/controllers/payment-form.php' );
		include_once( MODULESPATH . '/nexo-globalonepay-gateway/inc/angular/checkout-extended.php' );
	}

	/**
	 * Register for Multistore
	**/

	public function multistore( $array )
	{
		// to match this uri
		// dashboard/stores/nexo_premium/*
		// $array[ 'nexo_gateway' ]	=	new Gateway_Controller;

		return $array;
	}
}
new Nexo_GlobalOnePay_Gateway;
