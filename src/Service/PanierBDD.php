<?php

namespace App\Service;

use App\Repository\RepertoirRepository;
use Symfony\Component\HttpFoundation\Request;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class PanierBDD {

    public function ajouter(int $idUser, $article,Request $request){

        $query = "INSERT INTO pannier VALUES (?,?,?,?)" ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->bindValue(1,$idUser);
        $prep->bindValue(2,$article[0]);
        $prep->bindValue(3,$request->get('nomProduit'));
        $prep->bindValue(4,$request->get('nombreArticle'));

        $prep->execute();

    }

    public function getArticlePanier($idUser, RepertoirRepository $repertoirRepository){

        $query = "Select * FROM Pannier WHERE idClient = ".$idUser ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        $contenuePanier = $prep->fetchAll() ;

       if ($contenuePanier != []) {

           $articleBDD = new ArticleBDD() ;
           $produitBDD = new ProduitBDD() ;
           $i = 0;
           foreach ($contenuePanier as $article) {
               $listeArticle[$i] = $articleBDD->getOneByID($article['idArticle'],$article['tableArticle']) ;
               $listeArticle[$i]['nombre'] = $article['nombreArticle'];
               $listeArticle[$i]['colonneGroup'] = $produitBDD->getNomColonneGroup($repertoirRepository->findOneBy(["nom"=>$article['tableArticle']]));
               $i++;
           }

           return $listeArticle;
       } else {
           return $contenuePanier ;
       }
    }

}
