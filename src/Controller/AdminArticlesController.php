<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\ArticleBDD;
use App\Service\Connexion;
use App\Service\ProduitBDD;
use Exception;
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
    public function accueil(String $nomProduit, int $idArticle, bool $isModification, ProduitBDD $produitBDD){

        //recupère tout se qui est enregistrer dans la table
        $listeArticle = $produitBDD->get_JoinEtat($nomProduit) ;

        //récupère tout les etat de vente différent d'un article
        $listeEtat = $produitBDD->get('Etat');

        //récupère tout les nom de colone de la table
        $info = $produitBDD->info($nomProduit);
        $listeColonne = [];
        foreach ($info as $coloneInfo){
            if ($coloneInfo['Field']=='vip') { $isVIP = $coloneInfo['Default'] ; }
            elseif ($coloneInfo['Field']!='id' and $coloneInfo['Field']!='idEtat') { $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ; }
        }
        $listeColonne['Statut'] = "varchar(30)";
        $listeColonne['Images'] = "varchar(30)";

        //Compte le nombre de photo par article
        $resultat = $produitBDD->getNombrePhoto_ParArticle($nomProduit) ;
        $infoImage = [] ;
        foreach ($resultat as $info){
            $infoImage[$info[0]] = $info['COUNT(*)'] ;
        }
        $i=0;
        foreach ($listeArticle as $article) {
            if (array_key_exists($article[0] , $infoImage) ) {
                $article['Images'] = $infoImage[$article[0]] ;
            } else {
                $article['Images'] = 0 ;
            }
            $listeArticle[$i] = $article ;
            $i++;
        }

        return $this->render("adminArticles/accueil.html.twig", compact('listeArticle','nomProduit', 'listeColonne','isVIP','idArticle','isModification','listeEtat'));

    }

    /**
     * @Route("/ajouter/{nomProduit}", name="ajouter")
     */
    public function ajouter(String $nomProduit, ArticleBDD $articleBDD, Request $request){
        try {

            //Initialise la commande sql
            $idNewArticle= $articleBDD->ajouter($nomProduit,$request) ;

            //enregistre Les Photos
            foreach ($_FILES["repertoir_image"]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES["repertoir_image"]["tmp_name"][$key];
                    $filename = basename($_FILES["repertoir_image"]["name"][$key]);
                    $folder = "../public/uploads/photos/" . $filename;

                    $articleBDD->ajouterImage($idNewArticle,$nomProduit,$filename);

                    move_uploaded_file($tmp_name, $folder);
                }
            }

            $this->addFlash('success',"l'article a bien été ajouté");

        } catch (\Exception $e) {
            $this->addFlash('error',"l'article n'a pas pue etre ajouté");
        }

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0,"idArticle"=>$idNewArticle]);

    }

    /**
     * @Route("/supprimer/{nomProduit}/{idArticle}", name="supprimer")
     */
    public function supprimer(String $nomProduit, int $idArticle, ArticleBDD $articleBDD){

        try {

            $articleBDD->supprimer($nomProduit,$idArticle);

            $this->addFlash('success',"l'article a bien été supprimé");

        } catch (Exception $e) {
            $this->addFlash('error',"l'article n'a pas pue etre supprimé");
        }

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0]);
    }

    /**
     * @Route("/modifier/{nomProduit}/{idArticle}", name="modifier")
     */
    public function modifier(String $nomProduit, int $idArticle, ArticleBDD $articleBDD, Request $request){

        try {

            $articleBDD->modifier($nomProduit,$idArticle,$request);

            //enregistre Les Photos
            foreach ($_FILES["repertoir_image"]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {

                    $tmp_name = $_FILES["repertoir_image"]["tmp_name"][$key];
                    $filename = basename($_FILES["repertoir_image"]["name"][$key]);
                    $folder = "../public/uploads/photos/" . $filename;

                    $articleBDD->ajouterImage($idArticle,$nomProduit,$filename);

                    move_uploaded_file($tmp_name, $folder);
                }
            }

            $this->addFlash('success',"l'article a bien été modifié");

        } catch (Exception $e) {
            $this->addFlash('error',"l'article n'a pas pue etre modifié");
        }

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0]);

    }

    /**
     * @Route("/ChangeVIP/{nomProduit}", name="ChangeVIP")
     */
    public function ChangeVIP(String $nomProduit, ProduitBDD $produitBDD, Request $request) {

        $isVIP = $request->get('newVIP')?0:1 ;

        $produitBDD->changeVIP($nomProduit,$isVIP);

        return $this->redirectToRoute('adminArticle_accueil',["nomProduit"=>$nomProduit,"isModification"=>0]);
    }
}
