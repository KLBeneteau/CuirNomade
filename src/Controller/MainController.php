<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\FiltreArticleBDD;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController {

    /**
     * @Route("/", name="main_accueil")
     */
    public function accueil(FiltreArticleBDD $filtreArticleBDD, RepertoirRepository $repertoirRepository){

        $listeArticle = $filtreArticleBDD->randomGet_AvecGroup(12,$repertoirRepository->findAll());
        return $this->render("main/accueil.html.twig",compact('listeArticle')) ;
    }

    /**
     * @Route("/aProposDeNous", name="main_presentation")
     */
    public function presentation(){
        return $this->render("main/presentation.html.twig") ;
    }

}