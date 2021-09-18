<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class PannierBDD {

    public function ajouter(int $idUser, $article,Request $request){

        $query = "INSERT INTO pannier VALUES (?,?,?,?)" ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->bindValue(1,$idUser);
        $prep->bindValue(2,$article[0]);
        $prep->bindValue(3,$request->get('nomProduit'));
        $prep->bindValue(4,$request->get('nombreArticle'));

        $prep->execute();

    }
    public function supprimer(){}
    public function modifier(){}

}
