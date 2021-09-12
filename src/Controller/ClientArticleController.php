<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\ArticleBDD;
use App\Service\FiltreArticleBDD;
use App\Service\ProduitBDD;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/recherche/{nomProduit}" , name="recherche")
     */
    public function recherche(String $nomProduit, RepertoirRepository $repertoirRepository, ProduitBDD $produitBDD, FiltreArticleBDD  $filtreArticleBDD) {

        $produit = $repertoirRepository->findOneBy(["nom"=>$nomProduit]) ;
        $infoGroup = str_split($produit->getIsGroup());

        $info = $produitBDD->info($nomProduit);
        $i = 0;
        $colonneInfo['Nom'] = ["Type"=>"varchar(50)","isGroup"=>"0"];
        foreach ($info as $infoColonne){
            $colonneInfo[$infoColonne["Field"]]['Type'] = $infoColonne["Type"];
            $colonneInfo[$infoColonne["Field"]]['isGroup'] = $infoGroup[$i];
            $i++;
        }
        $colonneInfo['Prix Max']=$colonneInfo['Prix'] ;
        unset($colonneInfo['id']); unset($colonneInfo['Modele']); unset($colonneInfo['Stock']); unset($colonneInfo['idEtat']);
        unset($colonneInfo['Description']); unset($colonneInfo['Prix']);

        foreach ($colonneInfo as $nomColonne => $infoColonne){
            if ($infoColonne['isGroup']) {
                $colonneInfo[$nomColonne]['Valeurs'] = $filtreArticleBDD->getValeurColonneGroup($nomProduit,$nomColonne);
            }
        }


        if(count($_REQUEST)>0) {
            foreach ($colonneInfo as $nom=>$colonne) {
                if (array_key_exists(str_replace(' ','_',$nom),$_REQUEST)) {
                    $colonneInfo[$nom]['filtre'] = $_REQUEST[str_replace(' ','_',$nom)];
                } else {
                    $colonneInfo[$nom]['filtre'] = "" ;
                }
            }
            $listeArticle = $filtreArticleBDD->getArticles($colonneInfo,$produit);
        } else {
            $listeArticle = $filtreArticleBDD->randomGet_AvecGroup(18,[$produit]) ;
        }

        return $this->render('clientArticle/recherche.html.twig', compact('produit','colonneInfo', 'listeArticle')) ;

    }



}