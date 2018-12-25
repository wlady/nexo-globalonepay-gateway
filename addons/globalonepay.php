<?php

include_once(APPPATH . '/modules/nexo/vendor/autoload.php');

trait Nexo_globalonepay
{
    public function globalonepay_post()
    {
        global $Options;

        $url = @$Options['nexo_globalonepay_test_mode'] != 'no' ? Nexo_GlobalOnePay_Gateway::PROD_MODE_SALE_URL : Nexo_GlobalOnePay_Gateway::TEST_MODE_SALE_URL;
        $terminalId = $Options['nexo_globalonepay_terminal_id'];
        $secret = $Options['nexo_globalonepay_shared_secret'];
        $date = date('j-n-Y:H:m:i:v', time());
        $orderId = $this->post('order');
        $amount = $this->post('amount');

        $hash = md5($terminalId . $orderId . $amount . $date . $secret);

        $cardNumber = $this->post('number');
        $cardType = $this->post('type');
        $cardExpire = $this->post('expire');
        $parts = explode('/', $cardExpire);
        if (count($parts) != 2) {
            $this->response('Wrong card data', 500);

            return;
        }
        $parts[1] = substr($parts[1], 2);
        $cardHolder = $this->post('holder');
        $currency = $this->post('currency');
        $cardCvv = $this->post('cvv');
        $terminalType = 2; // Cardholder Present
        $transactionType = 7; // Cardholder Present (CHP) transaction

        $xmlRequest = "<?xml version='1.0' encoding='UTF-8'?>
<PAYMENT>
    <ORDERID>{$orderId}</ORDERID>
    <TERMINALID>{$terminalId}</TERMINALID>
    <AMOUNT>{$amount}</AMOUNT>
    <DATETIME>{$date}</DATETIME>
    <CARDNUMBER>{$cardNumber}</CARDNUMBER>
    <CARDTYPE>{$cardType}</CARDTYPE>
    <CARDEXPIRY>{$parts[0]}{$parts[1]}</CARDEXPIRY>
    <CARDHOLDERNAME>{$cardHolder}</CARDHOLDERNAME>
    <HASH>{$hash}</HASH>
    <CURRENCY>{$currency}</CURRENCY>
    <TERMINALTYPE>{$terminalType}</TERMINALTYPE>
    <TRANSACTIONTYPE>{$transactionType}</TRANSACTIONTYPE>
    <CVV>{$cardCvv}</CVV>
</PAYMENT>";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => 'UTF-8',
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $xmlRequest,
            CURLOPT_HTTPHEADER => array(
                'Cache-Control: no-cache',
                'Content-Type: application/xml'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->response($err, 500);
        } else {
            $array_data = json_decode(json_encode(simplexml_load_string($response)), true);
            if (isset($array_data['ERRORSTRING'])) {
                $this->response($array_data['ERRORSTRING'], 500);
            } else if (isset($array_data['RESPONSECODE']) && $array_data['RESPONSECODE'] == 'A') {
                $this->response(array(
                    'status'    =>    'payment_success'
                ), 200);
            } else if (isset($array_data['RESPONSETEXT'])) {
                $this->response($array_data['RESPONSETEXT'], 500);
            } else {
                $this->response('Unknown GlobalOne Payment Error', 500);
            }
        }
    }
}
