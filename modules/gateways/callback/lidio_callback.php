<?php
/**
 * Created by PhpStorm.
 * User: esh
 * Project name lidio
 * 12.05.2023 01:10
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
require_once __DIR__ . '/../../../init.php';

App::load_function('gateway');
$gatewayParams = getGatewayVariables('lidio');

global $CONFIG;
$systemurl      = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);

if (!function_exists('getallheaders')) {
    function getallheaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
    }
}

$headers = getallheaders();
$hash = $headers['parametershash'];
$raw_post_data = file_get_contents('php://input');
$raw_post_array = json_decode( $raw_post_data,true);

if(isset($raw_post_array['processInfo']['orderId'])) {

    $merchant_key    = $gatewayParams['merchant_key'];

    $orderId          = $raw_post_array['processInfo']['orderId'];
    $systemTransId    = $raw_post_array['paymentList'][0]['systemTransId'];
    $result           = $raw_post_array['paymentResult'];
    $totalAmount      = $raw_post_array['paymentList'][0]['amountProcessed'];
    $installmentCount = $raw_post_array['paymentList'][0]['installmentCount'];
    $invoiceid        = $orderId;
    $mdErrorMessage   = $message = '';
    $paymentSuccess   = false;

    $result_invoice = localAPI('GetInvoice', ['invoiceid' => $orderId]);

    $results_client = localAPI('GetClientsDetails', ['clientid' => $result_invoice['userid']]);

    $hashData = $orderId . ":" . $merchant_key . ":" . number_format($totalAmount, 2, '.', '') . ":" . $result . ":" . $results_client['client']['email'];
    $hash2    = base64_encode(hash('sha256', $hashData, true));

    if($result_invoice['status']=='Unpaid' && $hash == $hash2) {
        if ($result == 'Success') {
            $paymentSuccess = true;
            $message        = 'Ödeme Başarılı';
        } else {
            $paymentSuccess = false;
            $message        = 'Ödeme Başarısız';
        }
        $invoiceid = checkCbInvoiceID($invoiceid, $gatewayParams['name']);
        checkCbTransID($systemTransId);
        if ($paymentSuccess) {
            addInvoicePayment($invoiceid, $systemTransId, $totalAmount, 0, 'lidio');
            logTransaction($gatewayParams['name'], $raw_post_array, 'success');
        } else {
            logTransaction($gatewayParams['name'], $raw_post_array, 'failed');
        }
        echo 'OK';
    } else {
        echo 'FAIL';
    }

}