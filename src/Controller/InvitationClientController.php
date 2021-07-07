<?php

namespace App\Controller;

use App\Entity\InvitationClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InvitationClientController extends AbstractController {

    /**
     * @Route("/admin/invitationClient/", name="invitationClient_accueil")
     */
    public function accueil(){
        return $this->render("invitationClient/accueil.html.twig") ;
    }

    /**
     * @Route("/admin/invitationClient/ajouter", name="invitationClient_ajouter")
     */
    public function ajouter(Request $request,EntityManagerInterface $entityManager, \Swift_Mailer $mailer){

        $email = $request->get("email") ;

        if($email) {

            $invitationClient = new InvitationClient($email) ;
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
                        'email/invitationVIP.html.twig',compact('invitationClient')
                    ),
                    'text/html'
                )
            ;
            $mailer->send($message);

            $this->addFlash('sucess','Une invitation a '.$email.' été envoyer ! ');

        }

        return $this->redirectToRoute("invitationClient_accueil") ;
    }

}