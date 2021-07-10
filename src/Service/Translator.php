<?php

namespace App\Service;


use GuzzleHttp\Client;

class Translator
{

    public function getLanguages()
    {
        $params= array(
            "method" => "GET",
            "endpoint" => '/languages?api-version=3.0&scope=translation',
            "data" => []
        );
        return $this->query($params);
    }

    /**
     * @return mixed
     */
    public function translate()
    {
        $params= array(
            "method" => "POST",
            "endpoint" => '/translate?api-version=3.0&to=tr',
            "data" => array(
                ["Text" => "Hello"]
            )
        );

        return $this->query($params);
    }

    private function query($params)
    {
        $key = '7c586f2d663f4b99beca2652a2217fc9';
        $region = 'westus2';

        $client = new Client(['base_uri' => 'https://api.cognitive.microsofttranslator.com']);
        $requestParameters = [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $key,
                'Ocp-Apim-Subscription-Region' => $region,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 60,
            'json' => $params['data']
        ];
        $res = $client->request($params['method'], $params['endpoint'], $requestParameters);
        $ret = json_decode($res->getBody()->getContents());
        return $ret;
    }
}

