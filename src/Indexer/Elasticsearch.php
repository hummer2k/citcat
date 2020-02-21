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

    public function reindex(OutputInterface $output)
    {
        $criteria = function (QueryBuilder $queryBuilder) {
            $queryBuilder->where('t.isDeleted = 0');
        };
        $tweets = $this->tweetRepository->getTweetIterator($criteria);

        $count = $this->tweetRepository->count(['isDeleted' => 0]);

        $bulkSize = 500;
        $i = 1;

        $indexName = 'tweets_' . date('Y_m_d__H_i_s');
        $index = $this->client->getIndex($indexName);

        $index->create();

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
            /** @var Tweet $tweet */
            $tweet = $tweetRow[0];
            $rawData = $tweet->getRawData();
            $documents[]  = new Document($tweet->getId(), $rawData);

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

        $output->writeln('Refreshing index...');
        $index->addAlias(static::INDEX_ALIAS, true);
        $index->refresh();
    }
}
