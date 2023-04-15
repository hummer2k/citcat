<?php

namespace App\Controller\View;

use App\Repository\FriendRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Friends extends AbstractController
{
    /**
     * @Route("/view/friends")
     * @param FriendRepository $friendRepository
     * @return Response
     */
    public function execute(FriendRepository $friendRepository)
    {
        return $this->render('view/friends/table.html.twig', [
            'friends' => $friendRepository->findBy([], ['name' => 'ASC'])
        ]);
    }
}
