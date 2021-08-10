<?php

namespace App\Service;

use App\Repository\RepertoirRepository;
use Symfony\Component\HttpFoundation\Request;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class FiltreArticleBDD {

   public function randomGet(int $nombre, $table ){

       if (count($table)==1) {

           $query = "SELECT * FROM ".$table[0]->getNom()." as t
                      INNER JOIN image as i
                      WHERE t.id = i.idArticle AND i.nomTable = '".$table[0]->getNom()."'
                      GROUP BY t.Modele, t.Couleur 
                      ORDER BY RAND() LIMIT ".$nombre ;
           $prep= $GLOBALS['pdo']->prepare($query);
           $prep->execute();
           return $prep->fetchAll();

       } else {
            //Rapelle la fonction en découpant la liste
           $nombreArticleParTable = intdiv($nombre,count($table)) ;  ;
           $tableArticle = $this->randomGet($nombreArticleParTable+ceil(fmod($nombre,count($table))),array($table[0])) ;
           for($i=1; $i<count($table); $i++){
               $articleAAjouter = $this->randomGet($nombreArticleParTable,array($table[$i])) ;
               foreach ($articleAAjouter as $article)
               $tableArticle[] = $article ;
           }
       }

       return $tableArticle ;
   }

}
