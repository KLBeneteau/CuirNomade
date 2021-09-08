<?php

namespace App\Service;

use App\Repository\RepertoirRepository;
use Symfony\Component\HttpFoundation\Request;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class FiltreArticleBDD {

    public function  randomGet_SansGroup(int $nombre, $repertoir){

        if (count($repertoir)==1) {

            $query = "SELECT * FROM ".$repertoir[0]->getNom()." as t
                      INNER JOIN image as i
                      INNER JOIN etat as e
                      WHERE t.id = i.idArticle AND i.nomTable = '".$repertoir[0]->getNom()."' 
                            AND e.id = t.idEtat AND e.Statut = 'EN_VENTE'
                      GROUP BY t.Modele
                      ORDER BY RAND() LIMIT ".$nombre ;
            $prep= $GLOBALS['pdo']->prepare($query);
            $prep->execute();

            return $prep->fetchAll() ;

        } else {
            $listArticle = [] ;
            foreach ($repertoir as $table){
                $listArticle = array_merge($listArticle,$this->randomGet_SansGroup((int)($nombre/count($repertoir)),[$table]));
            }
            return $listArticle ;
        }

    }

   public function randomGet_AvecGroup(int $nombre, $repertoir) {

       $listeArticle_sansGroup = $this->randomGet_SansGroup($nombre,$repertoir,[]) ;
        $listeArticle_AvecGroup = [] ;
       foreach ($repertoir as $produit) {
           $tab = [];
           foreach ($listeArticle_sansGroup as $article) {
               if ($produit->getNom() == $article["nomTable"]) {
                   $tab[] = $article ;
               }
           }
           $result = $this->get_AvecGroup($tab,$produit) ;
           foreach ($result as $article) {
               $listeArticle_AvecGroup[] = $article ;
           }
       }
        return $listeArticle_AvecGroup;
   }

   public  function get_AvecGroup($article_SansGroup,$produit) {
        //j'ai décider de ne pas prendre les info group / modèle car trop de requete SQL
        //Je récup toute les info et les traitre ensuite

        //config SQL dynamique
       $infoCharGroup = str_split($produit->getIsGroup());

       $produitBDD = new ProduitBDD();
       $infoProduit = $produitBDD->info($produit->getNom());

       $listArticleTrier = [] ;
       $where = "" ;
       foreach ($article_SansGroup as $article) {
           $where.= "Modele = '".$article["Modele"]. "' OR " ;
           $listArticleTrier[$article["Modele"]] = [] ;
       }
       $where = substr($where,0,strlen($where)-3);

       $colonneAGroup = [] ;
       $select = "" ;
       foreach ($infoCharGroup as $key=>$bool) {
           if ($bool) {
               $select .= $infoProduit[$key]["Field"] ."," ;
               $colonneAGroup[] = $infoProduit[$key]["Field"] ;
               foreach ($listArticleTrier as $modele=>$listCharGroup) {
                   $listArticleTrier[$modele][$infoProduit[$key]["Field"]] = array();
               }
           }
       }
       $select = substr($select,0,strlen($select)-1) ;

       //récupère toute les valeurs
       $query = "SELECT Modele,".$select." 
                 FROM ".$produit->getNom() ."
                 WHERE ".$where;
       $prep= $GLOBALS['pdo']->prepare($query);
       $prep->execute();
       $listArticleBrut = $prep->fetchAll() ;

       //les classes
       foreach($listArticleBrut as $unArticle) {
           foreach ($colonneAGroup as $colonne) {
               if (!in_array($unArticle[$colonne],$listArticleTrier[$unArticle['Modele']][$colonne])){
                   $listArticleTrier[$unArticle['Modele']][$colonne][] = $unArticle[$colonne] ;
               }
           }
       }

       //les traites
       foreach($article_SansGroup as $key=>$article) {
           foreach ($colonneAGroup as $colonne) {
               $article_SansGroup[$key][$colonne] = $listArticleTrier[$article['Modele']][$colonne] ;
           }
       }

       return $article_SansGroup;
   }

}
