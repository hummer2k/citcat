<?php
/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace App\Indexer;

use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class IndexTweetSubscriber implements EventSubscriber
{
    /**
     * @var Elasticsearch
     */
    private $elasticsearch;

    /**
     * IndexTweetSubscriber constructor.
     * @param Elasticsearch $elasticsearch
     */
    public function __construct(Elasticsearch $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->index($args);
    }

    private function index(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Tweet) {
            $this->elasticsearch->reindexEntity($entity);
        }
    }
}
