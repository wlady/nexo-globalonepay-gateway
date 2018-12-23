<?php
global $Routes;

$Routes->get( '/nexo/settings/globalonepay', 'GlobalOnePayGateway_Controller@globalonepay_settings' );
$Routes->get( '/nexo/settings/globalonepay_payments', 'GlobalOnePayGateway_Controller@settings' );
