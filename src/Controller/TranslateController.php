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
        //$translated = $translator->translate();

        return $this->render('translate/index.html.twig', [
            'controller_name' => 'TranslateController',
        ]);
    }
}
