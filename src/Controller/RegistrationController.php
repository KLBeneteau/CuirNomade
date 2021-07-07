<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ValidatorMail;
use App\Form\RegistrationFormType;
use App\Repository\InvitationClientRepository;
use App\Repository\ValidatorMailRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Scalar\String_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/CeerUnCompte", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer): Response
    {
        $user = new User();
        $user->setRoles(["ROLE_CLIENT"]);
        $user->setEmailValider(false);
        $form = $this->createForm(RegistrationFormType::class, $user);
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
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a bien été enregistrer');

                $validator = new ValidatorMail($user) ;
                $entityManager->persist($validator);
                $entityManager->flush();

                $message = (new \Swift_Message('Cuir Nomade : Validation Email'))
                    // On attribue l'expéditeur
                    ->setFrom("cuirsnomades@gmail.com")
                    // On attribue le destinataire
                    ->setTo($user->getEmail())
                    // On crée le texte avec la vue
                    ->setBody(
                        $this->renderView(
                            'email/validationEmail.html.twig',compact('user','validator')
                        ),
                        'text/html'
                    )
                ;
                $mailer->send($message);

                $this->addFlash('success', "Un Email de validation vous a été envoyer de l'adresse : ".$user->getEmail() );

                return $this->redirectToRoute('app_login');

            } else {
                $this->addFlash('error', 'Le mot de passe et ça confirmation ne sont pas identique ');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/CeerUnCompteVIP/{email}/{code}", name="app_registerVIP")
     */
    public function registerVIP(Request $request,
                                UserPasswordEncoderInterface $passwordEncoder,
                                \Swift_Mailer $mailer,
                                String $email, String $code,
                                InvitationClientRepository $invitationClientRepository): Response
    {

        $invitation = $invitationClientRepository->findOneBy(['email'=>$email]);
        if ($invitation){

            $user = new User();
            $user->setRoles(["ROLE_CLIENT_VIP"]);
            $user->setEmailValider(true);
            $user->setEmail($email);
            $form = $this->createForm(RegistrationFormType::class, $user);
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
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Votre profil a bien été enregistrer');

                    return $this->redirectToRoute('app_login');

                } else {
                    $this->addFlash('error', 'Le mot de passe et ça confirmation ne sont pas identique ');
                }
            }

            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
            ]);

        } else {
            //TODO:erreur404
            return $this->redirectToRoute('main_accueil');
        }



    }

    /**
     * @Route("/validerEmail/{idCode}/{code}", name="app_validerEmail")
     */
    public function validerEmail(int $idCode, String $code, ValidatorMailRepository $validatorMailRepository, EntityManagerInterface $entityManager){

            $validatorCode = $validatorMailRepository->find($idCode);
            if ($validatorCode && $validatorCode->getCode() == $code ) {
                if (!$validatorCode->getCompte()->getEmailValider()) {
                    $validatorCode->getCompte()->setEmailValider(true);
                    $entityManager->remove($validatorCode);
                    $entityManager->flush();
                    $this->addFlash('success', "Votre Email à bien été validé ! " );
                } else {
                    $this->addFlash('error', "Votre mail à déja été validé !" );
                    return $this->redirectToRoute('main_accueil');
                }
            } else {
                //TODO:retourner ver page 404
            }

            return $this->render("main/accueil.html.twig") ;
        }
}
