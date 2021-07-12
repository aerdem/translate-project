<?php

namespace App\Service;


use GuzzleHttp\Client;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class Translator
{

    public function getLanguages()
    {
        $params = array(
            "method" => "GET",
            "endpoint" => '/languages?api-version=3.0&scope=translation',
            "data" => []
        );
        $languages = $this->query($params);
        $this->languageCache($languages);
        return $languages;
    }

    /**
     * @return mixed
     */
    public function translate($translateParams)
    {
        $from = ($translateParams['sourceLanguage']) ? "&from=" . $translateParams['sourceLanguage'] : '';
        $to = $translateParams['targetLanguage'];
        $params = array(
            "method" => "POST",
            "endpoint" => '/translate?api-version=3.0&to=' . $to . $from,
            "data" => array(
                ["Text" => $translateParams['sourceText']]
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

    private function languageCache($languages){
        $languageCacheArray = array();
        foreach ($languages->translation as $key => $language){
            $languageCacheArray[$key] = $language->name;
        }
        $redisClient = new \Predis\Client();
        $serializer = new JsonEncoder();
        $value = $serializer->encode($languageCacheArray,'json');
        $redisClient->set("languages", $value);
    }
}

