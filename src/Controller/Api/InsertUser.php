<?php

namespace App\Controller\Api;

use App\Entity\Friend;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Hydrator\HydratorInterface;

class InsertUser extends AbstractController
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * InsertUser constructor.
     * @param HydratorInterface $hydrator
     * @param ObjectManager $objectManager
     */
    public function __construct(
        HydratorInterface $hydrator,
        ObjectManager $objectManager
    ) {
        $this->hydrator = $hydrator;
        $this->objectManager = $objectManager;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/insert-user")
     */
    public function execute(Request $request)
    {
        $data = $request->request->all();
        $user = $this->hydrator->hydrate(
            $data,
            new Friend()
        );

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $response = $this->json(['success' => true]);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
}
