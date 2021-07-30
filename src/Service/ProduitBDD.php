<?php

namespace App\Service;

use App\Service\Connexion;
use PhpParser\Node\Scalar\String_;
use Symfony\Bundle\MakerBundle\Str;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class ProduitBDD {

    public function creer(String $nom, String $VIP) {

        $query = 'CREATE TABLE '.$nom.'
                             ( id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
                             Modele VARCHAR(50) NOT NULL,
                             Prix INT NOT NULL,
                             Description VARCHAR(300),
                             Stock INT NOT NULL,
                             vip tinyint(1) NOT NULL DEFAULT '.$VIP.',
                             Couleur VARCHAR(50) NOT NULL,
                             idEtat INT NOT NULL,
                            CONSTRAINT fk_IdEtat FOREIGN KEY (idEtat) REFERENCES etat(id)) ' ;

        $GLOBALS['pdo']->exec($query);

    }

    public function get(String $nom) {
        $query = "SELECT * FROM ".$nom ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function info(String $nom) {


        //récupère tout les nom de colone de la table
        $query = "DESCRIBE ".$nom;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function get_JoinEtat(String $nom) {

        //Récupère tout se qui est enregistrer dans la table
        $query = "SELECT * FROM ".$nom.'
                  LEFT JOIN Etat ON Etat.id = '.$nom.'.idEtat';
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();;
        return $prep->fetchAll();

    }

    public function supprimer(String $nom) {

        $query = 'DROP TABLE '.$nom ;
        $GLOBALS['pdo']->exec($query);

    }

    public function addColone(String $nomProduit, String $nomColonne, String $uniteColonne) {

        $query = "ALTER TABLE ".$nomProduit."
                     ADD ".$nomColonne." ".$uniteColonne ;
        if ($uniteColonne == 'varchar') { $query.='(255)' ; }

        $GLOBALS['pdo']->exec($query);

    }

    public function supprColonne(String $nomProduit, String $nomColonne) {

        $query = "ALTER TABLE ".$nomProduit."
                        DROP COLUMN ".$nomColonne ;
        $GLOBALS['pdo']->exec($query);

    }

    public function getNombrePhoto_ParArticle(String $nom) {

        $query = "SELECT idArticle,COUNT(*) FROM image WHERE nomTable = '".$nom."' GROUP BY idArticle ";
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function changeVIP(String $nom, String $VIP){

        $query = "UPDATE ".$nom." SET Vip = ".$VIP ;
        $GLOBALS['pdo']->exec($query);

        $query = "ALTER TABLE ".$nom." ALTER Vip SET DEFAULT ".$VIP;
        $GLOBALS['pdo']->exec($query);

    }
}