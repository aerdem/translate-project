<?php

namespace App\Controller;

use App\Entity\Translate;
use App\Service\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\Session\Session;


class TranslateController extends AbstractController
{
    private $session;
    public function __construct(){
        $this->session = new Session();
    }
    /**
     * @Route("/translate", name="translate")
     */
    public function index(translator $translator): Response
    {
        $languages = $translator->getLanguages();
        $this->languageCache($languages);

        return $this->render('translate/index.html.twig', [
            'controller_name' => 'TranslateController',
            'languages' => (array)$languages->translation
        ]);
    }

    /**
     * @Route ("getTranslate", name="getTranslate")
     * @param Translator $translator
     * @param RequestStack $requestStack
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

    private function setHistory($parameters){
        $serializer = new JsonEncoder();
        $key = $serializer->encode($parameters['requestParams'], 'json');
        $value = $serializer->encode($parameters['responseParams'], 'json');
        $this->session->set($key,$value);

        $languageCache = $serializer->decode($this->session->get('languages'), 'array');
        $key = "allCache";
        if ($this->session->get($key)) {
            $cacheArray = $serializer->decode($this->session->get($key), 'array');
        } else {
            $cacheArray = array();
        }

        $parameters['languageCodes'] = array(
            "sourceLanguage" => $languageCache[$parameters['requestParams']['sourceLanguage']],
            "targetLanguage" => $languageCache[$parameters['requestParams']['targetLanguage']]
        );

        array_push($cacheArray, $parameters);
        $this->session->set($key, $serializer->encode($cacheArray, 'json'));
    }

    private function controlFromHistory($parameters)
    {
        $serializer = new JsonEncoder();
        $key = $serializer->encode($parameters, 'json');
        if ($this->session->get($key)) {
            return $serializer->decode($this->session->get($key), 'object');
        } else {
            return null;
        }
    }

    /**
     * @Route ("getHistory", name="getHistory")
     * @return JsonResponse
     */
    public function getHistory(): JsonResponse
    {
        $serializer = new JsonEncoder();

        $allCache = $this->session->get("allCache");
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
    public function setSaved(): Response
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
    public function getSaved(): Response
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

    private function languageCache($languages){
        $languageCacheArray = array();
        foreach ($languages->translation as $key => $language){
            $languageCacheArray[$key] = $language->name;
        }
        $serializer = new JsonEncoder();
        $value = $serializer->encode($languageCacheArray,'json');

        $this->session->set("languages", $value);
    }
}
