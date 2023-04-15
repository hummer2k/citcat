<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Category
 * @package App\Entity
 * @ORM\Table(name="categories")
 * @ORM\Entity
 */
class Category
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var Category|null
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_category_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent(Category $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection
     */
    public function getChildren(): Collection
    {
        $criteria = Criteria::create()->orderBy(['name' => 'ASC']);
        return $this->children->matching($criteria);
    }

    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    public function addChild(Category $category): void
    {
        if (!$this->children->contains($category)) {
            $category->setParent($this);
            $this->children->add($category);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
