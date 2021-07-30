<?php

namespace App\Controller;

use App\Entity\Repertoir;
use App\Repository\BaboucheRepository;
use App\Repository\RepertoirRepository;
use App\Service\Connexion;
use App\Service\CreationProduit;
use App\Service\ProduitBDD;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/produit", name="adminProduit_")
 */
class AdminProduitsController extends AbstractController {

    /**
     * @Route("/accueil", name="accueil")
     */
    public function accueil(RepertoirRepository $repertoirRepository){

        $listeProduits = $repertoirRepository->findAll();

        if ($listeProduits)
            return $this->redirectToRoute('adminProduit_affichage',['nomProduit'=>$listeProduits[0]->getNom()]);
        else
            return $this->redirectToRoute('adminProduit_creer');
    }

    /**
     * @Route("/creer", name="creer")
     */
    public function creer(RepertoirRepository $repertoirRepository,
                          Request $request,
                          ProduitBDD $produitBDD,
                          EntityManagerInterface $entityManager)
    {

        $nomProduit = str_replace(' ','',ucwords($request->get("nom")," \t\r\n\f\v "));
        if ($request->get("isVIP")) {
            $VIP = 1;
        } else {
            $VIP = 0;
        }

        //Si le formulaire est envoyer
        if ($nomProduit) {
           // try {

                $produitBDD->creer($nomProduit,$VIP);

                $newRepertoir = new Repertoir($nomProduit);
                $entityManager->persist($newRepertoir);
                $entityManager->flush();

                $this->addFlash("success","le produit $nomProduit été créer");
                return $this->redirectToRoute('adminProduit_modifier',['nomProduit'=>$nomProduit]);

            /* } catch (\Exception $e) {
                $this->addFlash("error","Le produit $nomProduit n'a pas pu etre créé");
            } */
        }

        $listeProduits = $repertoirRepository->findAll();
        return $this->render("adminProduits/creer.html.twig", compact('listeProduits')) ;
    }

    /**
     * @Route("/affichage/{nomProduit}", name="affichage")
     */
    public function affichage(RepertoirRepository $repertoirRepository,
                              ProduitBDD $produitBDD,
                              string $nomProduit){

        $listeArticle = $produitBDD->get_JoinEtat($nomProduit) ;
        $info = $produitBDD->info($nomProduit) ;

        $listeColonne = [];
        foreach ($info as $coloneInfo){
            if ($coloneInfo['Field']=='vip') { $isVIP = $coloneInfo['Default'] ; }
            elseif ($coloneInfo['Field']!='id' and $coloneInfo['Field']!='idEtat') { $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ; }
        }
        $listeColonne['Statut'] = "varchar(30)";

        $listeProduits = $repertoirRepository->findAll();
        return $this->render("adminProduits/affichage.html.twig", compact('listeProduits','listeColonne', 'listeArticle','nomProduit','isVIP')) ;
    }

    /**
     * @Route("/supprimer/{nomProduit}", name="supprimer")
     */
    public function supprimer(Request $request,
                              RepertoirRepository $repertoirRepository,
                              String $nomProduit, ProduitBDD $produitBDD,
                              EntityManagerInterface $entityManager){

        if ($request->get('suppression')) {
            try {

                //Supprime l'enregistrement dans Repertoir
                $entityManager->remove($repertoirRepository->findOneBy(['nom'=>$nomProduit]));
                $entityManager->flush();

                //Supprime la BDD
                $produitBDD->supprimer($nomProduit);

                $this->addFlash('success','Le produit '.$nomProduit.' a bien été supprimé');

            } catch (\Exception $e) {
                $this->addFlash('error','Le produit '.$nomProduit." n'a pas pue etre supprimé");
            }

            return $this->redirectToRoute('adminProduit_accueil');
        }

        $listeProduits = $repertoirRepository->findAll();
        return $this->render("adminProduits/supprimer.html.twig", compact('listeProduits','nomProduit'));
    }

    /**
     * @Route("/modifier/{nomProduit}", name="modifier")
     */
    public function modifier(RepertoirRepository $repertoirRepository, String $nomProduit, ProduitBDD $produitBDD){

        $info = $produitBDD->info($nomProduit) ;
        $listeColonne = [];
        foreach ($info as $coloneInfo){
            if ($coloneInfo['Field']=='idEtat') { $listeColonne['Statut'] = "varchar(30)"; }
            elseif ($coloneInfo['Field']!='id' and $coloneInfo['Field']!='vip') { $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ; }
        }

        $listeProduits = $repertoirRepository->findAll();
        return $this->render("adminProduits/modifier.html.twig", compact('listeProduits','nomProduit','listeColonne'));
    }

    /**
     * @Route("/modifier/AjouterCharactéristique/{nomProduit}", name="modifier_AjoutChar")
     */
    public function AjoutChar(String $nomProduit, ProduitBDD $produitBDD, Request $request){

        try {
            $nomChara = '' ;
            foreach (explode(' ', $request->get('nomChara')) as $mot)
            { $nomChara .= ucfirst($mot) ; }

            $unite = $request->get('unite');

            $info = $produitBDD->info($nomProduit);
            $listeColonne = [];
            foreach ($info as $coloneInfo){
                $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ;
            }

            if (! array_key_exists($nomChara, $listeColonne)) {

                $produitBDD->addColone($nomProduit,$nomChara,$unite);

                $this->addFlash('success','La caractéritique '.$nomChara.' a bien été ajouté');

            } else {

                $this->addFlash('error','La caractéritique '.$nomChara.' existe déja !');
            }

        } catch (\Exception $e) {
            $this->addFlash('error','La caractéritique '.$nomChara." n'a pas pue etre ajouter");
        }

        return $this->redirectToRoute('adminProduit_modifier',compact('nomProduit'));
    }

    /**
     * @Route("/modifier/SupprimerCharactéristique/{nomProduit}/{nomChara}", name="modifier_SupprimerChar")
     */
    public function SupprimerChar(String $nomProduit,String $nomChara, ProduitBDD $produitBDD, Request $request){

        try {

            $produitBDD->supprColonne($nomProduit,$nomChara);

            $this->addFlash('success','La caractéritique '.$nomChara.' a bien été supprimé');

        } catch (\Exception $e) {
            $this->addFlash('error','La caractéritique '.$nomChara." n'a pas pue etre ajouter");
        }

        return $this->redirectToRoute('adminProduit_modifier',compact('nomProduit'));
    }

}
