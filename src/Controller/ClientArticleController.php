<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\ArticleBDD;
use App\Service\FiltreArticleBDD;
use App\Service\ProduitBDD;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/client/article", name="clientArticle_")
 */
class ClientArticleController extends AbstractController {

    /**
     * @Route("/accueil" , name="accueil")
     */
    public function accueil(RepertoirRepository $repertoirRepository, FiltreArticleBDD $filtreArticleBDD) {

        $repertoir = $repertoirRepository->findAll() ;

        foreach ($repertoir as $table) {
            $listeArticle[$table->getNom()] = $filtreArticleBDD->randomGet_AvecGroup(6,[$table]) ;
        }

        return $this->render('clientArticle/accueil.html.twig', compact('listeArticle', 'repertoir')) ;

    }


    /**
     * @Route("/detail/{nomProduit}/{modele}" , name="detail")
     */
    public function detail(String $nomProduit, String $modele, ArticleBDD $articleBDD, RepertoirRepository $repertoirRepository, ProduitBDD $produitBDD) {

        $produit = $repertoirRepository->findOneBy(["nom"=>$nomProduit]);

        $article = $articleBDD->get($produit,$modele) ; $article = $article[0];

        $info = $produitBDD->info($nomProduit);
        $infoGroup = str_split($produit->getIsGroup()) ;
        $i = 0;
        foreach ($info as $coloneInfo) {
            if ($coloneInfo['Field']!='id' and $coloneInfo['Field']!='idEtat' and $coloneInfo['Field']!='Stock') {
                $listeColonne[$coloneInfo['Field']] = $infoGroup[$i] ;
            }
            $i++ ;
        }

        return $this->render('clientArticle/detail.html.twig', compact('article','listeColonne','produit')) ;
    }



}