<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController {

    /**
     * @Route("/", name="main_accueil")
     */
    public function accueil(){
        return $this->render("main/accueil.html.twig") ;
    }

    /**
     * @Route("/a_propos_de_nous", name="main_presentation")
     */
    public function presentation(){
        return $this->render("main/presentation.html.twig") ;
    }

}