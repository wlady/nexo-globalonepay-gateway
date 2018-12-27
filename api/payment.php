<?php

class GlobalOnePayment extends Tendoo_Api
{
    public function pay()
    {
        global $Options;

        $url = '';
        $terminalId = '';
        $secret = '';

        if (@$Options[store_prefix() . 'nexo_enable_globalonepay'] != 'no') {
            $url = $Options['nexo_globalonepay_endpoint'];
            $terminalId = $Options['nexo_globalonepay_terminal_id'];
            $secret = $Options['nexo_globalonepay_shared_secret'];
        }

        if (empty($url) || empty($terminalId) || empty($secret)) {
            $this->response('Incorrect Gateway Settings', 500);

            return;
        }

        $date = date('j-n-Y:H:m:i:v', time());
        $orderId = $this->post('order');
        $amount = $this->post('amount');
        $hash = md5($terminalId . $orderId . $amount . $date . $secret);

        $cardNumber = $this->post('number');
        $cardType = $this->post('type');
        $cardExpire = $this->post('expire');
        $parts = explode('/', $cardExpire);
        if (count($parts) != 2 || strlen($parts[1]) != 4 || $parts[1] < date('Y') || ($parts[0] < date('m') && $parts[1] <= date('Y'))) {
            $this->response('Wrong Credit Card Expire Format', 500);

            return;
        }
        $parts[1] = substr($parts[1], 2, 2);
        $cardHolder = $this->post('holder');
        $currency = $this->post('currency');
        $cardCvv = $this->post('cvv');
        $terminalType = 2; // eCommerce
        $transactionType = 7; // eCommerce

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

        if (@$Options[store_prefix() . 'nexo_globalonepay_logs'] != 'no') {
            $this->log([
                'order' => $orderId,
                'terminal' => $terminalId,
                'amount' => $amount,
                'currency' => $currency,
                'cardNumber' => substr_replace($cardNumber, str_repeat("*", 8), 4, 8),
                'cardType' => $cardType,
                'cardExpire' => $cardExpire,
                'cardHolder' => $cardHolder,
            ]);
        }
        $response = curl_exec($curl);
        if (@$Options[store_prefix() . 'nexo_globalonepay_logs'] != 'no') {
            $this->log($response);
        }
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $this->response($err, 500);
        } else {
            $array_data = json_decode(json_encode(simplexml_load_string($response)), true);
            if (isset($array_data['ERRORSTRING'])) {
                $this->response([
                    'error' => [
                        'message' => $array_data['ERRORSTRING']
                    ]
                ], 500);
            } else if (isset($array_data['RESPONSECODE']) && $array_data['RESPONSECODE'] == 'A') {
                $this->response([
                    'status' => 'payment_success'
                ], 200);
            } else if (isset($array_data['RESPONSETEXT'])) {
                $this->response([
                    'error' => [
                        'message' => $array_data['RESPONSETEXT']
                    ]
                ], 500);
            } else {
                $this->response([
                    'error' => [
                        'message' => 'Unknown GlobalOne Payment Error'
                    ]
                ], 500);
            }
        }
    }

    private function _var_dump($var)
    {
        ob_start();
        print_r($var);
        $v = ob_get_contents();
        ob_end_clean();
        return $v . PHP_EOL;
    }

    private function log($var)
    {
        file_put_contents(APPPATH . 'logs/globalonepay-payments-' . date('Y-m-d') . '.log', '+---+ ' . date('Y-m-d H:i:s') . ' +-----+' . PHP_EOL . $this->_var_dump($var) . PHP_EOL, FILE_APPEND);
    }
}