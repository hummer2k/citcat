<?php

namespace App\Controller\Tweet;

use App\Entity\Category;
use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class Manage
 * @package App\Controller
 * @Route("/tweet/manage")
 */
class Manage extends AbstractController
{
    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var ObjectManager|EntityManagerInterface
     */
    private $objectManager;

    public function __construct(TweetRepository $tweetRepository, ObjectManager $objectManager)
    {
        $this->tweetRepository = $tweetRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param int $tweetId
     * @param int $categoryId
     * @throws EntityNotFoundException
     */
    private function addTweetToCategory(int $tweetId, int $categoryId)
    {
        /** @var Tweet $tweet */
        $tweet = $this->tweetRepository->find($tweetId);
        $category = $this->objectManager->find(Category::class, $categoryId);

        if (!$tweet) {
            throw new EntityNotFoundException(sprintf('Tweet "%s" nicht gefunden.', $tweetId));
        }
        if (!$category) {
            throw new EntityNotFoundException(sprintf('Datensatz "%s" nicht gefunden.', $categoryId));
        }

        $tweet->addCategory($category);
        $this->objectManager->flush();
    }

    private function deleteTweetFromCategory(int $tweetId, int $categoryId)
    {
        /** @var Tweet $tweet */
        $tweet = $this->tweetRepository->find($tweetId);
        $categories = $tweet->getCategories();
        $category = $this->objectManager->getReference(Category::class, $categoryId);
        $categories->removeElement($category);
        $this->objectManager->flush();
    }

    private function moveTweetToCategory(int $tweetId, int $fromId, int $toId)
    {
        $this->addTweetToCategory($tweetId, $toId);
        $this->deleteTweetFromCategory($tweetId, $fromId);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/move-to-category", name="tweet_manage_move_to_category")
     */
    public function moveToCategory(Request $request)
    {
        try {
            $tweetId = $request->get('tweet_id');
            $fromId  = $request->get('from_id');
            $toId    = $request->get('to_id');

            $this->moveTweetToCategory($tweetId, $fromId, $toId);

            $response = [
                'success' => true,
            ];
        } catch (\Throwable $e) {
            $response = [
                'error' => $e->getMessage()
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/add-to-category", name="tweet_manage_add_to_category")
     */
    public function addToCategory(Request $request)
    {
        try {
            $tweetId = $request->get('tweet_id');
            $categoryId = $request->get('category_id');

            $this->addTweetToCategory($tweetId, $categoryId);
            $category = $this->objectManager->find(Category::class, $categoryId);

            $response = [
                'success' => true,
                'message' => sprintf('Tweet "%s" zu Datensatz "%s" hinzugefügt', $tweetId, $category->getName())
            ];
        } catch (\Throwable $e) {
            $response = [
                'error' => $e->getMessage()
            ];
        }
        return new JsonResponse($response);
    }


    /**
     * @param int $tweetId
     * @param int $categoryId
     * @return JsonResponse
     * @Route("/delete-from-category/{tweetId}/{categoryId}", name="tweet_manage_delete_from_category")
     */
    public function deleteFromCategory(int $tweetId, int $categoryId)
    {
        try {
            $this->deleteTweetFromCategory($tweetId, $categoryId);
            $response = [
                'success' => true
            ];
        } catch (\Throwable $e) {
            $response = [
                'error' => $e->getMessage()
            ];
        }

        return new JsonResponse($response);
    }
}
