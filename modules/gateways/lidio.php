<?php
/**
 * Created by PhpStorm.
 * User: esh
 * Project name lidio
 * 10.05.2023 15:37
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
App::load_function('gateway');
$gatewayParams = getGatewayVariables('lidio');


function lidio_MetaData() {
    return array(
        'DisplayName'                 => 'LidIO',
        'APIVersion'                  => '1.6',
    );
}

function lidio_config() {
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName'  => array(
            'Type'  => 'System',
            'Value' => 'LidIO Payment Gateway 3D Module',
        ),
        // a text field type allows for single line text input
        'merchant_code' => array(
            'FriendlyName' => 'Merchant Code',
            'Type'         => 'text',
            'Size'         => '25',
            'Default'      => '',
            'Description'  => '',
        ),
        'api_key'       => array(
            'FriendlyName' => 'Api Key (token)',
            'Type'         => 'password',
            'Size'         => '25',
            'Default'      => '',
            'Description'  => 'Given API key(token) from LidIO. If you do not have contact us : <a href="mailto:destek@lidio.com">destek@lidio.com</a> ',
        ),
        'merchant_key'  => array(
            'FriendlyName' => 'Merchant Key',
            'Type'         => 'password',
            'Size'         => '25',
            'Default'      => '',
            'Description'  => 'Merchant key from LidIO. If you do not have contact us : <a href="mailto:destek@lidio.com">destek@lidio.com</a> ',
        ),
        'api_password'  => array(
            'FriendlyName' => 'Api Password',
            'Type'         => 'password',
            'Size'         => '25',
            'Default'      => '',
            'Description'  => 'Api Password from LidIO. If you do not have contact us : <a href="mailto:destek@lidio.com">destek@lidio.com</a> ',
        ),
        'mode' => array(
            'FriendlyName' => 'Payment Mode',
            'Type' => 'dropdown',
            'Options' => array(
                '3d' => '3DSecure With Iframe',
                '2d' => '2D Only',
                'link' => 'Linked Payment (With Redirect Callback)',
                'host' => 'Hosted Payment (Without Redirect Callback)',
            ),
            'Description' => 'Choose one',
        ),
        'test_mode'     => array(
            'FriendlyName' => 'Test Mode',
            'Type'         => 'yesno',
            'Description'  => 'If you are working on test enviroment',
        )
    );
}

if($gatewayParams['mode']=='3d') {
    function lidio_3dsecure($params) {

        require_once __DIR__ . '/lidio/lib/LidIO.php';

        // Gateway Configuration Parameters

        $merchant_code = $params['merchant_code'];
        $api_key       = $params['api_key'];
        $merchant_key  = $params['merchant_key'];
        $api_password  = $params['api_password'];
        $test_mode     = $params['test_mode'];

        $lidio_request = new LidIO($api_key, $merchant_code, $merchant_key, $api_password, $test_mode == 'on');


        $payment_arr = [
            "orderId"                       => $params['invoiceid'],
            "merchantProcessId"             => $params['invoiceid'],
            "totalAmount"                   => $params['amount'],
            "currency"                      => 'TRY',
            //$params['currency'],
            "customerInfo"                  => [
                "email"      => $params['clientdetails']['email'],
                "customerId" => $params['clientdetails']['id'],
                "name"       => $params['clientdetails']['fullname'],
                "phone"      => $params['clientdetails']['phonenumber'],
            ],
            "paymentInstrument"             => "NewCard",
            "paymentInstrumentInfo"         => [
                "newCard" => [
                    "processType"       => "sales",
                    "cardInfo"          => [
                        "cardHolderName" => $params['clientdetails']['fullname'],
                        "cardNumber"     => $params['cardnum'],
                        "lastMonth"      => substr($params['cardexp'], 0, 2) * 1,
                        "lastYear"       => '20' . substr($params['cardexp'], 2, 2)
                    ],
                    "use3DSecure"       => true,
                    "cvv"               => $params['cccvv'],
                    "loyaltyPointUsage" => "None",
                ],
            ],
            "dontDistributeSubsellerPayout" => true,
            "returnUrl"                     => $params['systemurl'] . '/modules/gateways/callback/lidio.php',
            //"notificationUrl"               => "https://webhook.site/5c2210d5-d4a5-4b4c-83a0-e3d1bfa665b6",
            "useExternalFraudControl"       => true,
            "clientType"                    => "Web",
            "clientInfo"                    => "string"
        ];

        $result_invoice = localAPI('GetInvoice', ['invoiceid' => $params['invoiceid']]);

        foreach ($result_invoice['items']['item'] as $k => $v) {

            $payment_arr['basketItems'][] = [
                'name'      => $v['description'],
                'category1' => 'WHMCS-' . $v['type'],
                'quantity'  => 1,
                'unitPrice' => $v['amount']
            ];

        }


        $request3d = $lidio_request->endpoint('/ProcessPayment')
                                   ->parameters('POST', $payment_arr)
                                   ->request();

        logTransaction(ucfirst('lidio'), $request3d, "3DRequest");

        $postfields = [];
        $htmlOutput = '';
        if ($request3d['result'] == 'RedirectFormCreated') {

            $html = $request3d['redirectForm'];
            preg_match('/<form[^>]*>.*<\/form>/s', $html, $matches);
            $form_tag = $matches[0];

            // Action URL'sini çekme
            preg_match('/action=[\'"]?([^\'" >]+)/i', $form_tag, $matches);
            $action_url = $matches[1];

            $htmlOutput = $form_tag;

        } else {
            $postfields = [
                'sys_error'     => 1,
                'error_message' => $request3d['resultMessage']
            ];

            if (isset($request3d['messageDetail'])) {
                $postfields['error_message'] = $request3d['messageDetail'];
            }

            $htmlOutput .= '<form method="post" action="' . $payment_arr['returnUrl'] . '">';
            foreach ($postfields as $k => $v) {
                $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
            }
            $htmlOutput .= '<input type="submit" value="OK" />';
            $htmlOutput .= '</form>';

        }


        return $htmlOutput;
    }
}

if ($gatewayParams['mode'] == 'link') {
    function lidio_link($params) {

        $payment_arr    = [
            "orderId"                       => $params['invoiceid'],
            "merchantProcessId"             => $params['invoiceid'],
            "totalAmount"                   => $params['amount'],
            "currency"                      => 'TRY',
            'uiDesignInfo'                  => [
                'viewType'   => 'Full',
                'designType' => 0
            ],
            'customerInfo'                  => [
                "email"      => $params['clientdetails']['email'],
                "customerId" => $params['clientdetails']['id'],
                "name"       => $params['clientdetails']['fullname'],
                "phone"      => $params['clientdetails']['phonenumber'],
            ],
            'paymentInstruments'            => [
                'NewCard'
            ],
            'paymentInstrumentInfo'         => [
                'card' => [
                    'processType'      => 'sales',
                    'useInstallment'   => null,
                    'useLoyaltyPoints' => null,
                    'noAmex'           => null,
                    'noDebitCard'      => null,
                    'noForeignCard'    => null,
                    'noCreditCard'     => null,
                    'newCard'          => [
                        'threeDSecureMode'   => 'None',
                        'useIVRForCardEntry' => null,
                        'noCvv'              => null,
                        'cardSaveOffer'      => 'PostPayment',
                        'cardConsents'       => [
                            'cardSaveExtraConsent1' => 'None'
                        ]
                    ]
                ]
            ],
            'dontDistributeSubsellerPayout' => null,

            'returnUrl'                => $params['systemurl'] . '/modules/gateways/callback/lidio.php?mode=delay',
            'notificationUrl'          => $params['systemurl'] . '/modules/gateways/callback/lidio.php?mode=notifycallback',
            'alternateNotificationUrl' => 'https://webhook.site/5c2210d5-d4a5-4b4c-83a0-e3d1bfa665b6',
            'useExternalFraudControl'  => true,
            'clientType'               => 'Web',
            'clientIp'                 => 'string',
            'clientUserAgent'          => 'string',
            'clientInfo'               => 'string'
        ];
        $result_invoice = localAPI('GetInvoice', ['invoiceid' => $params['invoiceid']]);

        foreach ($result_invoice['items']['item'] as $k => $v) {

            $payment_arr['basketItems'][] = [
                'name'      => $v['description'],
                'category1' => 'WHMCS-' . $v['type'],
                'quantity'  => 1,
                'unitPrice' => $v['amount']
            ];

        }
        $langPayNow = $params['langpaynow'];

        $htmlOutput = '<form method="post" action="' . $params['systemurl'] . '/modules/gateways/callback/lidio.php?mode=push' . '">';

        $htmlOutput .= '<input type="hidden" name="data" value="' . base64_encode(serialize($payment_arr)) . '" />';

        $htmlOutput .= '<input type="submit" class="btn btn-success" value="' . $langPayNow . '" />';
        $htmlOutput .= '</form>';

        return $htmlOutput;
    }
}
if(in_array($gatewayParams['mode'],['3d'])) {
    function lidio_capture($params) {


    }
}
