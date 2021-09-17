<?php

namespace App\Service;

use App\Repository\RepertoirRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Repertoir ;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class FiltreArticleBDD {

    public function  randomGet_SansGroup(int $nombre, $repertoir): array
    {

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

   public function randomGet_AvecGroup(int $nombre, $repertoir): array
   {

       $listeArticle_sansGroup = $this->randomGet_SansGroup($nombre,$repertoir) ;
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

   public  function get_AvecGroup($article_SansGroup,$produit): array
   {
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

   public function getValeurColonneGroup(String $nomProduit, String $nomColonne) {

       $query = "SELECT DISTINCT ".$nomColonne." FROM ".$nomProduit. " ORDER BY ".$nomColonne. " ASC";
       $prep= $GLOBALS['pdo']->prepare($query);
       $prep->execute();
       $result = $prep->fetchAll() ;

       foreach ($result as $ligne) {
           $return[] = $ligne[0] ;
       }

       return $return ;

   }

    public function getAvecFiltres_SansGroup(array $infoFiltre, Repertoir $produit) {

        $filtre="";

        foreach ($infoFiltre as $nomColonne=>$infoColonne) {
            if ($infoColonne["filtre"]!="") {
               if (preg_match("/varchar/",$infoColonne["Type"])) {
                   if ($nomColonne == "Nom") {
                       $filtre.= "( t.Modele LIKE ? OR t.Description LIKE ? ) AND ";
                   } else {
                       $filtre.= "t.".$nomColonne." LIKE ? AND ";
                   }
               }
               if ( preg_match("/int/",$infoColonne["Type"])) {
                   if ($nomColonne == "Prix Max") {
                       $filtre.= "t.Prix <= ? AND ";
                   } else {
                       $filtre.= "t.".$nomColonne." = ? AND ";
                   }
               }
               if (preg_match("/tinyint/",$infoColonne["Type"])) {
                   $filtre.= "t.".$nomColonne ." = ".$nomColonne["filtre"]?"TRUE":"FALSE"." AND " ;
               }
            }
        }

        $query = "SELECT * FROM ".$produit->getNom()." as t
                INNER JOIN image as i
                INNER JOIN etat as e
                WHERE ".$filtre." t.id = i.idArticle AND i.nomTable = '".$produit->getNom()."' AND e.id = t.idEtat AND e.Statut = 'EN_VENTE'
                GROUP BY t.Modele ";
        $prep= $GLOBALS['pdo']->prepare($query);
        $i = 1;
        foreach ($infoFiltre as $nomColonne=>$infoColonne) {
            if ($infoColonne["filtre"]!="") {
                if (preg_match("/varchar/",$infoColonne["Type"])) {
                    $prep->bindValue($i,"%".$infoColonne["filtre"]."%");
                    if ($nomColonne == "Nom") {
                        $i++ ;
                        $prep->bindValue($i,"%".$infoColonne["filtre"]."%");
                    }
                } else {
                    $prep->bindValue($i,$infoColonne["filtre"]);
                }
                $i++;
            }
        }

        $prep->execute();
        return $prep->fetchAll() ;
    }

}
