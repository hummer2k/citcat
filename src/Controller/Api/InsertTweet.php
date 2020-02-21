<?php

namespace App\Controller\Api;

use App\Entity\Tweet;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Hydrator\HydratorInterface;

class InsertTweet extends AbstractController
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
     * InsertTweet constructor.
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
     * @Route("/api/insert-tweet")
     */
    public function execute(Request $request)
    {
        try {

            $data = $request->request->all();
            $data['time'] = (new \DateTime())->setTimestamp($data['time']);

            /** @var Tweet $tweet */
            $tweet = $this->hydrator->hydrate(
                $data,
                new Tweet()
            );

            $this->objectManager->persist($tweet);
            $this->objectManager->flush();
            $response = $this->json(['success' => $tweet->getId()]);
        } catch (\Throwable $e) {
            $response = $this->json(['msg' => $e->getMessage()]);
        }

        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }
}
