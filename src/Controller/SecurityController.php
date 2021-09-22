<?php

namespace App\Controller;

use App\Entity\ValidatorChangementMDP;
use App\Form\ChangementMdpFormType;
use App\Repository\ValidatorChangementMDPRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/motDePasseOublier", name="app_motDePasseOublier")
     */
    public function motDePasseOublier(Request $request, UserRepository $userRepository, \Swift_Mailer $mailer, EntityManagerInterface $entityManager)
    {

        if ($request->get('email')){
            $user = $userRepository->findOneBy(['email'=>$request->get('email')]) ;
            if (!is_null($user)) {

                $validator = new ValidatorChangementMDP($user) ;
                $entityManager->persist($validator);
                $entityManager->flush();

                $message = (new \Swift_Message('Les Cuirs Nomades : changement mot de passe'))
                    // On attribue l'expéditeur
                    ->setFrom("cuirsnomades@gmail.com")
                    // On attribue le destinataire
                    ->setTo($user->getEmail())
                    // On crée le texte avec la vue
                    ->setBody(
                        $this->renderView(
                            'email/modificationMDP.html.twig',compact('user','validator')
                        ),
                        'text/html'
                    )
                ;
                $mailer->send($message);

                $this->addFlash('success','Un email de reinitialisation de mot de passe à été envoyé');
            } else {
                $this->addFlash('error','Aucun compte avec cette email existe ! ');
            }
        }

        return $this->render('security/motDePasseOublier.html.twig') ;
    }

    /**
     * @Route("/changerMotDePasse/{idCode}/{code}", name="app_changerMotDePasse")
     */
    public function changerMotDePasse(int $idCode, String $code,
                                      ValidatorChangementMDPRepository $validatorChangementMDPRepository,
                                      EntityManagerInterface $entityManager,
                                      Request $request,
                                      UserPasswordEncoderInterface $passwordEncoder){

        $validatorCode = $validatorChangementMDPRepository->find($idCode);
        if ($validatorCode && $validatorCode->getCode() == $code ) {

            $user = $validatorCode->getCompte() ;

            $form = $this->createForm(ChangementMdpFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                if ($form->get('mdp')->getData() == $form->get('confirmationMdp')->getData()) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $form->get('mdp')->getData()
                        )
                    );

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($validatorCode);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre mot de passe a bien été modifié ');

                    return $this->redirectToRoute('app_login');

                } else {
                    $this->addFlash('error', 'Le mot de passe et ça confirmation ne sont pas identique ');
                }
            }

            return $this->render("security/changerMotDePasse.html.twig", [
                'changerMDPForm' => $form->createView(),
            ]) ;

        } else {

            return $this->render("main/accueil.html.twig") ;
        }
    }
}
