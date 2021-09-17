<?php

namespace App\Service;

use App\Entity\Repertoir;
use App\Repository\RepertoirRepository;
use Symfony\Component\HttpFoundation\Request;

$connexion = new Connexion();
$GLOBALS['pdo'] = $connexion->createConnexion();

class ArticleBDD {

    public function getNombrePhoto_ParArticle(String $nom) {

        $query = "SELECT idArticle,COUNT(*) FROM image WHERE nomTable = '".$nom."' GROUP BY idArticle ";
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    public function get_JoinEtat(String $nom) {

        //Récupère tout se qui est enregistrer dans la table
        $query = "SELECT * FROM ".$nom.' as t
                  LEFT JOIN Etat as e ON e.id = t.idEtat
                  ORDER BY t.Modele ASC, t.Couleur ASC';
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();;
        return $prep->fetchAll();

    }

    public function getAll(String $nom) {
        $query = "SELECT * FROM ".$nom ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->execute();
        return $prep->fetchAll();
    }

    private function ajouterUneLigne(String $nomProduit, $valeurColonne) {

        //récupère tout les nom de colone de la table
        $produitBDD = new ProduitBDD();
        $info = $produitBDD->info($nomProduit);
        $listeColonne = [];
        foreach ($info as $coloneInfo){
            $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ;
        }
        unset($listeColonne['id']);

        //Initialise la commande sql
        $query = "INSERT INTO ".$nomProduit."(" ;
        foreach ($listeColonne as $nomColone=>$uniteColone){
            $query.= $nomColone.',' ;
        }
        $query = rtrim($query,',') ;
        $query .= ') VALUES (';
        foreach ($listeColonne as $nomColonne=>$uniteColonne){
            if (substr($uniteColonne,0,7) == 'varchar')
            { $query.= "'".$valeurColonne[$nomColonne]."'," ; }
            else if (substr($uniteColonne,0,7) == 'tinyint')
            { $query.= ($valeurColonne[$nomColonne]?1:0).',' ; }
            else { $query.= $valeurColonne[$nomColonne].',' ; }
        }
        $query = rtrim($query,',') ;
        $query .= ')' ;

        //l'execute
        $GLOBALS['pdo']->exec($query);

        //renvoie l'id générer
        return $GLOBALS['pdo']->lastInsertId() ;

    }

    private function ajoutMultiple(String $nomProduit, $infoColonnePasGrouper, $infoColonneGrouper) {

        if (count($infoColonneGrouper)>0) {

            $clef = array_key_last($infoColonneGrouper);
            $valeurs =  explode('|', $infoColonneGrouper[$clef]);
            unset($infoColonneGrouper[$clef]) ;
            foreach ($valeurs as $valeur) {
                $infoColonnePasGrouper[$clef] = $valeur ;
                $listID[] = $this->ajoutMultiple($nomProduit, $infoColonnePasGrouper, $infoColonneGrouper );
            }
            return $listID ;
        }
        else {
            return $this->ajouterUneLigne($nomProduit,$infoColonnePasGrouper);
        }

    }

    public function ajouter(String $nomProduit, Request $request, RepertoirRepository $repertoirRepository) {

        $infoGroup = str_split($repertoirRepository->findOneBy(['nom'=>$nomProduit])->getIsGroup()) ;

        $produitBDD = new ProduitBDD();
        $infoColonne = $produitBDD->info($nomProduit);

        $infoColonneGrouper = [] ;
        $infoColonnePasGrouper = [] ;
        foreach ($infoGroup as $clef => $info) {
            if ($info) {
                $infoColonneGrouper[$infoColonne[$clef]['Field']] = $request->get($infoColonne[$clef]['Field']);
            } else {
                $infoColonnePasGrouper[$infoColonne[$clef]['Field']] = $request->get($infoColonne[$clef]['Field']);
            }
        }

        $listID = $this->ajoutMultiple($nomProduit, $infoColonnePasGrouper, $infoColonneGrouper );
        while (is_array($listID[0])) {
            $listTemp = [] ;
            foreach ($listID as $tab) {
                foreach ($tab as $value) {
                    $listTemp[] = $value ;
                }
            }
            $listID = $listTemp ;
        }
        return $listID ;

    }

    public function modifier(String $nomProduit, int $idArticle, Request $request){

        //récupère tout les nom de colone de la table
        $produitBDD = new ProduitBDD();
        $info = $produitBDD->info($nomProduit);
        $listeColonne = [];
        foreach ($info as $coloneInfo){
            $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ;
        }
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

    public function get(Repertoir $produit, String $modele) {

        $query = "SELECT * FROM ". $produit->getNom() ." as a
                  INNER JOIN etat AS e
                  WHERE a.Modele = ? AND e.id = a.idEtat 
                  LIMIT 1" ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->bindValue(1, $modele);
        $prep->execute();
        $article = $prep->fetch();

        $query = "SELECT nomImage FROM Image WHERE nomTable = ? AND idArticle = ? " ;
        $prep = $GLOBALS['pdo']->prepare($query);
        $prep->bindValue(1, $produit->getNom());
        $prep->bindValue(2, $article[0]);
        $prep->execute();
        $listeImage = $prep->fetchall();

        foreach ($listeImage as $image) {
            $article['image'][] = $image['nomImage'] ;
        }

        $filtreArticleBDD = new FiltreArticleBDD() ;

        return $filtreArticleBDD->get_AvecGroup([$article],$produit) ;
    }

}