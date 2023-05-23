<?php

/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name lidio
 * 10.05.2023 01:32
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
class LidIO {


    const development_base = 'https://test.lidio.com/api';
    const production_base  = 'https://api.lidio.com';

    private $session     = null;
    private $_method     = 'POST';
    private $_parameters = null;
    private $_enviroment = 'prod';
    private $_endpoint   = '';
    private $_headers    = ['Content-Type: application/json','Accept: application/json'];


    public function __construct($token,$merchant_code,$merchant_key,$api_password, $testmode = true) {

        if ($testmode === true) {$this->_enviroment = 'test';}

        $this->_headers[] = "Authorization: MxS2S {$token}";
        $this->_headers[] = "MerchantCode: {$merchant_code}";

    }

    public function request() {

        $url = ($this->_enviroment == 'test' ? self::development_base : self::production_base) . $this->_endpoint;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->_method);


        if ($this->_parameters !== null && (!in_array($this->_method, ['GET', 'PUT']))) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->_parameters));
        }

        $response = curl_exec($curl);

        curl_close($curl);

        $vals = json_decode($response, true);

        if (function_exists('logModuleCall')) {
            logModuleCall('LidIO', $this->_endpoint, $this->_parameters, $vals);
        }

        return $vals;

    }

    public function endpoint($endpoint) {
        $this->_endpoint = $endpoint;
        $this->_method   = 'GET';
        return $this;
    }

    public function parameters($method, $parameters) {
        $this->_method     = $method;
        $this->_parameters = $parameters;
        return $this;
    }

    public function testmode($test) {
        $this->_enviroment = $test === true ? 'test' : 'prod';
        return $this;
    }

}


