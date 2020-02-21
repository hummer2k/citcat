<?php
/**
 * WordCount
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Controller\View;


use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WordCount extends AbstractController
{
    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * WordCount constructor.
     * @param TweetRepository $tweetRepository
     */
    public function __construct(TweetRepository $tweetRepository)
    {
        $this->tweetRepository = $tweetRepository;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/view/word-count")
     */
    public function execute()
    {
        $tweets = $this->tweetRepository->findAll();
        $wordCount = [];

        $stopWords = file(__DIR__ . '/stopwords.txt');
        foreach ($stopWords as &$stopWord) {
            $stopWord = trim($stopWord);
        }

        /** @var Tweet $tweet */
        foreach ($tweets as $tweet) {
            $rawContent = $tweet->getRawData();
            $content = $tweet->isRetweet() ? $rawContent['retweeted_status']['full_text'] : $rawContent['full_text'];
            $words = explode(' ', $content);
            foreach ($words as $word) {
                $word = trim(strtolower($word), "\t\n\r\0\x0B,.&#:„;");

                if (in_array($word, $stopWords) || !$word || strlen($word) <= 1 || is_numeric($word)) {
                    continue;
                }

                if (!isset($wordCount[$word])) {
                    $wordCount[$word] = 1;
                } else {
                    $wordCount[$word]++;
                }
            }
        }

        asort($wordCount);
        $wordCount = array_reverse($wordCount);
        $wordCount = array_filter($wordCount, function ($value) {
            return $value >= 28;
        });

        $wordCloud = [];
        foreach ($wordCount as $word => $count) {
            $wordCloud[] = [
                'word' => $word,
                'weight' => $count
            ];
        }

        $wordCloudJson = \json_encode($wordCloud);
        return $this->render('view/word-count.html.twig', [
            'wordCount' => $wordCount,
            'wordCloud' => $wordCloudJson
        ]);
    }
}
