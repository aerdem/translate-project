<?php

namespace App\Controller;

use App\Service\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TranslateController extends AbstractController
{
    /**
     * @Route("/translate", name="translate")
     */
    public function index(translator $translator): Response
    {

        $languages  = $translator->getLanguages();
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
    public function getTranslate(translator $translator): Response
    {
        $translated = $translator->translate();
        print_r($translated);
        exit;

        return json_encode($translated);
    }
}
