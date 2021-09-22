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

        $query = "INSERT INTO evenement(`nom`, `descritpion`, `image`,`nomProduit`) VALUES (
                                                             '".$saisieListe['nom']."',
                                                             '".$saisieListe['description']."',
                                                             '".$filename."',
                                                             '".$saisieListe['produit']."')" ;
        $GLOBALS['pdo']->exec($query);
        return $GLOBALS['pdo']->lastInsertId() ;

    }

    public function ajouterLiens(array $saisieListe) {

        foreach ($saisieListe as $nomModele=>$value) {
            $nomModele = str_replace('_',' ',$nomModele);
            if ($nomModele != "nomProduit" && $nomModele!="idEvenement") {
                $query = "INSERT INTO article_evenement VALUES ('".$nomModele."','".$saisieListe['idEvenement']."')";
                $GLOBALS['pdo']->exec($query);
            }
        }

    }

    public function supprimer(int $idEvenement)
    {
        $query = "DELETE FROM evenement WHERE id='".$idEvenement."'" ;
        $GLOBALS['pdo']->exec($query);

        $query = "DELETE FROM article_evenement WHERE idEvenement='".$idEvenement."'" ;
        $GLOBALS['pdo']->exec($query);
    }

    public function enregistrerPlacement(array $saisieListe) {

        foreach ($saisieListe as $numEmplacement=>$idEvenement) {

            $query = "UPDATE emplacement SET idEvenement = ".$idEvenement." WHERE id=".$numEmplacement ;
            $GLOBALS['pdo']->exec($query);

        }
    }

    public function getPhotoParEmplacement(){

        $query="SELECT emp.id,ev.image,ev.id FROM emplacement as emp 
                LEFT JOIN evenement as ev ON emp.idEvenement = ev.id ";
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();

    }

    public function findById(int $idEvenement) {

        $query="SELECT * FROM evenement WHERE id = ".$idEvenement;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        $evenement = $prep->fetch();


        $query="SELECT a.*,e.*,i.* FROM ".$evenement['nomProduit']." as a
                INNER JOIN article_evenement as a_e ON a.Modele = a_e.nomArticle
                INNER JOIN image as i
                INNER JOIN etat as e
                WHERE a_e.idEvenement = ".$idEvenement." 
                        AND a.id = i.idArticle AND i.nomTable = '".$evenement['nomProduit']."'
                        AND e.id = a.idEtat AND e.Statut = 'EN_VENTE'
                GROUP BY a.Modele" ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        $evenement['listArticle'] = $prep->fetchAll();

        return $evenement ;

    }
}