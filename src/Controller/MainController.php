<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController {

    /**
     * @Route("/", name="main_accueil")
     */
    public function accueil(){

        if ($this->isGranted("ROLE_ADMIN")) {
            return $this->render("main/accueil.html.twig") ;
        } else {
            return $this->redirectToRoute("main_adminAccueil");
        }
    }

    /**
     * @Route("/admin/", name="main_adminAccueil")
     */
    public function adminAccueil(){
        return $this->render("main/adminAccueil.html.twig") ;
    }

    /**
     * @Route("/aProposDeNous", name="main_presentation")
     */
    public function presentation(){
        return $this->render("main/presentation.html.twig") ;
    }

}