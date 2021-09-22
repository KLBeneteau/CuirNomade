<?php

namespace App\Controller;

use App\Repository\RepertoirRepository;
use App\Service\ArticleBDD;
use App\Service\EvenementBDD;
use App\Service\PanierBDD;
use Symfony\Bridge\PhpUnit\Legacy\ExpectDeprecationTraitBeforeV8_4;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EvenementController extends AbstractController {

    /**
     * @Route("/admin/accueil/" , name="Evenement_admin_accueil")
     */
    public function adminAccueil(EvenementBDD $evenementBDD){

        $listEvenement = $evenementBDD->findAll() ;
        $listEmplacement = $evenementBDD->findAllEmplacement() ;

        return $this->render('evenement/admin/accueil.html.twig', compact('listEvenement','listEmplacement'));
    }

    /**
     * @Route("/admin/creer/" , name="Evenement_admin_creer")
     */
    public function adminCreer(RepertoirRepository $repertoirRepository){

        $listProduit = $repertoirRepository->findAll() ;

        return $this->render('evenement/admin/creer.html.twig', compact('listProduit')) ;
    }

    /**
     * @Route("/admin/creer_suite/" , name="Evenement_admin_creerSuite")
     */
    public function adminCreerSuite(ArticleBDD $articleBDD, EvenementBDD $evenementBDD){

        try {

            if ($_FILES['image']['error'] == UPLOAD_ERR_OK ) {

                $tmp_name = $_FILES["image"]["tmp_name"];
                $filename = basename($_FILES["image"]["name"]);
                $folder = "../public/uploads/evenement/" . $filename;

                move_uploaded_file($tmp_name, $folder);
            }
            $idEvenement = $evenementBDD->creer($_REQUEST,$filename);

            $nomProduit = $_REQUEST['produit'] ;

            $listArticle = $articleBDD->getAllModele($_REQUEST['produit']) ;
            $this->addFlash('success',"l'évenement a bien été créé");

            return $this->render('evenement/admin/creerSuite.html.twig', compact('listArticle','idEvenement','nomProduit')) ;


        } catch (\Exception $e) {
            $this->addFlash('error',"l'évenement n'a pas pue être enregistré");
            return $this->redirectToRoute("Evenement_admin_creer") ;
        }

    }

    /**
     * @Route("/admin/ajouter/" , name="Evenement_admin_ajouter")
     */
    public function adminAjouter(EvenementBDD $evenementBDD){

        $evenementBDD->ajouterLiens($_REQUEST);

        return $this->redirectToRoute("Evenement_admin_accueil") ;

    }

    /**
     * @Route("/admin/supprmier/" , name="Evenement_admin_supprmier")
     */
    public function adminSupprimer(EvenementBDD $evenementBDD){

        try {
            $evenementBDD->supprimer($_REQUEST["idEvenement"]) ;

            $this->addFlash('success',"L'évenement a bien été supprimé");
        } catch (\Exception $e) {
            $this->addFlash('error',"L'evenement n'a pas pue être supprimé");
        }

        return $this->redirectToRoute('Evenement_admin_accueil');

    }

    /**
     * @Route("/client/enregistrer" , name="Evenement_admin_enregistrer")
     */
    public function adminEnregistrer(EvenementBDD $evenementBDD){

        try {
            $evenementBDD->enregistrerPlacement($_REQUEST) ;

            $this->addFlash('success',"Les emplacements ont bien été enregistré");
        } catch (\Exception $e) {
            $this->addFlash('error',"Echec de l'attribution des emplacements");
        }

        return $this->redirectToRoute('Evenement_admin_accueil');


    }

    /**
     * @Route("/client/afficher/{idEvenement}" , name="Evenement_client_afficher")
     */
    public function clientAfficher(EvenementBDD $evenementBDD, int $idEvenement){

        $evenement = $evenementBDD->findById($idEvenement) ;

        return $this->render('evenement/client/afficher.html.twig',compact('evenement')) ;


    }

}