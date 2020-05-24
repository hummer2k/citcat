<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Tweets
 *
 * @ORM\Table(name="tweets")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Tweet implements ArraySerializableInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=32, nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_at", type="datetime", nullable=true)
     */
    private $insertedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="string", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="screen_name", type="string", nullable=true)
     */
    private $screenName;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_retweet", type="boolean", nullable=false)
     */
    private $isRetweet = false;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=16777215, nullable=false)
     */
    private $content;

    /**
     * @var array|null
     * @ORM\Column(name="raw_data", type="json")
     */
    private $rawData;

    /**
     * @var boolean
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $isDeleted = false;

    /**
     * @var Category[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Category")
     * @ORM\JoinTable(name="tweet_category")
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        if ($this->getInsertedAt() === null) {
            $this->setInsertedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getInsertedAt(): ?\DateTime
    {
        return $this->insertedAt;
    }

    /**
     * @param \DateTime $insertedAt
     */
    public function setInsertedAt(\DateTime $insertedAt): void
    {
        $this->insertedAt = $insertedAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getScreenName(): string
    {
        return $this->screenName;
    }

    /**
     * @param string $screenName
     */
    public function setScreenName(string $screenName): void
    {
        $this->screenName = $screenName;
    }

    /**
     * @return bool
     */
    public function isRetweet(): bool
    {
        return $this->isRetweet;
    }

    /**
     * @param bool $isRetweet
     */
    public function setIsRetweet(bool $isRetweet): void
    {
        $this->isRetweet = $isRetweet;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string|null
     */
    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * @param array|null $rawData
     */
    public function setRawData(?array $rawData): void
    {
        $this->rawData = $rawData;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param array $tweetData
     * @return void
     */
    public function exchangeArray(array $tweetData)
    {
        $this->setId($tweetData['id']);
        $this->setCreatedAt(new \DateTime($tweetData['created_at']));
        $this->setUserId($tweetData['user']['id']);
        $this->setScreenName($tweetData['user']['screen_name']);
        $isRetweet = isset($tweetData['retweeted_status']['full_text']);
        $this->setIsRetweet($isRetweet);
        $this->setContent($tweetData['full_text']);
        $this->setRawData($tweetData);
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return (array) $this->rawData;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     */
    public function setIsDeleted(bool $isDeleted): void
    {
        $this->isDeleted = $isDeleted;
    }

    public function getUrl()
    {
        return 'https://twitter.com/' . $this->getScreenName() . '/status/' . $this->getId();
    }

    /**
     * @return Category[]
     */
    public function getCategories(): iterable
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }

    /**
     * @param Category[] $categories
     */
    public function setCategories(iterable $categories): void
    {
        foreach ($categories as $category) {
            $this->categories->add($category);
        }
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        if (null === $this->updatedAt) {
            return $this->insertedAt;
        }
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
