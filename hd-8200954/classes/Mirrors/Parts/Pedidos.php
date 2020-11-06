<?php

namespace Mirrors\Parts;

class Pedidos extends \Mirrors\AbstractMirror
{
    public function getCollection($params)
    {
        $args = [];

        if (array_key_exists('company', $params)) {
            $args['company'] = $params['company'];
        }

        if (array_key_exists('lang', $params)) {
            $args['lang'] = $params['lang'];
        }

        if (array_key_exists('inicio', $params)) {
            $args['inicio'] = $params['inicio'];
        }

        if (array_key_exists('fim', $params)) {
            $args['fim'] = $params['fim'];
        }

        foreach ($args as $key => $value) {
            $data = $key . '=' . $value;
            if (next($args) != NULL) {
                $data .= '&';
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getBaseURI() . '/parts/pedidos?' . $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "access-application-key: " . $this->getApplicationKey(),
                "access-env: " . $this->getApplicationEnvironment(),
            ),
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        $response = json_decode($response, 1);

        if (array_key_exists("exception", $response)) {
            throw new \Exception("Ocorreu um erro ao efetuar o upload: " . $response['message']);
        }

        return $response;
    }

    public function get($hash, $company)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getBaseURI() . '/parts/pedidos/' . $hash . '?company=' . $company,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Access-Application-Key: " . $this->getApplicationKey(),
                "Access-Env: " . $this->getApplicationEnvironment(),
            ),
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        $response = json_decode($response, 1);

        if (array_key_exists("exception", $response)) {
            throw new \Exception("Ocorreu um erro ao efetuar o upload: " . $response['message']);
        }

        return $response;
    }

    public function put($hash)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->getBaseURI() . '/parts/pedidos/' . $hash,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_HTTPHEADER => array(
                "Access-Application-Key: " . $this->getApplicationKey(),
                "Access-Env: " . $this->getApplicationEnvironment(),
            ),
        ));

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        $response = json_decode($response, 1);

        if (array_key_exists("exception", $response)) {
            throw new \Exception("Ocorreu um erro ao efetuar o upload: " . $response['message']);
        }

        return $response;
    }
}
