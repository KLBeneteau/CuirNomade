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

        return $this->render("adminProduits/accueil.html.twig") ;
    }

}
