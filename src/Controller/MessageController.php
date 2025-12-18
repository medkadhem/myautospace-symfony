<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Announcement;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/messages')]
#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{
    #[Route('', name: 'app_messages', methods: ['GET'])]
    public function index(MessageRepository $messageRepo): Response
    {
        $user = $this->getUser();
        $messages = $messageRepo->findUserConversations($user);
        
        // Group messages by conversation
        $conversations = [];
        foreach ($messages as $message) {
            $otherUser = $message->getSender() === $user 
                ? $message->getReceiver() 
                : $message->getSender();
            $announcementId = $message->getAnnouncement()->getId();
            $key = $otherUser->getId() . '_' . $announcementId;
            
            if (!isset($conversations[$key])) {
                $conversations[$key] = [
                    'otherUser' => $otherUser,
                    'announcement' => $message->getAnnouncement(),
                    'lastMessage' => $message,
                    'messages' => []
                ];
            }
            $conversations[$key]['messages'][] = $message;
            if ($message->getCreatedAt() > $conversations[$key]['lastMessage']->getCreatedAt()) {
                $conversations[$key]['lastMessage'] = $message;
            }
        }
        
        // Sort by last message date
        usort($conversations, function($a, $b) {
            return $b['lastMessage']->getCreatedAt() <=> $a['lastMessage']->getCreatedAt();
        });

        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    #[Route('/conversation/{announcementId}/{userId}', name: 'app_messages_conversation', methods: ['GET', 'POST'])]
    public function conversation(
        int $announcementId,
        int $userId,
        Request $request,
        EntityManagerInterface $em,
        MessageRepository $messageRepo
    ): Response {
        $currentUser = $this->getUser();
        $announcement = $em->getRepository(Announcement::class)->find($announcementId);
        $otherUser = $em->getRepository(\App\Entity\User::class)->find($userId);

        if (!$announcement || !$otherUser) {
            throw $this->createNotFoundException();
        }

        // Get conversation
        $messages = $messageRepo->findConversation($currentUser, $otherUser, $announcement);
        
        // Mark messages as read
        foreach ($messages as $message) {
            if ($message->getReceiver() === $currentUser && !$message->isRead()) {
                $message->setIsRead(true);
            }
        }
        $em->flush();

        // Handle sending message
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');
            if ($content) {
                $message = new Message();
                $message->setSender($currentUser);
                $message->setReceiver($otherUser);
                $message->setAnnouncement($announcement);
                $message->setContent($content);
                
                $em->persist($message);
                $em->flush();

                $this->addFlash('success', 'Message sent successfully!');
                return $this->redirectToRoute('app_messages_conversation', [
                    'announcementId' => $announcementId,
                    'userId' => $userId
                ]);
            }
        }

        return $this->render('message/conversation.html.twig', [
            'messages' => $messages,
            'otherUser' => $otherUser,
            'announcement' => $announcement,
        ]);
    }

    #[Route('/send/{announcementId}', name: 'app_messages_send', methods: ['POST'])]
    public function send(
        int $announcementId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $announcement = $em->getRepository(Announcement::class)->find($announcementId);
        
        if (!$announcement) {
            throw $this->createNotFoundException();
        }

        $content = $request->request->get('message');
        if ($content) {
            $message = new Message();
            $message->setSender($this->getUser());
            $message->setReceiver($announcement->getVendor());
            $message->setAnnouncement($announcement);
            $message->setContent($content);
            
            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Message sent to seller!');
        }

        return $this->redirectToRoute('app_announcement_show', ['id' => $announcementId]);
    }
}
