<?php

/**
 * Created by PhpStorm.
 * User: bunyaminakcay
 * Project name lidio
 * 10.05.2023 01:32
 * Bünyamin AKÇAY <bunyamin@bunyam.in>
 */
class LidIO {


    const development_base = 'https://api-test.metunic.com.tr/v1';
    const production_base  = 'https://api.metunic.com.tr/v1';

    private $session     = null;
    private $_method     = 'GET';
    private $_parameters = null;
    private $_enviroment = 'prod';
    private $_endpoint   = '';
    private $_headers    = ['accept: application/json'];


    public function __construct($username, $password, $testmode = true) {


        if ($testmode === true) {
            $this->_enviroment = 'test';
        }


        if ($data['ts'] > time() - 600) {
            $this->session = $data['se'];
        }

        $params = [
            'username' => $username,
            'password' => $password
        ];

        if ($this->session === null) {
            $this->endpoint('/login/auth')
                 ->parameters('POST', $params)
                 ->testmode($testmode)
                 ->request();
            $data       = [];
            $data['se'] = $this->session;
            $data['ts'] = time();
            file_put_contents(__DIR__ . '/.session', serialize($data));
        }

    }

    public function request() {

        $url = ($this->_enviroment == 'test' ? self::development_base : self::production_base) . $this->_endpoint;

        if ($this->_parameters !== null && ($this->_method == 'GET' || $this->_method == 'PUT')) {
            if (count($this->_parameters) > 0) {
                $url .= '?' . http_build_query($this->_parameters);
            }
        }
        //Remove indexes
        foreach (range(0, 5) as $k => $v) {
            $url = str_replace(urlencode("[{$v}]"), urlencode("[]"), $url);
        }

        //Not my fault...
        //$url = str_replace('domains/check?domainName=', 'domains/check?domainName%3D', $url);
        //$url = str_replace('queried-services?domainName=', 'queried-services?domainName%3D', $url);

        if ($this->_method == 'PUTJSON') {
            $this->_method     = 'PUT';
            $this->_headers[]  = 'application/json';
            $this->_parameters = json_encode($this->_parameters);
        }


        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->_method);

        if ($this->_parameters !== null && (!in_array($this->_method, [
                'GET',
                'PUT'
            ]))) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_parameters);
        }

        curl_setopt($curl, CURLOPT_HEADER, ($this->session === null));

        if ($this->session !== null) {
            $this->_headers[] = 'Cookie: ' . $this->session;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_headers);
        }

        $response = curl_exec($curl);

        curl_close($curl);


        if ($this->session === null) {
            preg_match('/SESSION=([A-Za-z0-9]*)/ix', $response, $_session);
            $this->session = $_session[0];
            return [];
        } else {
            $vals = json_decode($response, true);

            if (function_exists('logModuleCall')) {
                logModuleCall('Metunic', $this->_endpoint, $this->_parameters, $vals);
            }

            return $vals;
        }

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


