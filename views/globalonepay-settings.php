<?php
/**
 * Add support for Multi Store
 * @since 2.8
**/

global $store_id, $CurrentStore, $Options;

$option_prefix		=	'';

if( $store_id != null ) {
	$option_prefix	=	'store_' . $store_id . '_' ;
}

$this->Gui->col_width(1, 2);

$this->Gui->add_meta(array( 
	'namespace'    =>    'globalonepay_settings',
	'title'        =>    __('Réglages GlobalOne', 'nexo-globalonepay-gateway'),
	'col_id'    =>    1,
	'type'        =>    'box',
	'gui_saver'    =>    true,
	'use_namespace'    =>    false,
	'footer'        =>        array(
		'submit'    =>        array(
			'label'    =>        __('Sauvegarder les réglages', 'nexo-globalonepay-gateway')
		)
	)
));

$this->Gui->add_item(array(
	'type'        =>    'select',
	'name'        =>    $option_prefix	. 'nexo_enable_globalonepay',
	'label'        =>    __('Activer GlobalOne', 'nexo-globalonepay-gateway'),
	'options'    =>    array(
		'no'    =>    __('Non', 'nexo'),
		'yes'    =>    __('Oui', 'nexo')
	),
	'description'    =>    __('Désactiver GlobalOne empêchera au ressource de ce dernier de se charger dans l\'interface de la caisse.', 'nexo-globalonepay-gateway')
), 'globalonepay_settings', 1);

// GlobalOne Payment XML End Point
$this->Gui->add_item(array(
	'type'        =>    'text',
	'name'        =>    $option_prefix	. 'nexo_globalonepay_endpoint',
	'label'        =>    __('Payment XML End Point', 'nexo-globalonepay-gateway'),
	'description'    =>  __('Contact Nuvei.com integration team to get correct URL.', 'nexo-globalonepay-gateway')
), 'globalonepay_settings', 1);

// Terminal ID
$this->Gui->add_item(array(
	'type'        =>    'text',
	'name'        =>    $option_prefix	. 'nexo_globalonepay_terminal_id',
	'label'        =>    __('Terminal ID', 'nexo-globalonepay-gateway'),
	'description'    =>  __('Retrieve the "Terminal ID" from your Nuvei.com merchant account.', 'nexo-globalonepay-gateway')
), 'globalonepay_settings', 1);

// Shared Secret
$this->Gui->add_item(array(
	'type'        =>    'text',
	'name'        =>    $option_prefix	. 'nexo_globalonepay_shared_secret',
	'label'        =>    __('Shared Secret', 'nexo-globalonepay-gateway'),
	'description'    =>  __('Retrieve the "Shared Secret" from your Nuvei.com merchant account.', 'nexo-globalonepay-gateway')
), 'globalonepay_settings', 1);

$this->Gui->add_item(array(
    'type'        =>    'select',
    'name'        =>    $option_prefix	. 'nexo_globalonepay_logs',
    'label'        =>    __('Enable Logs', 'nexo-globalonepay-gateway'),
    'options'    =>    array(
        'no'    =>    __('Non', 'nexo'),
        'yes'    =>    __('Oui', 'nexo')
    ),
    'description'    =>    __('Daily log files are \'application/logs/globalonepay-payments-XXXXXX.log\'.', 'nexo-globalonepay-gateway')
), 'globalonepay_settings', 1);


$this->Gui->output();