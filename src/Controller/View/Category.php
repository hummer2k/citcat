<?php

namespace App\Controller\View;

use App\Repository\CategoryRepository;
use App\Repository\TweetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Category extends AbstractController
{
    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(TweetRepository $tweetRepository, CategoryRepository $categoryRepository)
    {
        $this->tweetRepository = $tweetRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route(path="/")
     * @param Request $request
     * @return Response
     */
    public function execute(Request $request)
    {
        $categoryId = $request->get('category_id');
        $categories = $this->categoryRepository->findBy(['parent' => null], ['name' => 'ASC']);

        if (!$categoryId) {
            return $this->render('view/categories.html.twig', ['categories' => $categories]);
        }

        /** @var \App\Entity\Category $category */
        $category   = $this->categoryRepository->find($categoryId);

        if (!$category) {
            $this->addFlash('danger', 'Datensatz nicht gefunden.');
            return $this->redirectToRoute('app_view_category_execute');
        }

        $tweets = $this->tweetRepository->findByCategory($category);

        return $this->render('view/category.html.twig', [
            'categories' => $categories,
            'currentCategory' => $category,
            'tweets' => $tweets
        ]);
    }
}
