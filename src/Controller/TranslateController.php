<?php

namespace App\Controller;

use App\Entity\Translate;
use App\Service\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Predis;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class TranslateController extends AbstractController
{
    /**
     * @Route("/translate", name="translate")
     */
    public function index(translator $translator): Response
    {

        $languages = $translator->getLanguages();
//        echo "<pre>";
//        print_r($languages->translation);
//        exit;

        return $this->render('translate/index.html.twig', [
            'controller_name' => 'TranslateController',
            'languages' => (array)$languages->translation
        ]);
    }

    /**
     * @Route ("getTranslate", name="getTranslate")
     * @param Translator $translator
     * @return Response
     */
    public function getTranslate(translator $translator, requestStack $requestStack): Response
    {
        $request = $requestStack->getCurrentRequest();
        $params = $request->request->all();

        $translated = $this->controlFromHistory($params);
        if (!$translated) {
            $translated = $translator->translate($params);

            if (isset($translated[0]->detectedLanguage->language)) {
                $params['sourceLanguage'] = $translated[0]->detectedLanguage->language;
            }

            $this->setHistory(
                array(
                    "requestParams" => $params,
                    "responseParams" => $translated
                )
            );
        }

        $response = new JsonResponse($translated, 200, array());
        $response->setCallback('callback');
        return $response;
    }

    private function setHistory($parameters)
    {
        $redisClient = new Predis\Client();
        $serializer = new JsonEncoder();
        $key = $serializer->encode($parameters['requestParams'], 'json');
        $value = $serializer->encode($parameters['responseParams'], 'json');
        $redisClient->set($key, $value);


        $languageCache = $serializer->decode($redisClient->get('languages'), 'array');
        $key = "allCache";
        if ($redisClient->get($key)) {
            $cacheArray = $serializer->decode($redisClient->get($key), 'array');
        } else {
            $cacheArray = array();
        }

        $parameters['languageCodes'] = array(
            "sourceLanguage" => $languageCache[$parameters['requestParams']['sourceLanguage']],
            "targetLanguage" => $languageCache[$parameters['requestParams']['targetLanguage']]
        );

        array_push($cacheArray, $parameters);
        $redisClient->set($key, $serializer->encode($cacheArray, 'json'));

    }

    private function controlFromHistory($parameters)
    {
        $redisClient = new Predis\Client();
        $serializer = new JsonEncoder();
        $key = $serializer->encode($parameters, 'json');
        if ($redisClient->get($key)) {
            return $serializer->decode($redisClient->get($key), 'object');
        } else {
            return null;
        }
    }

    /**
     * @Route ("getHistory", name="getHistory")
     * @return JsonResponse
     */
    public function getHistory()
    {
        $redisClient = new Predis\Client();
        $serializer = new JsonEncoder();

        $allCache = $redisClient->get("allCache");
        if ($allCache) {
            $history = $serializer->decode($allCache, 'array');
        } else {
            $history = [];
        }
        $response = new JsonResponse($history, 200, array());
        $response->setCallback('callback');
        return $response;
    }

    /**
     * @Route ("setSaved", name="setSaved")
     * @return Response
     */
    public function setSaved()
    {

        $entityManager = $this->getDoctrine()->getManager();
        $translate = new Translate();

        $translate
            ->setBrowserUniqueid("1")
            ->setTranslate(array("test" => "test"))
            ->setTest("Test");

        $entityManager->persist($translate);
        $entityManager->flush();


        return new Response(sprintf("Başarılı %s", $translate->getId()));

    }

    /**
     * @Route ("getSaved", name="getSaved")
     * @return Response
     */
    public function getSaved()
    {

        $translateRepository = $this->getDoctrine()->getRepository(Translate::class);
        $translate = ($translateRepository->findTranslate());
        $serializer = new JsonEncoder();
        $translateJson = $serializer->encode($translate, 'json');
        return new Response($translateJson, Response::HTTP_OK, ['content-type' => 'application/json']);

        /*
        $translates = $translateRepository->findAll();
        $translate = array();
        foreach ($translates as $translate_key => $translate_value) {
            $translate[$translate_key] = array(
                "id" => $translate_value->getId(),
                "browser_unqiue_id" => $translate_value->getBrowserUniqueid(),
                "translate" => $translate_value->getTranslate()
            );
        }
        */
    }
}
