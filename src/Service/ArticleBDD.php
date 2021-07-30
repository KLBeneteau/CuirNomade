<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class ArticleBDD {

    public function ajouter(String $nomProduit, Request $request){

        //récupère tout les nom de colone de la table
        $produitBDD = new ProduitBDD();
        $info = $produitBDD->info($nomProduit);
        $listeColonne = [];
        foreach ($info as $coloneInfo){
            $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ;
        }
        unset($listeColonne['vip']);
        unset($listeColonne['id']);

        //Initialise la commande sql
        $query = "INSERT INTO ".$nomProduit."(" ;
        foreach ($listeColonne as $nomColone=>$uniteColone){
            $query.= $nomColone.',' ;
        }
        $query = rtrim($query,',') ;
        $query .= ') VALUES (';
        foreach ($listeColonne as $nomColone=>$uniteColone){
            if (substr($uniteColone,0,7) == 'varchar')
            { $query.= "'".$request->get($nomColone)."'," ; }
            else if (substr($uniteColone,0,7) == 'tinyint')
            { $query.= ($request->get($nomColone)?1:0).',' ; }
            else { $query.= $request->get($nomColone).',' ; }
        }
        $query = rtrim($query,',') ;
        $query .= ')' ;

        //l'execute
        $GLOBALS['pdo']->exec($query);

        //renvoie l'id générer
        return $GLOBALS['pdo']->lastInsertId() ;

    }

    public function modifier(String $nomProduit, int $idArticle, Request $request){

        //récupère tout les nom de colone de la table
        $produitBDD = new ProduitBDD();
        $info = $produitBDD->info($nomProduit);
        $listeColonne = [];
        foreach ($info as $coloneInfo){
            $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ;
        }
        unset($listeColonne['vip']);
        unset($listeColonne['id']);

        //Initialise la commande sql
        $query = "UPDATE ".$nomProduit." SET "  ;
        foreach ($listeColonne as $nomColone=>$uniteColone){
            if (substr($uniteColone,0,7) == 'varchar')
                $query.= $nomColone."='".$request->get($nomColone)."'," ;
            else if (substr($uniteColone,0,7) == 'tinyint')
                $query.= $nomColone."=".($request->get($nomColone)?1:0)."," ;
            else
                $query.= $nomColone."=".$request->get($nomColone)."," ;
        }
        $query = rtrim($query,',') ;
        $query .= " WHERE id=".$idArticle ;

        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->bindValue(1, $idArticle);
        $prep->execute();

    }

    public function ajouterImage(int $idArticle, String $nomProduit, String $nomPhoto) {

        $query = "INSERT INTO image (idArticle,nomTable,nomImage) VALUES (" . $idArticle . ",'" . $nomProduit . "','" . $nomPhoto . "')";
        $GLOBALS['pdo']->exec($query);

    }

    public  function supprimer(String $nomProduit, int $idArticle){

        $query = "DELETE FROM image WHERE idArticle =".$idArticle." AND nomTable ='".$nomProduit."'";
        $GLOBALS['pdo']->exec($query);

        $query = "DELETE FROM ".$nomProduit." WHERE id = ?" ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->bindValue(1, $idArticle);
        $prep->execute();

    }

}