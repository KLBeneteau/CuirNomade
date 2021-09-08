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
            $listeArticle[$table->getNom()] = $filtreArticleBDD->randomGet_AvecGroup(10,[$table]) ;
        }

        return $this->render('clientArticle/accueil.html.twig', compact('listeArticle', 'repertoir')) ;

    }


    /**
     * @Route("/detail/{nomProduit}/{idArticle}" , name="detail")
     */
    public function detail(String $nomProduit, int $idArticle, ArticleBDD $articleBDD, ProduitBDD  $produitBDD) {

        //récupère tout les nom de colone de la table
        $info = $produitBDD->info($nomProduit);
        $listeColonne = [];
        foreach ($info as $coloneInfo){
            if ($coloneInfo['Field']!='id' and $coloneInfo['Field']!='idEtat') { $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ; }
        }
        $listeColonne['Statut'] = "varchar(30)";

        $article = $articleBDD->get($nomProduit,$idArticle) ;
        $article['nomTable'] = $nomProduit ;

        return $this->render('clientArticle/detail.html.twig', compact('article','listeColonne')) ;
    }



}