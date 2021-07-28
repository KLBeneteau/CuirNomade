<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\Connexion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/article", name="adminArticle_")
 */
class AdminArticlesController extends AbstractController {

    /**
     * @Route("/accueil/{nomProduit}/{isModification}/{idArticle<\d+>?0}" , name="accueil")
     */
    public function accueil(String $nomProduit, int $idArticle, bool $isModification, Connexion $connexion){

        $pdo = $connexion->createConnexion() ;

        //Récupère tout se qui est enregistrer dans la table
        $query = "SELECT * FROM ".$nomProduit.'
                  LEFT JOIN Etat ON Etat.id = '.$nomProduit.'.idEtat';
        $prep = $pdo->prepare($query);
        $prep->execute();;
        $listeArticle = $prep->fetchAll();


        //récupère tout les etat de vente différent d'un article
        $query = "SELECT * FROM etat" ;
        $prep = $pdo->prepare($query);
        $prep->execute();
        $listeEtat = $prep->fetchAll();

        //récupère tout les nom de colone de la table
        $query = "DESCRIBE ".$nomProduit;
        $prep = $pdo->prepare($query);
        $prep->execute();
        $resultat = $prep->fetchAll();

        $listeColonne = [];
        foreach ($resultat as $coloneInfo){
            if ($coloneInfo['Field']=='vip') { $isVIP = $coloneInfo['Default'] ; }
            elseif ($coloneInfo['Field']!='id' and $coloneInfo['Field']!='idEtat') { $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ; }
        }
        $listeColonne['Statut'] = "varchar(30)";

        return $this->render("adminArticles/accueil.html.twig", compact('listeArticle','nomProduit', 'listeColonne','isVIP','idArticle','isModification','listeEtat'));

    }

    /**
     * @Route("/ajouter/{nomProduit}", name="ajouter")
     */
    public function ajouter(String $nomProduit, Connexion $connexion, Request $request){

        try {
            $pdo = $connexion->createConnexion() ;

            //récupère tout les nom de colone de la table
            $query = "DESCRIBE ".$nomProduit;
            $prep = $pdo->prepare($query);
            $prep->execute();
            $resultat = $prep->fetchAll();

            $listeColonne = [];
            foreach ($resultat as $coloneInfo){
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

            $pdo->exec($query);

            $this->addFlash('success',"l'article a bien été ajouté");

        } catch (\Exception $e) {
            $this->addFlash('error',"l'article n'a pas pue etre ajouté");
        }

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0,"idArticle"=>$pdo->lastInsertId()]);

    }

    /**
     * @Route("/supprimer/{nomProduit}/{idArticle}", name="supprimer")
     */
    public function supprimer(String $nomProduit, int $idArticle, Connexion $connexion){

        try {
            $pdo = $connexion->createConnexion() ;

            $query = "DELETE FROM ".$nomProduit." WHERE id = ?" ;
            $prep = $pdo->prepare($query);
            $prep->bindValue(1, $idArticle);
            $prep->execute();

            $this->addFlash('success',"l'article a bien été supprimé");

        } catch (\Exception $e) {
            $this->addFlash('error',"l'article n'a pas pue etre supprimé");
        }

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0]);
    }

    /**
     * @Route("/modifier/{nomProduit}/{idArticle}", name="modifier")
     */
    public function modifier(String $nomProduit, int $idArticle, Connexion $connexion, Request $request){

        try {
            $pdo = $connexion->createConnexion() ;

            //récupère tout les nom de colone de la table
            $query = "DESCRIBE ".$nomProduit;
            $prep = $pdo->prepare($query);
            $prep->execute();
            $resultat = $prep->fetchAll();

            $listeColonne = [];
            foreach ($resultat as $coloneInfo){
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

            $prep = $pdo->prepare($query);
            $prep->bindValue(1, $idArticle);
            $prep->execute();

            $this->addFlash('success',"l'article a bien été modifié");

        } catch (\Exception $e) {
            $this->addFlash('error',"l'article n'a pas pue etre modifié");
        }

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0]);

    }

    /**
     * @Route("/ChangeVIP/{nomProduit}", name="ChangeVIP")
     */
    public function ChangeVIP(String $nomProduit, Connexion $connexion, Request $request) {

        $isVIP = $request->get('newVIP')?0:1 ;

        $pdo = $connexion->createConnexion();

        $query = "UPDATE ".$nomProduit." SET Vip = ".$isVIP ;
        $pdo->exec($query);

        $query = "ALTER TABLE ".$nomProduit." ALTER Vip SET DEFAULT ".$isVIP;
        $pdo->exec($query);

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0]);
    }
}
