<?php

namespace App\Controller;

use App\Entity\Repertoir;
use App\Repository\BaboucheRepository;
use App\Repository\RepertoirRepository;
use App\Service\Connexion;
use App\Service\CreationProduit;
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
                          Connexion $connexion,
                          Request $request,
                          CreationProduit $creationProduit,
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
            try {
                $pdo = $connexion->createConnexion();

                //file_put_contents('../src/Entity/'.$nomProduit.'.php', $creationProduit->getEntityPattern($nomProduit,$VIP));
                //file_put_contents('../src/Repository/'.$nomProduit.'Repository.php', $creationProduit->getRepositoryPattern($nomProduit));

                $query = 'CREATE TABLE '.$nomProduit.'
                             ( id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
                             Modele VARCHAR(50) NOT NULL,
                             Prix INT NOT NULL,
                             Description VARCHAR(300),
                             Stock INT NOT NULL,
                             vip tinyint(1) NOT NULL DEFAULT '.$VIP.',
                             Couleur VARCHAR(50) NOT NULL,
                             idEtat INT NOT NULL,
                            CONSTRAINT fk_IdEtat FOREIGN KEY (idEtat) REFERENCES etat(id)) ' ;

                $pdo->exec($query);

                $this->addFlash("success","le produit $nomProduit été créer");

                $newRepertoir = new Repertoir($nomProduit);
                $entityManager->persist($newRepertoir);
                $entityManager->flush();

                return $this->redirectToRoute('adminProduit_modifier',['nomProduit'=>$nomProduit]);

             } catch (\Exception $e) {
                $this->addFlash("error","Le produit $nomProduit n'a pas pu etre créé");
            }
        }

        $listeProduits = $repertoirRepository->findAll();

        return $this->render("adminProduits/creer.html.twig", compact('listeProduits')) ;
    }

    /**
     * @Route("/affichage/{nomProduit}", name="affichage")
     */
    public function affichage(RepertoirRepository $repertoirRepository,
                              Connexion $connexion,
                              string $nomProduit){

        $pdo = $connexion->createConnexion();

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

        /*
        $nomRepository = $nomProduit.'Repository' ;
        $listeArticle = $$nomRepository->findAll() ;
        */

        //Récupère tout se qui est enregistrer dans la table
        $query = "SELECT * FROM ".$nomProduit.'
                  LEFT JOIN Etat ON Etat.id = '.$nomProduit.'.idEtat';
        $prep = $pdo->prepare($query);
        $prep->execute();;
        $listeArticle = $prep->fetchAll();

        $listeProduits = $repertoirRepository->findAll();

        return $this->render("adminProduits/affichage.html.twig", compact('listeProduits','listeColonne', 'listeArticle','nomProduit','isVIP')) ;
    }

    /**
     * @Route("/supprimer/{nomProduit}", name="supprimer")
     */
    public function supprimer(Request $request,
                              RepertoirRepository $repertoirRepository,
                              String $nomProduit, Connexion $connexion,
                              EntityManagerInterface $entityManager){

        if ($request->get('suppression')) {
            try {
                //Supprime les fichier
                //@unlink( '../src/Entity/'.$nomProduit.'.php' ) ;
                //@unlink( '../src/Repository/'.$nomProduit.'Repository.php' ) ;

                //Supprime l'enregistrement dans Repertoir
                $entityManager->remove($repertoirRepository->findOneBy(['nom'=>$nomProduit]));
                $entityManager->flush();

                //Supprime la BDD
                $pdo = $connexion->createConnexion();
                $query = 'DROP TABLE '.$nomProduit ;
                $pdo->exec($query);

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
    public function modifier(RepertoirRepository $repertoirRepository, String $nomProduit, Connexion $connexion){

        $pdo = $connexion->createConnexion();

        //récupère tout les nom de colone de la table
        $query = "DESCRIBE ".$nomProduit;
        $prep = $pdo->prepare($query);
        $prep->execute();
        $prep->fetch(); //on ne veut jamais l'id
        $resultat = $prep->fetchAll();

        $listeColonne = [];
        foreach ($resultat as $coloneInfo){
            if ($coloneInfo['Field']=='vip') { $isVIP = $coloneInfo['Default'] ; }
            elseif ($coloneInfo['Field']=='idEtat') { $listeColonne['Statut'] = "varchar(30)"; }
            elseif ($coloneInfo['Field']!='id') { $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ; }
        }

        $listeProduits = $repertoirRepository->findAll();

        return $this->render("adminProduits/modifier.html.twig", compact('listeProduits','nomProduit','listeColonne'));
    }

    /**
     * @Route("/modifier/AjouterCharactéristique/{nomProduit}", name="modifier_AjoutChar")
     */
    public function AjoutChar(String $nomProduit, Connexion $connexion, Request $request){

        try {
            $nomChara = '' ;
            foreach (explode(' ', $request->get('nomChara')) as $mot)
            { $nomChara .= ucfirst($mot) ; }

            $unite = $request->get('unite');

            $pdo = $connexion->createConnexion();

            //récupère tout les nom de colone de la table
            $query = "DESCRIBE ".$nomProduit;
            $prep = $pdo->prepare($query);
            $prep->execute();
            $resultat = $prep->fetchAll();

            $listeColonne = [];
            foreach ($resultat as $coloneInfo){
                $listeColonne[$coloneInfo['Field']] = $coloneInfo['Type'] ;
            }

            if (! array_key_exists($nomChara, $listeColonne)) {

                $query = "ALTER TABLE ".$nomProduit."
                     ADD ".$nomChara." ".$unite ;
                if ($unite = 'varchar') { $query.='(255)' ; }

                $pdo->exec($query);

                $this->addFlash('success','La caractéritique '.$nomChara.' a bien été ajouté');

            } else {

                $this->addFlash('error','La caractéritique '.$nomChara.' existe déja !');
            }

        } catch (\Exception $e) {
            $this->addFlash('error','La caractéritique '.$nomChara." n'a pas pue etre ajouter");
        }

        return $this->redirectToRoute('adminProduit_modifier',['nomProduit'=>$nomProduit]);
    }

    /**
     * @Route("/modifier/SupprimerCharactéristique/{nomProduit}/{nomChara}", name="modifier_SupprimerChar")
     */
    public function SupprimerChar(String $nomProduit,String $nomChara, Connexion $connexion, Request $request){

        try {
            $pdo = $connexion->createConnexion();

            $query = "ALTER TABLE ".$nomProduit."
                        DROP COLUMN ".$nomChara ;

            $pdo->exec($query);

            $this->addFlash('success','La caractéritique '.$nomChara.' a bien été supprimé');

        } catch (\Exception $e) {
            $this->addFlash('error','La caractéritique '.$nomChara." n'a pas pue etre ajouter");
        }

        return $this->redirectToRoute('adminProduit_modifier',['nomProduit'=>$nomProduit]);
    }

}
