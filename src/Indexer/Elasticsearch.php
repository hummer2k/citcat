<?php
/**
 * Elasticsearch
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Indexer;


use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index\Settings;
use Elastica\Type\Mapping;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Elasticsearch
{
    const INDEX_ALIAS = 'tweets';

    /**
     * @var TweetRepository
     */
    private $tweetRepository;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(TweetRepository $tweetRepository, Client $client, ObjectManager $objectManager)
    {
        $this->tweetRepository = $tweetRepository;
        $this->client = $client;
        $this->objectManager = $objectManager;
    }

    /**
     * @param Tweet $tweet
     * @return \Elastica\Bulk\ResponseSet
     */
    public function reindexEntity(Tweet $tweet)
    {
        $index = $this->client->getIndex(static::INDEX_ALIAS);
        $document = $this->createDocument($tweet);
        return $index->addDocuments([$document]);
    }

    /**
     * @param Tweet $tweet
     * @return Document
     */
    private function createDocument(Tweet $tweet): Document
    {
        $rawData = $tweet->getRawData();
        $rawData['url'] = sprintf(
            'https://twitter.com/%s/status/%s',
            $rawData['user']['screen_name'],
            $rawData['id_str']
        );
        return new Document($tweet->getId(), $rawData);
    }

    /**
     * @param OutputInterface $output
     * @param iterable|null|Tweet[] $tweets
     */
    public function reindex(OutputInterface $output, $tweets = null)
    {
        $count  = $tweets ? count($tweets) : $this->tweetRepository->count([]);
        $tweets = $tweets ?: $this->tweetRepository->getTweetIterator();

        $bulkSize = 500;
        $i = 1;

        $index = $this->client->getIndex(static::INDEX_ALIAS);

        if (!$index->exists()) {
            $index->create();
        }

        $mappingDefinition = json_decode(file_get_contents(__DIR__ . '/mapping.json'), true);
        $mapping = new Mapping();
        $mapping->setType($index->getType('_doc'));
        $mapping->setProperties($mappingDefinition['properties']);
        $mapping->send();

        $index->close();

        $settingsDefinition = json_decode(file_get_contents(__DIR__ . '/settings.json'), true);
        $settings = new Settings($index);
        $settings->set($settingsDefinition);

        $index->open();

        $documents = [];
        $progress = new ProgressBar($output, $count);
        $progress->display();
        foreach ($tweets as $tweetRow) {
            if (is_array($tweetRow)) {
                /** @var Tweet $tweet */
                $tweet = $tweetRow[0];
            } elseif ($tweetRow instanceof Tweet) {
                $tweet = $tweetRow;
            } else {
                continue;
            }

            $documents[] = $this->createDocument($tweet);

            if ($i % $bulkSize === 0) {
                $index->addDocuments($documents);
                $documents = [];
                $progress->advance($bulkSize);
                $this->objectManager->clear();
            }

            $i++;
        }

        if (count($documents) > 0) {
            $index->addDocuments($documents);
            $progress->advance(count($documents));
        }

        $progress->finish();

        $output->writeln('');
        $output->writeln('Refreshing index...');
        $index->refresh();
    }
}
