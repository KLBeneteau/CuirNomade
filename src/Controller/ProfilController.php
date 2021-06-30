<?php

namespace App\Controller;

use App\Form\ModificationProfilType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\IsNull;

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
            dump($monProfil->getPassword()) ;
            dump($passwordEncoder->encodePassword( $monProfil, $profilForm->get('mdp')->getData() )) ;
            if (
                $passwordEncoder->encodePassword( $monProfil, $profilForm->get('mdp')->getData() )
                ==
                $monProfil->getPassword()
            ) {

                $nouveauMdp = $profilForm->get('NouveauMdp')->getData() ;
                dump($nouveauMdp);
                if ($nouveauMdp == $profilForm->get('confirmationMdp')->getData()) {
                    if (!$nouveauMdp) {
                        $monProfil->setPassword(
                            $passwordEncoder->encodePassword(
                                $monProfil, $nouveauMdp
                            )
                        );
                    }
                } else {
                    $this->addFlash('error', 'Confirmation du nouveau mot de passe incorect');
                }

                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a bien été modifier');

                return $this->redirectToRoute('profil_consulter');

            } else {
                $this->addFlash('error', 'Mot de passe incorect');
            }
        }

        return $this->render("profil/modifier.html.twig", [
                'profilForm' => $profilForm->createView(),
                'monProfil' => $monProfil ] ) ;
    }
}