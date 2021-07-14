<?php

namespace App\Controller;

use App\Entity\InvitationClient;
use App\Repository\InvitationClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/invitationClient", name="invitationClient_")
 */
class InvitationClientController extends AbstractController {

    /**
     * @Route("/", name="accueil")
     */
    public function accueil(InvitationClientRepository $invitationClientRepository){

        $invitationClients = $invitationClientRepository->findAll();

        return $this->render("invitationClient/accueil.html.twig", compact('invitationClients')) ;
    }

    /**
     * @Route("/ajouter", name="ajouter")
     */
    public function ajouter(Request $request,UserRepository $userRepository,EntityManagerInterface $entityManager, \Swift_Mailer $mailer){


        $email = $request->get("email") ;
        $user = $userRepository->findOneBy(['email'=>$email]) ;

        if ($email) {

            if($user){

                if (in_array('ROLE_CLIENT_VIP',$user->getRoles())) {
                    $this->addFlash('error','le compte client corespondant à cette adresse Email est déja VIP !');
                } else {
                    $user->setRoles(['ROLE_CLIENT_VIP']);
                    $entityManager->flush();

                    $message = (new \Swift_Message('Cuir Nomade : Promue Client VIP !'))
                        // On attribue l'expéditeur
                        ->setFrom("cuirsnomades@gmail.com")
                        // On attribue le destinataire
                        ->setTo($email)
                        // On crée le texte avec la vue
                        ->setBody(
                            $this->renderView(
                                'email/promotionClientVIP.html.twig'
                            ),
                            'text/html'
                        );
                    $mailer->send($message);

                    $this->addFlash('success','le compte client corespondant à cette adresse Email a été promu VIP, un mail pour lui annoncer la nouvelle a été envoyer !');
                }

            } else {

            $invitationClient = new InvitationClient($email);
            $entityManager->persist($invitationClient);
            $entityManager->flush();

            $message = (new \Swift_Message('Cuir Nomade : Invitation Client VIP !'))
                // On attribue l'expéditeur
                ->setFrom("cuirsnomades@gmail.com")
                // On attribue le destinataire
                ->setTo($email)
                // On crée le texte avec la vue
                ->setBody(
                    $this->renderView(
                        'email/invitationVIP.html.twig', compact('invitationClient')
                    ),
                    'text/html'
                );
            $mailer->send($message);

            $this->addFlash('success', 'Une invitation a ' . $email . ' été envoyer ! ');
            }
        }

        return $this->redirectToRoute("invitationClient_accueil") ;
    }

    /**
     * @Route("/renvoyer", name="renvoyer")
     */
    public function renvoyer(EntityManagerInterface $entityManager,UserRepository $userRepository, InvitationClientRepository $invitationClientRepository, Request $request, \Swift_Mailer $mailer){

        $invitation = $invitationClientRepository->find($request->get("invitationID"));
        $email = $invitation->getEmail();
        $entityManager->remove($invitation);
        $entityManager->flush();

        $user = $userRepository->findOneBy(['email'=>$email]) ;
        if($user){

            if (in_array('ROLE_CLIENT_VIP',$user->getRoles())) {
                $this->addFlash('error','le compte client corespondant à cette adresse Email est déja VIP !');
            } else {
                $user->setRoles(['ROLE_CLIENT_VIP']);
                $entityManager->flush();

                $message = (new \Swift_Message('Cuir Nomade : Promue Client VIP !'))
                    // On attribue l'expéditeur
                    ->setFrom("cuirsnomades@gmail.com")
                    // On attribue le destinataire
                    ->setTo($email)
                    // On crée le texte avec la vue
                    ->setBody(
                        $this->renderView(
                            'email/promotionClientVIP.html.twig'
                        ),
                        'text/html'
                    );
                $mailer->send($message);

                $this->addFlash('success','le compte client corespondant à cette adresse Email a été promu VIP, un mail pour lui annoncer la nouvelle a été envoyer !');
            }

        } else {
            $invitationClient = new InvitationClient($email);
            $entityManager->persist($invitationClient);
            $entityManager->flush();

            $message = (new \Swift_Message('Cuir Nomade : Invitation Client VIP !'))
                // On attribue l'expéditeur
                ->setFrom("cuirsnomades@gmail.com")
                // On attribue le destinataire
                ->setTo($email)
                // On crée le texte avec la vue
                ->setBody(
                    $this->renderView(
                        'email/invitationVIP.html.twig', compact('invitationClient')
                    ),
                    'text/html'
                );
            $mailer->send($message);

            $this->addFlash('success', 'Une invitation a ' . $email . ' été envoyer ! ');
        }

        return $this->redirectToRoute('invitationClient_accueil');
    }

    /**
     * @Route("/supprimer", name="supprimer")
     */
    public function supprimer(EntityManagerInterface $entityManager, InvitationClientRepository $invitationClientRepository, Request $request){

        $invitation = $invitationClientRepository->find($request->get("invitationID"));
        $entityManager->remove($invitation);
        $entityManager->flush();

        return $this->redirectToRoute('invitationClient_accueil');
    }


}