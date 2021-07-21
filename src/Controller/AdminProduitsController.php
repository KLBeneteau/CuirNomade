<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/produit", name="adminProduit_")
 */
class AdminProduitsController extends AbstractController {

    /**
     * @Route("/", name="accueil")
     */
    public function accueil(){

        $listeProduits = [] ;

        return $this->render("adminProduits/accueil.html.twig", compact('listeProduits')) ;
    }

    /**
     * @Route("/creer", name="creer")
     */
    public function creer(){

        $listeProduits = [] ;

        return $this->render("adminProduits/creer.html.twig", compact('listeProduits')) ;
    }

}
