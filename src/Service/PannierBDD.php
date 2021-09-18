<?php

namespace App\Service;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class PannierBDD {

    public function ajouter(int $idClient, int $idArticle, String $nomTable, int $nombreArticle = 1 ){}
    public function supprimer(int $idClient, int $idArticle, String $nomTable){}
    public function modifier(int $idClient, int $idArticle, String $nomTable, int $NewNombreArticle ){}

}
