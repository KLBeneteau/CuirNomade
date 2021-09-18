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

        if ($this->isGranted("ROLE_CLIENT_VIP")) {
            $repertoir = $repertoirRepository->findAll();
        } else {
            $repertoir = $repertoirRepository->findBy(["isVIP"=>false]) ;
        }

        $listeArticle = $filtreArticleBDD->randomGet_SansGroup(6,$repertoir);
        return $this->render("main/accueil.html.twig",compact('listeArticle')) ;
    }

    /**
     * @Route("/aProposDeNous", name="main_presentation")
     */
    public function presentation(){
        return $this->render("main/presentation.html.twig") ;
    }

}