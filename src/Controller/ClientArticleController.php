<?php

namespace App\Controller;

use App\Service\ArticleBDD;
use App\Service\ProduitBDD;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/client/article", name="clientArticle_")
 */
class ClientArticleController extends AbstractController {

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