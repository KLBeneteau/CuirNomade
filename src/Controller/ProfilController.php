<?php

namespace App\Controller;

use App\Form\ModificationProfilType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/monCompte", name="profil_")
 */

class ProfilController extends AbstractController {

    /**
     * @Route("", name="accueil")
     */
    public function accueil(){
        return $this->render("profil/accueil.html.twig") ;
    }

    /**
     * @Route("/consulter", name="consulter")
     */
    public function consulter(UserRepository $userRepository){

        $monProfil = $userRepository->find($this->getUser()) ;

        return $this->render("profil/consulter.html.twig", compact('monProfil')) ;
    }

    /**
     * @Route("/modifier", name="modifier")
     */
    public function modifier(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder)
    {

        $monProfil = $userRepository->find($this->getUser()) ;

        $profilForm =$this-> createForm(ModificationProfilType::class, $monProfil) ;

        $profilForm->handleRequest($request);

        if ($profilForm->isSubmitted() && $profilForm->isValid()) {

            $monProfil->setPassword(
                $passwordEncoder->encodePassword(
                    $monProfil,
                    $profilForm->get('plainPassword')->getData()
                )
            );

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a bien été modifier');

            $this->redirectToRoute('profil_consulter');
        }

        return $this->render("profil/modifier.html.twig", [
                'profilForm' => $profilForm->createView(),
                'monProfil' => $monProfil ] ) ;
    }
}