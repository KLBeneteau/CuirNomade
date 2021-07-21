<?php

namespace App\Controller;

use App\Entity\Repertoir;
use App\Repository\RepertoirRepository;
use App\Service\Connexion;
use App\Service\CreationProduit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/produit", name="adminProduit_")
 */
class AdminProduitsController extends AbstractController {

    /**
     * @Route("/", name="accueil")
     */
    public function accueil(RepertoirRepository $repertoirRepository){

        $listeProduits = $repertoirRepository->findAll();

        return $this->render("adminProduits/accueil.html.twig", compact('listeProduits')) ;
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
            $isVIP = 1;
        } else {
            $isVIP = 0;
        }
        //Si le formulaire est envoyer
        if ($nomProduit) {
            try {
                $pdo = $connexion->createConnexion();

                file_put_contents('../src/Entity/'.$nomProduit.'.php', $creationProduit->getEntityPattern($nomProduit,$isVIP));
                file_put_contents('../src/Repository/'.$nomProduit.'Repository.php', $creationProduit->getRepositoryPattern($nomProduit));

                $query = 'CREATE TABLE '.$nomProduit.'
                             ( id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
                             nom VARCHAR(50) NOT NULL UNIQUE,
                             prix INT NOT NULL,
                             description VARCHAR(300),
                             nb_stock INT NOT NULL,
                             is_vip tinyint(1) NOT NULL DEFAULT '.$isVIP.'
                             ) ' ;

                $pdo->exec($query);

                $this->addFlash("success","le produit $nomProduit été créer");

                $newRepertoir = new Repertoir($nomProduit);
                $entityManager->persist($newRepertoir);
                $entityManager->flush();

             } catch (\Exception $e) {
                $this->addFlash("error","le produit $nomProduit n'a pas pu etre créé");
            }

        }

        $listeProduits = $repertoirRepository->findAll();

        return $this->render("adminProduits/creer.html.twig", compact('listeProduits')) ;
    }

}
