<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class EvenementBDD {

    public function findAll() {

        $query = "SELECT * FROM evenement";
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();

    }

    public function findAllEmplacement() {

        $query = "SELECT * FROM emplacement";
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();

    }

    public function creer(array $saisieListe, String $filename) {

        $query = "INSERT INTO evenement(`nom`, `descritpion`, `image`) VALUES (
                                                             '".$saisieListe['nom']."',
                                                             '".$saisieListe['description']."',
                                                             '".$filename."')" ;
        $GLOBALS['pdo']->exec($query);

    }

    public function ajouterLiens(array $saisieListe) {

        foreach ($saisieListe as $nomModele=>$value) {
            if ($nomModele != "nomProduit" && $nomModele!="nomEvenement") {
                $query = "INSERT INTO article_evenement VALUES ('".$nomModele."','".$saisieListe['nomEvenement']."','".$saisieListe['nomProduit']."')";
                $GLOBALS['pdo']->exec($query);
            }
        }

    }

    public function supprimer(String $nomEvenement)
    {
        $query = "DELETE FROM evenement WHERE nom='".$nomEvenement."'" ;
        $GLOBALS['pdo']->exec($query);

        $query = "DELETE FROM article_evenement WHERE nomEvenement='".$nomEvenement."'" ;
        $GLOBALS['pdo']->exec($query);
    }

    public function enregistrerPlacement(array $saisieListe) {

        foreach ($saisieListe as $numEmplacement=>$idEvenement) {

            $query = "UPDATE emplacement SET idEvenement = ".$idEvenement." WHERE id=".$numEmplacement ;
            $GLOBALS['pdo']->exec($query);

        }
    }

    public function getPhotoParEmplacement(){

        $query="SELECT emp.id,ev.image FROM emplacement as emp 
                LEFT JOIN evenement as ev ON emp.idEvenement = ev.id ";
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();

    }
}