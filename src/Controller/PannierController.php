<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\ArticleBDD;
use App\Service\PannierBDD;
use App\Service\ProduitBDD;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/monCompte/pannier", name="pannier_")
 */
class PannierController extends AbstractController {

    /**
     * @Route("/accueil/" , name="accueil")
     */
    public function accueil(PannierBDD $pannierBDD, RepertoirRepository $repertoirRepository ) {

        $monPannier = $pannierBDD->getArticlePannier($this->getUser()->getId(),$repertoirRepository) ;

        return $this->render('pannier/accueil.html.twig',compact('monPannier')) ;

    }

    /**
     * @Route("/ajouter/" , name="ajouter")
     */
    public function ajouter(PannierBDD $pannierBDD, Request $request, ArticleBDD $articleBDD, RepertoirRepository $repertoirRepository, ProduitBDD $produitBDD) {

        try {

            $produit = $repertoirRepository->findOneBy(["nom"=>$request->get('nomProduit')]) ;
            $nomColonneGroup = $produitBDD->getNomColonneGroup($produit);

            $article = $articleBDD->getOne($request,$nomColonneGroup);

            if ($article['Stock']<$request->get('nombreArticle')) {
                $this->addFlash('error','Nous ne possedons plus assez de cet article, stock restant : '.$article['Stock']);
                return $this->redirectToRoute('clientArticle_detail', ['nomProduit'=>$request->get('nomProduit'),'modele'=>$article['Modele']]);
            }

            $pannierBDD->ajouter($this->getUser()->getId(),$article,$request);

            $this->addFlash('success',"l'article a bien été ajouter au pannier");

        } catch (Exception $e) {
            $this->addFlash("error","Une erreur est survenue lors de l'ajout de l'article au pannier");
        }

        return $this->redirectToRoute('main_accueil');

    }


}
