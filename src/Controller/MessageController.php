<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @Route("/chat/{id}", name="chat_user")
     * @param User $recipient
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function getChat(User $recipient, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if(! $recipient === $currentUser){
            $chat = $entityManager->getRepository('App:Message')->getMessagesBetweenUsers($currentUser, $recipient);

            foreach($chat as $message){
                if($currentUser === $message->getRecipient()){
                    $message->setReadByRecipient(true);
                    $entityManager->persist($message);
                }
            }
            $entityManager->flush();


            return $this->render('chat/chat.html.twig', [
                'chat' => $chat,
                'currentUser' => $currentUser,
                'recipient' => $recipient
            ]);
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Exception
     * @Route("/message/create", name="message_create_refresh")
     */

    public function createAndRefresh(Request $request, EntityManagerInterface $entityManager): Response
    {
        $recipientID = $request->request->get('recipientID', null);
        $msgText = $request->request->get('msgText', null);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $recipient = $entityManager->getRepository('App:User')->find($recipientID);

        $message = new Message();
        $message->setRecipient($recipient);
        $message->setMessageText($msgText);
        $message->setSender($currentUser);
        $message->setSentAt(new \DateTime());
        $entityManager->persist($message);
        $entityManager->flush();

        return new JsonResponse(['msg' => "Message sent" ], 200);
    }
}
