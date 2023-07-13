<?php
/**
 * Created by PhpStorm.
 * User: esh
 * Project name lidio
 * 11.05.2023 14:40
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../lidio/lib/LidIO.php';
App::load_function('gateway');
App::load_function('invoice');
global $CONFIG;
$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams     = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}
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

$systemurl            = ($CONFIG['SystemSSLURL'] ? $CONFIG['SystemSSLURL'] : $CONFIG['SystemURL']);
$merchant_code        = $gatewayParams['merchant_code'];
$api_key              = $gatewayParams['api_key'];
$merchant_key         = $gatewayParams['merchant_key'];
$api_password         = $gatewayParams['api_password'];
$test_mode            = $gatewayParams['test_mode'];
$mode                 = $_REQUEST['mode'];
$orderId              = $_REQUEST['OrderId'];
$systemTransId        = $_REQUEST['SystemTransId'];
$result               = $_REQUEST['Result'];
$totalAmount          = $_REQUEST['TotalAmount'];
$installmentCount     = $_REQUEST['InstallmentCount'];
$mDStatus             = $_REQUEST['MDStatus'];
$hash                 = $_REQUEST['Hash'];
$invoiceid            = $orderId;
$mdErrorMessage       = $message = $result_message = '';
$paymentSuccess       = false;
$redirectDelay        = 9 * 10;
$delaySuccess         = $_REQUEST['Result'] == 'Success';
$redirectPage         = $systemurl . "/viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true";
$redirectPage_success = $systemurl . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true";

if ($mode == '3dcallback') {


    $result_invoice = localAPI('GetInvoice', ['invoiceid' => $orderId]);

    $results_client = localAPI('GetClientsDetails', ['clientid' => $result_invoice['userid']]);

    $hashData = $orderId . ":" . $merchant_key . ":" . number_format($totalAmount, 2, '.', '') . ":" . $result . ":" . $results_client['client']['email'];
    $hash2    = base64_encode(hash('sha256', $hashData, true));


    if ($result_invoice['status'] == 'Unpaid' && $hash == $hash2) {

        $lidio_request = new LidIO($api_key, $merchant_code, $merchant_key, $api_password, $test_mode == 'on');

        $finish_arr = [
            'orderId'               => $orderId,
            'systemTransId'         => $systemTransId,
            'totalAmount'           => $totalAmount,
            'currency'              => 'TRY',
            'paymentInstrument'     => 'NewCard',
            'paymentInstrumentInfo' => [
                'newCard' => [
                    'posAccount' => [
                        'id' => 0,
                    ]
                ]
            ]
        ];

        $request3d = $lidio_request->endpoint('/FinishPaymentProcess')
                                   ->parameters('POST', $finish_arr)
                                   ->request();


        if ($request3d['result'] == 'Success') {

            $transid = $request3d['paymentInfo']['acquirerResultDetail']['pos']['transId'] . "-" . $request3d['paymentInfo']['acquirerResultDetail']['pos']['authCode'];

            checkCbInvoiceID($invoiceid, $gatewayParams['name']);
            checkCbTransID($invoiceid);
            addInvoicePayment($invoiceid, $transid, "", "", ucfirst($gatewayModuleName), "on");
            logTransaction(ucfirst($gatewayModuleName), ['request'  => $finish_arr, 'response' => $request3d], "Success");

            $paymentSuccess = true;
            callback3DSecureRedirect($invoiceid,true);

        } else {
            $mdErrorMessage = $request3d['RefTransactionNotFound'] . ' ' . $request3d['paymentInfo']['acquirerResultDetail']['pos']['message'];
            logTransaction(ucfirst($gatewayModuleName), ['request'  => $finish_arr, 'response' => $request3d], 'Error');
            callback3DSecureRedirect($invoiceid,false);
        }

    } elseif ($result_invoice['status'] != 'Unpaid') {

        $message        = 'Hata';
        $mdErrorMessage = 'Bu fatura zaten ödenmiş';

    } else {
        $message        = 'Hata';
        $mdErrorMessage = 'İşlem banka tarafından reddedildi';
        logTransaction(ucfirst($gatewayModuleName), $_REQUEST, 'failed');
    }

    $result_message = $paymentSuccess ? 'Ödeme başarılı' : $mdErrorMessage;

}

if ($mode == 'notifycallback') {

    $headers        = getallheaders();
    $hash           = $headers['parametershash'];
    $raw_post_data  = file_get_contents('php://input');
    $raw_post_array = json_decode($raw_post_data, true);

    if (isset($raw_post_array['processInfo']['orderId'])) {

        $merchant_key     = $gatewayParams['merchant_key'];
        $api_password     = $gatewayParams['api_password'];
        $orderId          = $raw_post_array['processInfo']['orderId'];
        $result           = $raw_post_array['paymentResult'];
        $systemTransId    = $raw_post_array['paymentList'][0]['systemTransId'];
        $totalAmount      = $raw_post_array['paymentList'][0]['amountProcessed'];
        $installmentCount = $raw_post_array['paymentList'][0]['installmentCount'];
        $invoiceid        = $orderId;
        $mdErrorMessage   = $message = '';
        $paymentSuccess   = false;

        $result_invoice = localAPI('GetInvoice', ['invoiceid' => $orderId]);

        $results_client = localAPI('GetClientsDetails', ['clientid' => $result_invoice['userid']]);

        //$hashData = $orderId . ":" . $merchant_key . ":" . number_format($totalAmount, 2, '.', '') . ":" . $result . ":" . $results_client['client']['email'];
        //$hash2    = base64_encode(hash('sha256', $hashData, true));
        $hash2    = base64_encode(hash('sha256', ($raw_post_data.$api_password), true));

        if ($result_invoice['status'] == 'Unpaid' && $hash == $hash2) {

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
                logTransaction($gatewayParams['name'], $raw_post_array, 'Success');
            } else {
                logTransaction($gatewayParams['name'], $raw_post_array, 'Failed');
            }

            echo 'OK';
        } else {
            echo 'FAIL';
        }

    }
    die();
}

if ($mode == 'push') {
    $lidio_request = new LidIO($api_key, $merchant_code, $merchant_key, $api_password, $test_mode == 'on');

    $full_data = unserialize(base64_decode($_REQUEST['data']));


    $response = $request3d = $lidio_request->endpoint('/StartHostedPaymentProcess')
                                           ->parameters('POST', $full_data)
                                           ->request();


    if ($response['result'] == 'Success') {
        $redirectPage   = $response['redirectURL'];
        $redirectDelay  = 10;
        $paymentSuccess = null;
        logTransaction(ucfirst('lidio'), ['request'  => $full_data, 'response' => $request3d], "Link Success");
    } else {
        $result_message = $response['resultMessage'];
        $paymentSuccess = false;
        logTransaction(ucfirst('lidio'), ['request'  => $full_data, 'response' => $request3d], "Link Failed");
    }
}

if ($mode == 'link') {
    $lidio_request = new LidIO($api_key, $merchant_code, $merchant_key, $api_password, $test_mode == 'on');

    $full_data = unserialize(base64_decode($_REQUEST['data']));


    $response = $request3d = $lidio_request->endpoint('/CreatePaymentLink')
                                           ->parameters('POST', $full_data)
                                           ->request();


    if ($response['result'] == 'Success') {
        $redirectPage   = $response['linkURL'];
        $redirectDelay  = 10;
        $paymentSuccess = null;
        logTransaction(ucfirst('lidio'), ['request'  => $full_data, 'response' => $request3d], "Linked Success");
    } else {
        $result_message = $response['resultMessage'];
        $paymentSuccess = false;
        logTransaction(ucfirst('lidio'), ['request'  => $full_data, 'response' => $request3d], "Linked Failed");
    }
}

if ($mode == 'host') {
    $lidio_request = new LidIO($api_key, $merchant_code, $merchant_key, $api_password, $test_mode == 'on');

    $full_data = unserialize(base64_decode($_REQUEST['data']));


    $response = $request3d = $lidio_request->endpoint('/StartHostedPaymentProcess')
                                           ->parameters('POST', $full_data)
                                           ->request();


    if ($response['result'] == 'Success') {
        $redirectPage   = $response['redirectURL'];
        $redirectDelay  = 10;
        $paymentSuccess = null;
        logTransaction(ucfirst('lidio'), ['request'  => $full_data, 'response' => $request3d], "Hosted Success");
    } else {
        $result_message = $response['resultMessage'];
        $paymentSuccess = false;
        logTransaction(ucfirst('lidio'), ['request'  => $full_data, 'response' => $request3d], "Hosted Failed");
    }

}

if ($mode == 'delay') {
    $result_message = !$delaySuccess ? 'İşleminiz Onaylanamadı. Tekrar denemek için faturanıza yönlendiriliyorsunuz.' : 'İşleminiz başarılıdır. Faturanıza yönlendiriliyorsunuz.';
}

if ($paymentSuccess === true || $delaySuccess === true) {
    $redirectPage = $redirectPage_success;
}

?>
<html>
<head>
    <title><?php echo $CONFIG['CompanyName']; ?> - LidIO</title>
</head>
<body style="background-color: ghostwhite;">
<div id="content">
    <div class="lds-ring">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div style="width: 50%">
        <br>

        <p><?php echo $result_message; ?></p>
        <p id="countdownText"></p>
        <a href="<?php echo $redirectPage; ?>" id="redirectButton">Şimdi Yönlendir.</a>

        <!--  https://github.com/bakcay  -->
    </div>
</div>
<style> #content {display: grid;place-items: center;}  #content p {font-size: 1.3em;font-weight: normal;font-family: sans-serif;}  #redirectButton {font-family: sans-serif;text-decoration: none;border: solid 2px black;padding: .375em 1.125em;font-size: 1rem;background: hsl(190deg, 30%, 15%);color: hsl(190deg, 10%, 95%);box-shadow: 0 0px 0px hsla(190deg, 15%, 5%, .2);transfrom: translateY(0);border-top-left-radius: 0px;border-top-right-radius: 0px;border-bottom-left-radius: 0px;border-bottom-right-radius: 0px;--dur: .15s;--delay: .15s;--radius: 16px;transition: border-top-left-radius var(--dur) var(--delay) ease-out, border-top-right-radius var(--dur) calc(var(--delay) * 2) ease-out, border-bottom-right-radius var(--dur) calc(var(--delay) * 3) ease-out, border-bottom-left-radius var(--dur) calc(var(--delay) * 4) ease-out, box-shadow calc(var(--dur) * 4) ease-out, transform calc(var(--dur) * 4) ease-out, background calc(var(--dur) * 4) steps(4, jump-end);}  #redirectButton:hover, #redirectButton:focus {box-shadow: 0 4px 8px hsla(190deg, 15%, 5%, .2);transform: translateY(-4px);background: hsl(230deg, 50%, 45%);border-top-left-radius: var(--radius);border-top-right-radius: var(--radius);border-bottom-left-radius: var(--radius);border-bottom-right-radius: var(--radius);}  .lds-ring {display: inline-block;position: relative;width: 80px;height: 80px;}  .lds-ring div {box-sizing: border-box;display: block;position: absolute;width: 64px;height: 64px;margin: 8px;border: 8px solid #252222;border-radius: 50%;animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;border-color: #252222 transparent transparent transparent;}  .lds-ring div:nth-child(1) {animation-delay: -0.45s;}  .lds-ring div:nth-child(2) {animation-delay: -0.3s;}  .lds-ring div:nth-child(3) {animation-delay: -0.15s;}  @keyframes lds-ring { 0% {         transform: rotate(0deg);     } 100% {transform: rotate(360deg);} } </style>
<script type="text/javascript">

  let redirectURL = "<?php echo $redirectPage; ?>"; // Yönlendirilecek URL
  let redirectDelay = <?php echo $redirectDelay; ?>; // Yönlendirme gecikme süresi (saniye)

  // Butonu etkinleştir ve geri sayımı başlat
  function startRedirectCountdown() {
    let redirectButton = document.getElementById('redirectButton');
    let countdownText = document.getElementById('countdownText');
    countdownText.innerHTML = Math.round(redirectDelay / 10) + ' saniye içinde yönlendiriliyor...';

    // Geri sayımı başlat
    let countdown = setInterval(function() {
      redirectDelay--;
      countdownText.innerHTML = Math.round(redirectDelay / 10) + ' saniye içinde yönlendiriliyor' + ('.').repeat(4-(redirectDelay % 4 + 1));
      if (redirectDelay <= 0) {
        clearInterval(countdown);
        redirectButton.innerHTML = 'Yönlendiriliyor.';
        window.location.href = redirectURL; // Yönlendirme işlemi
      }
    }, 100);
  }

  startRedirectCountdown();

</script>
</body>
</html>
