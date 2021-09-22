<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\ArticleBDD;
use App\Service\PanierBDD;
use App\Service\ProduitBDD;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/monCompte/panier", name="panier_")
 */
class PanierController extends AbstractController {

    /**
     * @Route("/accueil/" , name="accueil")
     */
    public function accueil(PanierBDD $panierBDD, RepertoirRepository $repertoirRepository ) {

        $monPanier = $panierBDD->getArticlePanier($this->getUser()->getId(),$repertoirRepository) ;

        $totalPanier = 0;
        foreach ($monPanier as $article){
            $totalPanier+=$article['Prix'];
        }

        return $this->render('panier/accueil.html.twig',compact('monPanier','totalPanier')) ;

    }

    /**
     * @Route("/ajouter/" , name="ajouter")
     */
    public function ajouter(PanierBDD $panierBDD, Request $request, ArticleBDD $articleBDD, RepertoirRepository $repertoirRepository, ProduitBDD $produitBDD) {

        try {

            $produit = $repertoirRepository->findOneBy(["nom"=>$request->get('nomProduit')]) ;
            $nomColonneGroup = $produitBDD->getNomColonneGroup($produit);

            $article = $articleBDD->getOne($request,$nomColonneGroup);

            if ($article['Stock']<$request->get('nombreArticle')) {
                $this->addFlash('error','Nous ne possedons plus assez de cet article, stock restant : '.$article['Stock']);
                return $this->redirectToRoute('clientArticle_detail', ['nomProduit'=>$request->get('nomProduit'),'modele'=>$article['Modele']]);
            }

            $panierBDD->ajouter($this->getUser()->getId(),$article,$request);

            $this->addFlash('success',"l'article a bien été ajouté au panier");

        } catch (Exception $e) {
            $this->addFlash("error","Une erreur est survenue lors de l'ajout de l'article au panier");
        }

        return $this->redirectToRoute('main_accueil');

    }


}
